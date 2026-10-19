<?php

declare(strict_types=1);

use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class AdminAccessTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use AuthenticationTesting;

    protected $namespace = ['CodeIgniter\\Shield', 'App'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed('App\\Database\\Seeds\\DemoShopSeeder');
    }

    public function testGuestIsRedirectedFromAdmin(): void
    {
        $this->get('/admin')->assertRedirect();
    }

    public function testCustomerReceives403FromAdmin(): void
    {
        $this->actingAs($this->makeUser('customer-access', 'customer'));
        $this->get('/admin')->assertStatus(403);
    }

    public function testAdminCanViewDashboard(): void
    {
        $this->actingAs($this->makeUser('admin-access', 'admin'));
        $result = $this->get('/admin');
        $result->assertOK();
        $result->assertSee('Dashboard KUBO');
    }

    private function makeUser(string $name, string $group): \CodeIgniter\Shield\Entities\User
    {
        $users = model(\CodeIgniter\Shield\Models\UserModel::class);
        $user = $users->createNewUser([
            'username' => $name, 'email' => $name . '@example.test',
            'password' => 'StrongPassword123!', 'password_confirm' => 'StrongPassword123!', 'active' => true,
        ]);
        $users->save($user);
        $user->id = (int) $users->getInsertID();
        $user->active = true;
        $user->addGroup($group);
        return $user;
    }
}