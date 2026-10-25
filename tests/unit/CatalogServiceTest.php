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
            'q' => 'bolu', 'min_price' => 20000, 'max_price' => 25000, 'sort' => 'name_asc',
        ]);

        $this->assertSame(5, $result['count']);
        $this->assertSame(['Bolu Marmer Cokelat', 'Bolu Marmer Pandan', 'Bolu Meses Warna-Warni', 'Bolu Panggang Meses Cokelat', 'Bolu Pisang'], array_column($result['products'], 'name'));
    }

    public function testArchivedProductIsHidden(): void
    {
        $this->db->table('products')->where('slug', 'bolu-marmer-cokelat')->update(['archived_at' => date('Y-m-d H:i:s')]);

        $this->assertNull((new CatalogService($this->db))->getBySlug('bolu-marmer-cokelat'));
        $this->assertSame(4, (new CatalogService($this->db))->search([])['count']);
    }

    public function testProductPageShowsOnlyActiveVariants(): void
    {
        $this->db->table('product_variants')->where('label', 'Loyang 24 cm')->update(['is_active' => false]);
        $product = (new CatalogService($this->db))->getBySlug('bolu-marmer-cokelat');

        $this->assertNotNull($product);
        $this->assertCount(1, $product['variants']);
        $this->assertSame('Loyang 20 cm', $product['variants'][0]['label']);
    }

    public function testUnknownSlugReturnsNull(): void
    {
        $this->assertNull((new CatalogService($this->db))->getBySlug('tidak-ada'));
    }
}
