<?php

namespace App\Domains\Email;

use App\Shared\BaseController;
use App\Shared\Libraries\CpanelApi;
use App\Shared\Models\AppSettingModel;
use App\Shared\Models\StatusAsnModel;
use App\Shared\Models\EselonModel;
use App\Domains\UnitKerja\UnitKerjaModel;
use App\Shared\Services\SyncService;
use CodeIgniter\Controller;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;

class Email extends BaseController
{
    private $emailModel;
    private $pkModel;
    private $eselonModel;
    private $unitKerjaModel;
    private $statusAsnModel;
    private $emailExportService;
    private $syncService;
    private $emailService;

    public function __construct()
    {
        $this->emailModel = new EmailModel();
        $this->pkModel = new PkModel();
        $this->eselonModel = new EselonModel();
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->statusAsnModel = new StatusAsnModel();
        $this->emailExportService = new EmailExportService();
        $this->syncService = new SyncService();
        $this->emailService = new EmailService();
    }

    public function eselon_list()
    {
        $data['eselons'] = $this->eselonModel->orderBy('nama_eselon', 'ASC')->findAll();
        $data['title'] = 'Eselon';
        return view('email/eselon_list', $data);
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
            $data['pagination'] = $data['pager'];
            $data['bsre_status_options'] = $data['bsre_status_labels'];

            return view('email/index', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function unit_kerja_list()
    {
        try {
            $navData = $this->emailService->getGlobalNavigationData();
            $data = [
                'title' => 'Unit Kerja',
                'unit_kerja' => $navData['unit_kerja_nav'],
                'back_url' => site_url('/')
            ];
            return view('email/unit_kerja_list', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
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
        } catch (Exception $e) {
            $message = 'Failed to synchronize: ' . $e->getMessage();
            if (is_cli()) {
                return ['success' => false, 'message' => $message];
            }
            return redirect()->to('email')->with('error', $message);
        }
    }

    public function detail($username)
    {
        try {
            $data = $this->emailService->getEmailDetail($username);
            $data['title'] = 'Detail Akun';
            $data['back_url'] = site_url('email');
            return view('email/detail', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['title'] = 'Detail Akun';
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function edit_profile($username)
    {
        try {
            $data = $this->emailService->getEmailDetail($username);
            $data['title'] = 'Edit Profil';
            return view('email/edit_profile', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function edit_password($username)
    {
        try {
            $data = $this->emailService->getEmailDetail($username);
            $data['title'] = 'Edit Password';
            return view('email/edit_password', $data);
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            log_message('error', 'Error updating password: ' . $e->getMessage());
            return redirect()->to('email/edit_password/' . $username)->with('error', 'Gagal memperbarui password: ' . $e->getMessage());
        }
    }

    public function update_details($username)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('email/detail/' . $username)->with('error', 'Metode permintaan tidak valid.');
        }

        $currentData = $this->emailService->getEmailDetail($username);
        $currentEmail = $currentData['email']['email'];

        $updateData = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email') ?: $currentEmail,
            'gelar_depan' => $this->request->getPost('gelar_depan'),
            'gelar_belakang' => $this->request->getPost('gelar_belakang'),
            'nik' => $this->request->getPost('nik'),
            'nip' => $this->request->getPost('nip'),
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            'pendidikan' => $this->request->getPost('pendidikan'),
            'jabatan' => $this->request->getPost('jabatan'),
            'golongan' => $this->request->getPost('golongan'),
            'status_asn_id' => $this->request->getPost('status_asn') ?: null,
            'eselon_id' => $this->request->getPost('eselon') ?: null,
            'unit_kerja_id' => $this->request->getPost('unit_kerja_id') ?: null,
            'pimpinan' => $this->request->getPost('pimpinan'),
            'pimpinan_desa' => $this->request->getPost('pimpinan_desa'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir') ?: null,
        ];

        try {
            $this->emailService->updateEmailDetails($username, $updateData);
            return redirect()->to('email/detail/' . $username)->with('success', 'Data profil berhasil diperbarui.');
        } catch (Exception $e) {
            log_message('error', 'Database error during email details update: ' . $e->getMessage());
            return redirect()->to('email/detail/' . $username)->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function unit_kerja_detail($unitKerjaId)
    {
        try {
            $params = [
                'per_page' => $this->request->getGet('per_page'),
                'search' => $this->request->getGet('search'),
                'status_asn' => $this->request->getGet('status_asn'),
                'bsre_status' => $this->request->getGet('bsre_status'),
                'pimpinan_desa' => $this->request->getGet('pimpinan_desa'),
            ];

            $data = $this->emailService->getUnitKerjaDetail($unitKerjaId, $params);

            $data['title'] = $data['unit_kerja']['nama_unit_kerja'];
            $data['per_page'] = $params['per_page'] ?? 100;
            $data['search'] = $params['search'];
            $data['status_asn'] = $params['status_asn'];
            $data['bsre_status'] = $params['bsre_status'];
            $data['pimpinan_desa'] = $params['pimpinan_desa'] ?? 1;
            $data['back_url'] = site_url('email');

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

            $total_emails = $emailBuilder->countAllResults(false);

            $emails = $emailBuilder->orderBy('uk.nama_unit_kerja', 'ASC')
                ->orderBy('jabatan', 'ASC')
                ->orderBy('name', 'ASC')
                ->paginate($perPage);
            $pager = $this->emailModel->pager;

            $bsre_status_options = [
                'ISSUE' => 'ISSUE',
                'EXPIRED' => 'EXPIRED',
                'RENEW' => 'RENEW',
                'WAITING_FOR_VERIFICATION' => 'WAITING_FOR_VERIFICATION',
                'NEW' => 'NEW',
                'NO_CERTIFICATE' => 'NO_CERTIFICATE',
                'NOT_REGISTERED' => 'NOT_REGISTERED',
                'SUSPEND' => 'SUSPEND',
                'REVOKE' => 'REVOKE',
                'not_synced' => 'NOT_SYNCED'
            ];

            $data = [
                'title' => "Eselon " . $eselon['nama_eselon'],
                'eselon' => $eselon,
                'emails' => $emails,
                'total_emails' => $total_emails,
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

    public function api_unit_emails($unitKerjaId)
    {
        $unitKerja = $this->unitKerjaModel->find($unitKerjaId);
        if (!$unitKerja) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unit Kerja not found']);
        }

        $statusPppk = $this->statusAsnModel->where('nama_status_asn', 'PPPK')->first();
        $statusPppkPw = $this->statusAsnModel->where('nama_status_asn', 'PPPK PARUH WAKTU')->first();
        
        $pkType = $this->request->getGet('pk_type');
        $allowedStatusIds = [];
        
        if ($pkType === 'pppk') {
            if ($statusPppk) $allowedStatusIds[] = $statusPppk['id'];
        } elseif ($pkType === 'pppk_pw') {
            if ($statusPppkPw) $allowedStatusIds[] = $statusPppkPw['id'];
        } else {
            // Default to both if not specified (legacy behavior)
            if ($statusPppk) $allowedStatusIds[] = $statusPppk['id'];
            if ($statusPppkPw) $allowedStatusIds[] = $statusPppkPw['id'];
        }

        if (empty($allowedStatusIds)) {
            return $this->response->setJSON(['success' => false, 'emails' => [], 'message' => 'Status PPPK belum dikonfigurasi di sistem.']);
        }

        $children = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->findAll();
        $childrenIds = array_column($children, 'id');
        $allUnitIds = array_merge([$unitKerjaId], $childrenIds);

        $search = $this->request->getGet('search');
        $bsre_status = $this->request->getGet('bsre_status');

        $builder = $this->emailModel->whereIn('unit_kerja_id', $allUnitIds);
        $builder->whereIn('emails.status_asn_id', $allowedStatusIds);

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
            ->orderBy('emails.eselon_id IS NULL', 'ASC', false)
            ->orderBy('emails.eselon_id', 'ASC')
            ->orderBy('emails.status_asn_id IS NULL', 'ASC', false)
            ->orderBy('emails.status_asn_id', 'ASC')
            ->orderBy('emails.jabatan IS NULL', 'ASC', false)
            ->orderBy('emails.jabatan', 'ASC')
            ->orderBy('emails.name', 'ASC')
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
            $this->emailExportService->generateAndSavePerjanjianKerja($emailId, $unitId);
            return $this->response->setJSON(['success' => true]);
        } catch (Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function api_download_zip($unitId)
    {
        set_time_limit(0);
        $unitKerja = $this->unitKerjaModel->find($unitId);
        if (!$unitKerja) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unit Kerja not found']);
        }

        $tempDir = WRITEPATH . 'uploads/temp_export_' . $unitId;
        if (!is_dir($tempDir)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No files generated to zip.']);
        }

        $pdfFiles = [];
        $addedUsers = [];

        $it = new \RecursiveDirectoryIterator($tempDir);
        foreach (new \RecursiveIteratorIterator($it) as $file) {
            if ($file->isDir()) continue;
            
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($tempDir) + 1);
            
            if (preg_match('/_([^_]+)\.pdf$/', $relativePath, $matches)) {
                $username = $matches[1];
                if (in_array($username, $addedUsers)) continue;
                $addedUsers[] = $username;
            }
            $pdfFiles[] = $relativePath;
        }

        if (empty($pdfFiles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Temp folder is empty.']);
        }

        $limit = 250;
        $chunks = array_chunk($pdfFiles, $limit);
        $generatedZips = [];
        $baseName = url_title($unitKerja['nama_unit_kerja'], '_', true);

        // Detect type for filename
        $typeLabel = '';
        if (is_dir($tempDir . '/PPPK')) $typeLabel = 'pppk_';
        if (is_dir($tempDir . '/PPPK_PARUH_WAKTU')) $typeLabel = 'paruh_waktu_';

        foreach ($chunks as $index => $chunk) {
            $zip = new \ZipArchive();
            $partSuffix = (count($chunks) > 1) ? '_part_' . ($index + 1) : '';
            $zipFileName = 'perjanjian_kerja_' . $typeLabel . $baseName . $partSuffix . '.zip';
            $zipFilePath = WRITEPATH . 'uploads/' . $zipFileName;

            if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                log_message('error', 'Failed to create zip: ' . $zipFileName);
                continue;
            }

            foreach ($chunk as $file) {
                $zip->addFile($tempDir . '/' . $file, $file);
            }
            $zip->close();
            $generatedZips[] = $zipFileName;
        }

        // Cleanup
        $it = new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($tempDir);

        return $this->response->setJSON(['success' => true, 'files' => $generatedZips]);
    }

    public function download_zip_file($filename)
    {
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            throw new \Exception('Invalid filename');
        }

        $path = WRITEPATH . 'uploads/' . $filename;
        if (file_exists($path)) {
            return $this->response->download($path, null);
        } else {
            throw new \CodeIgniter\Exceptions\PageNotFoundException($filename . ' not found');
        }
    }

    public function export_unit_kerja_csv($unitKerjaId)
    {
        try {
            $params = [
                'search' => $this->request->getGet('search'),
                'status_asn' => $this->request->getGet('status_asn'),
                'bsre_status' => $this->request->getGet('bsre_status'),
            ];

            $result = $this->emailExportService->generateUnitKerjaCsv($unitKerjaId, $params);

            if ($result['type'] === 'csv') {
                return $this->response->download($result['path'], null)->setFileName($result['filename']);
            } else {
                return $this->response->download($result['path'], null)->setFileName($result['filename']);
            }
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function export_single_perjanjian_kerja_pdf($username)
    {
        try {
            $result = $this->emailExportService->generatePerjanjianKerjaPdf($username);
            $result['dompdf']->stream($result['filename'], ["Attachment" => true]);
            exit();
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function export_perjanjian_kerja_pdf($unitKerjaId)
    {
        try {
            $result = $this->emailExportService->generatePerjanjianKerjaZip($unitKerjaId);

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
            header('Content-Length: ' . filesize($result['path']));
            readfile($result['path']);
            unlink($result['path']);
            exit();
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function export_unit_kerja_pdf($unitKerjaId)
    {
        try {
            $search = $this->request->getGet('search');
            $status_asn = $this->request->getGet('status_asn');
            $bsre_status = $this->request->getGet('bsre_status');
            $pimpinan_desa = $this->request->getGet('pimpinan_desa') ?? 1;

            $result = $this->emailExportService->generateUnitKerjaPdf(
                $unitKerjaId,
                $search,
                $status_asn,
                $bsre_status,
                $pimpinan_desa
            );

            $result['dompdf']->stream($result['filename'], ["Attachment" => true]);
            exit();
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function export_account_detail_pdf($unitKerjaId)
    {
        try {
            $search = $this->request->getGet('search');
            $status_asn = $this->request->getGet('status_asn');
            $bsre_status = $this->request->getGet('bsre_status');
            $pimpinan_desa = $this->request->getGet('pimpinan_desa') ?? 1;

            $result = $this->emailExportService->generateAccountDetailPdf(
                $unitKerjaId,
                $search,
                $status_asn,
                $bsre_status,
                $pimpinan_desa
            );

            $result['dompdf']->stream($result['filename'], ["Attachment" => true]);
            exit();
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
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
        } catch (Exception $e) {
            log_message('error', 'Failed to delete email: ' . $e->getMessage());
            $this->emailModel->delete($id);
            return redirect()->back()->with('error', 'Failed to delete email account from cPanel, but removed from local list.');
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
            $email = $this->emailService->createSingleEmail($data);
            return $this->response->setJSON(['success' => true, 'email' => $data['email']]);
        } catch (Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
