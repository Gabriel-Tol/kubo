<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Session\SessionInterface;
use InvalidArgumentException;

class CartService
{
    private SessionInterface $session;

    public function __construct(private readonly BaseConnection $db, ?SessionInterface $session = null)
    {
        $this->session = $session ?? service('session');
    }

    /** @return array{items: list<array<string, mixed>>, subtotal: int} */
    public function get(?int $userId): array
    {
        if ($userId === null) {
            $items = $this->guestItems();
            $hydrated = [];
            $subtotal = 0;
            foreach ($items as $item) {
                $line = $this->variantDetails((int) $item['variant_id']);
                if ($line === null) {
                    continue;
                }
                $line['quantity'] = (int) $item['quantity'];
                $line['note'] = (string) ($item['note'] ?? '');
                $line['line_total'] = (int) $line['price'] * $line['quantity'];
                $subtotal += $line['line_total'];
                $hydrated[] = $line;
            }
            return ['items' => $hydrated, 'subtotal' => $subtotal];
        }

        $cart = $this->accountCart($userId, false);
        if ($cart === null) {
            return ['items' => [], 'subtotal' => 0];
        }
        $items = $this->db->table('cart_items ci')
            ->select('ci.id, ci.variant_id, ci.quantity, ci.note, pv.label AS variant_label, pv.price, pv.stock, p.name AS product_name, p.slug AS product_slug, p.is_active AS product_active, p.archived_at')
            ->join('product_variants pv', 'pv.id = ci.variant_id')
            ->join('products p', 'p.id = pv.product_id')
            ->where('ci.cart_id', $cart['id'])
            ->orderBy('ci.id', 'ASC')
            ->get()->getResultArray();
        $subtotal = 0;
        foreach ($items as &$item) {
            $item['line_total'] = (int) $item['price'] * (int) $item['quantity'];
            $subtotal += $item['line_total'];
        }
        unset($item);
        return ['items' => $items, 'subtotal' => $subtotal];
    }

