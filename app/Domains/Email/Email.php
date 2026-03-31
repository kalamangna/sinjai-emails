<?php

namespace App\Domains\Email;

use App\Shared\BaseController;
use App\Shared\Models\AppSettingModel;
use App\Shared\Models\StatusAsnModel;
use App\Shared\Models\EselonModel;
use App\Domains\UnitKerja\UnitKerjaModel;
use App\Shared\Services\SyncService;
use Exception;

class Email extends BaseController
{
    private $emailModel;
    private $pkModel;
    private $eselonModel;
    private $unitKerjaModel;
    private $statusAsnModel;
    private $syncService;
    private $emailService;

    public function __construct()
    {
        $this->emailModel = new EmailModel();
        $this->pkModel = new PkModel();
        $this->eselonModel = new EselonModel();
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->statusAsnModel = new StatusAsnModel();
        $this->syncService = new SyncService();
        $this->emailService = new EmailService();
    }

    public function index()
    {
        try {
            $perPage = $this->request->getGet('per_page') ?? 100;
            $search = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');

            $data = $this->emailService->getEmailDashboardData($search, $bsre_status, $perPage);

            $appSettingModel = new AppSettingModel();
            $lastSyncSetting = $appSettingModel->where('key', 'last_sync_time')->first();

            $data['title'] = 'Email';
            $data['search'] = $search;
            $data['bsre_status'] = $bsre_status;
            $data['per_page'] = $perPage;
            $data['last_sync_time'] = $lastSyncSetting['value'] ?? null;
            $data['bsre_status_options'] = $data['bsre_status_labels'];

            return view('email/index', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function detail($username)
    {
        try {
            $data = $this->emailService->getEmailDetail($username);
            $data['title'] = 'Detail Akun';
            $data['back_url'] = site_url('email');

            // Add secure hash for public verification based on NIK
            $data['verification_hash'] = !empty($data['email']['nik']) 
                ? md5($data['email']['nik'] . 'sinjai_secure_salt') 
                : null;

            return view('email/detail', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            $data['title'] = 'Detail Akun';
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function create()
    {
        $data['unit_kerja_options'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
        $data['title'] = 'Buat Akun Tunggal';
        return view('email/create', $data);
    }

    public function sync()
    {
        try {
            $result = $this->syncService->syncFromCpanel();
            if (is_cli()) {
                return $result;
            }

            if ($result['success']) {
                return redirect()->to('email')->with('success', $result['message']);
            } else {
                return redirect()->to('email')->with('error', $result['message']);
            }
        } catch (\Throwable $e) {
            $message = 'Failed to synchronize: ' . $e->getMessage();
            if (is_cli()) {
                return ['success' => false, 'message' => $message];
            }
            return redirect()->to('email')->with('error', $message);
        }
    }

    public function edit_profile($username)
    {
        try {
            $data = $this->emailService->getEmailDetail($username);
            $data['title'] = 'Edit Profil';
            return view('email/edit_profile', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function update_details($username)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('email/detail/' . $username)->with('error', 'Metode permintaan tidak valid.');
        }

        $newEmail = $this->request->getPost('email');
        $emailParts = explode('@', $newEmail);
        $newUser = $emailParts[0];

        $profileData = [
            'name' => $this->request->getPost('name'),
            'gelar_depan' => $this->request->getPost('gelar_depan'),
            'gelar_belakang' => $this->request->getPost('gelar_belakang'),
            'nik' => $this->request->getPost('nik'),
            'nip' => $this->request->getPost('nip'),
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            'pendidikan' => $this->request->getPost('pendidikan'),
            'jabatan' => $this->request->getPost('jabatan'),
            'golongan' => $this->request->getPost('golongan'),
            'pangkat_golruang' => $this->request->getPost('pangkat_golruang'),
            'pangkat_nama' => $this->request->getPost('pangkat_nama'),
            'status_asn_id' => $this->request->getPost('status_asn') ?: null,
            'eselon_id' => $this->request->getPost('eselon') ?: null,
            'unit_kerja_id' => $this->request->getPost('unit_kerja_id') ?: null,
            'pimpinan' => $this->request->getPost('pimpinan'),
            'pimpinan_desa' => $this->request->getPost('pimpinan_desa'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir') ?: null,
        ];

        try {
            $sourceRecord = $this->emailModel->where('user', $username)->first();
            $targetRecord = $this->emailModel->where('email', $newEmail)->first();

            if (!$sourceRecord) throw new \Exception("Akun asal tidak ditemukan.");
            if (!$targetRecord) throw new \Exception("Akun tujuan tidak ditemukan.");

            if ($sourceRecord['email'] !== $newEmail) {
                $emptyData = array_fill_keys(array_keys($profileData), null);
                $emptyData['pimpinan'] = 0;
                $emptyData['pimpinan_desa'] = 0;
                $this->emailModel->update($sourceRecord['id'], $emptyData);
                $this->emailModel->update($targetRecord['id'], $profileData);
                $this->pkModel->where('email', $sourceRecord['email'])->set(['email' => $newEmail])->update();
            } else {
                $this->emailModel->update($sourceRecord['id'], $profileData);
            }

            return redirect()->to('email/detail/' . $newUser)->with('success', 'Data profil berhasil diperbarui.');
        } catch (\Throwable $e) {
            log_message('error', 'Database error during email details update: ' . $e->getMessage());
            return redirect()->to('email/detail/' . $username)->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function edit_password($username)
    {
        try {
            $data = $this->emailService->getEmailDetail($username);
            $data['title'] = 'Edit Password';
            return view('email/edit_password', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function update_password($username)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('email/detail/' . $username)->with('error', 'Metode permintaan tidak valid.');
        }

        $newPassword = $this->request->getPost('password');
        if (empty($newPassword)) {
            return redirect()->to('email/edit_password/' . $username)->with('error', 'Password tidak boleh kosong.');
        }

        try {
            $this->emailService->updatePassword($username, $newPassword);
            return redirect()->to('email/detail/' . $username)->with('success', 'Password berhasil diperbarui.');
        } catch (\Throwable $e) {
            log_message('error', 'Error updating password: ' . $e->getMessage());
            return redirect()->to('email/edit_password/' . $username)->with('error', 'Gagal memperbarui password: ' . $e->getMessage());
        }
    }

    public function edit_pk($username)
    {
        try {
            $data = $this->emailService->getEmailDetail($username);
            $data['title'] = 'Edit PK';
            $data['back_url'] = site_url('email/detail/' . $username);
            return view('email/edit_pk', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            $data['title'] = 'Edit PK';
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function update_pk($username)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('email/detail/' . $username)->with('error', 'Metode permintaan tidak valid.');
        }

        $email_detail = $this->emailModel->where('user', $username)->first();
        if (!$email_detail) {
            return redirect()->to('email')->with('error', 'Email tidak ditemukan.');
        }

        $updateData = [
            'nomor' => $this->request->getPost('nomor'),
            'status_asn_id' => $email_detail['status_asn_id'],
            'gaji_nominal' => str_replace(['.', ','], '', $this->request->getPost('gaji_nominal')),
            'gaji_terbilang' => $this->request->getPost('gaji_terbilang'),
            'tanggal_kontrak_awal' => $this->request->getPost('tanggal_kontrak_awal'),
            'tanggal_kontrak_akhir' => $this->request->getPost('tanggal_kontrak_akhir'),
        ];

        try {
            $pk = $this->pkModel->where('email', $email_detail['email'])->first();
            if ($pk) {
                $this->pkModel->update($pk['id'], $updateData);
            } else {
                $updateData['email'] = $email_detail['email'];
                $this->pkModel->insert($updateData);
            }
            return redirect()->to('email/detail/' . $username)->with('success', 'Data PK berhasil diperbarui.');
        } catch (\Throwable $e) {
            log_message('error', 'Error updating PK: ' . $e->getMessage());
            return redirect()->to('email/edit_pk/' . $username)->with('error', 'Gagal memperbarui data PK: ' . $e->getMessage());
        }
    }

    public function profile($hash)
    {
        try {
            // Find user by matching the calculated hash against NIK
            $emails = $this->emailModel->where('bsre_status', 'ISSUE')->where('nik !=', null)->findAll();
            $found_user = null;

            foreach ($emails as $email) {
                if (md5($email['nik'] . 'sinjai_secure_salt') === $hash) {
                    $found_user = $email['user'];
                    break;
                }
            }

            if (!$found_user) {
                throw new \Exception('Data identitas tidak ditemukan atau tidak valid.');
            }

            $data = $this->emailService->getEmailDetail($found_user);
            $data['title'] = 'Verifikasi Akun';
            return view('email/verifikasi', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            $data['title'] = 'Verifikasi Akun';
            return view('email/error', $data);
        }
    }

    public function delete($id)
    {
        try {
            $cpanelApi = new \App\Shared\Libraries\CpanelApi();
            $email = $this->emailModel->find($id);
            if (!$email) return redirect()->to('email')->with('error', 'Email account not found.');
            $cpanelApi->delete_email_account($email['email']);
            $this->emailModel->delete($id);
            return redirect()->back()->with('success', 'Email account ' . $email['email'] . ' has been deleted successfully.');
        } catch (\Throwable $e) {
            log_message('error', 'Failed to delete email: ' . $e->getMessage());
            $this->emailModel->delete($id);
            return redirect()->back()->with('error', 'Failed to delete email account from cPanel, but removed from local list.');
        }
    }
}
