<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use InvalidArgumentException;
use Throwable;

class OrderStatusService
{
    public function __construct(private readonly BaseConnection $db)
    {
    }

    public function transition(int $orderId, string $toStatus, ?int $actorId, ?string $note = null): void
    {
        $this->db->transBegin();
        try {
            $order = $this->db->table('orders')->where('id', $orderId)->get()->getRowArray();
            if ($order === null) {
                throw new InvalidArgumentException('Pesanan tidak ditemukan.');
            }
            $from = $order['status'];
            $valid = match ($from) {
                'pending_payment' => ['cancelled'],
                'confirmed' => ['processing', 'cancelled'],
                'processing' => [$order['fulfillment_type'] === 'pickup' ? 'ready_for_pickup' : 'out_for_delivery', 'cancelled'],
                'ready_for_pickup', 'out_for_delivery' => ['completed', 'cancelled'],
                default => [],
            };
            if ($toStatus === 'confirmed' && $from === 'pending_payment') {
                $valid[] = 'confirmed';
            }
            if (! in_array($toStatus, $valid, true)) {
                throw new InvalidArgumentException('Perubahan status pesanan tidak sesuai urutan.');
            }
            if ($toStatus === 'cancelled' && trim((string) $note) === '') {
                throw new InvalidArgumentException('Alasan pembatalan wajib diisi.');
            }

            if ($toStatus === 'confirmed') {
                $items = $this->db->table('order_items')->where('order_id', $orderId)->get()->getResultArray();
                foreach ($items as $item) {
                    if ($item['variant_id'] === null) {
                        throw new InvalidArgumentException('Varian pesanan tidak lagi tersedia. Stok belum dikurangi.');
                    }
                    $variantId = (int) $item['variant_id'];
                    if ($this->db->getPlatform() === 'MySQLi') {
                        $this->db->query('SELECT id FROM product_variants WHERE id = ? FOR UPDATE', [$variantId]);
                    }
                    $result = $this->db->table('product_variants')->set('stock', 'stock - ' . (int) $item['quantity'], false)
                        ->where('id', $variantId)->where('stock >=', (int) $item['quantity'])->update();
                    if ($result !== true || $this->db->affectedRows() !== 1) {
                        throw new InvalidArgumentException('Stok tidak mencukupi untuk mengonfirmasi pembayaran.');
                    }
                }
                $order['stock_deducted'] = true;
            }

            if ($toStatus === 'cancelled' && (bool) $order['stock_deducted']) {
                $items = $this->db->table('order_items')->where('order_id', $orderId)->get()->getResultArray();
                foreach ($items as $item) {
                    if ($item['variant_id'] !== null) {
                        $this->db->table('product_variants')->set('stock', 'stock + ' . (int) $item['quantity'], false)
                            ->where('id', (int) $item['variant_id'])->update();
                    }
                }
                $order['stock_deducted'] = false;
            }

            $now = date('Y-m-d H:i:s');
            $this->db->table('orders')->where('id', $orderId)->update([
                'status' => $toStatus, 'stock_deducted' => (bool) $order['stock_deducted'],
                'cancellation_reason' => $toStatus === 'cancelled' ? trim((string) $note) : $order['cancellation_reason'],
                'updated_at' => $now,
            ]);
            $this->db->table('order_status_logs')->insert([
                'order_id' => $orderId, 'actor_id' => $actorId, 'from_status' => $from,
                'to_status' => $toStatus, 'note' => $note, 'created_at' => $now,
            ]);
            if ($this->db->transStatus() === false) {
                throw new InvalidArgumentException('Status pesanan gagal disimpan.');
            }
            $this->db->transCommit();
        } catch (Throwable $error) {
            $this->db->transRollback();
            throw $error;
        }
    }
}