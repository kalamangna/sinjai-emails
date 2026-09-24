<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\UserModel;
use Exception;

class AuthService
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Otentikasi pengguna menggunakan verifikasi password lokal.
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
        $user = $this->userModel->where('username', $username)->first();
        if (!$user) {
            throw new Exception('Username atau password salah.');
        }

        if (empty($user['password']) || !password_verify($password, $user['password'])) {
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

