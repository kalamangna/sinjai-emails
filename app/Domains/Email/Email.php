<?php

namespace App\Domains\Email;

use App\Shared\BaseController;
use App\Shared\Models\AppSettingModel;
use App\Shared\Models\StatusAsnModel;
use App\Shared\Models\EselonModel;
use App\Domains\UnitKerja\UnitKerjaModel;
use App\Shared\Libraries\TelegramLibrary;
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
            
            // Ensure options includes non_tte explicitly if not handled by labels
            $options = $data['bsre_status_labels'] ?? [];
            if (!isset($options['non_tte'])) {
                $options['non_tte'] = 'NON_TTE';
            }
            $data['bsre_status_options'] = $options;

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

            $appSettingModel = new \App\Shared\Models\AppSettingModel();
            $last_sync_tte = $appSettingModel->where('key', 'last_sync_tte')->first();
            $last_sync_pegawai = $appSettingModel->where('key', 'last_sync_pegawai')->first();

            $data['last_sync_tte'] = $last_sync_tte['value'] ?? null;
            $data['last_sync_pegawai'] = $last_sync_pegawai['value'] ?? null;

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

    public function edit_profile($username)
    {
        try {
            $data = $this->emailService->getEmailDetail($username);
            $data['unit_kerja_options'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
            $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
            $data['eselon_options'] = $this->eselonModel->orderBy('nama_eselon', 'ASC')->findAll();
            $data['title'] = 'Edit Profil';
            return view('email/edit_profile', $data);
        } catch (\Throwable $e) {
            return redirect()->to('email')->with('error', $e->getMessage());
        }
    }

    public function update_details($username)
    {
        try {
            $sourceRecord = $this->emailModel->where('user', $username)->first();
            if (!$sourceRecord) throw new Exception('Account not found.');

            $newUser = $this->request->getPost('user');
            $profileData = [
                'name' => $this->request->getPost('name'),
                'nik' => $this->request->getPost('nik'),
                'nip' => $this->request->getPost('nip'),
                'user' => $newUser,
                'email' => $newUser . '@sinjaikab.go.id',
                'jabatan' => $this->request->getPost('jabatan'),
                'unit_kerja_id' => $this->request->getPost('unit_kerja_id'),
                'status_asn_id' => $this->request->getPost('status_asn_id'),
                'eselon_id' => $this->request->getPost('eselon_id') ?: null,
                'pimpinan' => $this->request->getPost('pimpinan') ?? 0,
                'pimpinan_desa' => $this->request->getPost('pimpinan_desa') ?? 0,
            ];

            // 1. If username changed, update in cPanel first
            if ($newUser !== $username) {
                $cpanelApi = new \App\Shared\Libraries\CpanelApi();
                $result = $cpanelApi->rename_email_account($username . '@sinjaikab.go.id', $newUser);
                if (!$result['success']) {
                    return redirect()->back()->withInput()->with('error', 'Gagal mengubah username di cPanel: ' . $result['message']);
                }
            }

            // 2. Update all records with this NIP (including source)
            if (!empty($profileData['nip'])) {
                $this->emailModel->where('nip', $profileData['nip'])->set($profileData)->update();
            } else {
                $this->emailModel->update($sourceRecord['id'], $profileData);
            }

            $this->clearEmailCaches();

            return redirect()->to('email/detail/' . $newUser)->with('success', 'Data profil berhasil diperbarui.');
        } catch (\Throwable $e) {
            log_message('error', 'Database error during email details update: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    private function clearEmailCaches()
    {
        $cache = \Config\Services::cache();
        $cache->delete('dashboard_summary_data_v3');
        $cache->delete('email_dashboard_summary');
    }

    public function edit_password($username)
    {
        try {
            $data = $this->emailService->getEmailDetail($username);
            $data['title'] = 'Edit Password';
            return view('email/edit_password', $data);
        } catch (\Throwable $e) {
            return redirect()->to('email')->with('error', $e->getMessage());
        }
    }

    public function update_password($username)
    {
        try {
            $password = $this->request->getPost('password');
            if (empty($password)) throw new Exception('Password cannot be empty.');

            $cpanelApi = new \App\Shared\Libraries\CpanelApi();
            $result = $cpanelApi->change_password($username . '@sinjaikab.go.id', $password);

            if ($result['success']) {
                $this->emailModel->where('user', $username)->set(['password' => $password])->update();
                return redirect()->to('email/detail/' . $username)->with('success', 'Password berhasil diperbarui.');
            } else {
                return redirect()->back()->with('error', 'Gagal memperbarui password di cPanel: ' . $result['message']);
            }
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit_pk($username)
    {
        try {
            $data = $this->emailService->getEmailDetail($username);
            $data['title'] = 'Edit PK';
            return view('email/edit_pk', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            $data['title'] = 'Edit PK';
            return view('email/error', $data);
        }
    }

    public function update_pk($username)
    {
        try {
            $email = $this->emailModel->where('user', $username)->first();
            $pkData = [
                'email' => $email['email'],
                'nomor' => $this->request->getPost('nomor'),
                'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
                'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            ];

            $this->pkModel->savePk($pkData);
            return redirect()->to('email/detail/' . $username)->with('success', 'Data Perjanjian Kerja berhasil diperbarui.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function mark_pensiun($username)
    {
        try {
            $email = $this->emailModel->where('user', $username)->first();
            if (!$email) throw new Exception('Account not found.');

            // 1. Suspend in cPanel
            $cpanelApi = new \App\Shared\Libraries\CpanelApi();
            $suspendResult = $cpanelApi->suspend_email_account($email['email']);

            if (!$suspendResult['success']) {
                throw new Exception('Gagal menangguhkan akun di cPanel: ' . $suspendResult['message']);
            }

            // 2. Mark as retired and suspended in DB
            $this->emailModel->update($email['id'], [
                'pensiun_at' => date('Y-m-d H:i:s'),
                'suspended_login' => 1
            ]);

            $this->clearEmailCaches();

            helper('audit');
            log_audit('MARK_PENSIUN', 'Email', $email['id'], 'Account marked as retired and suspended: ' . $email['email']);

            return redirect()->to('email/detail/' . $username)->with('success', 'Akun berhasil ditandai sebagai Pensiun dan telah ditangguhkan.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
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
