<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Shop\Home::index', ['as' => 'home']);
$routes->get('katalog', 'Shop\Catalog::index', ['as' => 'catalog']);
$routes->get('produk/(:segment)', 'Shop\Product::show/$1', ['as' => 'product.show']);
$routes->get('keranjang', 'Shop\Cart::index', ['as' => 'cart']);
$routes->post('keranjang/tambah', 'Shop\Cart::add');
$routes->post('keranjang/ubah/(:num)', 'Shop\Cart::update/$1');
$routes->post('keranjang/hapus/(:num)', 'Shop\Cart::remove/$1');

$routes->group('', ['filter' => 'session'], static function ($routes): void {
    $routes->get('akun', 'Account\Profile::index', ['as' => 'account']);
    $routes->post('akun/profil', 'Account\Profile::save');
    $routes->post('akun/alamat', 'Account\Profile::saveAddress');
    $routes->post('akun/alamat/(:num)/hapus', 'Account\Profile::deleteAddress/$1');
    $routes->get('checkout', 'Shop\Orders::checkout', ['as' => 'checkout']);
    $routes->post('checkout', 'Shop\Orders::place');
    $routes->get('pesanan', 'Shop\Orders::index', ['as' => 'orders.index']);
    $routes->get('pesanan/(:num)', 'Shop\Orders::show/$1', ['as' => 'orders.show']);
    $routes->get('pesanan/(:num)/bayar', 'Shop\Orders::payment/$1', ['as' => 'orders.payment']);
    $routes->post('pesanan/(:num)/bukti', 'Shop\Orders::submitProof/$1');
    $routes->get('pesanan/(:num)/bukti/(:num)', 'Shop\Orders::proof/$1/$2');
});
$routes->get('admin/login', 'Admin\\Login::index', ['as' => 'admin.login']);
$routes->group('admin', ['filter' => 'adminonly'], static function ($routes): void {
    $routes->get('/', 'Admin\\Dashboard::index', ['as' => 'admin.dashboard']);
    $routes->get('produk', 'Admin\Products::index', ['as' => 'admin.products']);
    $routes->get('produk/tambah', 'Admin\Products::form');
    $routes->post('produk', 'Admin\Products::save');
    $routes->get('produk/(:num)/edit', 'Admin\Products::form/$1');
    $routes->post('produk/(:num)', 'Admin\Products::save/$1');
    $routes->post('produk/(:num)/arsip', 'Admin\Products::archive/$1');
    $routes->post('kategori', 'Admin\Products::category');
    $routes->post('kategori/(:num)', 'Admin\Products::renameCategory/$1');
    $routes->post('kategori/(:num)/hapus', 'Admin\Products::deleteCategory/$1');
    $routes->get('pesanan', 'Admin\\Orders::index', ['as' => 'admin.orders']);
    $routes->get('pesanan/(:num)', 'Admin\\Orders::show/$1', ['as' => 'admin.order']);
    $routes->post('pesanan/(:num)/bukti/(:num)', 'Admin\\Orders::review/$1/$2');
    $routes->post('pesanan/(:num)/status', 'Admin\\Orders::transition/$1');
    $routes->get('pesanan/(:num)/bukti/(:num)', 'Admin\\Orders::proof/$1/$2');
});
service('auth')->routes($routes);