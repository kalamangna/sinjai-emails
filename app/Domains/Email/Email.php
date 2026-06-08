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

    public function ambiguous_list()
    {
        $emailModel = new \App\Domains\Email\EmailModel();
        
        // Fetch all accounts that MUST have a NIP (ASN or Pimpinan)
        // We let the Model's afterFind callback decrypt the data automatically
        $all_target_emails = $emailModel->select('emails.id, emails.user, emails.name, emails.email, emails.nip, emails.jabatan, unit_kerja.nama_unit_kerja')
                                       ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                                       ->groupStart()
                                           ->whereIn('emails.status_asn_id', [1, 2]) // PNS or PPPK
                                           ->orWhere('emails.pimpinan', 1)
                                           ->orWhere('emails.pimpinan_desa', 1)
                                       ->groupEnd()
                                       ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
                                       ->orderBy('emails.name', 'ASC')
                                       ->findAll();

        $ambiguous_emails = [];

        foreach ($all_target_emails as $email) {
            $nip = $email['nip'] ?? '';
            
            // Clean it just in case there are spaces
            $cleanNip = str_replace([' ', '.', '-', '\''], '', $nip);

            // A valid NIP must be exactly 18 digits.
            // If it's empty, or if its length is not 18, it's either missing or corrupted (e.g. a decrypted base64 hash)
            if (empty($cleanNip) || strlen($cleanNip) !== 18 || !is_numeric($cleanNip)) {
                $ambiguous_emails[] = $email;
            }
        }

        $data = [
            'title' => 'Data Ambigu (Butuh Perbaikan NIP)',
            'emails' => $ambiguous_emails
        ];

        return view('email/ambiguous', $data);
    }

    public function detail($username)
    {
        try {
            $data = $this->emailService->getEmailDetail($username);
            $data['title'] = 'Detail Akun';
            $data['back_url'] = site_url('email');

            // Add secure hash for public verification based on NIK blind index
            $data['verification_hash'] = $data['email']['nik_hash'] ?? null;

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

            $this->clearEmailCaches();

            // 3. Send Telegram Notification
            try {
                $telegram = new TelegramLibrary();
                $msg = "🚪 <b>PEMBERSIHAN AKUN (MANUAL)</b>\n";
                $msg .= "Seorang pegawai telah ditandai sebagai <b>PENSIUN</b> oleh Admin:\n";
                $msg .= "------------------------------------------\n\n";
                $msg .= "👤 " . ($email['name'] ?: '-') . " (" . ($email['nip'] ?: '-') . ")\n";
                $msg .= "🏛️ " . ($email['unit_kerja_name'] ?? '-') . "\n";
                $msg .= "📧 " . $email['email'] . "\n\n";
                $msg .= "⚠️ <i>Akses login ditangguhkan. Akun akan dihapus permanen dalam 30 hari.</i>";
                $telegram->sendMessage($msg);
            } catch (\Throwable $te) {
                log_message('error', 'Failed to send Telegram notification for retirement: ' . $te->getMessage());
            }

            return redirect()->to('email/detail/' . $username)->with('success', 'Akun telah ditandai sebagai Pensiun. Data pegawai telah dihapus, akses login ditangguhkan, dan akun akan dihapus permanen dalam 30 hari.');
            
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
            'nik' => $this->request->getPost('nik'),
            'nip' => $this->request->getPost('nip'),
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            'pendidikan' => $this->request->getPost('pendidikan'),
            'jabatan' => mb_strtoupper($this->request->getPost('jabatan'), 'UTF-8'),
            'golongan' => $this->request->getPost('golongan'),
            'pangkat_golruang' => $this->request->getPost('pangkat_golruang'),
            'pangkat_nama' => $this->request->getPost('pangkat_nama'),
            'status_asn_id' => $this->request->getPost('status_asn') ?: null,
            'eselon_id' => $this->request->getPost('eselon') ?: null,
            'unit_kerja_id' => $this->request->getPost('unit_kerja_id') ?: null,
            'pimpinan' => $this->request->getPost('pimpinan'),
            'pimpinan_desa' => $this->request->getPost('pimpinan_desa'),
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
            $this->emailModel->update($sourceRecord['id'], $profileData);

            // 3. If a NIP is provided, ensure other records with the same NIP (if any) are also synced
            if (!empty($profileData['nip'])) {
                // We use the normalized nip hash to find others
                $cleanNip = str_replace([' ', '.', '-', '\''], '', $profileData['nip']);
                $nipHash = hash('sha256', $cleanNip);
                
                // Exclude the current record to avoid redundant update, though harmless
                $this->emailModel->where('nip_hash', $nipHash)
                                 ->where('id !=', $sourceRecord['id'])
                                 ->set($profileData)
                                 ->update();
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Gagal menyimpan data ke database.');
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

    public function profile($hash)
    {
        try {
            // Optimization: Directly query by nik_hash (blind index)
            $email = $this->emailModel->where('nik_hash', $hash)
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
            $this->emailModel->delete($id);
            return redirect()->back()->with('success', 'Email account ' . $email['email'] . ' has been deleted successfully.');
        } catch (\Throwable $e) {
            log_message('error', 'Failed to delete email: ' . $e->getMessage());
            $this->emailModel->delete($id);
            return redirect()->back()->with('error', 'Failed to delete email account from cPanel, but removed from local list.');
        }
    }
}
