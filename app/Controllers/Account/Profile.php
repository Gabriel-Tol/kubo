<?php

declare(strict_types=1);
namespace App\Controllers\Account;
use App\Controllers\BaseController;
class Profile extends BaseController
{
    public function index(): string
    {
        $db = db_connect(); $userId = (int) auth()->id();
        return view('account/profile', [
            'title' => 'Akun Saya',
            'profile' => $db->table('customer_profiles')->where('user_id', $userId)->get()->getRowArray() ?? [],
            'addresses' => $db->table('addresses')->select('addresses.*, delivery_zones.name AS zone_name')->join('delivery_zones', 'delivery_zones.id = addresses.delivery_zone_id', 'left')->where('addresses.user_id', $userId)->orderBy('addresses.is_default', 'DESC')->get()->getResultArray(),
            'zones' => $db->table('delivery_zones')->where('is_active', 1)->get()->getResultArray(),
        ]);
    }
    public function save(): mixed
    {
        $name = trim((string) $this->request->getPost('full_name')); $phone = preg_replace('/\D+/', '', (string) $this->request->getPost('phone')) ?? '';
        if ($name === '' || mb_strlen($name) > 120 || strlen($phone) < 10 || strlen($phone) > 15) return redirect()->back()->withInput()->with('error', 'Nama wajib diisi dan nomor HP harus terdiri dari 10–15 angka.');
        $db = db_connect(); $data = ['full_name' => $name, 'phone' => $phone, 'updated_at' => date('Y-m-d H:i:s')];
        $found = $db->table('customer_profiles')->where('user_id', (int) auth()->id())->get()->getRowArray();
        if ($found === null) $db->table('customer_profiles')->insert($data + ['user_id' => (int) auth()->id(), 'created_at' => date('Y-m-d H:i:s')]);
        else $db->table('customer_profiles')->where('user_id', (int) auth()->id())->update($data);
        return redirect()->to(site_url('akun'))->with('success', 'Profil disimpan.');
    }
    public function saveAddress(): mixed
    {
        $data = ['label'=>trim((string)$this->request->getPost('label')), 'recipient_name'=>trim((string)$this->request->getPost('recipient_name')), 'phone'=>preg_replace('/\D+/', '', (string)$this->request->getPost('phone')) ?? '', 'address_line'=>trim((string)$this->request->getPost('address_line')), 'delivery_zone_id'=>(int)$this->request->getPost('delivery_zone_id'), 'landmark'=>trim((string)$this->request->getPost('landmark'))];
        if ($data['label']==='' || $data['recipient_name']==='' || $data['address_line']==='' || strlen($data['phone'])<10 || strlen($data['phone'])>15) return redirect()->back()->withInput()->with('error','Lengkapi label, penerima, alamat, dan nomor HP 10–15 digit.');
        $zone=db_connect()->table('delivery_zones')->where('id',$data['delivery_zone_id'])->where('is_active',1)->get()->getRowArray();
        if($zone===null) return redirect()->back()->withInput()->with('error','Pilih zona antar yang tersedia.');
        $db=db_connect(); $id=(int)$this->request->getPost('address_id');
        if($id>0) $db->table('addresses')->where('id',$id)->where('user_id',(int)auth()->id())->update($data+['updated_at'=>date('Y-m-d H:i:s')]);
        else $db->table('addresses')->insert($data+['user_id'=>(int)auth()->id(),'is_default'=>false,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
        return redirect()->to(site_url('akun'))->with('success','Alamat disimpan.');
    }
    public function deleteAddress(int $id): mixed
    {
        db_connect()->table('addresses')->where('id',$id)->where('user_id',(int)auth()->id())->delete();
        return redirect()->to(site_url('akun'))->with('success','Alamat dihapus.');
    }
}