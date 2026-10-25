<?php

declare(strict_types=1);

use App\Services\CartService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/** @internal */
final class CartServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = ['CodeIgniter\\Shield', 'App'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed('App\\Database\\Seeds\\DemoShopSeeder');
    }

    public function testGuestCartPersistsAcrossServiceInstances(): void
    {
        $variantId = (int) $this->db->table('product_variants v')->select('v.id')->join('products p', 'p.id = v.product_id')->where('p.slug', 'bolu-panggang-meses-cokelat')->get()->getRow('id');
        $first = new CartService($this->db);
        $first->add(null, 'test-session', $variantId, 2, 'Selamat ulang tahun');

        $cart = (new CartService($this->db))->get(null, 'test-session');
        $this->assertCount(1, $cart['items']);
        $this->assertSame(2, (int) $cart['items'][0]['quantity']);
        $this->assertSame(40000, (int) $cart['subtotal']);
    }

    public function testGuestCartRejectsInactiveVariantAndExcessQuantity(): void
    {
        $variantId = (int) $this->db->table('product_variants v')->select('v.id')->join('products p', 'p.id = v.product_id')->where('p.slug', 'bolu-panggang-meses-cokelat')->get()->getRow('id');
        $this->db->table('product_variants')->where('id', $variantId)->update(['is_active' => false]);
        $this->expectException(InvalidArgumentException::class);
        (new CartService($this->db))->add(null, 'inactive-session', $variantId, 1, '');
    }

    public function testGuestCartRejectsQuantityAboveStock(): void
    {
        $variant = $this->db->table('product_variants v')->select('v.*')->join('products p', 'p.id = v.product_id')->where('p.slug', 'bolu-panggang-meses-cokelat')->get()->getRowArray();
        $this->expectException(InvalidArgumentException::class);
        (new CartService($this->db))->add(null, 'excess-session', (int) $variant['id'], (int) $variant['stock'] + 1, '');
    }
}
