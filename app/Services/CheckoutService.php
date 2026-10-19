<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;
use InvalidArgumentException;
use Throwable;

class CheckoutService
{
    public function __construct(private readonly BaseConnection $db)
    {
    }

    /** @return array{id:int, order_no:string, status:string, subtotal:int, delivery_fee:int, total:int, payment_due_at:string} */
    public function placeOrder(int $userId, array $input): array
    {
        $name = trim((string) ($input['customer_name'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string) ($input['customer_phone'] ?? '')) ?? '';
        $email = trim((string) ($input['customer_email'] ?? ''));
        $fulfillment = (string) ($input['fulfillment_type'] ?? '');
        $date = (string) ($input['scheduled_date'] ?? '');
        $slotId = (int) ($input['time_slot_id'] ?? 0);

        if ($name === '' || mb_strlen($name) > 120) {
            throw new InvalidArgumentException('Nama pemesan wajib diisi (maksimal 120 karakter).');
        }
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            throw new InvalidArgumentException('Nomor HP harus terdiri dari 10 sampai 15 angka.');
        }
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Masukkan alamat email yang valid.');
        }
        if (! in_array($fulfillment, ['pickup', 'delivery'], true)) {
            throw new InvalidArgumentException('Pilih ambil di toko atau antar lokal.');
        }
        $scheduledDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if ($scheduledDate === false || $scheduledDate->format('Y-m-d') !== $date || $date < date('Y-m-d')) {
            throw new InvalidArgumentException('Pilih tanggal pesanan hari ini atau setelahnya.');
        }
        $slot = $this->db->table('time_slots')->where('id', $slotId)->where('is_active', 1)->get()->getRowArray();
        if ($slot === null || (bool) $slot['is_full']) {
            throw new InvalidArgumentException('Slot waktu tidak tersedia. Silakan pilih slot lain.');
        }
        if ($date === date('Y-m-d')) {
            $leadMinutes = $this->shopSettingInt('slot_lead_minutes', 120);
            $earliest = (new DateTimeImmutable())->modify('+' . $leadMinutes . ' minutes')->format('H:i:s');
            if ($slot['start_time'] < $earliest) {
                throw new InvalidArgumentException('Slot waktu sudah lewat batas pemesanan. Pilih waktu yang lebih siang.');
            }
        }

        $zone = null;
        $address = null;
        $deliveryFee = 0;
        if ($fulfillment === 'delivery') {
            $zoneId = (int) ($input['delivery_zone_id'] ?? 0);
            $zone = $this->db->table('delivery_zones')->where('id', $zoneId)->where('is_active', 1)->get()->getRowArray();
            $address = trim((string) ($input['address'] ?? ''));
            if ($zone === null) {
                throw new InvalidArgumentException('Pilih area antar yang tersedia.');
            }
            if ($address === '' || mb_strlen($address) > 1000) {
                throw new InvalidArgumentException('Alamat lengkap wajib diisi untuk pengantaran.');
            }
            $deliveryFee = (int) $zone['fee'];
        }

        $this->db->transBegin();
        try {
            $cart = $this->db->table('carts')->where('user_id', $userId)->get()->getRowArray();
            if ($cart === null) {
                throw new InvalidArgumentException('Keranjang kosong. Tambahkan bolu sebelum checkout.');
            }
            $cartItems = $this->db->table('cart_items ci')
                ->select('ci.variant_id, ci.quantity, ci.note, pv.label AS variant_label, pv.price, pv.stock, pv.is_active AS variant_active, p.name AS product_name, p.is_active AS product_active, p.archived_at')
                ->join('product_variants pv', 'pv.id = ci.variant_id')
                ->join('products p', 'p.id = pv.product_id')
                ->where('ci.cart_id', $cart['id'])->get()->getResultArray();
            if ($cartItems === []) {
                throw new InvalidArgumentException('Keranjang kosong. Tambahkan bolu sebelum checkout.');
            }

            $quantities = [];
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $this->lockVariant((int) $item['variant_id']);
                if (! (bool) $item['variant_active'] || ! (bool) $item['product_active'] || $item['archived_at'] !== null) {
                    throw new InvalidArgumentException('Ada produk yang sudah tidak tersedia. Hapus item tersebut dari keranjang.');
                }
                $quantity = (int) $item['quantity'];
                $quantities[$item['variant_id']] = ($quantities[$item['variant_id']] ?? 0) + $quantity;
                $subtotal += (int) $item['price'] * $quantity;
            }
            foreach ($quantities as $variantId => $quantity) {
                $variant = $this->db->table('product_variants')->where('id', $variantId)->get()->getRowArray();
                if ($variant === null || $quantity > (int) $variant['stock']) {
                    throw new InvalidArgumentException('Stok berubah. Perbarui jumlah bolu di keranjang sebelum checkout.');
                }
            }

