<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminOnly implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! auth()->loggedIn()) {
            session()->setTempdata('beforeLoginUrl', current_url(), 300);
            return redirect()->to(site_url('admin/login'));
        }
        if (! auth()->user()->inGroup('admin')) {
            return service('response')->setStatusCode(403)->setBody('Akses admin ditolak.');
        }
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}