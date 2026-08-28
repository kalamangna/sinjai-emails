<?php

namespace App\Domains\Auth\Controllers;

use App\Shared\BaseController;
use App\Domains\Auth\Models\UserModel;
use App\Shared\Libraries\PegawaiApi;
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
        $name = $this->request->getPost('name');
        
        $rules = [
            'username'   => 'required|min_length[3]|max_length[20]|is_unique[users.username]',
            'role'       => 'required|in_list[admin,super_admin]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validasi gagal: ' . implode(', ', $this->validator->getErrors()));
        }

        $this->userModel->insert([
            'username'   => $username,
            'name'       => $name ?: null,
            'password'   => null,
            'role'       => $this->request->getPost('role')
        ]);

        return redirect()->to('/auth/users')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function checkNip()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $nip = trim($this->request->getPost('nip') ?? '');
        if (empty($nip)) {
            return $this->response->setJSON(['success' => false, 'message' => 'NIP wajib diisi.']);
        }

        $pegawaiApi = new PegawaiApi();
        $result = $pegawaiApi->getPegawaiData($nip);

        if ($result['success'] && !empty($result['data'])) {
            $source = (is_array($result['data']) && isset($result['data'][0])) ? $result['data'][0] : $result['data'];
            
            $hasActualData = isset($source['nama']) || isset($source['name']) || isset($source['jabatan_nama']);

            if (!$hasActualData) {
                return $this->response->setJSON(['success' => false, 'message' => 'Data pegawai tidak ditemukan di API.']);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'nama' => $source['nama'] ?? ($source['name'] ?? 'PEGAWAI')
                ]
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Gagal mengambil data dari API: ' . ($result['message'] ?? 'Unknown error')]);
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

        $data = [
            'username'   => $this->request->getPost('username'),
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
