<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\UserModel;
use App\Shared\Libraries\PegawaiApi;
use Exception;

class AuthService
{
    protected $userModel;
    protected $pegawaiApi;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->pegawaiApi = new PegawaiApi();
    }

    /**
     * Otentikasi pengguna menggunakan verifikasi lokal dan eksternal (SSO).
     * Jika sukses, mengembalikan data user untuk disimpan di session.
     * Jika gagal, melempar Exception dengan pesan kesalahan.
     *
     * @param string $username
     * @param string $password
     * @return array
     * @throws Exception
     */
    public function authenticate(string $username, string $password): array
    {
        // 1. Mandatory Local Registration Check
        $user = $this->userModel->where('username', $username)->first();
        if (!$user) {
            throw new Exception('Akun Anda belum didaftarkan sebagai Administrator. Silakan hubungi tim IT.');
        }

        // 2. Try External API Authentication first
        $externalAuth = $this->pegawaiApi->authenticate($username, $password);
        
        $isAuthSuccessful = false;
        
        if ($externalAuth['success']) {
            $isAuthSuccessful = true;
            
            // Sync fresh name from API on successful SSO login
            $freshData = $this->pegawaiApi->getPegawaiData($username);
            if ($freshData['success'] && !empty($freshData['data'])) {
                $source = (is_array($freshData['data']) && isset($freshData['data'][0])) ? $freshData['data'][0] : $freshData['data'];
                $newName = $source['nama'] ?? ($source['name'] ?? null);
                if ($newName) {
                    $this->userModel->update($user['id'], ['name' => $newName]);
                    $user['name'] = $newName; // Update current variable for session
                }
            }
        } else {
            // 3. Fallback to Local Password Verification (if local password exists)
            if (!empty($user['password']) && password_verify($password, $user['password'])) {
                $isAuthSuccessful = true;
            }
        }

        if (!$isAuthSuccessful) {
            throw new Exception('Username atau password salah.');
        }

        return [
            'id'       => $user['id'],
            'username' => $user['username'],
            'name'     => $user['name'] ?? $user['username'],
            'role'     => $user['role'],
        ];
    }
}
