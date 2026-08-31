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

        $data = [
            'title' => 'Login'
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
        log_audit('LOGOUT', 'User', session()->get('id'), 'Logout: ' . session()->get('username'));
        session()->destroy();
        return redirect()->to('/login');
    }
}