            $now = new DateTimeImmutable();
            $dueAt = $now->modify('+' . $this->shopSettingInt('payment_deadline_minutes', 60) . ' minutes')->format('Y-m-d H:i:s');
            $orderNo = $this->nextOrderNumber($now);
            $this->db->table('orders')->insert([
                'user_id' => $userId, 'order_no' => $orderNo, 'status' => 'pending_payment',
                'fulfillment_type' => $fulfillment, 'customer_name' => $name, 'customer_phone' => $phone,
                'customer_email' => $email === '' ? null : $email,
                'zone_name_snapshot' => $zone['name'] ?? null, 'address_snapshot' => $address,
                'pickup_date' => $date, 'time_slot_snapshot' => $slot['label'],
                'subtotal' => $subtotal, 'delivery_fee' => $deliveryFee, 'total' => $subtotal + $deliveryFee,
                'payment_due_at' => $dueAt, 'stock_deducted' => false,
                'created_at' => $now->format('Y-m-d H:i:s'), 'updated_at' => $now->format('Y-m-d H:i:s'),
            ]);
            $orderId = (int) $this->db->insertID();
            foreach ($cartItems as $item) {
                $this->db->table('order_items')->insert([
                    'order_id' => $orderId, 'variant_id' => (int) $item['variant_id'],
                    'product_name_snapshot' => $item['product_name'], 'variant_name_snapshot' => $item['variant_label'],
                    'unit_price' => (int) $item['price'], 'quantity' => (int) $item['quantity'],
                    'note' => $item['note'], 'line_total' => (int) $item['price'] * (int) $item['quantity'],
                    'created_at' => $now->format('Y-m-d H:i:s'),
                ]);
            }
            $this->db->table('order_status_logs')->insert([
                'order_id' => $orderId, 'actor_id' => null, 'from_status' => null,
                'to_status' => 'pending_payment', 'note' => 'Pesanan dibuat.', 'created_at' => $now->format('Y-m-d H:i:s'),
            ]);
            $this->db->table('cart_items')->where('cart_id', $cart['id'])->delete();
            if ($this->db->transStatus() === false) {
                throw new InvalidArgumentException('Pesanan belum tersimpan. Silakan coba lagi.');
            }
            $this->db->transCommit();

            return [
                'id' => $orderId, 'order_no' => $orderNo, 'status' => 'pending_payment',
                'subtotal' => $subtotal, 'delivery_fee' => $deliveryFee, 'total' => $subtotal + $deliveryFee,
                'payment_due_at' => $dueAt,
            ];
        } catch (Throwable $error) {
            $this->db->transRollback();
            throw $error;
        }
    }

    private function lockVariant(int $variantId): void
    {
        if ($this->db->getPlatform() === 'MySQLi') {
            $this->db->query('SELECT id FROM product_variants WHERE id = ? FOR UPDATE', [$variantId]);
        }
    }

    private function nextOrderNumber(DateTimeImmutable $now): string
    {
        $prefix = 'BH-' . $now->format('dmy') . '-';
        $last = $this->db->table('orders')->like('order_no', $prefix, 'after')->orderBy('id', 'DESC')->get()->getRowArray();
        $sequence = $last === null ? 1 : ((int) substr((string) $last['order_no'], -3)) + 1;
        if ($sequence > 999) {
            throw new InvalidArgumentException('Nomor pesanan hari ini sudah mencapai batas. Hubungi toko.');
        }
        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    private function shopSettingInt(string $key, int $default): int
    {
        $row = $this->db->table('shop_settings')->where('setting_key', $key)->get()->getRowArray();
        return $row === null ? $default : max(0, (int) $row['setting_value']);
    }
}