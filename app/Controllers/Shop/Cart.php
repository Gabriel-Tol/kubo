<?php

declare(strict_types=1);

namespace App\Controllers\Shop;

use App\Controllers\BaseController;
use App\Services\CartService;
use InvalidArgumentException;

class Cart extends BaseController
{
    public function index(): string
    {
        $cart = $this->service()->get($this->userId());
        return view('shop/cart', ['title' => 'Keranjang', 'cart' => $cart, 'userId' => $this->userId()]);
    }

    public function add(): mixed
    {
        try {
            $this->service()->add(
                $this->userId(),
                (int) $this->request->getPost('variant_id'),
                (int) $this->request->getPost('quantity'),
                trim((string) $this->request->getPost('note')),
            );
        } catch (InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }
        return redirect()->to(site_url('keranjang'))->with('success', 'Bolu ditambahkan ke keranjang.');
    }

    public function update(int $itemId): mixed
    {
        try {
            $this->service()->update($this->userId(), $itemId, (int) $this->request->getPost('quantity'));
        } catch (InvalidArgumentException $error) {
            return redirect()->to(site_url('keranjang'))->with('error', $error->getMessage());
        }
        return redirect()->to(site_url('keranjang'))->with('success', 'Jumlah pesanan diperbarui.');
    }

    public function remove(int $itemId): mixed
    {
        $this->service()->remove($this->userId(), $itemId);
        return redirect()->to(site_url('keranjang'))->with('success', 'Item dihapus dari keranjang.');
    }

    private function service(): CartService
    {
        return new CartService(db_connect());
    }

    private function userId(): ?int
    {
        return auth()->loggedIn() ? (int) auth()->id() : null;
    }
}