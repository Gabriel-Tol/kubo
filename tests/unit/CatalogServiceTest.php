<?php

declare(strict_types=1);

use App\Services\CatalogService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class CatalogServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = ['CodeIgniter\\Shield', 'App'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed('App\\Database\\Seeds\\DemoShopSeeder');
    }

    public function testSearchFiltersSortsAndCountsActiveProducts(): void
    {
        $result = (new CatalogService($this->db))->search([
            'q' => 'bolu', 'min_price' => 70000, 'max_price' => 95000, 'sort' => 'name_asc',
        ]);

        $this->assertSame(5, $result['count']);
        $this->assertSame(['Bolu Cokelat', 'Bolu Keju', 'Bolu Marmer', 'Bolu Pandan', 'Bolu Pisang'], array_column($result['products'], 'name'));
    }

    public function testArchivedProductIsHidden(): void
    {
        $this->db->table('products')->where('slug', 'bolu-cokelat')->update(['archived_at' => date('Y-m-d H:i:s')]);

        $this->assertNull((new CatalogService($this->db))->getBySlug('bolu-cokelat'));
        $this->assertSame(5, (new CatalogService($this->db))->search([])['count']);
    }

    public function testProductPageShowsOnlyActiveVariants(): void
    {
        $this->db->table('product_variants')->where('label', 'Loyang 24 cm')->update(['is_active' => false]);
        $product = (new CatalogService($this->db))->getBySlug('bolu-marmer');

        $this->assertNotNull($product);
        $this->assertCount(1, $product['variants']);
        $this->assertSame('Loyang 20 cm', $product['variants'][0]['label']);
    }

    public function testUnknownSlugReturnsNull(): void
    {
        $this->assertNull((new CatalogService($this->db))->getBySlug('tidak-ada'));
    }
}