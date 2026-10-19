<?php

declare(strict_types=1);

use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class AdminCatalogTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use AuthenticationTesting;

    protected $namespace = ['CodeIgniter\Shield', 'App'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed('App\Database\Seeds\DemoShopSeeder');
    }

    public function testAdminCanAddAndRenameCategory(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->post('/admin/kategori', [csrf_token() => csrf_hash(), 'name' => 'Bolu Baru'])->assertRedirect();
        $category = $this->db->table('categories')->where('slug', 'bolu-baru')->get()->getRowArray();
        self::assertNotNull($category);
        $this->post('/admin/kategori/' . $category['id'], [csrf_token() => csrf_hash(), 'name' => 'Bolu Pilihan'])->assertRedirect();
        self::assertSame('bolu-pilihan', $this->db->table('categories')->where('id', $category['id'])->get()->getRow('slug'));
    }

    public function testCategoryInUseCannotBeDeleted(): void
    {
        $this->actingAs($this->makeAdmin());
        $category = $this->db->table('categories')->where('slug', 'bolu-favorit')->get()->getRowArray();
        $this->post('/admin/kategori/' . $category['id'] . '/hapus', [csrf_token() => csrf_hash()])->assertRedirect();
        self::assertSame(1, $this->db->table('categories')->where('id', $category['id'])->countAllResults());
    }

    public function testCustomerCannotManageCategories(): void
    {
        $users = model(\CodeIgniter\Shield\Models\UserModel::class);
        $user = $users->createNewUser(['username' => 'catalog-customer', 'email' => 'catalog-customer@example.test', 'password' => 'StrongPassword123!', 'password_confirm' => 'StrongPassword123!', 'active' => true]);
        $users->save($user);
        $user->id = (int) $users->getInsertID();
        $user->active = true;
        $user->addGroup('customer');
        $this->actingAs($user);
        $this->post('/admin/kategori', [csrf_token() => csrf_hash(), 'name' => 'Terlarang'])->assertStatus(403);
        self::assertSame(0, $this->db->table('categories')->where('slug', 'terlarang')->countAllResults());
    }

    private function makeAdmin(): \CodeIgniter\Shield\Entities\User
    {
        $users = model(\CodeIgniter\Shield\Models\UserModel::class);
        $user = $users->createNewUser(['username' => 'catalog-admin', 'email' => 'catalog-admin@example.test', 'password' => 'StrongPassword123!', 'password_confirm' => 'StrongPassword123!', 'active' => true]);
        $users->save($user);
        $user->id = (int) $users->getInsertID();
        $user->active = true;
        $user->addGroup('admin');
        return $user;
    }
}