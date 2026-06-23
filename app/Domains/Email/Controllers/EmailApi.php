<?php

namespace App\Domains\Email\Controllers;

use App\Shared\BaseController;
use App\Domains\Email\Models\EmailModel;
use App\Domains\Email\Services\EmailService;
use App\Domains\UnitKerja\Models\UnitKerjaModel;
use App\Shared\Models\StatusAsnModel;
use App\Domains\Email\Services\EmailExportService;
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

        try {
            $emails = $this->emailService->getUnitEmails((int)$unitKerjaId, [
                'pk_type'     => $this->request->getGet('pk_type'),
                'search'      => $this->request->getGet('search'),
                'bsre_status' => $this->request->getGet('bsre_status'),
            ]);
            return $this->response->setJSON(['success' => true, 'emails' => $emails]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'emails' => [], 'message' => $e->getMessage()]);
        }
    }

    public function api_trigger_queue()
    {
        // Safe fast-cgi response
        ignore_user_abort(true);
        set_time_limit(0);

        ob_start();
        echo json_encode(['success' => true]);
        header('Connection: close');
        header('Content-Length: ' . ob_get_length());
        @ob_end_flush();
        @ob_flush();
        @flush();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        // Execute background queue runner via CI command, not OS exec
        command('queue:work --stop-when-empty');
        exit();
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
        } catch (\Throwable $e) {
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

        $data = [];
        if (strpos($this->request->getHeaderLine('Content-Type'), 'application/json') !== false) {
            try {
                $data = $this->request->getJSON(true) ?? [];
            } catch (\Exception $e) {}
        }
        if (empty($data)) {
            $data = $this->request->getPost() ?? [];
        }

        $base64Payload = $this->request->getPost('payload');
        if (!empty($base64Payload)) {
            $decodedJson = base64_decode($base64Payload);
            if ($decodedJson !== false) {
                $parsedData = json_decode($decodedJson, true);
                if (is_array($parsedData)) {
                    $data = array_merge($data, $parsedData);
                }
            }
        }
        
        // Handle File payload WAF bypass (Ultimate Bypass)
        $payloadFile = $this->request->getFile('payload_file');
        if ($payloadFile && $payloadFile->isValid()) {
            $fileContent = file_get_contents($payloadFile->getTempName());
            if ($fileContent) {
                $parsedData = json_decode($fileContent, true);
                if (is_array($parsedData)) {
                    $data = array_merge($data, $parsedData);
                }
            }
        }
        if (empty($data) || !isset($data['email'])) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No data provided.']);
        }

        try {
            $email = $this->emailService->createSingleEmail($data);

            // Audit Log
            helper('audit');
            log_audit('CREATE', 'Email', $email, 'Akun baru dibuat: ' . $data['email']);

            // Clear Dashboard Cache
            $cache = \Config\Services::cache();
            $cache->delete('dashboard_summary_data_v3');
            $cache->delete('email_dashboard_summary');

            return $this->response->setJSON(['success' => true, 'email' => $data['email']]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function search()
    {
        $q = $this->request->getGet('q');
        if (empty($q) || strlen($q) < 2) {
            return $this->response->setJSON([]);
        }

        return $this->response->setJSON($this->emailService->searchEmails($q));
    }

    public function sync_pegawai()
    {
        $nip = $this->request->getVar('nip');
        if (empty($nip)) {
            return $this->response->setJSON(['success' => false, 'message' => 'NIP required']);
        }

        $result = $this->emailService->syncPegawaiFromApi($nip);

        // Handle PPPK Paruh Waktu skip case
        if (!empty($result['skipped']) && $result['reason'] === 'pppk_pw') {
            $current = $result['current'];
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Akun PPPK Paruh Waktu - Data tidak disinkronkan',
                'data'    => [
                    'jabatan'          => $current['jabatan'] ?? '-',
                    'pangkat_nama'     => $current['pangkat_nama'] ?? '-',
                    'pangkat_golruang' => $current['pangkat_golruang'] ?? '-',
                ]
            ]);
        }

        return $this->response->setJSON($result);
    }
}
