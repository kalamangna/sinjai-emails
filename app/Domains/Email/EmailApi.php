<?php

namespace App\Domains\Email;

use App\Shared\BaseController;
use App\Domains\Email\EmailModel;
use App\Domains\Email\EmailService;
use App\Domains\UnitKerja\UnitKerjaModel;
use App\Shared\Models\StatusAsnModel;
use App\Domains\Email\EmailExportService;
use Exception;

class EmailApi extends BaseController
{
    private $emailModel;
    private $unitKerjaModel;
    private $statusAsnModel;
    private $emailService;
    private $emailExportService;

    public function __construct()
    {
        $this->emailModel = new EmailModel();
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->statusAsnModel = new StatusAsnModel();
        $this->emailService = new EmailService();
        $this->emailExportService = new EmailExportService();
    }

    public function api_unit_emails($unitKerjaId)
    {
        $unitKerja = $this->unitKerjaModel->find($unitKerjaId);
        if (!$unitKerja) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unit Kerja not found']);
        }

        $statusPppk = $this->statusAsnModel->where('nama_status_asn', 'PPPK')->asArray()->first();
        $statusPppkPw = $this->statusAsnModel->where('nama_status_asn', 'PPPK PARUH WAKTU')->asArray()->first();
        
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

        $children = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->asArray()->findAll();
        $childrenIds = array_column($children, 'id');
        $allUnitIds = array_merge([$unitKerjaId], $childrenIds);

        $search = $this->request->getGet('search');
        $bsre_status = $this->request->getGet('bsre_status');

        $builder = $this->emailModel->withDetails()->whereIn('unit_kerja_id', $allUnitIds);
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
                $nip = $matches[1];
                if (in_array($nip, $addedUsers)) continue;
                $addedUsers[] = $nip;
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

    public function search()
    {
        $q = $this->request->getGet('q');
        if (empty($q) || strlen($q) < 2) {
            return $this->response->setJSON([]);
        }

        $results = $this->emailModel
            ->select('email, name, user, nik, nip')
            ->like('email', $q)
            ->orLike('name', $q)
            ->orLike('nik', $q)
            ->orLike('nip', $q)
            ->limit(10)
            ->findAll();

        return $this->response->setJSON($results);
    }
}
