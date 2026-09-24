<?php

namespace App\Domains\Auth\Controllers;

use App\Shared\BaseController;
use App\Domains\Auth\Models\UserModel;
use Exception;

class UserManagementController extends BaseController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data = [
            'title' => 'User Login',
            'users' => $this->userModel->findAll()
        ];
        return view('auth/user_index', $data);
    }

    public function add()
    {
        $data = [
            'title' => 'Tambah User'
        ];
        return view('auth/user_add', $data);
    }

    public function store()
    {
        $username = trim($this->request->getPost('username') ?? '');
        $name = trim($this->request->getPost('name') ?? '');
        
        $rules = [
            'username'   => 'required|min_length[3]|max_length[20]|is_unique[users.username]',
            'password'   => 'required|min_length[6]',
            'role'       => 'required|in_list[admin,super_admin]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validasi gagal: ' . implode(', ', $this->validator->getErrors()));
        }

        $this->userModel->insert([
            'username'   => $username,
            'name'       => $name ?: null,
            'password'   => $this->request->getPost('password'),
            'role'       => $this->request->getPost('role')
        ]);

        return redirect()->to('/auth/users')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/auth/users')->with('error', 'User tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit User',
            'user'  => $user
        ];
        return view('auth/user_edit', $data);
    }

    public function update($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/auth/users')->with('error', 'User tidak ditemukan.');
        }

        $rules = [
            'username'   => "required|min_length[3]|max_length[20]|is_unique[users.username,id,{$id}]",
            'role'       => 'required|in_list[admin,super_admin]'
        ];

        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[6]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validasi gagal: ' . implode(', ', $this->validator->getErrors()));
        }

        $name = trim($this->request->getPost('name') ?? '');

        $data = [
            'username'   => $this->request->getPost('username'),
            'name'       => $name ?: null,
            'role'       => $this->request->getPost('role')
        ];

        if ($this->request->getPost('password')) {
            $data['password'] = $this->request->getPost('password');
        }

        $this->userModel->update($id, $data);

        return redirect()->to('/auth/users')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function delete($id)
    {
        if (session()->get('id') == $id) {
            return redirect()->to('/auth/users')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $this->userModel->delete($id);
        return redirect()->to('/auth/users')->with('success', 'Pengguna berhasil dihapus.');
    }
}

