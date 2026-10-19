<?php

declare(strict_types=1);

use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class CustomerAccountTest extends CIUnitTestCase
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

    public function testRegistrationRequiresEightCharacterPassword(): void
    {
        $tokenName = csrf_token();
        $this->post('/register', [$tokenName => csrf_hash(), 'username'=>'shortpass','full_name'=>'Nabila Putri','phone'=>'081234567890','email'=>'nabila@example.test','password'=>'1234567','password_confirm'=>'1234567'])
            ->assertRedirect();
        $this->assertSame(0, $this->db->table('users')->countAllResults());
    }

    public function testCustomerCanSaveProfileAndAddress(): void
    {
        $user=$this->makeUser();
        $this->actingAs($user);
        $csrfName=csrf_token();
        $this->post('/akun/profil',[$csrfName=>csrf_hash(),'full_name'=>'Nabila Putri','phone'=>'081234567890'])->assertRedirect();
        $zoneId=(int)$this->db->table('delivery_zones')->get()->getRow('id');
        $csrfName=csrf_token();
        $this->post('/akun/alamat',[$csrfName=>csrf_hash(),'label'=>'Rumah','recipient_name'=>'Nabila Putri','phone'=>'081234567890','address_line'=>'Jalan Contoh 1','delivery_zone_id'=>$zoneId,'landmark'=>'Dekat taman'])->assertRedirect();
        $this->assertSame('Nabila Putri',$this->db->table('customer_profiles')->where('user_id',(int)$user->id)->get()->getRow('full_name'));
        $this->assertSame(1,$this->db->table('addresses')->where('user_id',(int)$user->id)->countAllResults());
    }

    private function makeUser(): \CodeIgniter\Shield\Entities\User
    {
        $users=model(\CodeIgniter\Shield\Models\UserModel::class);
        $user=$users->createNewUser(['username'=>'account-customer','email'=>'account@example.test','password'=>'StrongPassword123!','password_confirm'=>'StrongPassword123!','active'=>true]);
        $users->save($user); $user->id=(int)$users->getInsertID(); $user->active=true; $user->addGroup('customer');
        return $user;
    }
}
