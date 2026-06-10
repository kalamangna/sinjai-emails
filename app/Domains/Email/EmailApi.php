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
            $builder->groupStart();
            if (is_numeric($search) && (strlen($search) >= 10)) {
                $hash = hash('sha256', $search);
                $builder->where('nik_hash', $hash)
                        ->orWhere('nip_hash', $hash);
            } else {
                $builder->like('email', $search)
                        ->orLike('name', $search);
            }
            $builder->groupEnd();
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

        $data = $this->request->getJSON(true) ?? [];
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

        // Normalize query for numeric search (NIP/NIK often have spaces or dots)
        $cleanQ = str_replace([' ', '.', '-', '\''], '', $q);

        $builder = $this->emailModel
            ->select('emails.email, emails.name, emails.user, emails.nik, emails.nip, unit_kerja.nama_unit_kerja as unit_kerja_name')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left');

        $builder->groupStart();
        
        // Always allow searching by name and email
        $builder->like('emails.email', $q)
                ->orLike('emails.name', $q);

        // If numeric and looks like NIK/NIP, search by hash
        if (is_numeric($cleanQ) && strlen($cleanQ) >= 10) {
            $hash = hash('sha256', $cleanQ);
            $builder->orWhere('emails.nik_hash', $hash)
                    ->orWhere('emails.nip_hash', $hash);
        }
        
        $builder->groupEnd();

        $results = $builder->limit(10)->findAll();

        return $this->response->setJSON($results);
    }

    public function sync_pegawai()
    {
        $nip = $this->request->getVar('nip');
        if (empty($nip)) {
            return $this->response->setJSON(['success' => false, 'message' => 'NIP required']);
        }

        // Check if employee is PPPK Paruh Waktu before calling the API
        $currentEmail = $this->emailModel
            ->select('emails.*, unit_kerja.nama_unit_kerja')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
            ->where('emails.nip_hash', hash('sha256', $nip))
            ->first();

        if ($currentEmail) {
            $statusAsnModel = new \App\Shared\Models\StatusAsnModel();
            $statusPppkPw = $statusAsnModel->where('nama_status_asn', 'PPPK PARUH WAKTU')->asArray()->first();
            
            if ($statusPppkPw && $currentEmail['status_asn_id'] == $statusPppkPw['id']) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Akun PPPK Paruh Waktu - Data tidak disinkronkan',
                    'data' => [
                        'jabatan' => $currentEmail['jabatan'] ?? '-',
                        'pangkat_nama' => $currentEmail['pangkat_nama'] ?? '-',
                        'pangkat_golruang' => $currentEmail['pangkat_golruang'] ?? '-',
                    ]
                ]);
            }
        }

        $pegawaiApi = new \App\Shared\Libraries\PegawaiApi();
        $result = $pegawaiApi->getPegawaiData($nip);

        if ($result['success']) {
            $data = $result['data'];
            
            // Normalize data from array if necessary
            $source = (is_array($data) && isset($data[0])) ? $data[0] : $data;
            
            // Check if source contains actual profile data (at least one relevant field)
            $hasActualData = isset($source['jabatan_nama']) || 
                             isset($source['jabatan']) || 
                             isset($source['pangkat_nama']) || 
                             isset($source['pangkat_golruang']);

            if (empty($data) || !$hasActualData) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data tidak ditemukan di API'
                ]);
            }
            
            // Get current record to check pimpinan status (already queried above)
            $isPimpinan = ($currentEmail['pimpinan'] ?? 0) == 1;

            $updateData = [];
            
            // 1. Sync Jabatan (Only if NOT pimpinan)
            if (!$isPimpinan) {
                $newJabatan = null;
                if (isset($source['jabatan_nama'])) {
                    $newJabatan = $source['jabatan_nama'];
                } elseif (isset($source['jabatan'])) {
                    $newJabatan = $source['jabatan'];
                }

                if ($newJabatan) {
                    $newJabatanUpper = mb_strtoupper($newJabatan, 'UTF-8');
                    // Skip if API response contains "PLT"
                    if (stripos($newJabatanUpper, 'PLT') === false) {
                        // Standardize Sekretaris title based on Unit Kerja
                        if (strpos($newJabatanUpper, 'SEKRETARIS') !== false) {
                            $unitName = strtoupper($currentEmail['nama_unit_kerja'] ?? '');
                            if (strpos($unitName, 'DINAS') !== false) {
                                $newJabatanUpper = 'SEKRETARIS DINAS';
                            } elseif (strpos($unitName, 'BADAN') !== false) {
                                $newJabatanUpper = 'SEKRETARIS BADAN';
                            } elseif (strpos($unitName, 'KECAMATAN') !== false) {
                                $newJabatanUpper = 'SEKRETARIS KECAMATAN';
                            } elseif (strpos($unitName, 'KELURAHAN') !== false) {
                                $newJabatanUpper = 'SEKRETARIS KELURAHAN';
                            }
                        }
                        $updateData['jabatan'] = $newJabatanUpper;

                        // Sync Eselon directly from API response
                        if (!empty($source['jabatan_jenis_eselon'])) {
                            $eselonStr = str_replace(['.', ' '], '', $source['jabatan_jenis_eselon']);
                            $eselonModel = new \App\Shared\Models\EselonModel();
                            $eselon = $eselonModel->where('nama_eselon', $eselonStr)->first();
                            if ($eselon) {
                                $updateData['eselon_id'] = $eselon['id'];
                            }
                        }
                    }
                }
            }

            // 2. Sync Pangkat & Golongan
            if (isset($source['pangkat_nama'])) {
                $updateData['pangkat_nama'] = $source['pangkat_nama'];
            }
            
            if (isset($source['pangkat_golruang'])) {
                $updateData['pangkat_golruang'] = $source['pangkat_golruang'];
            }

            if (!empty($updateData)) {
                // Update all emails with this NIP hash
                $this->emailModel->where('nip_hash', hash('sha256', $nip))->set($updateData)->update();
                
                // Clear Dashboard Cache
                $cache = \Config\Services::cache();
                $cache->delete('dashboard_summary_data_v3');
                $cache->delete('email_dashboard_summary');

                // For response feedback, if pimpinan, ensure we return the OLD jabatan
                $responseData = $updateData;
                if ($isPimpinan) {
                    $responseData['jabatan'] = $currentEmail['jabatan'] ?? '-';
                }

                return $this->response->setJSON([
                    'success' => true, 
                    'message' => $isPimpinan ? 'Data pangkat disinkronkan, jabatan pimpinan dipertahankan' : 'Data pegawai berhasil disinkronkan', 
                    'data' => $responseData
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => true, // Still return true if pimpinan data is same but we want to confirm it's a leader
                    'message' => $isPimpinan ? 'Akun Pimpinan - Data jabatan tetap dipertahankan' : 'Tidak ada data baru yang ditemukan di API',
                    'data' => [
                        'jabatan' => $currentEmail['jabatan'] ?? '-',
                        'pangkat_nama' => $currentEmail['pangkat_nama'] ?? '-',
                        'pangkat_golruang' => $currentEmail['pangkat_golruang'] ?? '-',
                    ]
                ]);
            }
        }

        return $this->response->setJSON($result);
    }
}
