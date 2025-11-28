<?php

namespace App\Controllers;

use App\Libraries\CpanelApi;
use App\Models\EmailModel;
use App\Models\AppSettingModel;
use App\Models\UnitKerjaModel;
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

    public function __construct()
    {
        $this->cpanelApi = new CpanelApi();
        $this->emailModel = new EmailModel();
        $this->appSettingModel = new AppSettingModel();
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->pkModel = new PkModel(); // Add this
    }

    public function index()
    {
        // load time helper
        helper('time');

        try {
            $perPage = $this->request->getGet('per_page') ?? 100;
            $search = $this->request->getGet('search');
            $nik = $this->request->getGet('nik');
            $nip = $this->request->getGet('nip');

            $builder = $this->emailModel;

            if (!empty($search)) {
                $builder->groupStart()
                    ->like('email', $search)
                    ->orLike('name', $search)
                    ->groupEnd();
            }

            if (!empty($nik)) {
                $builder->like('nik', $nik);
            }

            if (!empty($nip)) {
                $builder->like('nip', $nip);
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

            $data = [
                'emails' => $emails,
                'total_emails' => $counts['total_emails'],
                'filtered_count' => $pager->getTotal(),
                'active_count' => $counts['active_count'],
                'suspended_count' => $counts['suspended_count'],
                'per_page' => $perPage,
                'pagination' => $pager,
                'search' => $search,
                'nik' => $nik,
                'nip' => $nip,
                'last_sync_time' => $lastSync['value'] ?? null,
                'unit_kerja_list' => $unitKerjaList,
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
        return view('email/batch', $data);
    }

    public function batch_update()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
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
        $newJenisFormasi = $data['jenis_formasi'] ?? null; // Added
        $newUnitKerja = $data['unit_kerja'] ?? null;
        $newSubUnitKerja = $data['sub_unit_kerja'] ?? [];

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
            if (!empty($newJenisFormasi)) { // Added
                $emailUpdateData['jenis_formasi'] = $newJenisFormasi;
            }
            if (!empty($newUnitKerja)) {
                $emailUpdateData['unit_kerja'] = $newUnitKerja; // Assuming unit_kerja is in EmailModel
            }
            if (isset($newSubUnitKerja[$index]) && !empty($newSubUnitKerja[$index])) {
                $emailUpdateData['sub_unit_kerja'] = $newSubUnitKerja[$index]; // Assuming sub_unit_kerja is in EmailModel
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

                    // Save the new email with its unit_kerja, nip, and jabatan to the local DB
                    $this->emailModel->insert([
                        'email'      => $item->email,
                        'user'       => explode('@', $item->email)[0],
                        'domain'     => explode('@', $item->email)[1],
                        'unit_kerja' => $item->unitKerja ?? null,
                        'password'   => $item->password ?? null,
                        'nik'        => $item->nik ?? null,
                        'nip'        => $item->nip ?? null, // Added
                        'name'       => $item->name ?? null,
                        'jabatan'    => $item->jabatan ?? null, // Added
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
            $data['unit_kerja_options'] = $this->unitKerjaModel->where('parent_id IS NULL')->orderBy('nama_unit_kerja', 'ASC')->findAll();
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

            return view('email/detail', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function update_unit_kerja($username)
    {
        if ($this->request->getMethod() === 'POST') {
            $unitKerjaId = $this->request->getPost('unit_kerja_id');

            $email = $this->emailModel->where('user', $username)->first();
            if (!$email) {
                return redirect()->to('email')->with('error', 'Email account not found.');
            }

            $updated = $this->emailModel->update($email['id'], ['unit_kerja_id' => $unitKerjaId]);

            if ($updated) {
                return redirect()->to('email/detail/' . $username)->with('success', 'Unit Kerja has been updated successfully.');
            } else {
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update Unit Kerja. No changes were detected.');
            }
        }

        return redirect()->to('email');
    }

    public function update_name($username)
    {
        if (strtolower($this->request->getMethod()) === 'post') {
            $newName = $this->request->getPost('name');

            $email = $this->emailModel->where('user', $username)->first();
            if (!$email) {
                return redirect()->to('email/detail/' . $username)->with('error', 'Email account not found.');
            }

            // Check if the new name is actually different from the current name
            if ($newName === $email['name']) {
                return redirect()->to('email/detail/' . $username)->with('info', 'No changes detected. Name is already up to date.');
            }

            // Validate newName (e.g., not empty)
            if (empty($newName)) {
                return redirect()->to('email/detail/' . $username)->with('error', 'Name cannot be empty.');
            }

            try {
                $updated = $this->emailModel->update($email['id'], ['name' => $newName]);

                if ($updated) {
                    return redirect()->to('email/detail/' . $username)->with('success', 'Name has been updated successfully.');
                } else {
                    // This case might not be reachable if the name check above is thorough,
                    // but it's good for robustness.
                    return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update name. The database did not report any changes.');
                }
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                log_message('error', 'Database error during name update: ' . $e->getMessage());
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update name due to a database error: ' . $e->getMessage());
            }
        }

        return redirect()->to('email/detail/' . $username);
    }

    public function update_email($username)
    {
        if (strtolower($this->request->getMethod()) === 'post') {
            $newEmail = $this->request->getPost('email');

            $email = $this->emailModel->where('user', $username)->first();
            if (!$email) {
                return redirect()->to('email/detail/' . $username)->with('error', 'Email account not found.');
            }

            if ($newEmail === $email['email']) {
                return redirect()->to('email/detail/' . $username)->with('info', 'No changes detected. Email is already up to date.');
            }

            if (empty($newEmail)) {
                return redirect()->to('email/detail/' . $username)->with('error', 'Email cannot be empty.');
            }

            try {
                $updated = $this->emailModel->update($email['id'], ['email' => $newEmail]);

                if ($updated) {
                    return redirect()->to('email/detail/' . $username)->with('success', 'Email has been updated successfully.');
                } else {
                    return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update email. The database did not report any changes.');
                }
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                log_message('error', 'Database error during email update: ' . $e->getMessage());
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update email due to a database error: ' . $e->getMessage());
            }
        }

        return redirect()->to('email/detail/' . $username);
    }

    public function update_password($username)
    {
        if (strtolower($this->request->getMethod()) === 'post') {
            $newPassword = $this->request->getPost('password');

            if (empty($newPassword)) {
                return redirect()->to('email/detail/' . $username)->with('error', 'Password cannot be empty.');
            }

            $email = $this->emailModel->where('user', $username)->first();
            if (!$email) {
                return redirect()->to('email/detail/' . $username)->with('error', 'Email account not found.');
            }

            try {
                // 1. Update in cPanel
                $this->cpanelApi->change_password($email['email'], $newPassword);

                // 2. Update in local DB
                $updated = $this->emailModel->update($email['id'], ['password' => $newPassword]);

                if ($updated) {
                    return redirect()->to('email/detail/' . $username)->with('success', 'Password has been updated successfully locally and on server.');
                } else {
                    return redirect()->to('email/detail/' . $username)->with('warning', 'Password updated on server but failed to update locally.');
                }
            } catch (Exception $e) {
                log_message('error', 'Error updating password: ' . $e->getMessage());
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update password: ' . $e->getMessage());
            }
        }

        return redirect()->to('email/detail/' . $username);
    }

    public function update_nik($username)
    {
        if (strtolower($this->request->getMethod()) === 'post') {
            $newNik = $this->request->getPost('nik');

            $email = $this->emailModel->where('user', $username)->first();
            if (!$email) {
                return redirect()->to('email/detail/' . $username)->with('error', 'Email account not found.');
            }

            if ($newNik === $email['nik']) {
                return redirect()->to('email/detail/' . $username)->with('info', 'No changes detected. NIK is already up to date.');
            }

            if (empty($newNik)) {
                return redirect()->to('email/detail/' . $username)->with('error', 'NIK cannot be empty.');
            }

            try {
                $updated = $this->emailModel->update($email['id'], ['nik' => $newNik]);

                if ($updated) {
                    return redirect()->to('email/detail/' . $username)->with('success', 'NIK/NIP has been updated successfully.');
                } else {
                    return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update NIK/NIP. The database did not report any changes.');
                }
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                log_message('error', 'Database error during NIK update: ' . $e->getMessage());
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update NIK due to a database error.');
            }
        }

        return redirect()->to('email/detail/' . $username);
    }

    public function update_nip($username)
    {
        if (strtolower($this->request->getMethod()) === 'post') {
            $newNip = $this->request->getPost('nip');

            $email = $this->emailModel->where('user', $username)->first();
            if (!$email) {
                return redirect()->to('email/detail/' . $username)->with('error', 'Email account not found.');
            }

            if ($newNip === $email['nip']) {
                return redirect()->to('email/detail/' . $username)->with('info', 'No changes detected. NIP is already up to date.');
            }

            // NIP can be empty
            // if (empty($newNip)) {
            //     return redirect()->to('email/detail/' . $username)->with('error', 'NIP cannot be empty.');
            // }

            try {
                $updated = $this->emailModel->update($email['id'], ['nip' => $newNip]);

                if ($updated) {
                    return redirect()->to('email/detail/' . $username)->with('success', 'NIP has been updated successfully.');
                } else {
                    return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update NIP. The database did not report any changes.');
                }
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                log_message('error', 'Database error during NIP update: ' . $e->getMessage());
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update NIP due to a database error.');
            }
        }

        return redirect()->to('email/detail/' . $username);
    }

    public function update_tempat_lahir($username)
    {
        if (strtolower($this->request->getMethod()) === 'post') {
            $newTempatLahir = $this->request->getPost('tempat_lahir');

            $email = $this->emailModel->where('user', $username)->first();
            if (!$email) {
                return redirect()->to('email/detail/' . $username)->with('error', 'Email account not found.');
            }

            if ($newTempatLahir === $email['tempat_lahir']) {
                return redirect()->to('email/detail/' . $username)->with('info', 'No changes detected. Tempat Lahir is already up to date.');
            }

            try {
                $updated = $this->emailModel->update($email['id'], ['tempat_lahir' => $newTempatLahir]);

                if ($updated) {
                    return redirect()->to('email/detail/' . $username)->with('success', 'Tempat Lahir has been updated successfully.');
                } else {
                    return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update Tempat Lahir. The database did not report any changes.');
                }
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                log_message('error', 'Database error during Tempat Lahir update: ' . $e->getMessage());
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update Tempat Lahir due to a database error.');
            }
        }

        return redirect()->to('email/detail/' . $username);
    }

    public function update_tanggal_lahir($username)
    {
        if (strtolower($this->request->getMethod()) === 'post') {
            $newTanggalLahir = $this->request->getPost('tanggal_lahir');

            $email = $this->emailModel->where('user', $username)->first();
            if (!$email) {
                return redirect()->to('email/detail/' . $username)->with('error', 'Email account not found.');
            }

            if ($newTanggalLahir === $email['tanggal_lahir']) {
                return redirect()->to('email/detail/' . $username)->with('info', 'No changes detected. Tanggal Lahir is already up to date.');
            }

            try {
                $updated = $this->emailModel->update($email['id'], ['tanggal_lahir' => $newTanggalLahir]);

                if ($updated) {
                    return redirect()->to('email/detail/' . $username)->with('success', 'Tanggal Lahir has been updated successfully.');
                } else {
                    return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update Tanggal Lahir. The database did not report any changes.');
                }
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                log_message('error', 'Database error during Tanggal Lahir update: ' . $e->getMessage());
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update Tanggal Lahir due to a database error.');
            }
        }

        return redirect()->to('email/detail/' . $username);
    }

    public function update_pendidikan($username)
    {
        if (strtolower($this->request->getMethod()) === 'post') {
            $newPendidikan = $this->request->getPost('pendidikan');

            $email = $this->emailModel->where('user', $username)->first();
            if (!$email) {
                return redirect()->to('email/detail/' . $username)->with('error', 'Email account not found.');
            }

            if ($newPendidikan === $email['pendidikan']) {
                return redirect()->to('email/detail/' . $username)->with('info', 'No changes detected. Pendidikan is already up to date.');
            }

            try {
                $updated = $this->emailModel->update($email['id'], ['pendidikan' => $newPendidikan]);

                if ($updated) {
                    return redirect()->to('email/detail/' . $username)->with('success', 'Pendidikan has been updated successfully.');
                } else {
                    return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update Pendidikan. The database did not report any changes.');
                }
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                log_message('error', 'Database error during Pendidikan update: ' . $e->getMessage());
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update Pendidikan due to a database error.');
            }
        }

        return redirect()->to('email/detail/' . $username);
    }

    public function update_jabatan($username)
    {
        if (strtolower($this->request->getMethod()) === 'post') {
            $newJabatan = $this->request->getPost('jabatan');

            $email = $this->emailModel->where('user', $username)->first();
            if (!$email) {
                return redirect()->to('email/detail/' . $username)->with('error', 'Email account not found.');
            }

            if ($newJabatan === $email['jabatan']) {
                return redirect()->to('email/detail/' . $username)->with('info', 'No changes detected. Jabatan is already up to date.');
            }

            try {
                $updated = $this->emailModel->update($email['id'], ['jabatan' => $newJabatan]);

                if ($updated) {
                    return redirect()->to('email/detail/' . $username)->with('success', 'Jabatan has been updated successfully.');
                } else {
                    return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update Jabatan. The database did not report any changes.');
                }
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                log_message('error', 'Database error during Jabatan update: ' . $e->getMessage());
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update Jabatan due to a database error.');
            }
        }

        return redirect()->to('email/detail/' . $username);
    }

    public function update_gelar_depan($username)
    {
        if (strtolower($this->request->getMethod()) === 'post') {
            $newGelarDepan = $this->request->getPost('gelar_depan');

            $email = $this->emailModel->where('user', $username)->first();
            if (!$email) {
                return redirect()->to('email/detail/' . $username)->with('error', 'Email account not found.');
            }

            if ($newGelarDepan === $email['gelar_depan']) {
                return redirect()->to('email/detail/' . $username)->with('info', 'No changes detected. Gelar Depan is already up to date.');
            }

            try {
                $updated = $this->emailModel->update($email['id'], ['gelar_depan' => $newGelarDepan]);

                if ($updated) {
                    return redirect()->to('email/detail/' . $username)->with('success', 'Gelar Depan has been updated successfully.');
                } else {
                    return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update Gelar Depan. The database did not report any changes.');
                }
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                log_message('error', 'Database error during Gelar Depan update: ' . $e->getMessage());
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update Gelar Depan due to a database error.');
            }
        }

        return redirect()->to('email/detail/' . $username);
    }

    public function update_gelar_belakang($username)
    {
        if (strtolower($this->request->getMethod()) === 'post') {
            $newGelarBelakang = $this->request->getPost('gelar_belakang');

            $email = $this->emailModel->where('user', $username)->first();
            if (!$email) {
                return redirect()->to('email/detail/' . $username)->with('error', 'Email account not found.');
            }

            if ($newGelarBelakang === $email['gelar_belakang']) {
                return redirect()->to('email/detail/' . $username)->with('info', 'No changes detected. Gelar Belakang is already up to date.');
            }

            try {
                $updated = $this->emailModel->update($email['id'], ['gelar_belakang' => $newGelarBelakang]);

                if ($updated) {
                    return redirect()->to('email/detail/' . $username)->with('success', 'Gelar Belakang has been updated successfully.');
                } else {
                    return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update Gelar Belakang. The database did not report any changes.');
                }
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                log_message('error', 'Database error during Gelar Belakang update: ' . $e->getMessage());
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update Gelar Belakang due to a database error.');
            }
        }

        return redirect()->to('email/detail/' . $username);
    }

    public function update_jenis_formasi($username)
    {
        if (strtolower($this->request->getMethod()) === 'post') {
            $newJenisFormasi = $this->request->getPost('jenis_formasi');

            $email = $this->emailModel->where('user', $username)->first();
            if (!$email) {
                return redirect()->to('email/detail/' . $username)->with('error', 'Email account not found.');
            }

            if ($newJenisFormasi === $email['jenis_formasi']) {
                return redirect()->to('email/detail/' . $username)->with('info', 'No changes detected. Jenis Formasi is already up to date.');
            }

            try {
                $updated = $this->emailModel->update($email['id'], ['jenis_formasi' => $newJenisFormasi]);

                if ($updated) {
                    return redirect()->to('email/detail/' . $username)->with('success', 'Jenis Formasi has been updated successfully.');
                } else {
                    return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update Jenis Formasi. The database did not report any changes.');
                }
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                log_message('error', 'Database error during Jenis Formasi update: ' . $e->getMessage());
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update Jenis Formasi due to a database error.');
            }
        }

        return redirect()->to('email/detail/' . $username);
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
            $childrenIds = array_column($children, 'id');

            // Find all emails belonging to this unit AND all its children
            $allUnitIds = array_merge([$unitKerjaId], $childrenIds);

            $perPage = $this->request->getGet('per_page') ?? 100;
            $search = $this->request->getGet('search');
            $nik = $this->request->getGet('nik');
            $nip = $this->request->getGet('nip');
            $jenis_formasi = $this->request->getGet('jenis_formasi');

            $emailBuilder = $this->emailModel->whereIn('unit_kerja_id', $allUnitIds);

            if ($search) {
                $emailBuilder->groupStart()
                    ->like('email', $search)
                    ->orLike('name', $search)
                    ->groupEnd();
            }

            if ($nik) {
                $emailBuilder->like('nik', $nik);
            }

            if ($nip) {
                $emailBuilder->like('nip', $nip);
            }

            if ($jenis_formasi) {
                $emailBuilder->where('jenis_formasi', $jenis_formasi);
            }

            $emails = $emailBuilder->orderBy('unit_kerja_name', 'ASC')
                ->orderBy('name', 'ASC')
                ->paginate($perPage);
            $pager = $this->emailModel->pager;

            $data = [
                'unit_kerja' => $unitKerja,
                'parent_unit' => !empty($unitKerja['parent_id']) ? $this->unitKerjaModel->find($unitKerja['parent_id']) : null,
                'child_units' => $children,
                'emails' => $emails,
                'total_emails' => $pager->getTotal(),
                'pagination' => $pager,
                'per_page' => $perPage,
                'search' => $search,
                'nik' => $nik,
                'nip' => $nip,
                'jenis_formasi' => $jenis_formasi,
                'back_url' => site_url('email'),
            ];

            return view('email/unit_kerja_detail', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }



    public function export_unit_kerja_csv($unitKerjaId)
    {
        try {
            $unitKerja = $this->unitKerjaModel->find($unitKerjaId);

            if (!$unitKerja) {
                throw new Exception('Unit Kerja not found.');
            }

            $unitKerjaName = $unitKerja['nama_unit_kerja'];
            $emails = $this->emailModel->where('unit_kerja', $unitKerjaName)->findAll();
            $totalEmails = count($emails);
            $limit = 50;

            if ($totalEmails <= $limit) {
                // Original logic for a single file
                $filename = url_title($unitKerjaName, '_', true) . '.csv';

                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '"');

                $output = fopen('php://output', 'w');
                fputcsv($output, ['nama', 'emailAddress'], ',');
                foreach ($emails as $email) {
                    fputcsv($output, [$email['name'], $email['email']], ',');
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
                        fputcsv($stream, [$email['name'], $email['email']], ',');
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
                ->orderBy('unit_kerja_name', 'ASC')
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

            foreach ($emails as $email) {
                $dompdf = new Dompdf($options);

                $data = [
                    'email' => $email,
                    'unit_kerja' => $unitKerja,
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
                ->orderBy('unit_kerja_name', 'ASC')
                ->orderBy('name', 'ASC')
                ->findAll();

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

            // Save to local DB
            $this->emailModel->insert([
                'email'      => $data['email'],
                'user'       => explode('@', $data['email'])[0],
                'domain'     => explode('@', $data['email'])[1],
                'unit_kerja' => $data['unitKerja'] ?? null,
                'password'   => $data['password'] ?? null,
                'nik'    => $data['nik'] ?? null,
                'name'       => $data['name'] ?? null,
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
