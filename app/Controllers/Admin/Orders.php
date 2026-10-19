<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ExpiryService;
use App\Services\OrderStatusService;
use App\Services\PaymentProofService;
use CodeIgniter\Exceptions\PageNotFoundException;
use InvalidArgumentException;

class Orders extends BaseController
{
    public function index(): string
    {
        $db = db_connect();
        $due = $db->table('orders')->select('id')->where('status', 'pending_payment')->where('payment_due_at <=', date('Y-m-d H:i:s'))->get()->getResultArray();
        $expiry = new ExpiryService($db);
        foreach ($due as $row) { $expiry->expireIfDue((int) $row['id'], new \DateTimeImmutable()); }
        $query = $db->table('orders')->orderBy('created_at', 'DESC');
        $status = trim((string) $this->request->getGet('status'));
        if ($status !== '') { $query->where('status', $status); }
        $orders = $query->limit(100)->get()->getResultArray();
        return view('admin/orders', ['title' => 'Pesanan', 'orders' => $orders, 'status' => $status]);
    }

    public function show(int $id): string
    {
        $order = db_connect()->table('orders')->where('id', $id)->get()->getRowArray();
        if ($order === null) { throw PageNotFoundException::forPageNotFound(); }
        (new ExpiryService(db_connect()))->expireIfDue($id, new \DateTimeImmutable());
        $order = db_connect()->table('orders')->where('id', $id)->get()->getRowArray();
        return view('admin/order', [
            'title' => 'Detail Pesanan', 'order' => $order,
            'items' => db_connect()->table('order_items')->where('order_id', $id)->get()->getResultArray(),
            'proofs' => db_connect()->table('payment_proofs')->where('order_id', $id)->orderBy('created_at', 'DESC')->get()->getResultArray(),
            'logs' => db_connect()->table('order_status_logs')->where('order_id', $id)->orderBy('created_at')->get()->getResultArray(),
        ]);
    }

    public function review(int $id, int $proofId): mixed
    {
        $accepted = $this->request->getPost('decision') === 'accept';
        try {
            (new PaymentProofService(db_connect()))->review($proofId, (int) auth()->id(), $accepted, $this->request->getPost('reason'));
        } catch (InvalidArgumentException $error) {
            return redirect()->back()->with('error', $error->getMessage());
        }
        return redirect()->to(site_url('admin/pesanan/' . $id))->with('success', $accepted ? 'Pembayaran dikonfirmasi dan stok diperbarui.' : 'Bukti ditolak dengan alasan.');
    }

    public function transition(int $id): mixed
    {
        try {
            (new OrderStatusService(db_connect()))->transition($id, (string) $this->request->getPost('status'), (int) auth()->id(), trim((string) $this->request->getPost('note')) ?: null);
        } catch (InvalidArgumentException $error) {
            return redirect()->back()->with('error', $error->getMessage());
        }
        return redirect()->to(site_url('admin/pesanan/' . $id))->with('success', 'Status pesanan diperbarui.');
    }

    public function proof(int $id, int $proofId): mixed
    {
        $proof = db_connect()->table('payment_proofs')->where('id', $proofId)->where('order_id', $id)->get()->getRowArray();
        if ($proof === null) { throw PageNotFoundException::forPageNotFound(); }
        $path = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'payment-proofs' . DIRECTORY_SEPARATOR . basename($proof['file_path']);
        if (! is_file($path)) { throw PageNotFoundException::forPageNotFound(); }
        return $this->response->setHeader('Content-Type', $proof['mime_type'])->setHeader('X-Content-Type-Options', 'nosniff')->setHeader('Cache-Control', 'private, no-store')->setBody((string) file_get_contents($path));
    }
}