<?php

declare(strict_types=1);

namespace App\Controllers\Shop;

use App\Controllers\BaseController;
use App\Services\CatalogService;

class Catalog extends BaseController
{
    public function index(): string
    {
        $db = db_connect();
        $result = (new CatalogService($db))->search($this->request->getGet());

        return view('shop/catalog', [
            'title' => 'Katalog Bolu',
            'products' => $result['products'],
            'count' => $result['count'],
            'categories' => $db->table('categories')->orderBy('sort_order')->get()->getResultArray(),
            'filters' => $this->request->getGet(),
        ]);
    }
}