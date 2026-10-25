<?php

declare(strict_types=1);

namespace App\Controllers\Shop;

use App\Controllers\BaseController;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\ExpiryService;
use App\Services\PaymentProofService;
use CodeIgniter\Exceptions\PageNotFoundException;
use InvalidArgumentException;

class Orders extends BaseController
{
    public function checkout(): mixed
    {
        if (! auth()->loggedIn()) {
            service('session')->setTempdata('beforeLoginUrl', site_url('checkout'), 600);
            return redirect()->to(site_url('login'))->with('error', 'Masuk ke akun untuk melanjutkan checkout.');
        }
        $userId = (int) auth()->id();
        $cart = (new CartService(db_connect()))->get($userId);
        if ($cart['items'] === []) {
            return redirect()->to(site_url('keranjang'))->with('error', 'Keranjang kosong. Tambahkan bolu sebelum checkout.');
        }
        $user = auth()->user();
        $profile = db_connect()->table('customer_profiles')->where('user_id', $userId)->get()->getRowArray();
        return view('shop/checkout', [
            'title' => 'Checkout', 'cart' => $cart, 'zones' => db_connect()->table('delivery_zones')->where('is_active', 1)->get()->getResultArray(),
            'slots' => db_connect()->table('time_slots')->where('is_active', 1)->where('is_full', 0)->get()->getResultArray(),
            'profile' => $profile ?? [], 'email' => $user?->email ?? '',
        ]);
    }

    public function place(): mixed
    {
        if (! auth()->loggedIn()) {
            return redirect()->to(site_url('login'));
        }
        try {
            $order = (new CheckoutService(db_connect()))->placeOrder((int) auth()->id(), $this->request->getPost());
        } catch (InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }
        return redirect()->to(site_url('pesanan/' . $order['id'] . '/bayar'))->with('success', 'Pesanan dibuat. Silakan bayar dengan QRIS.');
    }

    public function index(): string
    {
        $orders = db_connect()->table('orders')->where('user_id', (int) auth()->id())->orderBy('created_at', 'DESC')->get()->getResultArray();
        return view('shop/orders', ['title' => 'Pesanan Saya', 'orders' => $orders]);
    }

    public function show(int $id): string
    {
        $order = $this->ownedOrder($id);
        (new ExpiryService(db_connect()))->expireIfDue($id, new \DateTimeImmutable());
        $order = $this->ownedOrder($id);
        $items = db_connect()->table('order_items')->where('order_id', $id)->get()->getResultArray();
        $logs = db_connect()->table('order_status_logs')->where('order_id', $id)->orderBy('created_at')->get()->getResultArray();
        $proofs = db_connect()->table('payment_proofs')->where('order_id', $id)->orderBy('created_at', 'DESC')->get()->getResultArray();
        return view('shop/order', ['title' => 'Detail Pesanan', 'order' => $order, 'items' => $items, 'logs' => $logs, 'proofs' => $proofs]);
    }

    public function payment(int $id): string
    {
        $order = $this->ownedOrder($id);
        (new ExpiryService(db_connect()))->expireIfDue($id, new \DateTimeImmutable());
        $order = $this->ownedOrder($id);
        $proofs = db_connect()->table('payment_proofs')->where('order_id', $id)->orderBy('created_at', 'DESC')->get()->getResultArray();
        return view('shop/payment', ['title' => 'Pembayaran QRIS', 'order' => $order, 'proofs' => $proofs]);
    }

    public function submitProof(int $id): mixed
    {
        $file = $this->request->getFile('proof');
        if ($file === null) {
            return redirect()->back()->with('error', 'Pilih foto bukti pembayaran terlebih dahulu.');
        }
        try {
            (new PaymentProofService(db_connect()))->submit($id, (int) auth()->id(), $file);
        } catch (InvalidArgumentException $error) {
            return redirect()->back()->with('error', $error->getMessage());
        }
        return redirect()->to(site_url('pesanan/' . $id))->with('success', 'Bukti diterima dan menunggu verifikasi manual. Bukti terkirim bukan berarti pembayaran disetujui.');
    }

    public function proof(int $id, int $proofId): mixed
    {
        $this->ownedOrder($id);
        $proof = db_connect()->table('payment_proofs')->where('id', $proofId)->where('order_id', $id)->get()->getRowArray();
        if ($proof === null) {
            throw PageNotFoundException::forPageNotFound();
        }
        $path = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'payment-proofs' . DIRECTORY_SEPARATOR . basename($proof['file_path']);
        if (! is_file($path)) {
            throw PageNotFoundException::forPageNotFound();
        }
        return $this->response->setHeader('Content-Type', $proof['mime_type'])
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody((string) file_get_contents($path));
    }

    private function ownedOrder(int $id): array
    {
        if (! auth()->loggedIn()) {
            throw PageNotFoundException::forPageNotFound();
        }
        $order = db_connect()->table('orders')->where('id', $id)->where('user_id', (int) auth()->id())->get()->getRowArray();
        if ($order === null) {
            throw PageNotFoundException::forPageNotFound();
        }
        return $order;
    }
}
