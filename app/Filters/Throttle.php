<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class Throttle implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $throttler = Services::throttler();

        // Allow 120 requests per minute per IP Address
        // This is generous enough for normal use but strict enough to prevent rapid bot scraping
        if ($throttler->check(md5($request->getIPAddress()), 120, MINUTE) === false) {
            return Services::response()->setStatusCode(429)->setJSON([
                'success' => false,
                'message' => 'Terlalu banyak permintaan (Rate limit exceeded). Harap tunggu beberapa saat.'
            ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
