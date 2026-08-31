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

class EmailController extends BaseController
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
            $search  = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');
            $disk_usage = $this->request->getGet('disk_usage');

            $data = $this->emailService->getEmailDashboardData($search, $bsre_status, $perPage, $disk_usage);

            $data['title']       = 'Email';
            $data['search']      = $search;
            $data['bsre_status'] = $bsre_status;
            $data['disk_usage']  = $disk_usage;
            $data['per_page']    = $perPage;

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
        $data['unit_kerja_options'] = $this->unitKerjaModel
            ->select('unit_kerja.*, parent.nama_unit_kerja as parent_name')
            ->join('unit_kerja as parent', 'parent.id = unit_kerja.parent_id', 'left')
            ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
            ->findAll();
        $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
        $data['title'] = 'Buat Akun Tunggal';
        return view('email/create', $data);
    }

    public function editProfile($username)
    {
        try {
            $data = $this->emailService->getEmailDetail($username);
            $data['unit_kerja_options'] = $this->unitKerjaModel
                ->select('unit_kerja.*, parent.nama_unit_kerja as parent_name')
                ->join('unit_kerja as parent', 'parent.id = unit_kerja.parent_id', 'left')
                ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
                ->findAll();
            $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
            $data['eselon_options'] = $this->eselonModel->orderBy('nama_eselon', 'ASC')->findAll();
            $data['title'] = 'Edit Profil';
            return view('email/edit_profile', $data);
        } catch (\Throwable $e) {
            return redirect()->to('email')->with('error', $e->getMessage());
        }
    }

    public function markPensiun($username)
    {
        try {
            $email = $this->emailModel->select('emails.*, unit_kerja.nama_unit_kerja as unit_kerja_name')
                                      ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                                      ->where('emails.user', $username)
                                      ->first();
            if (!$email) {
                throw new \Exception("Akun email tidak ditemukan.");
            }

            // 1. Mandatory cPanel API Suspend (Will throw Exception if fails)
            $cpanelApi = new \App\Shared\Libraries\CpanelApi();
            $cpanelApi->suspend_email_login($email['email']);

            // 2. Update DB & Trigger Soft Delete (Move to Trash)
            $model = new EmailModel();
            $model->update($email['id'], [
                'suspended_login' => 1,
                'pensiun_at'      => date('Y-m-d H:i:s'),
                'pimpinan'        => 0,
                'pimpinan_desa'   => 0,
            ]);

            // Move to Kotak Sampah (Soft Delete)
            $model->delete($email['id']);

            $this->clearEmailCaches();

            // 3. Audit Log
            helper('audit');
            log_audit('PENSIUN', 'Email', $email['id'], 'Akun ditandai pensiun / ditangguhkan: ' . $email['email']);

            // 4. Send Telegram Notification
            try {
                $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
                $builder->setTitle('AKUN EMAIL DITANGGUHKAN', '🚫')
                        ->addUserProfile(
                            $email['name'] ?? '',
                            !empty($email['nip']) ? 'NIP: ' . $email['nip'] : '',
                            $email['jabatan'] ?? '',
                            $email['unit_kerja_name'] ?? '',
                            $email['email']
                        );

                $telegram = new TelegramLibrary();
                $telegram->sendMessage($builder->build());
            } catch (\Throwable $te) {
                log_message('error', 'Failed to send Telegram notification for retirement: ' . $te->getMessage());
            }

            return redirect()->to('email')->with('success', 'Akun berhasil ditangguhkan.');
            
        } catch (\Throwable $e) {
            return redirect()->to('email/detail/' . $username)->with('error', 'Gagal memproses pensiun: ' . $e->getMessage());
        }
    }

    public function updateDetails($username)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('email/detail/' . $username)->with('error', 'Metode permintaan tidak valid.');
        }

        $newEmail = $this->request->getPost('email');
        $newUser  = explode('@', $newEmail)[0];

        $profileData = [
            'name'           => $this->request->getPost('name'),
            'gelar_depan'    => $this->request->getPost('gelar_depan'),
            'gelar_belakang' => $this->request->getPost('gelar_belakang'),
            'nik'            => $this->request->getPost('nik') ?: null,
            'nip'            => $this->request->getPost('nip') ?: null,
            'tempat_lahir'   => $this->request->getPost('tempat_lahir'),
            'pendidikan'     => $this->request->getPost('pendidikan'),
            'jabatan'        => mb_strtoupper($this->request->getPost('jabatan'), 'UTF-8'),
            'golongan'       => $this->request->getPost('golongan'),
            'pangkat_golruang' => $this->request->getPost('pangkat_golruang'),
            'pangkat_nama'   => $this->request->getPost('pangkat_nama'),
            'status_asn_id'  => $this->request->getPost('status_asn') ?: null,
            'eselon_id'      => $this->request->getPost('eselon') ?: null,
            'unit_kerja_id'  => $this->request->getPost('unit_kerja_id') ?: null,
            'pimpinan'       => $this->request->getPost('pimpinan') ? 1 : 0,
            'pimpinan_desa'  => $this->request->getPost('pimpinan_desa') ? 1 : 0,
            'tanggal_lahir'  => $this->request->getPost('tanggal_lahir') ?: null,
            'user'           => $newUser,
            'email'          => $newEmail,
        ];

        try {
            $newUser = $this->emailService->updateProfileDetails($username, $profileData);

            $this->clearEmailCaches();

            helper('audit');
            log_audit('UPDATE', 'Email', null, 'Profil diperbarui: ' . $newEmail);

            return redirect()->to('email/detail/' . $newUser)->with('success', 'Data profil berhasil diperbarui.');
        } catch (\Throwable $e) {
            log_message('error', 'Update error for ' . $username . ': ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    private function clearEmailCaches()
    {
        \App\Shared\Services\CacheService::invalidateDashboard();
    }

    public function editPassword($username)
    {
        try {
            $data = $this->emailService->getEmailDetail($username);
            $data['title'] = 'Edit Password';
            return view('email/edit_password', $data);
        } catch (\Throwable $e) {
            return redirect()->to('email')->with('error', $e->getMessage());
        }
    }

    public function updatePassword($username)
    {
        try {
            $password = $this->request->getPost('password');

            if (empty($password)) {
                throw new Exception('Password tidak boleh kosong.');
            }
            if (strlen($password) < 8) {
                throw new Exception('Password minimal 8 karakter.');
            }

            $this->emailService->updatePassword($username, $password);

            helper('audit');
            log_audit('CHANGE_PASSWORD', 'Email', null, 'Password diubah untuk akun: ' . $username . '@sinjaikab.go.id');

            return redirect()->to('email/detail/' . $username)->with('success', 'Password berhasil diperbarui.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function editPk($username)
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

    public function updatePk($username)
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
            $data['meta_type'] = 'profile';

            return view('email/verify', $data);
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
            $this->emailModel->delete($id, true); // True to purge from soft deletes

            // Send Telegram Notification
            try {
                $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
                $builder->setTitle('AKUN EMAIL DIHAPUS PERMANEN', '🔥')
                        ->addDivider()
                        ->addUserProfile(
                            $email['name'] ?? '',
                            !empty($email['nip']) ? 'NIP: ' . $email['nip'] : '',
                            '',
                            '',
                            $email['email']
                        );

                $telegram = new \App\Shared\Libraries\TelegramLibrary();
                $telegram->sendMessage($builder->build());
            } catch (\Throwable $te) {
                log_message('error', 'Failed to send Telegram notification for deletion: ' . $te->getMessage());
            }

            return redirect()->to('email')->with('success', 'Akun berhasil dihapus permanen.');
        } catch (\Throwable $e) {
            log_message('error', 'Failed to delete email: ' . $e->getMessage());
            $this->emailModel->delete($id, true); // True to purge from soft deletes
            return redirect()->to('email')->with('error', 'Gagal menghapus dari cPanel, namun berhasil dihapus dari database.');
        }
    }

    public function swapForm()
    {
        $data['title'] = 'Tukar Data Akun (Swap)';
        return view('email/swap_data', $data);
    }

    public function swapProcess()
    {
        $email1 = $this->request->getPost('email_1');
        $email2 = $this->request->getPost('email_2');
        
        try {
            $this->emailService->swapAccountData($email1, $email2);
            log_audit('SWAP_DATA', 'emails', null, "Tukar data profil antara $email1 dan $email2");
            return redirect()->to('email')->with('success', 'Data profil akun berhasil ditukar.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }
}
