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

        $data = $this->request->getJSON(true);
        if (empty($data) || !isset($data['email'])) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No data provided.']);
        }

        try {
            $email = $this->emailService->createSingleEmail($data);
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

        $results = $this->emailModel
            ->select('emails.email, emails.name, emails.user, emails.nik, emails.nip, unit_kerja.nama_unit_kerja as unit_kerja_name')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
            ->groupStart()
                ->like('emails.email', $q)
                ->orLike('emails.name', $q)
                ->orLike('emails.nik', $q)
                ->orLike('emails.nip', $q)
            ->groupEnd()
            ->limit(10)
            ->findAll();

        return $this->response->setJSON($results);
    }

    public function sync_pegawai()
    {
        $nip = $this->request->getVar('nip');
        if (empty($nip)) {
            return $this->response->setJSON(['success' => false, 'message' => 'NIP required']);
        }

        // Check if employee is PPPK Paruh Waktu before calling the API
        $currentEmail = $this->emailModel->where('nip', $nip)->first();
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
            
            // Get current record to check pimpinan status
            $currentEmail = $this->emailModel->where('nip', $nip)->first();
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
                        // Standardize Sekretaris title and assign Eselon
                        $targetEselonNames = [];
                        if (strpos($newJabatanUpper, 'SEKRETARIS') !== false) {
                            if (strpos($newJabatanUpper, 'DINAS') !== false) {
                                $newJabatanUpper = 'SEKRETARIS DINAS';
                                $targetEselonNames = ['IIIa', '3a'];
                            } elseif (strpos($newJabatanUpper, 'BADAN') !== false) {
                                $newJabatanUpper = 'SEKRETARIS BADAN';
                                $targetEselonNames = ['IIIa', '3a'];
                            } elseif (strpos($newJabatanUpper, 'KECAMATAN') !== false) {
                                $newJabatanUpper = 'SEKRETARIS KECAMATAN';
                                $targetEselonNames = ['IIIb', '3b'];
                            } elseif (strpos($newJabatanUpper, 'KELURAHAN') !== false) {
                                $newJabatanUpper = 'SEKRETARIS KELURAHAN';
                                $targetEselonNames = ['IVb', '4b'];
                            }
                        } elseif (strpos($newJabatanUpper, 'KEPALA BIDANG') !== false) {
                            $targetEselonNames = ['IIIb', '3b'];
                        }

                        if (!empty($targetEselonNames)) {
                            $eselonModel = new \App\Shared\Models\EselonModel();
                            $eselon = $eselonModel->whereIn('nama_eselon', $targetEselonNames)->first();
                            if ($eselon) {
                                $updateData['eselon_id'] = $eselon['id'];
                            }
                        }
                        $updateData['jabatan'] = $newJabatanUpper;
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
                // Update all emails with this NIP
                $this->emailModel->where('nip', $nip)->set($updateData)->update();
                
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
