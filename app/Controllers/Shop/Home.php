<?php

declare(strict_types=1);

namespace App\Controllers\Shop;

use App\Controllers\BaseController;
use App\Services\CatalogService;

class Home extends BaseController
{
    public function index(): string
    {
        $catalog = new CatalogService(db_connect());
        $featured = $catalog->search(['sort' => 'name_asc'])['products'];
        $featured = array_values(array_filter($featured, static fn (array $product): bool => $product['available_stock'] > 0));

        return view('shop/home', ['title' => 'Bolu Hangat KUBO', 'featured' => array_slice($featured, 0, 3)]);
    }
}