<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;

class ExpiryService
{
    public function __construct(private readonly BaseConnection $db)
    {
    }

    public function expireIfDue(int $orderId, DateTimeImmutable $now): bool
    {
        $order = $this->db->table('orders')->where('id', $orderId)->get()->getRowArray();
        if ($order === null || $order['status'] !== 'pending_payment' || $order['payment_due_at'] > $now->format('Y-m-d H:i:s')) {
            return false;
        }
        (new OrderStatusService($this->db))->transition($orderId, 'cancelled', null, 'Batas pembayaran berakhir');
        return true;
    }
}