<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Login extends BaseController
{
    public function index(): mixed
    {
        if (auth()->loggedIn()) {
            if (auth()->user()->inGroup('admin')) {
                return redirect()->to(site_url('admin'));
            }

            return service('response')->setStatusCode(403)->setBody('Akses admin ditolak. Keluar dari akun pelanggan terlebih dahulu.');
        }

        session()->setTempdata('beforeLoginUrl', site_url('admin'), 300);

        return view('admin/login', ['title' => 'Masuk Admin KUBO']);
    }
}
