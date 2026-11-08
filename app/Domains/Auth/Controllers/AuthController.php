<?php

namespace App\Domains\Auth\Controllers;

use App\Shared\BaseController;
use App\Domains\Auth\Services\AuthService;

class AuthController extends BaseController
{
    protected $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('dashboard');
        }

        // Cegah caching halaman login agar token keamanan selalu sinkron
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', '0');

        $data = [
            'title' => 'Masuk'
        ];

        return view('auth/login', $data);
    }

    public function attemptLogin()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        try {
            $sessionData = $this->authService->authenticate($username, $password);
            $sessionData['isLoggedIn'] = true;

            session()->set($sessionData);

            log_audit('LOGIN', 'User', $sessionData['id'], 'Login: ' . $sessionData['username']);

            return redirect()->to('dashboard')->with('success', 'Selamat datang kembali, ' . $sessionData['name']);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function logout()
    {
        $isExpired = $this->request->getGet('expired') == '1';
        $userId = session()->get('id');
        $username = session()->get('username');

        if ($userId) {
            $reason = $isExpired ? 'Logout otomatis: Sesi berakhir karena tidak aktif' : 'Logout: ' . $username;
            log_audit('LOGOUT', 'User', $userId, $reason);
        }

        session()->destroy();

        if ($isExpired) {
            return redirect()->to('/login')->withCookies()->with('error', 'Sesi berakhir. Silakan login kembali.');
        }

        return redirect()->to('/login')->withCookies();
    }

    public function keepAlive()
    {
        if (! session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Sesi telah kedaluwarsa.'
            ]);
        }

        session()->set('last_active_time', time());

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Sesi berhasil diperpanjang.',
            'timestamp' => time()
        ]);
    }
}