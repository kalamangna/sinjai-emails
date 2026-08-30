<?php

namespace App\Domains\Email\Controllers;

use App\Shared\BaseController;
use App\Domains\Email\Models\EmailModel;
use App\Domains\Email\Services\EmailService;
use App\Domains\UnitKerja\Models\UnitKerjaModel;
use App\Shared\Models\StatusAsnModel;
use App\Domains\Email\Services\EmailExportService;
use Exception;

class EmailApiController extends BaseController
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

    public function apiUnitEmails($unitKerjaId)
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

    public function apiTriggerQueue()
    {
        log_message('info', 'apiTriggerQueue called via HTTP');
        // Release session lock to prevent blocking other user requests
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        // Safe fast-cgi response
        ignore_user_abort(true);
        set_time_limit(0);
        ini_set('memory_limit', '512M');

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

        try {
            log_message('info', 'Executing QueueWorker directly from apiTriggerQueue');
            $worker = new \App\Commands\QueueWorker(service('logger'), service('commands'));
            $worker->run(['stop-when-empty' => true]);
            log_message('info', 'Queue worker finished successfully');
        } catch (\Throwable $e) {
            log_message('error', 'Queue worker failed: ' . $e->getMessage());
        }
        exit();
    }

    public function apiGeneratePdf()
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

    public function apiDownloadZip($unitId)
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

    public function createSingleEmail()
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
            \App\Shared\Services\CacheService::invalidateDashboard();

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

    public function syncPegawai()
    {
        $nip = trim((string)$this->request->getVar('nip'));
        $email = trim((string)$this->request->getVar('email'));

        $record = null;
        if (!empty($email)) {
            $record = $this->emailModel
                ->select('emails.*, unit_kerja.nama_unit_kerja')
                ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                ->where('emails.email', $email)
                ->first();
        } elseif (!empty($nip)) {
            $record = $this->emailModel
                ->select('emails.*, unit_kerja.nama_unit_kerja')
                ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                ->where('emails.nip', $nip)
                ->first();
        }

        if (empty($nip) && $record && !empty($record['nip'])) {
            $nip = $record['nip'];
        }

        // Guard: Jika akun sudah terdata bukan PNS, skip hit API SIMPEG secara langsung
        if ($record && !empty($record['status_asn_id']) && (int)$record['status_asn_id'] !== 1) {
            $unitKerjaName = '';
            $parentUnitKerjaName = '';
            $parentUnitKerjaId = null;
            if (!empty($record['unit_kerja_id'])) {
                $u = $this->unitKerjaModel->find($record['unit_kerja_id']);
                if ($u) {
                    $unitKerjaName = $u['nama_unit_kerja'];
                    if (!empty($u['parent_id'])) {
                        $p = $this->unitKerjaModel->find($u['parent_id']);
                        $parentUnitKerjaName = $p['nama_unit_kerja'] ?? '';
                        $parentUnitKerjaId = $p['id'] ?? null;
                    }
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'skipped' => true,
                'message' => 'Akun bukan berstatus PNS (API SIMPEG dilewati)',
                'data'    => [
                    'jabatan'               => $record['jabatan'] ?? '-',
                    'pangkat_nama'          => $record['pangkat_nama'] ?? '-',
                    'pangkat_golruang'      => $record['pangkat_golruang'] ?? '-',
                    'unit_kerja_id'         => $record['unit_kerja_id'] ?? null,
                    'unit_kerja_name'       => $unitKerjaName ?: ($record['nama_unit_kerja'] ?? ($record['unit_kerja_name'] ?? '-')),
                    'parent_unit_kerja_name'=> $parentUnitKerjaName,
                    'parent_unit_kerja_id'  => $parentUnitKerjaId,
                    'parent_id'             => $parentUnitKerjaId,
                    'eselon_name'           => null,
                ]
            ]);
        }

        if (empty($nip)) {
            return $this->response->setJSON(['success' => false, 'message' => 'NIP tidak ditemukan pada akun']);
        }

        $result = $this->emailService->syncPegawaiFromApi($nip, $email);

        if (!empty($result['skipped'])) {
            return $this->response->setJSON($result);
        }

        return $this->response->setJSON($result);
    }
}
