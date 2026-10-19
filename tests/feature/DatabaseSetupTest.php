<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class DatabaseSetupTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = ['CodeIgniter\\Shield', 'App'];

    public function testShopMigrationsCreateRequiredTables(): void
    {
        foreach ([
            'categories', 'products', 'product_variants', 'delivery_zones',
            'orders', 'order_items', 'payment_proofs', 'customer_profiles',
            'addresses', 'product_images', 'carts', 'cart_items', 'time_slots',
            'order_status_logs', 'settings', 'shop_settings',
        ] as $table) {
            $this->assertTrue($this->db->tableExists($table), sprintf('Expected migration to create %s.', $table));
        }
    }

    public function testDemoSeederCreatesSixProductsAndTwoZones(): void
    {
        $this->seed('App\\Database\\Seeds\\DemoShopSeeder');

        $this->assertSame(6, $this->db->table('products')->countAllResults());
        $this->assertSame(2, $this->db->table('delivery_zones')->countAllResults());
        $this->assertSame(4, $this->db->table('time_slots')->countAllResults());
    }
    public function testDemoSeederCreatesAdminFromEnvironment(): void
    {
        putenv('DEMO_ADMIN_EMAIL=admin-test@example.test');
        putenv('DEMO_ADMIN_PASSWORD=TestAdminPassword123!');
        try {
            $this->seed('App\\Database\\Seeds\\DemoAdminSeeder');
            $user = model(\CodeIgniter\Shield\Models\UserModel::class)->findByCredentials(['email' => 'admin-test@example.test']);
            $this->assertNotNull($user);
            $membership = $this->db->table('auth_groups_users')->where('user_id', $user->id)->where('group', 'admin')->get()->getRowArray();
            $this->assertNotNull($membership);
        } finally {
            putenv('DEMO_ADMIN_EMAIL');
            putenv('DEMO_ADMIN_PASSWORD');
        }
    }
}
