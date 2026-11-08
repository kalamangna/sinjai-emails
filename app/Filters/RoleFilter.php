<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $role = $session->get('role');

        if (! in_array($role, $arguments)) {
            if ($request->isAJAX()) {
                return service('response')->setJSON([
                    'success' => false, 
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk aksi ini.'
                ])->setStatusCode(403);
            }
            return redirect()->back()->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk aksi ini.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}