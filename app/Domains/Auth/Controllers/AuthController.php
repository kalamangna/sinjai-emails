<?php

namespace App\Domains\Auth\Controllers;

use App\Shared\BaseController;
use App\Domains\Auth\Models\UserModel;
use App\Shared\Libraries\PegawaiApi;
use CodeIgniter\Controller;

class AuthController extends BaseController
{
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
        $userModel = new UserModel();
        $pegawaiApi = new PegawaiApi();
        
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // 1. Mandatory Local Registration Check
        $user = $userModel->where('username', $username)->first();
        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Akun Anda belum didaftarkan sebagai Administrator. Silakan hubungi tim IT.');
        }

        // 2. Try External API Authentication first
        $externalAuth = $pegawaiApi->authenticate($username, $password);
        
        $isAuthSuccessful = false;
        
        if ($externalAuth['success']) {
            $isAuthSuccessful = true;
            
            // Sync fresh name from API on successful SSO login
            $freshData = $pegawaiApi->getPegawaiData($username);
            if ($freshData['success'] && !empty($freshData['data'])) {
                $source = (is_array($freshData['data']) && isset($freshData['data'][0])) ? $freshData['data'][0] : $freshData['data'];
                $newName = $source['nama'] ?? ($source['name'] ?? null);
                if ($newName) {
                    $userModel->update($user['id'], ['name' => $newName]);
                    $user['name'] = $newName; // Update current variable for session
                }
            }
        } else {
            // 3. Fallback to Local Password Verification (if local password exists)
            if (!empty($user['password']) && password_verify($password, $user['password'])) {
                $isAuthSuccessful = true;
            }
        }

        if ($isAuthSuccessful) {
            $sessionData = [
                'id'         => $user['id'],
                'username'   => $user['username'],
                'name'       => $user['name'] ?? $user['username'],
                'role'       => $user['role'],
                'isLoggedIn' => true,
            ];

            session()->set($sessionData);

            log_audit('LOGIN', 'User', $user['id'], 'User login berhasil: ' . $user['username']);

            return redirect()->to('dashboard')->with('success', 'Selamat datang kembali, ' . ($user['name'] ?? $user['username']));
        }

        return redirect()->back()->withInput()->with('error', 'Username atau password salah.');
    }

    public function logout()
    {
        log_audit('LOGOUT', 'User', session()->get('id'), 'User melakukan logout: ' . session()->get('username'));
        session()->destroy();
        return redirect()->to('/login');
    }
}