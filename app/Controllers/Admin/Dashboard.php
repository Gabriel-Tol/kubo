<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\DashboardService;
use App\Services\ExpiryService;
use DateTimeImmutable;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $db = db_connect();
        $dueOrders = $db->table('orders')->select('id')->where('status', 'pending_payment')->where('payment_due_at <=', date('Y-m-d H:i:s'))->get()->getResultArray();
        $expiry = new ExpiryService($db);
        foreach ($dueOrders as $order) {
            $expiry->expireIfDue((int) $order['id'], new DateTimeImmutable());
        }
        return view('admin/dashboard', ['title' => 'Dashboard KUBO', 'summary' => (new DashboardService($db))->summary(new DateTimeImmutable('today'))]);
    }
}