    public function add(?int $userId, int $variantId, int $quantity, string $note = ''): void
    {
        $variant = $this->variantDetails($variantId);
        $this->assertPurchasable($variant, $quantity, $note);

        if ($userId === null) {
            $items = $this->guestItems();
            $found = false;
            foreach ($items as &$item) {
                if ((int) $item['variant_id'] === $variantId && (string) ($item['note'] ?? '') === $note) {
                    $newQuantity = (int) $item['quantity'] + $quantity;
                    $this->assertPurchasable($variant, $newQuantity, $note);
                    $item['quantity'] = $newQuantity;
                    $found = true;
                    break;
                }
            }
            unset($item);
            if (!$found) {
                $items[] = ['variant_id' => $variantId, 'quantity' => $quantity, 'note' => $note];
            }
            $this->session->set($this->guestKey(), $items);
            return;
        }

        $cart = $this->accountCart($userId, true);
        $itemQuery = $this->db->table('cart_items')->where('cart_id', $cart['id'])->where('variant_id', $variantId);
        $note === '' ? $itemQuery->where('note', null) : $itemQuery->where('note', $note);
        $existing = $itemQuery->get()->getRowArray();
        if ($existing === null) {
            $this->db->table('cart_items')->insert([
                'cart_id' => $cart['id'], 'variant_id' => $variantId, 'quantity' => $quantity,
                'note' => $note === '' ? null : $note, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
            ]);
            return;
        }
        $newQuantity = (int) $existing['quantity'] + $quantity;
        $this->assertPurchasable($variant, $newQuantity, $note);
        $this->db->table('cart_items')->where('id', $existing['id'])->update([
            'quantity' => $newQuantity, 'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function update(?int $userId, int $itemId, int $quantity): void
    {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Jumlah minimal satu.');
        }
        if ($userId === null) {
            $items = $this->guestItems();
            foreach ($items as &$item) {
                if ((int) $item['variant_id'] === $itemId) {
                    $variant = $this->variantDetails($itemId);
                    $this->assertPurchasable($variant, $quantity, (string) ($item['note'] ?? ''));
                    $item['quantity'] = $quantity;
                    $this->session->set($this->guestKey(), $items);
                    return;
                }
            }
            throw new InvalidArgumentException('Item keranjang tidak ditemukan.');
        }
        $item = $this->accountItem($userId, $itemId);
        if ($item === null) {
            throw new InvalidArgumentException('Item keranjang tidak ditemukan.');
        }
        $variant = $this->variantDetails((int) $item['variant_id']);
        $this->assertPurchasable($variant, $quantity, (string) ($item['note'] ?? ''));
        $this->db->table('cart_items')->where('id', $itemId)->update(['quantity' => $quantity, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function remove(?int $userId, int $itemId): void
    {
        if ($userId === null) {
            $items = array_values(array_filter($this->guestItems(), static fn (array $item): bool => (int) $item['variant_id'] !== $itemId));
            $this->session->set($this->guestKey(), $items);
            return;
        }
        $cart = $this->accountCart($userId, false);
        if ($cart !== null) {
            $this->db->table('cart_items')->where('cart_id', $cart['id'])->where('id', $itemId)->delete();
        }
    }

    public function clear(?int $userId): void
    {
        if ($userId === null) {
            $this->session->remove($this->guestKey());
            $this->session->remove('guest_cart_key');
            return;
        }
        $cart = $this->accountCart($userId, false);
        if ($cart !== null) {
            $this->db->table('cart_items')->where('cart_id', $cart['id'])->delete();
        }
    }

    public function mergeGuestCart(int $userId): void
    {
        foreach ($this->guestItems() as $item) {
            $this->add($userId, (int) $item['variant_id'], (int) $item['quantity'], (string) ($item['note'] ?? ''));
        }
        $this->session->remove($this->guestKey());
        $this->session->remove('guest_cart_key');
    }

    private function guestItems(): array
    {
        $items = $this->session->get($this->guestKey());
        return is_array($items) ? $items : [];
    }

    private function guestKey(): string
    {
        $key = $this->session->get('guest_cart_key');
        if (! is_string($key) || $key === '') {
            $key = 'guest_cart_' . bin2hex(random_bytes(16));
            $this->session->set('guest_cart_key', $key);
        }
        return $key;
    }

    private function variantDetails(int $variantId): ?array
    {
        return $this->db->table('product_variants pv')
            ->select('pv.id AS variant_id, pv.label AS variant_label, pv.price, pv.stock, pv.is_active AS variant_active, p.name AS product_name, p.slug AS product_slug, p.is_active AS product_active, p.archived_at')
            ->join('products p', 'p.id = pv.product_id')
            ->where('pv.id', $variantId)->get()->getRowArray();
    }

    private function assertPurchasable(?array $variant, int $quantity, string $note): void
    {
        if ($variant === null || !(bool) $variant['variant_active'] || !(bool) $variant['product_active'] || $variant['archived_at'] !== null) {
            throw new InvalidArgumentException('Produk atau ukuran sudah tidak tersedia.');
        }
        if ($quantity < 1 || $quantity > (int) $variant['stock']) {
            throw new InvalidArgumentException('Jumlah melebihi stok yang tersedia. Perbarui jumlah pesanan.');
        }
        if (mb_strlen($note) > 150) {
            throw new InvalidArgumentException('Catatan maksimal 150 karakter.');
        }
    }

    private function accountCart(int $userId, bool $create): ?array
    {
        $cart = $this->db->table('carts')->where('user_id', $userId)->get()->getRowArray();
        if ($cart !== null || !$create) {
            return $cart;
        }
        $now = date('Y-m-d H:i:s');
        $this->db->table('carts')->insert(['user_id' => $userId, 'session_key' => null, 'created_at' => $now, 'updated_at' => $now]);
        return $this->db->table('carts')->where('id', $this->db->insertID())->get()->getRowArray();
    }

    private function accountItem(int $userId, int $itemId): ?array
    {
        return $this->db->table('cart_items ci')->select('ci.*')->join('carts c', 'c.id = ci.cart_id')
            ->where('c.user_id', $userId)->where('ci.id', $itemId)->get()->getRowArray();
    }
}