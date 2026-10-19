<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class CatalogPagesTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = ['CodeIgniter\\Shield', 'App'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed('App\\Database\\Seeds\\DemoShopSeeder');
    }

    public function testHomeShowsFeaturedProducts(): void
    {
        $result = $this->get('/');
        $result->assertOK();
        $result->assertSee('Untuk meja');
        $result->assertSee('Favorit dari oven');
    }

    public function testCatalogCanSearchProducts(): void
    {
        $result = $this->get('/katalog?q=pandan');
        $result->assertOK();
        $result->assertSee('Bolu Pandan');
        $result->assertDontSee('Bolu Cokelat');
    }

    public function testProductDetailShowsCompositionAndVariants(): void
    {
        $result = $this->get('/produk/bolu-marmer');
        $result->assertOK();
        $result->assertSee('Loyang 20 cm');
        $result->assertSee('Komposisi');
    }

    public function testUnknownProductReturns404(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->get('/produk/tidak-ada');
    }
}