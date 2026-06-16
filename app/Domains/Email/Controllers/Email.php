<?php

namespace App\Domains\Email\Controllers;

use App\Domains\Email\Services\EmailService;

use App\Domains\Email\Models\PkModel;

use App\Domains\Email\Models\EmailModel;

use App\Shared\BaseController;
use App\Shared\Models\AppSettingModel;
use App\Shared\Models\StatusAsnModel;
use App\Shared\Models\EselonModel;
use App\Domains\UnitKerja\Models\UnitKerjaModel;
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

            // Add secure hash for public verification based on NIK blind index
            $data['verification_hash'] = $data['email']['nik'] ?? null;

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

    public function mark_pensiun($username)
    {
        try {
            $email = $this->emailModel->select('emails.*, unit_kerja.nama_unit_kerja as unit_kerja_name')
                                      ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                                      ->where('emails.user', $username)
                                      ->first();
            if (!$email) {
                throw new \Exception("Akun email tidak ditemukan.");
            }

            $cpanelApi = new \App\Shared\Libraries\CpanelApi();
            
            // 1. Suspend in cPanel
            $cpanelApi->suspend_email_login($email['email']);

            // 2. Update DB: Set suspended and clear employee data
            $this->emailModel->update($email['id'], [
                'suspended_login' => 1,
                'pensiun_at' => date('Y-m-d H:i:s'),
                'unit_kerja_id' => null,
                'nik' => null,
                'nip' => null,
                'jabatan' => null,
                'golongan' => null,
                'pangkat_golruang' => null,
                'pangkat_nama' => null,
                'status_asn_id' => null,
                'eselon_id' => null,
                'bsre_status' => null,
                'pimpinan' => 0,
                'pimpinan_desa' => 0,
                'gelar_depan' => null,
                'gelar_belakang' => null,
                'tempat_lahir' => null,
                'tanggal_lahir' => null,
                'pendidikan' => null
            ]);

            // Trigger soft delete so it moves to Trash
            $this->emailModel->delete($email['id']);

            $this->clearEmailCaches();

            // 3. Send Telegram Notification
            try {
                $telegram = new TelegramLibrary();
                $msg = "♻️ <b>AKUN DIPINDAHKAN KE TEMPAT SAMPAH</b>\n";
                $msg .= "Seorang pegawai telah ditandai <b>PENSIUN / KELUAR</b>:\n";
                $msg .= "------------------------------------------\n\n";
                $msg .= "👤 " . ($email['name'] ?: '-') . " (" . ($email['nip'] ?: '-') . ")\n";
                $msg .= "🏛️ " . ($email['unit_kerja_name'] ?? '-') . "\n";
                $msg .= "📧 " . $email['email'] . "\n\n";
                $msg .= "⚠️ <i>Akses ditangguhkan (Soft Delete). Akun mengendap di Manajemen Sampah dan akan dihapus permanen dalam 30 hari.</i>";
                $telegram->sendMessage($msg);
            } catch (\Throwable $te) {
                log_message('error', 'Failed to send Telegram notification for retirement: ' . $te->getMessage());
            }

            return redirect()->to('email')->with('success', 'Akun telah ditandai sebagai Pensiun. Data dipindahkan ke Tempat Sampah dan akan dihapus permanen dalam 30 hari.');
            
        } catch (\Throwable $e) {
            return redirect()->to('email/detail/' . $username)->with('error', 'Gagal memproses pensiun: ' . $e->getMessage());
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
            'nik' => $this->request->getPost('nik') ?: null,
            'nip' => $this->request->getPost('nip') ?: null,
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            'pendidikan' => $this->request->getPost('pendidikan'),
            'jabatan' => mb_strtoupper($this->request->getPost('jabatan'), 'UTF-8'),
            'golongan' => $this->request->getPost('golongan'),
            'pangkat_golruang' => $this->request->getPost('pangkat_golruang'),
            'pangkat_nama' => $this->request->getPost('pangkat_nama'),
            'status_asn_id' => $this->request->getPost('status_asn') ?: null,
            'eselon_id' => $this->request->getPost('eselon') ?: null,
            'unit_kerja_id' => $this->request->getPost('unit_kerja_id') ?: null,
            'pimpinan' => $this->request->getPost('pimpinan') ? 1 : 0,
            'pimpinan_desa' => $this->request->getPost('pimpinan_desa') ? 1 : 0,
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir') ?: null,
            'user' => $newUser,
            'email' => $newEmail
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $sourceRecord = $this->emailModel->where('user', $username)->first();
            if (!$sourceRecord) throw new \Exception("Akun asal tidak ditemukan.");

            // 1. Handle username change in cPanel if needed
            if ($newUser !== $username) {
                $cpanelApi = new \App\Shared\Libraries\CpanelApi();
                $result = $cpanelApi->rename_email_account($username . '@sinjaikab.go.id', $newUser);
                if (!$result['success']) {
                    throw new \Exception('Gagal mengubah username di cPanel: ' . $result['message']);
                }
            }

            // 2. Always update the primary record first
            if ($this->emailModel->update($sourceRecord['id'], $profileData) === false) {
                $errors = $this->emailModel->errors();
                throw new \Exception("Gagal menyimpan data utama. " . implode(', ', $errors));
            }

            // 3. If a NIP is provided, ensure other records with the same NIP (if any) are also synced
            if (!empty($profileData['nip'])) {
                // Filter data to only sync personal info, NOT account-specific info to avoid UNIQUE errors
                $syncData = $profileData;
                unset($syncData['email'], $syncData['user'], $syncData['jabatan']);
                unset($syncData['unit_kerja_id'], $syncData['eselon_id']);
                unset($syncData['pimpinan'], $syncData['pimpinan_desa']);

                // We use the normalized nip hash to find others
                $cleanNip = str_replace([' ', '.', '-', '\''], '', $profileData['nip']);
                $nipHash = $cleanNip;
                
                // Exclude the current record to avoid redundant update
                $this->emailModel->where('nip', $nipHash)
                                 ->where('id !=', $sourceRecord['id'])
                                 ->set($syncData)
                                 ->update();
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                $error = $db->error();
                throw new \Exception('Gagal menyimpan data ke database. Detail: ' . ($error['message'] ?? 'Unknown SQL error'));
            }

            $this->clearEmailCaches();

            return redirect()->to('email/detail/' . $newUser)->with('success', 'Data profil berhasil diperbarui.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Update error for ' . $username . ': ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
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

            // CpanelApi returns raw response where status == 1 means success
            if (isset($result['status']) && $result['status'] == 1) {
                $this->emailModel->where('user', $username)->set(['password' => $password])->update();
                return redirect()->to('email/detail/' . $username)->with('success', 'Password berhasil diperbarui.');
            } else {
                $errorMsg = $result['errors'][0] ?? 'Gagal memperbarui password di cPanel.';
                return redirect()->back()->with('error', $errorMsg);
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

    public function profile($hash)
    {
        try {
            // Optimization: Directly query by nik (blind index)
            $email = $this->emailModel->where('nik', $hash)
                                      ->where('bsre_status', 'ISSUE')
                                      ->first();

            if (!$email) {
                throw new \Exception('Data identitas tidak ditemukan atau tidak valid.');
            }

            $data = $this->emailService->getEmailDetail($email['user']);
            $data['title'] = 'Verifikasi Akun';
            
            // SEO for Public Profile
            $data['meta_robots'] = 'noindex, follow'; 
            $data['meta_description'] = 'Verifikasi Identitas Digital Pegawai: ' . $data['email']['name'] . ' - ' . $data['email']['jabatan'];
            $data['meta_type'] = 'profile';

            return view('email/verifikasi', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            $data['title'] = 'Error Verifikasi';
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
            $this->emailModel->delete($id, true); // True to purge from soft deletes

            // Send Telegram Notification
            try {
                $telegram = new \App\Shared\Libraries\TelegramLibrary();
                $msg = "🔥 <b>PENGHAPUSAN AKUN PERMANEN</b>\n";
                $msg .= "Admin telah mengeksekusi Hapus Permanen (Hard Delete):\n";
                $msg .= "------------------------------------------\n\n";
                $msg .= "👤 " . ($email['name'] ?: '-') . " (" . ($email['nip'] ?: '-') . ")\n";
                $msg .= "📧 " . $email['email'] . "\n\n";
                $msg .= "⚠️ <i>Data telah dibumihanguskan dari Database maupun server cPanel. (Bypass Trash)</i>";
                $telegram->sendMessage($msg);
            } catch (\Throwable $te) {
                log_message('error', 'Failed to send Telegram notification for deletion: ' . $te->getMessage());
            }

            return redirect()->to('email')->with('success', 'Email account ' . $email['email'] . ' has been deleted successfully.');
        } catch (\Throwable $e) {
            log_message('error', 'Failed to delete email: ' . $e->getMessage());
            $this->emailModel->delete($id, true); // True to purge from soft deletes
            return redirect()->to('email')->with('error', 'Failed to delete email account from cPanel, but removed from local list.');
        }
    }
}
