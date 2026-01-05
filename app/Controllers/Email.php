<?php

namespace App\Controllers;

use App\Libraries\CpanelApi;
use App\Models\EmailModel;
use App\Models\AppSettingModel;
use App\Models\UnitKerjaModel;
use App\Models\StatusAsnModel;
use App\Models\EselonModel;
use CodeIgniter\Controller;
use Dompdf\Dompdf;
use App\Models\PkModel;
use Dompdf\Options;
use Exception;

class Email extends BaseController
{
    private $cpanelApi;
    private $emailModel;
    private $appSettingModel;
    private $unitKerjaModel;
    private $pkModel; // Add this
    private $statusAsnModel;
    private $eselonModel;

    public function __construct()
    {
        $this->cpanelApi = new CpanelApi();
        $this->emailModel = new EmailModel();
        $this->appSettingModel = new AppSettingModel();
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->pkModel = new PkModel(); // Add this
        $this->statusAsnModel = new StatusAsnModel();
        $this->eselonModel = new EselonModel();
    }

    public function index()
    {
        // load time helper
        helper('time');

        try {
            $perPage = $this->request->getGet('per_page') ?? 100;
            $search = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');

            $builder = $this->emailModel;

            if (!empty($search)) {
                $builder->groupStart()
                    ->like('email', $search)
                    ->orLike('name', $search)
                    ->orLike('nik', $search)
                    ->orLike('nip', $search)
                    ->groupEnd();
            }

            if ($bsre_status) {
                if ($bsre_status === 'not_synced') {
                    $builder->groupStart()
                        ->where('bsre_status', null)
                        ->orWhere('bsre_status', '')
                        ->groupEnd();
                } else {
                    $builder->where('bsre_status', $bsre_status);
                }
            }

            // Default sorting
            $builder->orderBy('mtime', 'DESC');

            $emails = $builder->paginate($perPage);
            $pager = $builder->pager;

            $counts = $this->emailModel->allowCallbacks(false)->select('COUNT(*) as total_emails, SUM(CASE WHEN suspended_login = 0 THEN 1 ELSE 0 END) as active_count, SUM(CASE WHEN suspended_login = 1 THEN 1 ELSE 0 END) as suspended_count')->first();

            $lastSync = $this->appSettingModel->where('key', 'last_sync_time')->first();

            // Fetch all parent unit_kerja
            $parentUnitKerjaList = $this->unitKerjaModel->where('parent_id IS NULL')->orderBy('nama_unit_kerja', 'ASC')->findAll();

            // Calculate email count for each parent unit (including children)
            $unitKerjaList = [];
            foreach ($parentUnitKerjaList as $parentUnit) {
                $parentId = $parentUnit['id'];
                $childrenIds = $this->unitKerjaModel->where('parent_id', $parentId)->findColumn('id');
                $allUnitIds = array_merge([$parentId], $childrenIds);

                $emailCount = $this->emailModel->allowCallbacks(false)->whereIn('unit_kerja_id', $allUnitIds)->countAllResults();

                $parentUnit['email_count'] = $emailCount;
                $unitKerjaList[] = $parentUnit;
            }

            // --- Status ASN Statistics ---
            $allStatusAsnOptions = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
            $statusAsnCounts = [];
            foreach ($allStatusAsnOptions as $option) {
                $count = $this->emailModel->allowCallbacks(false)
                    ->where('status_asn_id', $option['id'])
                    ->countAllResults();
                $statusAsnCounts[] = [
                    'id' => $option['id'],
                    'name' => $option['nama_status_asn'],
                    'count' => $count
                ];
            }

            // --- Eselon Statistics ---
            $allEselonOptions = $this->eselonModel->orderBy('nama_eselon', 'ASC')->findAll();
            $eselonCounts = [];
            foreach ($allEselonOptions as $option) {
                $count = $this->emailModel->allowCallbacks(false)
                    ->where('eselon_id', $option['id'])
                    ->countAllResults();
                $eselonCounts[] = [
                    'id' => $option['id'],
                    'name' => $option['nama_eselon'],
                    'count' => $count
                ];
            }

            // --- BSrE Status Statistics ---
            $bsreStatusCounts = [];
            $rawBsreCounts = $this->emailModel->allowCallbacks(false)
                ->select('bsre_status, COUNT(*) as count')
                ->groupBy('bsre_status')
                ->findAll();

            // Define BSrE status labels for display
            $bsre_status_labels = [
                'ISSUE' => 'Sertifikat Aktif / Siap TTE',
                'EXPIRED' => 'Masa Berlaku Habis',
                'RENEW' => 'Proses Pembaruan',
                'WAITING_FOR_VERIFICATION' => 'Menunggu Verifikasi',
                'NEW' => 'Belum Aktivasi',
                'NO_CERTIFICATE' => 'Belum Ada Sertifikat',
                'NOT_REGISTERED' => 'Pengguna Tidak Terdaftar',
                'SUSPEND' => 'Akun Ditangguhkan',
                'REVOKE' => 'Sertifikat Dicabut'
            ];

            $notSyncedCount = 0;
            foreach ($rawBsreCounts as $row) {
                if (empty($row['bsre_status'])) {
                    $notSyncedCount += $row['count'];
                } else {
                    $bsreStatusCounts[] = [
                        'status' => $row['bsre_status'],
                        'label' => $bsre_status_labels[$row['bsre_status']] ?? $row['bsre_status'],
                        'count' => $row['count']
                    ];
                }
            }
            if ($notSyncedCount > 0) {
                $bsreStatusCounts[] = [
                    'status' => 'not_synced',
                    'label' => 'Not Synced',
                    'count' => $notSyncedCount
                ];
            }

            // --- Pimpinan Statistics ---
            $pimpinanCount = $this->emailModel->allowCallbacks(false)->where('pimpinan', 1)->countAllResults();

            // --- Pimpinan Desa Statistics ---
            $pimpinanDesaCount = $this->emailModel->allowCallbacks(false)->where('pimpinan_desa', 1)->countAllResults();


            $data = [
                'emails' => $emails,
                'total_emails' => $counts['total_emails'],
                'filtered_count' => $pager->getTotal(),
                'active_count' => $counts['active_count'],
                'suspended_count' => $counts['suspended_count'],
                'per_page' => $perPage,
                'pagination' => $pager,
                'search' => $search,
                'bsre_status' => $bsre_status,
                'bsre_status_options' => $bsre_status_labels,
                'last_sync_time' => $lastSync['value'] ?? null,
                'unit_kerja_list' => $unitKerjaList,
                'status_asn_counts' => $statusAsnCounts, // New data
                'eselon_counts' => $eselonCounts, // New data
                'bsre_status_counts' => $bsreStatusCounts, // New data
            ];

            return view('email/index', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function batch()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
        return view('email/batch', $data);
    }

    public function batch_update()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
        return view('email/batch_update', $data);
    }

    public function batch_update_process()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $data = $this->request->getJSON(true);
        if (empty($data) || !isset($data['identifiers']) || !is_array($data['identifiers'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No identifiers provided.']);
        }

        $mode = $data['mode'] ?? 'email';
        $identifiers = $data['identifiers'];
        $newNames = $data['names'] ?? [];
        $newPasswords = $data['passwords'] ?? [];
        $newNiks = $data['niks'] ?? [];
        $newNips = $data['nips'] ?? [];
        $newGelarDepans = $data['gelar_depans'] ?? []; // Added
        $newGelarBelakangs = $data['gelar_belakangs'] ?? []; // Added
        $newNomors = $data['nomors'] ?? [];
        $newGajiNominals = $data['gaji_nominals'] ?? []; // Modified
        $newGajiTerbilangs = $data['gaji_terbilangs'] ?? []; // Added
        $newTanggalKontrakAwals = $data['tanggal_kontrak_awals'] ?? []; // Added
        $newTanggalKontrakAkhirs = $data['tanggal_kontrak_akhirs'] ?? []; // Added
        $newTempatLahirs = $data['tempat_lahirs'] ?? [];
        $newTanggalLahirs = $data['tanggal_lahirs'] ?? [];
        $newPendidikans = $data['pendidikans'] ?? [];
        $newJabatans = $data['jabatans'] ?? [];
        $newStatusAsn = $data['status_asn'] ?? null; // Added
        $newPimpinan = $data['pimpinan'] ?? null;
        $newPimpinanDesa = $data['pimpinan_desa'] ?? null;
        $newUnitKerja = $data['unit_kerja'] ?? null;

        $results = [];
        foreach ($identifiers as $index => $identifier) {
            $emailRecord = null;
            if ($mode === 'email') {
                $emailRecord = $this->emailModel->where('email', $identifier)->first();
            } else { // nik mode
                $emailRecord = $this->emailModel->where('nik', $identifier)->first();
            }

            if (!$emailRecord) {
                $results[] = ['identifier' => $identifier, 'success' => false, 'message' => 'Record not found in local database.'];
                continue;
            }

            $emailUpdateData = []; // For EmailModel
            if (isset($newNames[$index]) && !empty($newNames[$index])) {
                $emailUpdateData['name'] = $newNames[$index];
            }
            if (isset($newGelarDepans[$index]) && !empty($newGelarDepans[$index])) {
                $emailUpdateData['gelar_depan'] = $newGelarDepans[$index];
            }
            if (isset($newGelarBelakangs[$index]) && !empty($newGelarBelakangs[$index])) {
                $emailUpdateData['gelar_belakang'] = $newGelarBelakangs[$index];
            }
            if (isset($newPasswords[$index]) && !empty($newPasswords[$index])) {
                $emailUpdateData['password'] = $newPasswords[$index];
            }
            if (isset($newNiks[$index]) && !empty($newNiks[$index])) {
                $emailUpdateData['nik'] = $newNiks[$index];
            }
            if (isset($newNips[$index]) && !empty($newNips[$index])) {
                $emailUpdateData['nip'] = $newNips[$index];
            }
            if (isset($newTempatLahirs[$index]) && !empty($newTempatLahirs[$index])) {
                $emailUpdateData['tempat_lahir'] = $newTempatLahirs[$index];
            }
            if (isset($newTanggalLahirs[$index]) && !empty($newTanggalLahirs[$index])) {
                $emailUpdateData['tanggal_lahir'] = $newTanggalLahirs[$index];
            }
            if (isset($newPendidikans[$index]) && !empty($newPendidikans[$index])) {
                $emailUpdateData['pendidikan'] = $newPendidikans[$index];
            }
            if (isset($newJabatans[$index]) && !empty($newJabatans[$index])) {
                $emailUpdateData['jabatan'] = $newJabatans[$index];
            }
            if (!empty($newStatusAsn)) { // Added
                $emailUpdateData['status_asn_id'] = $newStatusAsn;
            }
            if (isset($newPimpinan) && $newPimpinan !== '') {
                $emailUpdateData['pimpinan'] = $newPimpinan;
            }
            if (isset($newPimpinanDesa) && $newPimpinanDesa !== '') {
                $emailUpdateData['pimpinan_desa'] = $newPimpinanDesa;
            }
            if (!empty($newUnitKerja)) {
                $unit = $this->unitKerjaModel->where('nama_unit_kerja', $newUnitKerja)->first();
                if ($unit) {
                    $emailUpdateData['unit_kerja_id'] = $unit['id'];
                }
            }


            $pkUpdateData = []; // For PkModel
            if (isset($newNomors[$index]) && !empty($newNomors[$index])) {
                $pkUpdateData['nomor'] = $newNomors[$index];
            }
            if (isset($newGajiNominals[$index]) && !empty($newGajiNominals[$index])) {
                $pkUpdateData['gaji_nominal'] = $newGajiNominals[$index];
            }
            if (isset($newGajiTerbilangs[$index]) && !empty($newGajiTerbilangs[$index])) {
                $pkUpdateData['gaji_terbilang'] = $newGajiTerbilangs[$index];
            }
            if (isset($newTanggalKontrakAwals[$index]) && !empty($newTanggalKontrakAwals[$index])) {
                $pkUpdateData['tanggal_kontrak_awal'] = $newTanggalKontrakAwals[$index];
            }
            if (isset($newTanggalKontrakAkhirs[$index]) && !empty($newTanggalKontrakAkhirs[$index])) {
                $pkUpdateData['tanggal_kontrak_akhir'] = $newTanggalKontrakAkhirs[$index];
            }

            if (empty($emailUpdateData) && empty($pkUpdateData)) {
                $results[] = ['identifier' => $identifier, 'success' => true, 'message' => 'Skipped (no data to update).'];
                continue;
            }

            $updatedEmail = false;
            $updatedPk = false;

            if (!empty($emailUpdateData)) {
                try {
                    $updatedEmail = $this->emailModel->update($emailRecord['id'], $emailUpdateData);
                } catch (Exception $e) {
                    // Log error but continue to try and update PK data
                    log_message('error', 'Error updating EmailModel for ' . $identifier . ': ' . $e->getMessage());
                }
            }

            if (!empty($pkUpdateData)) {
                try {
                    $pkRecord = $this->pkModel->where('email', $emailRecord['email'])->first();
                    if ($pkRecord) {
                        $updatedPk = $this->pkModel->update($pkRecord['id'], $pkUpdateData);
                    } else {
                        // Insert new PK record if it doesn't exist
                        $pkUpdateData['email'] = $emailRecord['email'];
                        $updatedPk = $this->pkModel->insert($pkUpdateData);
                    }
                } catch (Exception $e) {
                    log_message('error', 'Error updating PkModel for ' . $identifier . ': ' . $e->getMessage());
                }
            }

            if ($updatedEmail || $updatedPk) {
                $results[] = ['identifier' => $identifier, 'success' => true, 'message' => 'Successfully updated.'];
            } else {
                $results[] = ['identifier' => $identifier, 'success' => false, 'message' => 'Failed to update (no changes or database error).'];
            }
        }

        return $this->response->setJSON(['success' => true, 'results' => $results]);
    }

    public function batch_create()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/email');
        }

        $data = $this->request->getJSON();
        if (empty($data)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No data provided.']);
        }

        $emails = array_map(function ($item) {
            return $item->email;
        }, $data);

        try {
            $existing_emails = $this->emailModel->whereIn('email', $emails)->findColumn('email');

            if (!empty($existing_emails)) {
                $message = 'The following email(s) already exist in the local database: ' . implode(', ', $existing_emails) . '. Please remove them from the list and try again.';
                return $this->response->setJSON(['success' => false, 'message' => $message]);
            }

            $results = [];
            foreach ($data as $item) {
                try {
                    $this->cpanelApi->create_email_account($item->email, $item->password, $item->quota);

                    // Resolve Unit Kerja ID
                    $unitKerjaId = null;
                    if (!empty($item->unitKerja)) {
                        $unit = $this->unitKerjaModel->where('nama_unit_kerja', $item->unitKerja)->first();
                        if ($unit) {
                            $unitKerjaId = $unit['id'];
                        }
                    }

                    // Save the new email with its unit_kerja, nip, and jabatan to the local DB
                    $this->emailModel->insert([
                        'email'      => $item->email,
                        'user'       => explode('@', $item->email)[0],
                        'domain'     => explode('@', $item->email)[1],
                        'unit_kerja_id' => $unitKerjaId,
                        'password'   => $item->password ?? null,
                        'nik'        => $item->nik ?? null,
                        'nip'        => $item->nip ?? null, // Added
                        'name'       => $item->name ?? null,
                        'jabatan'    => $item->jabatan ?? null, // Added
                        'status_asn_id' => $item->statusAsn ?? null,
                    ]);

                    $results[] = ['email' => $item->email, 'success' => true];
                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                    if (strpos($errorMessage, 'already exists') !== false) {
                        $results[] = ['email' => $item->email, 'success' => false, 'message' => 'Email already exists on cPanel.'];
                    } else {
                        $results[] = ['email' => $item->email, 'success' => false, 'message' => $errorMessage];
                    }
                }
            }

            return $this->response->setJSON(['success' => true, 'results' => $results]);
        } catch (Exception $e) {
            log_message('error', 'Batch creation failed: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'An unexpected error occurred during the process.']);
        }
    }

    public function sync()

    {

        try {

            $all_emails = $this->cpanelApi->get_email_accounts_detailed();

            $this->emailModel->upsertBatch($all_emails);



            // Save last sync time

            $this->appSettingModel->where('key', 'last_sync_time')->set(['value' => date('Y-m-d H:i:s')])->update();

            if ($this->appSettingModel->affectedRows() == 0) {

                $this->appSettingModel->insert(['key' => 'last_sync_time', 'value' => date('Y-m-d H:i:s')]);
            }



            $result = ['success' => true, 'message' => 'Email data synchronization from cPanel was successful.'];



            if (is_cli()) {

                return $result;
            }

            return $this->response->setJSON($result);
        } catch (Exception $e) {

            $result = ['success' => false, 'message' => 'Failed to synchronize: ' . $e->getMessage()];

            if (is_cli()) {

                return $result;
            }

            return $this->response->setStatusCode(500)->setJSON($result);
        }
    }

    public function detail($username)
    {
        try {
            $email_detail = $this->emailModel->allowCallbacks(false)->where('user', $username)->first();

            if (!$email_detail) {
                throw new Exception('Email tidak ditemukan di database lokal.');
            }

            $data['email'] = $email_detail;
            $data['unit_kerja_options'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
            $data['back_url'] = site_url('email');

            $current_unit_kerja = null;
            if (!empty($email_detail['unit_kerja_id'])) {
                $current_unit_kerja = $this->unitKerjaModel->find($email_detail['unit_kerja_id']);
            }

            $parent_unit_kerja = null;
            if (!empty($current_unit_kerja['parent_id'])) {
                $parent_unit_kerja = $this->unitKerjaModel->find($current_unit_kerja['parent_id']);
            }

            $data['current_unit_kerja'] = $current_unit_kerja;
            $data['parent_unit_kerja'] = $parent_unit_kerja;
            $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
            $data['eselon_options'] = $this->eselonModel->orderBy('nama_eselon', 'ASC')->findAll();

            return view('email/detail', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function update_details($username)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('email/detail/' . $username)->with('error', 'Invalid request method.');
        }

        $email = $this->emailModel->where('user', $username)->first();
        if (!$email) {
            return redirect()->to('email')->with('error', 'Email account not found.');
        }

        $statusAsnId = $this->request->getPost('status_asn');
        $eselonId = $this->request->getPost('eselon');
        $unitKerjaId = $this->request->getPost('unit_kerja_id');
        $pimpinan = $this->request->getPost('pimpinan');
        $pimpinanDesa = $this->request->getPost('pimpinan_desa');

        $updateData = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'gelar_depan' => $this->request->getPost('gelar_depan'),
            'gelar_belakang' => $this->request->getPost('gelar_belakang'),
            'nik' => $this->request->getPost('nik'),
            'nip' => $this->request->getPost('nip'),
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            // Only update tanggal_lahir if it's not empty
            'pendidikan' => $this->request->getPost('pendidikan'),
            'jabatan' => $this->request->getPost('jabatan'),
            'status_asn_id' => !empty($statusAsnId) ? $statusAsnId : null,
            'eselon_id' => !empty($eselonId) ? $eselonId : null,
            'unit_kerja_id' => !empty($unitKerjaId) ? $unitKerjaId : null,
            'pimpinan' => $pimpinan,
            'pimpinan_desa' => $pimpinanDesa,
        ];

        $tanggalLahir = $this->request->getPost('tanggal_lahir');
        if (!empty($tanggalLahir)) {
            $updateData['tanggal_lahir'] = $tanggalLahir;
        } else {
            // If tanggal_lahir is empty, set it to NULL to clear it in DB
            // or if the column doesn't allow NULL, you might want to handle it differently
            $updateData['tanggal_lahir'] = null;
        }


        // Handle password update separately
        $newPassword = $this->request->getPost('password');
        if (!empty($newPassword) && $newPassword !== $email['password']) {
            try {
                $this->cpanelApi->change_password($email['email'], $newPassword);
                $updateData['password'] = $newPassword;
            } catch (Exception $e) {
                log_message('error', 'Error updating password on cPanel: ' . $e->getMessage());
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update password on cPanel: ' . $e->getMessage());
            }
        }

        try {
            $updated = $this->emailModel->update($email['id'], $updateData);

            if ($updated) {
                return redirect()->to('email/detail/' . $username)->with('success', 'Email details have been updated successfully.');
            } else {
                return redirect()->to('email/detail/' . $username)->with('info', 'No changes were detected.');
            }
        } catch (Exception $e) {
            log_message('error', 'Database error during email details update: ' . $e->getMessage());
            return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update email details due to a database error.');
        }
    }

    public function unit_kerja_detail($unitKerjaId)
    {
        try {
            $unitKerja = $this->unitKerjaModel->find($unitKerjaId);
            if (!$unitKerja) {
                throw new Exception('Unit Kerja not found.');
            }

            // Find children of the current unit
            $children = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->findAll();

            // Sort children using natural sort
            usort($children, function ($a, $b) {
                return strnatcasecmp($a['nama_unit_kerja'] ?? '', $b['nama_unit_kerja'] ?? '');
            });

            $childrenIds = array_column($children, 'id');

            // Find all emails belonging to this unit AND all its children
            $allUnitIds = array_merge([$unitKerjaId], $childrenIds);

            $perPage = $this->request->getGet('per_page') ?? 100;
            $search = $this->request->getGet('search');
            $jenis_formasi = $this->request->getGet('jenis_formasi'); // This is effectively 'status_asn' now in view, but controller param might still be this name? 
            // Wait, I updated the view input name to 'status_asn' in previous turn.
            // So I should retrieve 'status_asn'.
            $status_asn = $this->request->getGet('status_asn');
            $bsre_status = $this->request->getGet('bsre_status');

            $emailBuilder = $this->emailModel->whereIn('unit_kerja_id', $allUnitIds);

            if ($search) {
                $emailBuilder->groupStart()
                    ->like('email', $search)
                    ->orLike('name', $search)
                    ->orLike('nik', $search)
                    ->orLike('nip', $search)
                    ->groupEnd();
            }

            if ($status_asn) {
                $emailBuilder->where('emails.status_asn_id', $status_asn);
            }

            if ($bsre_status) {
                if ($bsre_status === 'not_synced') {
                    $emailBuilder->groupStart()
                        ->where('emails.bsre_status', null)
                        ->orWhere('emails.bsre_status', '')
                        ->groupEnd();
                } else {
                    $emailBuilder->where('emails.bsre_status', $bsre_status);
                }
            }

            $emails = $emailBuilder
                ->orderBy('emails.eselon_id IS NULL', 'ASC', false)
                ->orderBy('emails.eselon_id', 'ASC')
                ->orderBy('emails.status_asn_id IS NULL', 'ASC', false)
                ->orderBy('emails.status_asn_id', 'ASC')
                ->orderBy('emails.jabatan IS NULL', 'ASC', false)
                ->orderBy('emails.jabatan', 'ASC')
                ->orderBy('emails.name', 'ASC')
                ->paginate($perPage);
            $pager = $this->emailModel->pager;

            // Define BSrE status options
            $bsre_status_options = [
                'ISSUE' => 'Sertifikat Aktif / Siap TTE',
                'EXPIRED' => 'Masa Berlaku Habis',
                'RENEW' => 'Proses Pembaruan',
                'WAITING_FOR_VERIFICATION' => 'Menunggu Verifikasi',
                'NEW' => 'Belum Aktivasi',
                'NO_CERTIFICATE' => 'Belum Ada Sertifikat',
                'NOT_REGISTERED' => 'Pengguna Tidak Terdaftar',
                'SUSPEND' => 'Akun Ditangguhkan',
                'REVOKE' => 'Sertifikat Dicabut',
                'not_synced' => 'Not Synced'
            ];

            $data = [
                'unit_kerja' => $unitKerja,
                'parent_unit' => !empty($unitKerja['parent_id']) ? $this->unitKerjaModel->find($unitKerja['parent_id']) : null,
                'child_units' => $children,
                'emails' => $emails,
                'total_emails' => $pager->getTotal(),
                'pagination' => $pager,
                'per_page' => $perPage,
                'search' => $search,
                'status_asn' => $status_asn,
                'status_asn_options' => $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll(),
                'bsre_status' => $bsre_status,
                'bsre_status_options' => $bsre_status_options,
                'back_url' => site_url('email'),
            ];

            return view('email/unit_kerja_detail', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function eselon_detail($eselonId)
    {
        try {
            $eselon = $this->eselonModel->find($eselonId);
            if (!$eselon) {
                throw new Exception('Eselon not found.');
            }

            $perPage = $this->request->getGet('per_page') ?? 100;
            $search = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');

            $emailBuilder = $this->emailModel
                ->select('emails.*, uk.nama_unit_kerja as unit_kerja_name, parent_uk.nama_unit_kerja as parent_unit_kerja_name')
                ->join('unit_kerja as uk', 'uk.id = emails.unit_kerja_id', 'left')
                ->join('unit_kerja as parent_uk', 'parent_uk.id = uk.parent_id', 'left')
                ->where('eselon_id', $eselonId);

            if ($search) {
                $emailBuilder->groupStart()
                    ->like('email', $search)
                    ->orLike('name', $search)
                    ->orLike('nik', $search)
                    ->orLike('nip', $search)
                    ->groupEnd();
            }

            if ($bsre_status) {
                if ($bsre_status === 'not_synced') {
                    $emailBuilder->groupStart()
                        ->where('emails.bsre_status', null)
                        ->orWhere('emails.bsre_status', '')
                        ->groupEnd();
                } else {
                    $emailBuilder->where('emails.bsre_status', $bsre_status);
                }
            }

            $emails = $emailBuilder->orderBy('uk.nama_unit_kerja', 'ASC')
                ->orderBy('jabatan', 'ASC')
                ->orderBy('name', 'ASC')
                ->paginate($perPage);
            $pager = $this->emailModel->pager;

            // Define BSrE status options
            $bsre_status_options = [
                'ISSUE' => 'Sertifikat Aktif / Siap TTE',
                'EXPIRED' => 'Masa Berlaku Habis',
                'RENEW' => 'Proses Pembaruan',
                'WAITING_FOR_VERIFICATION' => 'Menunggu Verifikasi',
                'NEW' => 'Belum Aktivasi',
                'NO_CERTIFICATE' => 'Belum Ada Sertifikat',
                'NOT_REGISTERED' => 'Pengguna Tidak Terdaftar',
                'SUSPEND' => 'Akun Ditangguhkan',
                'REVOKE' => 'Sertifikat Dicabut',
                'not_synced' => 'Not Synced'
            ];

            $data = [
                'eselon' => $eselon,
                'emails' => $emails,
                'total_emails' => $pager->getTotal(),
                'pagination' => $pager,
                'per_page' => $perPage,
                'search' => $search,
                'bsre_status' => $bsre_status,
                'bsre_status_options' => $bsre_status_options,
                'back_url' => site_url('email'),
            ];

            return view('email/eselon_detail', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function pimpinan()
    {
        try {
            $perPage = $this->request->getGet('per_page') ?? 100;
            $search = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');

            $emailBuilder = $this->emailModel->where('pimpinan', 1);

            if ($search) {
                $emailBuilder->groupStart()
                    ->like('email', $search)
                    ->orLike('name', $search)
                    ->orLike('nik', $search)
                    ->orLike('nip', $search)
                    ->groupEnd();
            }

            if ($bsre_status) {
                if ($bsre_status === 'not_synced') {
                    $emailBuilder->groupStart()
                        ->where('emails.bsre_status', null)
                        ->orWhere('emails.bsre_status', '')
                        ->groupEnd();
                } else {
                    $emailBuilder->where('emails.bsre_status', $bsre_status);
                }
            }

            $emails = $emailBuilder
                ->orderBy('emails.eselon_id', 'ASC')
                ->orderBy('COALESCE(parent_unit_kerja.nama_unit_kerja, unit_kerja.nama_unit_kerja)', 'ASC', false)
                ->orderBy('unit_kerja.parent_id IS NOT NULL', 'ASC', false)
                ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
                ->orderBy('emails.jabatan', 'ASC')
                ->orderBy('emails.name', 'ASC')
                ->paginate($perPage);

            $pager = $this->emailModel->pager;

            $bsre_status_options = [
                'ISSUE' => 'Sertifikat Aktif / Siap TTE',
                'EXPIRED' => 'Masa Berlaku Habis',
                'RENEW' => 'Proses Pembaruan',
                'WAITING_FOR_VERIFICATION' => 'Menunggu Verifikasi',
                'NEW' => 'Belum Aktivasi',
                'NO_CERTIFICATE' => 'Belum Ada Sertifikat',
                'NOT_REGISTERED' => 'Pengguna Tidak Terdaftar',
                'SUSPEND' => 'Akun Ditangguhkan',
                'REVOKE' => 'Sertifikat Dicabut',
                'not_synced' => 'Not Synced'
            ];

            $data = [
                'title' => 'Daftar Email Pimpinan',
                'emails' => $emails,
                'total_emails' => $pager->getTotal(),
                'pagination' => $pager,
                'per_page' => $perPage,
                'search' => $search,
                'bsre_status' => $bsre_status,
                'bsre_status_options' => $bsre_status_options,
                'back_url' => site_url('email'),
            ];

            return view('email/pimpinan', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function pimpinan_desa()
    {
        try {
            $perPage = $this->request->getGet('per_page') ?? 100;
            $search = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');

            $emailBuilder = $this->emailModel->where('pimpinan_desa', 1);

            if ($search) {
                $emailBuilder->groupStart()
                    ->like('email', $search)
                    ->orLike('name', $search)
                    ->orLike('nik', $search)
                    ->orLike('nip', $search)
                    ->groupEnd();
            }

            if ($bsre_status) {
                if ($bsre_status === 'not_synced') {
                    $emailBuilder->groupStart()
                        ->where('emails.bsre_status', null)
                        ->orWhere('emails.bsre_status', '')
                        ->groupEnd();
                } else {
                    $emailBuilder->where('emails.bsre_status', $bsre_status);
                }
            }

            $emails = $emailBuilder
                ->orderBy('emails.eselon_id', 'ASC')
                ->orderBy('COALESCE(parent_unit_kerja.nama_unit_kerja, unit_kerja.nama_unit_kerja)', 'ASC', false)
                ->orderBy('unit_kerja.parent_id IS NOT NULL', 'ASC', false)
                ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
                ->orderBy('emails.jabatan', 'ASC')
                ->orderBy('emails.name', 'ASC')
                ->paginate($perPage);

            $pager = $this->emailModel->pager;

            $bsre_status_options = [
                'ISSUE' => 'Sertifikat Aktif / Siap TTE',
                'EXPIRED' => 'Masa Berlaku Habis',
                'RENEW' => 'Proses Pembaruan',
                'WAITING_FOR_VERIFICATION' => 'Menunggu Verifikasi',
                'NEW' => 'Belum Aktivasi',
                'NO_CERTIFICATE' => 'Belum Ada Sertifikat',
                'NOT_REGISTERED' => 'Pengguna Tidak Terdaftar',
                'SUSPEND' => 'Akun Ditangguhkan',
                'REVOKE' => 'Sertifikat Dicabut',
                'not_synced' => 'Not Synced'
            ];

            $data = [
                'title' => 'Daftar Email Pimpinan Desa',
                'emails' => $emails,
                'total_emails' => $pager->getTotal(),
                'pagination' => $pager,
                'per_page' => $perPage,
                'search' => $search,
                'bsre_status' => $bsre_status,
                'bsre_status_options' => $bsre_status_options,
                'back_url' => site_url('email'),
            ];

            return view('email/pimpinan_desa', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function export_pimpinan_pdf()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $search = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');

            $builder = $this->emailModel->where('pimpinan', 1);

            if ($search) {
                $builder->groupStart()
                    ->like('email', $search)
                    ->orLike('name', $search)
                    ->orLike('nik', $search)
                    ->orLike('nip', $search)
                    ->groupEnd();
            }

            if ($bsre_status) {
                if ($bsre_status === 'not_synced') {
                    $builder->groupStart()
                        ->where('emails.bsre_status', null)
                        ->orWhere('emails.bsre_status', '')
                        ->groupEnd();
                } else {
                    $builder->where('emails.bsre_status', $bsre_status);
                }
            }

            $emails = $builder
                ->orderBy('emails.eselon_id', 'ASC')
                ->orderBy('COALESCE(parent_unit_kerja.nama_unit_kerja, unit_kerja.nama_unit_kerja)', 'ASC', false)
                ->orderBy('unit_kerja.parent_id IS NOT NULL', 'ASC', false)
                ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
                ->orderBy('emails.jabatan', 'ASC')
                ->orderBy('emails.name', 'ASC')
                ->findAll();

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);

            if (!function_exists('esc')) {
                function esc($str)
                {
                    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
                }
            }

            $logoPath = FCPATH . 'logo.png';
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;

            $data = [
                'title' => 'DAFTAR EMAIL & TTE PIMPINAN',
                'subtitle' => 'PEMERINTAH KABUPATEN SINJAI',
                'emails' => $emails,
                'showUnitKerjaColumn' => true,
                'logoSrc' => $logoSrc,
                'current_date' => format_indo_date(date('Y-m-d')),
            ];

            $html = view('email/pimpinan_pdf', $data);

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'Email & TTE Pimpinan - ' . format_indo_date(date('Y-m-d'), true) . '.pdf';
            $dompdf->stream($filename, ["Attachment" => true]);
            exit();
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('templates/header') .
                view('email/error', $data) .
                view('templates/footer');
        }
    }

    public function export_pimpinan_desa_pdf()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $search = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');

            $builder = $this->emailModel->where('pimpinan_desa', 1);

            if ($search) {
                $builder->groupStart()
                    ->like('email', $search)
                    ->orLike('name', $search)
                    ->orLike('nik', '' . $search . '%')
                    ->orLike('nip', '' . $search . '%')
                    ->groupEnd();
            }

            if ($bsre_status) {
                if ($bsre_status === 'not_synced') {
                    $builder->groupStart()
                        ->where('emails.bsre_status', null)
                        ->orWhere('emails.bsre_status', '')
                        ->groupEnd();
                } else {
                    $builder->where('emails.bsre_status', $bsre_status);
                }
            }

            $emails = $builder
                ->orderBy('emails.eselon_id', 'ASC')
                ->orderBy('COALESCE(parent_unit_kerja.nama_unit_kerja, unit_kerja.nama_unit_kerja)', 'ASC', false)
                ->orderBy('unit_kerja.parent_id IS NOT NULL', 'ASC', false)
                ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
                ->orderBy('emails.jabatan', 'ASC')
                ->orderBy('emails.name', 'ASC')
                ->findAll();

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);

            if (!function_exists('esc')) {
                function esc($str)
                {
                    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
                }
            }

            $logoPath = FCPATH . 'logo.png';
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;

            $data = [
                'title' => 'DAFTAR EMAIL & TTE PIMPINAN DESA',
                'subtitle' => 'PEMERINTAH KABUPATEN SINJAI',
                'emails' => $emails,
                'showUnitKerjaColumn' => true,
                'logoSrc' => $logoSrc,
                'current_date' => format_indo_date(date('Y-m-d')),
            ];

            $html = view('email/pimpinan_desa_pdf', $data);

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'Email & TTE Pimpinan Desa - ' . format_indo_date(date('Y-m-d'), true) . '.pdf';
            $dompdf->stream($filename, ["Attachment" => true]);
            exit();
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('templates/header') .
                view('email/error', $data) .
                view('templates/footer');
        }
    }

    public function api_unit_emails($unitKerjaId)
    {
        $unitKerja = $this->unitKerjaModel->find($unitKerjaId);
        if (!$unitKerja) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unit Kerja not found']);
        }

        // Find children
        $children = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->findAll();
        $childrenIds = array_column($children, 'id');
        $allUnitIds = array_merge([$unitKerjaId], $childrenIds);

        $search = $this->request->getGet('search');
        $status_asn = $this->request->getGet('status_asn');

        $builder = $this->emailModel->whereIn('unit_kerja_id', $allUnitIds);

        if ($search) {
            $builder->groupStart()
                ->like('email', $search)
                ->orLike('name', $search)
                ->orLike('nik', $search)
                ->orLike('nip', $search)
                ->groupEnd();
        }

        if ($status_asn) {
            $builder->where('emails.status_asn_id', $status_asn);
        }
        $emails = $builder
            ->orderBy('LENGTH(unit_kerja_name)', 'ASC', false)->orderBy('unit_kerja_name', 'ASC')
            ->orderBy('name', 'ASC')
            ->select('emails.id, emails.email, emails.name')
            ->findAll();

        return $this->response->setJSON(['success' => true, 'emails' => $emails]);
    }

    public function api_generate_pdf()
    {
        $unitId = $this->request->getPost('unit_id');
        $emailId = $this->request->getPost('email_id');

        if (!$unitId || !$emailId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid parameters']);
        }

        try {
            $email = $this->emailModel->find($emailId);
            if (!$email) {
                throw new Exception('Email not found');
            }

            $unitKerja = $this->unitKerjaModel->find($unitId); // Main unit for the export context
            // Ideally, we should use the email's actual unit for the template display?
            // The original logic passed $unitKerja (the filter unit) to the view.
            // But the email might belong to a sub-unit. 
            // Let's stick to the original logic: passing the requested Unit Kerja object.

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);

            $logoPath = FCPATH . 'garuda.png';
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;

            $pk_data = $this->pkModel->where('email', $email['email'])->first();

            $data = [
                'email' => $email,
                'unit_kerja' => $unitKerja,
                'logoSrc' => $logoSrc,
                'pk_data' => $pk_data,
            ];

            $html = view('email/perjanjian_kerja_template', $data);

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $output = $dompdf->output();

            $tempDir = WRITEPATH . 'uploads/temp_export_' . $unitId;
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0775, true);
            }

            $filename = 'perjanjian_kerja_' . url_title($email['name'], '_', true) . '.pdf';
            file_put_contents($tempDir . '/' . $filename, $output);

            return $this->response->setJSON(['success' => true]);
        } catch (Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function api_download_zip($unitId)
    {
        $unitKerja = $this->unitKerjaModel->find($unitId);
        if (!$unitKerja) {
            return redirect()->back()->with('error', 'Unit Kerja not found');
        }

        $tempDir = WRITEPATH . 'uploads/temp_export_' . $unitId;
        if (!is_dir($tempDir)) {
            return redirect()->back()->with('error', 'No files generated to zip.');
        }

        $zip = new \ZipArchive();
        $zipFileName = 'perjanjian_kerja_' . url_title($unitKerja['nama_unit_kerja'], '_', true) . '.zip';
        $zipFilePath = WRITEPATH . 'uploads/' . $zipFileName;

        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            return redirect()->back()->with('error', 'Cannot create ZIP archive.');
        }

        $files = scandir($tempDir);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            $filePath = $tempDir . '/' . $file;
            $zip->addFile($filePath, $file);
        }

        $zip->close();

        // Cleanup temp files
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            unlink($tempDir . '/' . $file);
        }
        rmdir($tempDir);

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
        header('Content-Length: ' . filesize($zipFilePath));
        readfile($zipFilePath);
        unlink($zipFilePath);
        exit();
    }

    public function export_unit_kerja_csv($unitKerjaId)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $unitKerja = $this->unitKerjaModel->find($unitKerjaId);

            if (!$unitKerja) {
                throw new Exception('Unit Kerja not found.');
            }

            // Find children of the current unit
            $children = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->findAll();
            $childrenIds = array_column($children, 'id');
            $allUnitIds = array_merge([$unitKerjaId], $childrenIds);

            $search = $this->request->getGet('search');
            $status_asn = $this->request->getGet('status_asn');

            $builder = $this->emailModel->whereIn('unit_kerja_id', $allUnitIds);

            if ($search) {
                $builder->groupStart()
                    ->like('email', $search)
                    ->orLike('name', $search)
                    ->orLike('nik', $search)
                    ->orLike('nip', $search)
                    ->groupEnd();
            }

            if ($status_asn) {
                $builder->where('emails.status_asn_id', $status_asn);
            }

            $emails = $builder->findAll();
            $totalEmails = count($emails);
            $limit = 50;

            $unitKerjaName = $unitKerja['nama_unit_kerja'];

            if ($totalEmails <= $limit) {
                // Original logic for a single file
                $filename = url_title($unitKerjaName, '_', true) . '.csv';

                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '"');

                $output = fopen('php://output', 'w');
                fputcsv($output, ['nama', 'emailAddress'], ',');
                foreach ($emails as $email) {
                    fputcsv($output, [strtoupper($email['name']), $email['email']], ',');
                }
                fclose($output);
                exit();
            } else {
                // New logic for multiple files (ZIP archive)
                $zip = new \ZipArchive();
                $zipFileName = url_title($unitKerjaName, '_', true) . '.zip';
                $tempZipPath = WRITEPATH . 'uploads/' . $zipFileName;

                if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                    throw new Exception('Cannot create ZIP archive.');
                }

                $chunks = array_chunk($emails, $limit);
                $fileCount = 1;

                foreach ($chunks as $chunk) {
                    $csvFileName = url_title($unitKerjaName, '_', true) . '_part_' . $fileCount . '.csv';

                    // Using memory stream to avoid creating temporary CSV files on disk
                    $stream = fopen('php://memory', 'w+');
                    fputcsv($stream, ['nama', 'emailAddress'], ',');
                    foreach ($chunk as $email) {
                        fputcsv($stream, [strtoupper($email['name']), $email['email']], ',');
                    }
                    rewind($stream);
                    $csvContent = stream_get_contents($stream);
                    fclose($stream);

                    $zip->addFromString($csvFileName, $csvContent);
                    $fileCount++;
                }

                $zip->close();

                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
                header('Content-Length: ' . filesize($tempZipPath));

                readfile($tempZipPath);

                // Clean up the temporary zip file
                unlink($tempZipPath);

                exit();
            }
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('templates/header') .
                view('email/error', $data) .
                view('templates/footer');
        }
    }

    public function export_single_perjanjian_kerja_pdf($username)
    {
        try {
            $email = $this->emailModel->where('user', $username)->first();

            if (!$email) {
                throw new Exception('Email account not found.');
            }

            $unitKerja = null;
            if (!empty($email['unit_kerja_id'])) {
                $unitKerja = $this->unitKerjaModel->find($email['unit_kerja_id']);
            }

            if (!$unitKerja) {
                throw new Exception('Unit Kerja not found for this email account.');
            }

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);

            $logoPath = FCPATH . 'garuda.png';
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;

            // Fetch data from pk table using email
            $pk_data = $this->pkModel->where('email', $email['email'])->first();

            $data = [
                'email' => $email,
                'unit_kerja' => $unitKerja,
                'logoSrc' => $logoSrc,
                'pk_data' => $pk_data, // Pass pk_data to the view
            ];

            $html = view('email/perjanjian_kerja_template', $data);

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'perjanjian_kerja_' . url_title($email['name'], '_', true) . '.pdf';
            $dompdf->stream($filename, ["Attachment" => true]);
            exit();
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('templates/header') .
                view('email/error', $data) .
                view('templates/footer');
        }
    }

    public function export_perjanjian_kerja_pdf($unitKerjaId)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $unitKerja = $this->unitKerjaModel->find($unitKerjaId);

            if (!$unitKerja) {
                throw new Exception('Unit Kerja not found.');
            }

            // Find children of the current unit
            $children = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->findAll();
            $childrenIds = array_column($children, 'id');

            // Find all emails belonging to this unit AND all its children, sorted by name
            $allUnitIds = array_merge([$unitKerjaId], $childrenIds);
            $emails = $this->emailModel
                ->whereIn('unit_kerja_id', $allUnitIds)
                ->orderBy('LENGTH(unit_kerja_name)', 'ASC', false)->orderBy('unit_kerja_name', 'ASC')
                ->orderBy('name', 'ASC')
                ->findAll();

            if (empty($emails)) {
                throw new Exception('No email accounts found for this Unit Kerja.');
            }

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);

            $zip = new \ZipArchive();
            $zipFileName = 'perjanjian_kerja_' . url_title($unitKerja['nama_unit_kerja'], '_', true) . '.zip';
            $tempZipPath = WRITEPATH . 'uploads/' . $zipFileName;

            if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                throw new Exception('Cannot create ZIP archive.');
            }

            $logoPath = FCPATH . 'garuda.png';
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;

            foreach ($emails as $email) {
                $dompdf = new Dompdf($options);

                // Fetch data from pk table using email
                $pk_data = $this->pkModel->where('email', $email['email'])->first();

                $data = [
                    'email' => $email,
                    'unit_kerja' => $unitKerja,
                    'logoSrc' => $logoSrc,
                    'pk_data' => $pk_data,
                ];

                $html = view('email/perjanjian_kerja_template', $data);

                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                $pdfOutput = $dompdf->output();
                $pdfFileName = 'perjanjian_kerja_' . url_title($email['name'], '_', true) . '.pdf';

                $zip->addFromString($pdfFileName, $pdfOutput);
            }

            $zip->close();

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
            header('Content-Length: ' . filesize($tempZipPath));

            readfile($tempZipPath);

            // Clean up the temporary zip file
            unlink($tempZipPath);

            exit();
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('templates/header') .
                view('email/error', $data) .
                view('templates/footer');
        }
    }

    public function export_unit_kerja_pdf($unitKerjaId)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $unitKerja = $this->unitKerjaModel->find($unitKerjaId);

            if (!$unitKerja) {
                throw new Exception('Unit Kerja not found.');
            }

            // Find children of the current unit
            $children = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->findAll();
            $childrenIds = array_column($children, 'id');

            // Find all emails belonging to this unit AND all its children, sorted by name
            $allUnitIds = array_merge([$unitKerjaId], $childrenIds);

            $search = $this->request->getGet('search');
            $status_asn = $this->request->getGet('status_asn');
            $bsre_status = $this->request->getGet('bsre_status');

            $builder = $this->emailModel
                ->whereIn('unit_kerja_id', $allUnitIds)
                ->orderBy('emails.eselon_id IS NULL', 'ASC', false)
                ->orderBy('emails.eselon_id', 'ASC')
                ->orderBy('emails.status_asn_id IS NULL', 'ASC', false)
                ->orderBy('emails.status_asn_id', 'ASC')
                ->orderBy('emails.jabatan', 'ASC')
                ->orderBy('emails.name', 'ASC');

            if ($search) {
                $builder->groupStart()
                    ->like('email', $search)
                    ->orLike('name', $search)
                    ->orLike('nik', $search)
                    ->orLike('nip', $search)
                    ->groupEnd();
            }

            if ($status_asn) {
                $builder->where('emails.status_asn_id', $status_asn);
            }

            if ($bsre_status) {
                if ($bsre_status === 'not_synced') {
                    $builder->groupStart()
                        ->where('emails.bsre_status', null)
                        ->orWhere('emails.bsre_status', '')
                        ->groupEnd();
                } else {
                    $builder->where('emails.bsre_status', $bsre_status);
                }
            }

            $emails = $builder->findAll();

            // Determine if the "Unit Kerja" column should be shown
            $uniqueUnitKerjaIds = array_unique(array_column($emails, 'unit_kerja_id'));
            $showUnitKerjaColumn = count($uniqueUnitKerjaIds) > 1;

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);

            // Fungsi esc() untuk keamanan
            if (!function_exists('esc')) {
                function esc($str)
                {
                    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
                }
            }

            $logoPath = FCPATH . 'logo.png';
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;

            $data = [
                'unit_kerja' => $unitKerja,
                'emails' => $emails,
                'showUnitKerjaColumn' => $showUnitKerjaColumn,
                'logoSrc' => $logoSrc,
                'current_date' => format_indo_date(date('Y-m-d')), // Add current date and time
            ];

            $html = view('email/unit_kerja_pdf', $data);

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = url_title($unitKerja['nama_unit_kerja'], '_', true) . '.pdf';
            $dompdf->stream($filename, ["Attachment" => true]);
            exit();
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('templates/header') .
                view('email/error', $data) .
                view('templates/footer');
        }
    }

    private function apply_sorting($builder, $sort_type)
    {
        switch ($sort_type) {
            case 'newest':
                $builder->orderBy('mtime', 'DESC');
                break;
            case 'oldest':
                $builder->orderBy('mtime', 'ASC');
                break;
            case 'email_asc':
                $builder->orderBy('email', 'ASC');
                break;
            case 'email_desc':
                $builder->orderBy('email', 'DESC');
                break;
            case 'name_asc':
                $builder->orderBy('name', 'ASC');
                break;
            case 'name_desc':
                $builder->orderBy('name', 'DESC');
                break;

            default:
                $builder->orderBy('mtime', 'DESC');
                break;
        }
    }

    public function check_nik()
    {
        $data = [
            'title' => 'Check NIK Association',
            'results' => [],
            'input_niks' => ''
        ];

        $method = strtolower($this->request->getMethod());
        
        if ($method === 'post') {
            $niksInput = $this->request->getPost('nik_list');
            $data['input_niks'] = $niksInput;

            if (!empty($niksInput)) {
                // Split by newlines (handle Windows/Mac/Linux)
                $niks = preg_split('/\r\n|\r|\n/', $niksInput);
                $results = [];

                foreach ($niks as $nik) {
                    // Remove control characters and whitespace
                    $nik = preg_replace('/[\x00-\x1F\x7F]/', '', $nik);
                    $nik = trim($nik);
                    
                    if (empty($nik)) {
                        continue;
                    }

                    // Use exact match for NIK
                    $foundEmails = $this->emailModel->where('nik', $nik)->findAll();

                    $results[] = [
                        'searched_nik' => $nik,
                        'found' => !empty($foundEmails),
                        'emails' => $foundEmails
                    ];
                }

                $data['results'] = $results;
            }
        }

        return view('email/check_nik', $data);
    }

    public function delete($id)
    {
        try {
            $email = $this->emailModel->find($id);

            if (!$email) {
                return redirect()->to('email')->with('error', 'Email account not found.');
            }

            // Delete from cPanel
            $this->cpanelApi->delete_email_account($email['email']);

            // Delete from local database
            $this->emailModel->delete($id);

            return redirect()->back()->with('success', 'Email account ' . $email['email'] . ' has been deleted successfully.');
        } catch (Exception $e) {
            // If cPanel deletion fails, we still might want to delete from local DB or handle differently
            // For now, just log the error and redirect with a generic error message.
            log_message('error', 'Failed to delete email: ' . $e->getMessage());
            // Optionally, attempt to delete from local DB even if cPanel fails
            $this->emailModel->delete($id);
            return redirect()->back()->with('error', 'Failed to delete email account from cPanel, but removed from local list. Please check cPanel manually.');
        }
    }

    public function create_single_email()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $data = $this->request->getJSON(true);
        if (empty($data) || !isset($data['email'])) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No data provided.']);
        }

        try {
            // Check if email exists in local DB first
            $existing_email = $this->emailModel->where('email', $data['email'])->first();
            if ($existing_email) {
                return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'Email already exists in local database.']);
            }

            // Create on cPanel
            $this->cpanelApi->create_email_account($data['email'], $data['password'], $data['quota'] ?? 1024);

            // Resolve Unit Kerja ID
            $unitKerjaId = null;
            if (!empty($data['unitKerja'])) {
                $unit = $this->unitKerjaModel->where('nama_unit_kerja', $data['unitKerja'])->first();
                if ($unit) {
                    $unitKerjaId = $unit['id'];
                }
            }

            // Save to local DB
            $this->emailModel->insert([
                'email'      => $data['email'],
                'user'       => explode('@', $data['email'])[0],
                'domain'     => explode('@', $data['email'])[1],
                'unit_kerja_id' => $unitKerjaId,
                'password'   => $data['password'] ?? null,
                'nik'        => $data['nik'] ?? null,
                'nip'        => $data['nip'] ?? null,
                'name'       => $data['name'] ?? null,
                'jabatan'    => $data['jabatan'] ?? null,
                'status_asn_id' => $data['jenisFormasi'] ?? null,
            ]);

            return $this->response->setJSON(['success' => true, 'email' => $data['email']]);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            // Check for specific cPanel error messages
            if (strpos($errorMessage, 'already exists') !== false) {
                return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'Email already exists on cPanel.']);
            }
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $errorMessage]);
        }
    }

    public function test_perjanjian_kerja()
    {
        $dummy_email = [
            'name' => 'Nama Pegawai Contoh',
            'nik' => '1234567890123456',
            'tempat_lahir' => 'Sinjai',
            'tanggal_lahir' => '01-01-1990',
            'pendidikan' => 'S1 Teknik Informatika',
            'jabatan' => 'Ahli Pertama - Pranata Komputer',
        ];

        $dummy_unit_kerja = [
            'nama_unit_kerja' => 'Dinas Komunikasi, Informatika dan Persandian'
        ];

        $logoPath = FCPATH . 'garuda.png';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        } else {
            $logoSrc = ''; // Fallback if logo not found
        }

        $data = [
            'email' => $dummy_email,
            'unit_kerja' => $dummy_unit_kerja,
            'logoSrc' => $logoSrc,
        ];

        return view('email/perjanjian_kerja_template', $data);
    }
}
