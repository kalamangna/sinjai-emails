<?php

namespace App\Domains\Batch\Controllers;

use App\Shared\BaseController;
use App\Domains\UnitKerja\Models\UnitKerjaModel;
use App\Shared\Models\StatusAsnModel;
use App\Domains\Batch\Services\EmailBatchService;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class BatchController extends BaseController
{
    private $unitKerjaModel;
    private $statusAsnModel;
    private $emailBatchService;

    public function __construct()
    {
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->statusAsnModel = new StatusAsnModel();
        $this->emailBatchService = new EmailBatchService();
    }

    public function downloadTemplate()
    {
        $header = ['nama', 'nip', 'nik'];
        $this->generateTemplate($header, 'batch-create.xlsx');
    }

    public function downloadUpdateTemplate()
    {
        $header = ['identifier', 'name', 'nik', 'nip', 'jabatan', 'golongan', 'pendidikan', 'gelar_depan', 'gelar_belakang', 'tempat_lahir', 'tanggal_lahir', 'unit_kerja_id'];
        $this->generateTemplate($header, 'batch-update.xlsx');
    }

    public function downloadPkTemplate()
    {
        $header = ['identifier', 'nomor', 'gaji_nominal', 'gaji_terbilang', 'tanggal_kontrak_awal', 'tanggal_kontrak_akhir'];
        $this->generateTemplate($header, 'batch-pk.xlsx');
    }

    public function downloadUnitKerjaTemplate()
    {
        $header = ['nama_unit_kerja', 'parent_id'];
        $this->generateTemplate($header, 'batch-unit-kerja.xlsx');
    }

    private function generateTemplate(array $header, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($header, NULL, 'A1');

        $writer = new XlsxWriter($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. $filename .'"');
        $writer->save('php://output');
        exit();
    }

    public function importGenericSpreadsheet()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $file = $this->request->getFile('spreadsheet_file');
        $expectedHeaderString = $this->request->getPost('expected_headers');

        if (!$file || !$file->isValid() || $file->getClientMimeType() !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'File tidak valid. Harap unggah file XLSX.']);
        }

        if (empty($expectedHeaderString)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Tipe template tidak dispesifikasikan.']);
        }
        
        $expectedHeader = explode(',', $expectedHeaderString);

        try {
            $reader = new Xlsx();
            $spreadsheet = $reader->load($file->getTempName());
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            
            $sheetData = array_filter($sheetData, fn($row) => !empty(implode('', array_map('trim', $row))));
            
            $header = array_values(array_shift($sheetData));

            // Trim headers from file
            $header = array_map('trim', $header);

            if ($header !== $expectedHeader) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Header file tidak cocok. Harap gunakan template yang sesuai.']);
            }

            $records = [];
            foreach ($sheetData as $row) {
                $rowData = array_values($row);
                $records[] = array_combine($expectedHeader, $rowData);
            }

            return $this->response->setJSON(['success' => true, 'data' => $records]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Gagal memproses file: ' . $e->getMessage()]);
        }
    }

    public function index()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
        $data['title'] = 'Buat Akun Massal';
        return view('batch/create', $data);
    }

    public function update()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
        $data['eselon_options'] = (new \App\Shared\Models\EselonModel())->orderBy('nama_eselon', 'ASC')->findAll();
        $data['title'] = 'Edit Akun Massal';
        return view('batch/update', $data);
    }

    public function pk()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
        $data['eselon_options'] = (new \App\Shared\Models\EselonModel())->orderBy('nama_eselon', 'ASC')->findAll();
        $data['title'] = 'Edit PK Massal';
        return view('batch/pk', $data);
    }

    public function saveBatchUpdate()
    {
        $method = $this->request->getMethod();
        $headers = $this->request->getHeaders();
        log_message('debug', "Batch Update Request - Method: {$method}");
        log_message('debug', "Batch Update Headers: " . json_encode($headers));

        if (strtolower($method) !== 'post') {
            log_message('error', 'Batch Update received GET request. Possible server redirect issue.');
            
            $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'unknown';
            $isSecure = $this->request->isSecure() ? 'YES' : 'NO';
            $baseUrl = config('App')->baseURL;
            $uri = (string)$this->request->getUri();

            $msg = "Permintaan ditolak (Diterima sebagai {$method}, seharusnya POST).\n\n";
            $msg .= "DIAGNOSA SERVER:\n";
            $msg .= "- Proto: {$proto}\n";
            $msg .= "- Is Secure (Detect): {$isSecure}\n";
            $msg .= "- BaseURL Config: {$baseUrl}\n";
            $msg .= "- Detected URI: {$uri}\n\n";
            $msg .= "SOLUSI: Pastikan app.baseURL di .env server menggunakan https://tte.sinjaikab.go.id/ dan lakukan Hard Refresh.";

            return $this->response->setJSON([
                'success' => false, 
                'message' => $msg
            ]);
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
        
        // Handle Base64 payload WAF bypass
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

        if (empty($data) || !isset($data['identifiers']) || !is_array($data['identifiers'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No identifiers provided.']);
        }

        try {
            $results = $this->emailBatchService->processBatchUpdate($data);
            $this->sendBatchNotification('UPDATE', $results);

            // Audit Log
            helper('audit');
            $successCount = count(array_filter($results, fn($r) => $r['success'] ?? false));
            log_audit('BATCH_UPDATE', 'Email', null, "Batch update: $successCount akun berhasil diperbarui.");

            // Clear Dashboard Cache
            \App\Shared\Services\CacheService::invalidateDashboard();

            return $this->response->setJSON(['success' => true, 'results' => $results]);
        } catch (\Throwable $e) {
            log_message('error', 'Batch Update Failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage() ?: 'Internal Server Error (Batch Update)']);
        }
    }

    public function saveBatchCreate()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/email');
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
        if (empty($data)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No data provided.']);
        }

        try {
            $results = $this->emailBatchService->processBatchCreate($data);
            $this->sendBatchNotification('CREATE', $results);

            // Audit Log
            helper('audit');
            $successCount = count(array_filter($results, fn($r) => $r['success'] ?? false));
            log_audit('BATCH_CREATE', 'Email', null, "Batch create: $successCount akun berhasil dibuat.");

            // Clear Dashboard Cache
            \App\Shared\Services\CacheService::invalidateDashboard();

            return $this->response->setJSON(['success' => true, 'results' => $results]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function sendBatchNotification(string $type, array $results)
    {
        try {
            $successCount = 0;
            $failCount = 0;
            
            foreach ($results as $res) {
                if (isset($res['success']) && $res['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }

            if ($successCount > 0 || $failCount > 0) {
                $adminName = session()->get('name') ?? 'Admin';
                $emoji = $type === 'CREATE' ? '✅' : '🔄';
                $label = $type === 'CREATE' ? 'BUAT AKUN MASSAL' : 'UPDATE AKUN MASSAL';

                $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
                $builder->setTitle($label, $emoji)
                        ->addDivider()
                        ->addKeyValue('Dieksekusi oleh', "<b>$adminName</b>", '👤')
                        ->addKeyValue('Berhasil', "<b>$successCount</b> Akun", '✅')
                        ->addKeyValue('Gagal', "<b>$failCount</b> Akun", '❌')
                        ->addText("\n🕒 " . date('d M Y, H:i:s'));

                $telegram = new \App\Shared\Libraries\TelegramLibrary();
                $telegram->sendMessage($builder->build());
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to send batch Telegram notification: ' . $e->getMessage());
        }
    }
}
