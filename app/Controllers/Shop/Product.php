<?php

declare(strict_types=1);

namespace App\Controllers\Shop;

use App\Controllers\BaseController;
use App\Services\CatalogService;

class Product extends BaseController
{
    public function show(string $slug): string
    {
        $product = (new CatalogService(db_connect()))->getBySlug($slug);
        if ($product === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
        }

        return view('shop/product', ['title' => $product['name'], 'product' => $product]);
    }
}