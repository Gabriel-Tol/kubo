<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;

class DashboardService
{
    public function __construct(private readonly BaseConnection $db)
    {
    }

    public function summary(DateTimeImmutable $day): array
    {
        $start = $day->setTime(0, 0)->format('Y-m-d H:i:s');
        $end = $day->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        $ordersToday = $this->db->table('orders')->where('created_at >=', $start)->where('created_at <=', $end)->countAllResults();
        $sales = $this->db->table('orders')->selectSum('subtotal', 'sales')->whereIn('status', ['confirmed', 'processing', 'ready_for_pickup', 'out_for_delivery', 'completed'])->get()->getRowArray();
        $proofs = $this->db->table('payment_proofs')->where('status', 'submitted')->countAllResults();
        $queue = $this->db->table('orders')->whereIn('status', ['processing', 'ready_for_pickup', 'out_for_delivery'])->orderBy('created_at')->get()->getResultArray();
        $recent = $this->db->table('orders')->orderBy('created_at', 'DESC')->limit(8)->get()->getResultArray();
        return ['orders_today' => $ordersToday, 'confirmed_sales' => (int) ($sales['sales'] ?? 0), 'proofs_to_review' => $proofs, 'production_queue' => $queue, 'recent_orders' => $recent];
    }
}