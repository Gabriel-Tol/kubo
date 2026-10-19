<?php

declare(strict_types=1);
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Models\UserModel;
use InvalidArgumentException;

class DemoAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email=trim((string)env('DEMO_ADMIN_EMAIL'));$password=(string)env('DEMO_ADMIN_PASSWORD');
        if(filter_var($email,FILTER_VALIDATE_EMAIL)===false||strlen($password)<12)throw new InvalidArgumentException('Set DEMO_ADMIN_EMAIL and a DEMO_ADMIN_PASSWORD of at least 12 characters in local .env before seeding the demo admin.');
        $users=model(UserModel::class);
        if($users->findByCredentials(['email'=>$email])!==null)throw new InvalidArgumentException('The configured demo admin email already exists.');
        $username=substr((string)preg_replace('/[^a-zA-Z0-9.]/','.',strstr($email,'@',true)),0,30);
        if(strlen($username)<3)$username='kubo.admin';
        $user=$users->createNewUser(['username'=>$username,'email'=>$email,'password'=>$password,'active'=>true]);
        if(!$users->save($user))throw new InvalidArgumentException('Demo admin could not be created. Check the local credentials and validation settings.');
        $user->id=(int)$users->getInsertID();$user->active=true;$user->addGroup('admin');
    }
}