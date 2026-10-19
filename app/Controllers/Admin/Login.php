<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Login extends BaseController
{
    public function index(): string
    {
        if (auth()->loggedIn() && auth()->user()->inGroup('admin')) {
            return redirect()->to(site_url('admin'));
        }
        return view('admin/login', ['title' => 'Masuk Admin KUBO']);
    }
}