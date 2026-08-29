<?php

namespace App\Domains\Email\Services;

use App\Domains\Email\Models\EmailModel;
use App\Domains\UnitKerja\Models\UnitKerjaModel;
use App\Shared\Models\StatusAsnModel;
use App\Domains\Email\Models\PkModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Dompdf\Dompdf;
use Dompdf\Options;
use ZipArchive;
use Exception;

class EmailExportService
{
    protected $emailModel;
    protected $unitKerjaModel;
    protected $statusAsnModel;
    protected $pkModel;

    public function __construct()
    {
        $this->emailModel = new EmailModel();
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->statusAsnModel = new StatusAsnModel();
        $this->pkModel = new PkModel();
        require_once APPPATH . 'Shared/Helpers/TanggalHelper.php';
    }

    private function getDompdf()
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        return new Dompdf($options);
    }

    private function getLogoSrc()
    {
        $logoPath = FCPATH . 'logo.png';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            return 'data:image/png;base64,' . $logoData;
        }
        return '';
    }

    private function getGarudaLogoSrc()
    {
        $logoPath = FCPATH . 'garuda.png';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            return 'data:image/png;base64,' . $logoData;
        }
        return '';
    }

    public function generatePimpinanPdf($search = null, $bsre_status = null)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $builder = $this->emailModel->getPimpinanBuilder();
        if ($search) {
            $builder->groupStart();
            $cleanSearch = str_replace([' ', '.', '-', '\''], '', $search);
            $builder->like('email', $search)
                ->orLike('name', $search);

            if (is_numeric($cleanSearch) && strlen($cleanSearch) >= 10) {
                $hash = $cleanSearch;
                $builder->orWhere('nik', $hash)
                    ->orWhere('nip', $hash);
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
            ->orderBy('emails.eselon_id', 'ASC')
            ->orderBy('COALESCE(parent_unit_kerja.nama_unit_kerja, unit_kerja.nama_unit_kerja)', 'ASC', false)
            ->orderBy('unit_kerja.parent_id IS NOT NULL', 'ASC', false)
            ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
            ->orderBy('emails.jabatan', 'ASC')
            ->orderBy('emails.name', 'ASC')
            ->findAll();

        $data = [
            'title' => 'DAFTAR EMAIL & TTE PIMPINAN',
            'subtitle' => 'PEMERINTAH KABUPATEN SINJAI',
            'emails' => $emails,
            'showUnitKerjaColumn' => true,
            'logoSrc' => $this->getLogoSrc(),
            'current_date' => formatTanggal('now'),
        ];

        $html = view('email/exports/pimpinan_pdf', $data);
        $dompdf = $this->getDompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf;
    }

    public function generatePimpinanDesaPdf($search = null, $bsre_status = null)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $builder = $this->emailModel->getPimpinanDesaBuilder();
        if ($search) {
            $builder->groupStart();
            $cleanSearch = str_replace([' ', '.', '-', '\''], '', $search);
            $builder->like('email', $search)
                ->orLike('name', $search);

            if (is_numeric($cleanSearch) && strlen($cleanSearch) >= 10) {
                $hash = $cleanSearch;
                $builder->orWhere('nik', $hash)
                    ->orWhere('nip', $hash);
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
            ->orderBy('emails.eselon_id', 'ASC')
            ->orderBy('COALESCE(parent_unit_kerja.nama_unit_kerja, unit_kerja.nama_unit_kerja)', 'ASC', false)
            ->orderBy('unit_kerja.parent_id IS NOT NULL', 'ASC', false)
            ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
            ->orderBy('emails.jabatan', 'ASC')
            ->orderBy('emails.name', 'ASC')
            ->findAll();

        $data = [
            'title' => 'DAFTAR EMAIL & TTE KEPALA DESA',
            'subtitle' => 'PEMERINTAH KABUPATEN SINJAI',
            'emails' => $emails,
            'showUnitKerjaColumn' => true,
            'logoSrc' => $this->getLogoSrc(),
            'current_date' => formatTanggal('now'),
        ];

        $html = view('email/exports/pimpinan_desa_pdf', $data);
        $dompdf = $this->getDompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf;
    }

    public function generateUnitKerjaPdf($unitKerjaId, $search = null, $status_asn = null, $bsre_status = null, $pimpinan_desa = 1)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $unitKerja = $this->unitKerjaModel->find($unitKerjaId);
        if (!$unitKerja) throw new Exception('Unit Kerja not found.');

        $children = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->findAll();
        $childrenIds = array_column($children, 'id');
        $allUnitIds = array_merge([$unitKerjaId], $childrenIds);

        $isKecamatan = stripos($unitKerja['nama_unit_kerja'], 'Kecamatan') !== false;

        $builder = $this->emailModel->withDetails()
            ->whereIn('unit_kerja_id', $allUnitIds)
            ->orderBy('emails.eselon_id IS NULL', 'ASC', false)
            ->orderBy('emails.eselon_id', 'ASC')
            ->orderBy('emails.status_asn_id IS NULL', 'ASC', false)
            ->orderBy('emails.status_asn_id', 'ASC')
            ->orderBy('emails.jabatan', 'ASC')
            ->orderBy('emails.name', 'ASC');

        if ($isKecamatan && $pimpinan_desa == 0) $builder->where('pimpinan_desa', 0);
        if ($search) {
            $builder->groupStart();
            $cleanSearch = str_replace([' ', '.', '-', '\''], '', $search);
            $builder->like('email', $search)
                ->orLike('name', $search);

            if (is_numeric($cleanSearch) && strlen($cleanSearch) >= 10) {
                $hash = $cleanSearch;
                $builder->orWhere('nik', $hash)
                    ->orWhere('nip', $hash);
            }
            $builder->groupEnd();
        }
        if ($status_asn) $builder->where('emails.status_asn_id', $status_asn);
        if ($bsre_status) {
            if ($bsre_status === 'not_synced') {
                $builder->groupStart()->where('emails.bsre_status', null)->orWhere('emails.bsre_status', '')->groupEnd();
            } else {
                $builder->where('emails.bsre_status', $bsre_status);
            }
        }

        $emails = $builder->findAll();
        $uniqueUnitKerjaIds = array_unique(array_column($emails, 'unit_kerja_id'));
        $showUnitKerjaColumn = count($uniqueUnitKerjaIds) > 1;

        // Apply refined sorting
        $builder = $this->emailModel->withDetails()
            ->whereIn('unit_kerja_id', $allUnitIds);

        if ($isKecamatan && $pimpinan_desa == 0) $builder->where('pimpinan_desa', 0);
        if ($search) {
            $builder->groupStart();
            $cleanSearch = str_replace([' ', '.', '-', '\''], '', $search);
            $builder->like('email', $search)
                ->orLike('name', $search);

            if (is_numeric($cleanSearch) && strlen($cleanSearch) >= 10) {
                $hash = $cleanSearch;
                $builder->orWhere('nik', $hash)
                    ->orWhere('nip', $hash);
            }
            $builder->groupEnd();
        }
        if ($status_asn) $builder->where('emails.status_asn_id', $status_asn);
        if ($bsre_status) {
            if ($bsre_status === 'not_synced') {
                $builder->groupStart()->where('emails.bsre_status', null)->orWhere('emails.bsre_status', '')->groupEnd();
            } else {
                $builder->where('emails.bsre_status', $bsre_status);
            }
        }

        if ($showUnitKerjaColumn) {
            $builder->orderBy('emails.eselon_id IS NULL', 'ASC', false)
                    ->orderBy('emails.eselon_id', 'ASC')
                    ->orderBy('emails.status_asn_id IS NULL', 'ASC', false)
                    ->orderBy('emails.status_asn_id', 'ASC')
                    ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
                    ->orderBy('emails.name', 'ASC');
        } else {
            $builder->orderBy('emails.eselon_id IS NULL', 'ASC', false)
                    ->orderBy('emails.eselon_id', 'ASC')
                    ->orderBy('emails.status_asn_id IS NULL', 'ASC', false)
                    ->orderBy('emails.status_asn_id', 'ASC')
                    ->orderBy('emails.name', 'ASC');
        }

        $emails = $builder->findAll();

        $data = [
            'unit_kerja' => $unitKerja,
            'emails' => $emails,
            'showUnitKerjaColumn' => $showUnitKerjaColumn,
            'logoSrc' => $this->getLogoSrc(),
            'current_date' => formatTanggal('now'),
        ];

        $html = view('email/exports/unit_kerja_pdf', $data);
        $dompdf = $this->getDompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return [
            'dompdf' => $dompdf,
            'filename' => url_title($unitKerja['nama_unit_kerja'] . ' ' . formatBulanTahun('now'), '_', true) . '.pdf'
        ];
    }

    public function generateAccountDetailPdf($unitKerjaId, $search = null, $status_asn = null, $bsre_status = null, $pimpinan_desa = 1)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $unitKerja = $this->unitKerjaModel->find($unitKerjaId);
        if (!$unitKerja) throw new Exception('Unit Kerja not found.');

        $children = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->findAll();
        $childrenIds = array_column($children, 'id');
        $allUnitIds = array_merge([$unitKerjaId], $childrenIds);

        $isKecamatan = stripos($unitKerja['nama_unit_kerja'], 'Kecamatan') !== false;

        $builder = $this->emailModel->withDetails()
            ->whereIn('unit_kerja_id', $allUnitIds)
            ->orderBy('emails.eselon_id IS NULL', 'ASC', false)
            ->orderBy('emails.eselon_id', 'ASC')
            ->orderBy('emails.status_asn_id IS NULL', 'ASC', false)
            ->orderBy('emails.status_asn_id', 'ASC')
            ->orderBy('emails.jabatan', 'ASC')
            ->orderBy('emails.name', 'ASC');

        if ($isKecamatan && $pimpinan_desa == 0) $builder->where('pimpinan_desa', 0);
        if ($search) {
            $builder->groupStart();
            $cleanSearch = str_replace([' ', '.', '-', '\''], '', $search);
            $builder->like('email', $search)
                ->orLike('name', $search);

            if (is_numeric($cleanSearch) && strlen($cleanSearch) >= 10) {
                $hash = $cleanSearch;
                $builder->orWhere('nik', $hash)
                    ->orWhere('nip', $hash);
            }
            $builder->groupEnd();
        }
        if ($status_asn) $builder->where('emails.status_asn_id', $status_asn);
        if ($bsre_status) {
            if ($bsre_status === 'not_synced') {
                $builder->groupStart()->where('emails.bsre_status', null)->orWhere('emails.bsre_status', '')->groupEnd();
            } else {
                $builder->where('emails.bsre_status', $bsre_status);
            }
        }

        $emails = $builder->findAll();
        $uniqueUnitKerjaIds = array_unique(array_column($emails, 'unit_kerja_id'));
        $showUnitKerjaColumn = count($uniqueUnitKerjaIds) > 1;

        // Apply refined sorting
        $builder = $this->emailModel->withDetails()
            ->whereIn('unit_kerja_id', $allUnitIds);

        if ($isKecamatan && $pimpinan_desa == 0) $builder->where('pimpinan_desa', 0);
        if ($search) {
            $builder->groupStart();
            $cleanSearch = str_replace([' ', '.', '-', '\''], '', $search);
            $builder->like('email', $search)
                ->orLike('name', $search);

            if (is_numeric($cleanSearch) && strlen($cleanSearch) >= 10) {
                $hash = $cleanSearch;
                $builder->orWhere('nik', $hash)
                    ->orWhere('nip', $hash);
            }
            $builder->groupEnd();
        }
        if ($status_asn) $builder->where('emails.status_asn_id', $status_asn);
        if ($bsre_status) {
            if ($bsre_status === 'not_synced') {
                $builder->groupStart()->where('emails.bsre_status', null)->orWhere('emails.bsre_status', '')->groupEnd();
            } else {
                $builder->where('emails.bsre_status', $bsre_status);
            }
        }

        if ($showUnitKerjaColumn) {
            $builder->orderBy('emails.eselon_id IS NULL', 'ASC', false)
                    ->orderBy('emails.eselon_id', 'ASC')
                    ->orderBy('emails.status_asn_id IS NULL', 'ASC', false)
                    ->orderBy('emails.status_asn_id', 'ASC')
                    ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
                    ->orderBy('emails.name', 'ASC');
        } else {
            $builder->orderBy('emails.eselon_id IS NULL', 'ASC', false)
                    ->orderBy('emails.eselon_id', 'ASC')
                    ->orderBy('emails.status_asn_id IS NULL', 'ASC', false)
                    ->orderBy('emails.status_asn_id', 'ASC')
                    ->orderBy('emails.name', 'ASC');
        }

        $emails = $builder->findAll();

        $data = [
            'unit_kerja' => $unitKerja,
            'emails' => $emails,
            'showUnitKerjaColumn' => $showUnitKerjaColumn,
            'logoSrc' => $this->getLogoSrc(),
            'current_date' => formatTanggal('now'),
        ];

        $html = view('email/exports/account_detail_pdf', $data);
        $dompdf = $this->getDompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return [
            'dompdf' => $dompdf,
            'filename' => url_title($unitKerja['nama_unit_kerja'] . ' Detail Akun ' . formatBulanTahun('now'), '_', true) . '.pdf'
        ];
    }

    public function generatePerjanjianKerjaPdf($username)
    {
        $email = $this->emailModel->withDetails()->where('emails.user', $username)->asArray()->first();
        if (!$email) throw new Exception('Email account not found.');

        $statusPppk = $this->statusAsnModel->where('nama_status_asn', 'PPPK')->first();
        $statusPppkPw = $this->statusAsnModel->where('nama_status_asn', 'PPPK PARUH WAKTU')->first();

        $isPppk = $statusPppk && $email['status_asn_id'] == $statusPppk['id'];
        $isPppkPw = $statusPppkPw && $email['status_asn_id'] == $statusPppkPw['id'];

        if (!$isPppk && !$isPppkPw) {
            throw new Exception('Perjanjian Kerja hanya tersedia untuk PPPK atau PPPK PARUH WAKTU.');
        }

        $template = $isPppk ? 'email/exports/perjanjian_kerja_pppk_template' : 'email/exports/perjanjian_kerja_template';
        $prefix = $isPppk ? 'pppk_' : 'paruh_waktu_';

        // Fallback to raw unit_kerja column if joined name is missing
        $name = $email['unit_kerja_name'] ?? $email['unit_kerja'] ?? 'N/A';
        
        $unitKerja = [
            'nama_unit_kerja' => $name
        ];
        
        if (!empty($email['parent_unit_kerja_name'])) {
            $unitKerja['nama_unit_kerja'] = $unitKerja['nama_unit_kerja'] . ' - ' . $email['parent_unit_kerja_name'];
        }

        $pk_data = $this->pkModel->where('email', $email['email'])->first();
        $data = [
            'email' => $email,
            'unit_kerja' => $unitKerja,
            'logoSrc' => $this->getGarudaLogoSrc(),
            'pk_data' => $pk_data,
        ];

        $html = view($template, $data);
        $dompdf = $this->getDompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return [
            'dompdf' => $dompdf,
            'filename' => 'perjanjian_kerja_' . $prefix . url_title($email['name'], '_', true) . '_' . ($email['nip'] ?? 'NIP_NONE') . '.pdf'
        ];
    }

    public function generatePerjanjianKerjaZip($unitKerjaId, $pkType = null)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $unitKerja = $this->unitKerjaModel->find($unitKerjaId);
        if ($unitKerja && !empty($unitKerja['parent_id'])) {
            $parentUnit = $this->unitKerjaModel->find($unitKerja['parent_id']);
            if ($parentUnit) $unitKerja['nama_unit_kerja'] = $unitKerja['nama_unit_kerja'] . ', ' . $parentUnit['nama_unit_kerja'];
        }
        if (!$unitKerja) throw new Exception('Unit Kerja not found.');

        $children = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->findAll();
        $childrenIds = array_column($children, 'id');
        
        $statusPppk = $this->statusAsnModel->where('nama_status_asn', 'PPPK')->first();
        $statusPppkPw = $this->statusAsnModel->where('nama_status_asn', 'PPPK PARUH WAKTU')->first();
        
        if (!$statusPppk && !$statusPppkPw) throw new Exception('PPPK status not configured.');

        $allowedStatusIds = [];
        if ($pkType === 'pppk') {
            if ($statusPppk) $allowedStatusIds[] = $statusPppk['id'];
        } elseif ($pkType === 'pppk_pw') {
            if ($statusPppkPw) $allowedStatusIds[] = $statusPppkPw['id'];
        } else {
            if ($statusPppk) $allowedStatusIds[] = $statusPppk['id'];
            if ($statusPppkPw) $allowedStatusIds[] = $statusPppkPw['id'];
        }

        if (empty($allowedStatusIds)) throw new Exception('No matching PPPK status found for this export type.');

        $allUnitIds = array_merge([$unitKerjaId], $childrenIds);
        $emails = $this->emailModel->withDetails()
            ->whereIn('unit_kerja_id', $allUnitIds)
            ->whereIn('emails.status_asn_id', $allowedStatusIds)
            ->orderBy('name', 'ASC')
            ->asArray()
            ->findAll();

        if (empty($emails)) throw new Exception('No email accounts found for this Unit Kerja.');

        // Pre-fetch all PK data to avoid N+1 queries
        $emailList = array_column($emails, 'email');
        $pkRaw = $this->pkModel->whereIn('email', $emailList)->asArray()->findAll();
        $pkMap = [];
        foreach ($pkRaw as $pk) {
            $pkMap[$pk['email']] = $pk;
        }

        $typeLabel = '';
        if ($pkType === 'pppk') $typeLabel = 'pppk_';
        elseif ($pkType === 'pppk_pw') $typeLabel = 'paruh_waktu_';

        $zip = new ZipArchive();
        $zipFileName = 'perjanjian_kerja_' . $typeLabel . url_title($unitKerja['nama_unit_kerja'], '_', true) . '.zip';
        $tempZipPath = WRITEPATH . 'uploads/' . $zipFileName;
        
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new Exception('Cannot create ZIP archive.');
        }

        $logoSrc = $this->getGarudaLogoSrc();
        $addedFiles = [];
        
        foreach ($emails as $email) {
            $uniqueKey = $email['user'];
            if (in_array($uniqueKey, $addedFiles)) continue;
            
            $isPppk = $statusPppk && $email['status_asn_id'] == $statusPppk['id'];
            $template = $isPppk ? 'email/exports/perjanjian_kerja_pppk_template' : 'email/exports/perjanjian_kerja_template';
            $folderName = $isPppk ? 'PPPK' : 'PPPK_PARUH_WAKTU';
            $filePrefix = $isPppk ? 'pppk_' : 'paruh_waktu_';

            $name = $email['unit_kerja_name'] ?? $email['unit_kerja'] ?? 'N/A';
            $itemUnitKerja = [
                'nama_unit_kerja' => $name
            ];
            if (!empty($email['parent_unit_kerja_name'])) {
                $itemUnitKerja['nama_unit_kerja'] = $itemUnitKerja['nama_unit_kerja'] . ' - ' . $email['parent_unit_kerja_name'];
            }

            $dompdf = $this->getDompdf();
            $pk_data = $pkMap[$email['email']] ?? null;
            $data = [
                'email' => $email,
                'unit_kerja' => $itemUnitKerja,
                'logoSrc' => $logoSrc,
                'pk_data' => $pk_data,
            ];
            
            $html = view($template, $data);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $pdfOutput = $dompdf->output();
            $pdfFileName = $folderName . '/perjanjian_kerja_' . $filePrefix . url_title($email['name'], '_', true) . '_' . ($email['nip'] ?? 'NIP_NONE') . '.pdf';
            $zip->addFromString($pdfFileName, $pdfOutput);
            $addedFiles[] = $uniqueKey;
        }
        $zip->close();

        return [
            'path' => $tempZipPath,
            'filename' => $zipFileName
        ];
    }

    public function generateUnitKerjaExcel($unitKerjaId, $params = [])
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $unitKerja = $this->unitKerjaModel->find($unitKerjaId);
        if (!$unitKerja) throw new Exception('Unit Kerja not found.');

        $children = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->findAll();
        $childrenIds = array_column($children, 'id');
        $allUnitIds = array_merge([$unitKerjaId], $childrenIds);

        $builder = $this->emailModel->withDetails()->whereIn('unit_kerja_id', $allUnitIds);
        if (!empty($params['search'])) {
            $search = $params['search'];
            $builder->groupStart();
            $cleanSearch = str_replace([' ', '.', '-', '\''], '', $search);
            $builder->like('emails.email', $search)
                ->orLike('emails.name', $search);

            if (is_numeric($cleanSearch) && strlen($cleanSearch) >= 10) {
                $hash = $cleanSearch;
                $builder->orWhere('emails.nik', $hash)
                    ->orWhere('emails.nip', $hash);
            }
            $builder->groupEnd();
        }
        if (!empty($params['status_asn'])) $builder->where('emails.status_asn_id', $params['status_asn']);
        if (!empty($params['bsre_status'])) {
            if ($params['bsre_status'] === 'not_synced') {
                $builder->groupStart()->where('emails.bsre_status', null)->orWhere('emails.bsre_status', '')->groupEnd();
            } else {
                $builder->where('emails.bsre_status', $params['bsre_status']);
            }
        }

        $emails = $builder->orderBy('emails.name', 'ASC')->findAll();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $header = ['NO', 'NAMA', 'NIP', 'NIK', 'EMAIL', 'PASSWORD', 'UNIT KERJA'];
        $sheet->fromArray($header, NULL, 'A1');
        
        // Styling Header
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        
        // Data
        $data = [];
        $no = 1;
        foreach ($emails as $email) {
            $unitKerjaName = $email['unit_kerja_name'] ?? '';
            
            $data[] = [
                $no++,
                strtoupper($email['name'] ?? ''),
                $email['nip'] ?? '',
                $email['nik'] ?? '',
                $email['email'] ?? '',
                $email['password'] ?? '',
                strtoupper($unitKerjaName)
            ];
        }
        
        if (!empty($data)) {
            $sheet->fromArray($data, NULL, 'A2');
        }
        
        // Auto size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = 'Detail Akun - ' . $unitKerja['nama_unit_kerja'] . '.xlsx';
        $path = WRITEPATH . 'uploads/' . url_title($filename, '_', true);
        
        $writer = new XlsxWriter($spreadsheet);
        $writer->save($path);
        
        return ['type' => 'excel', 'path' => $path, 'filename' => $filename];
    }

    public function generatePnsExcel($params = [])
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $statusPns = $this->statusAsnModel->where('nama_status_asn', 'PNS')->asArray()->first();
        if (!$statusPns) throw new Exception('Status PNS belum dikonfigurasi.');

        $builder = $this->emailModel->withDetails()->where('emails.status_asn_id', $statusPns['id']);

        if (!empty($params['has_nip'])) {
            if ($params['has_nip'] === 'yes') {
                $builder->where('emails.nip !=', '')->where('emails.nip IS NOT NULL');
            } elseif ($params['has_nip'] === 'no') {
                $builder->groupStart()->where('emails.nip', '')->orWhere('emails.nip', null)->groupEnd();
            }
        }

        if (!empty($params['bsre_status'])) {
            if ($params['bsre_status'] === 'not_synced') {
                $builder->groupStart()->where('emails.bsre_status', null)->orWhere('emails.bsre_status', '')->groupEnd();
            } else {
                $builder->where('emails.bsre_status', $params['bsre_status']);
            }
        }

        if (!empty($params['parent_unit_kerja_id'])) {
            $db = \Config\Database::connect();
            $parentId = $params['parent_unit_kerja_id'];
            $builder->where('(unit_kerja.parent_id = ' . $db->escape($parentId) . ' OR emails.unit_kerja_id = ' . $db->escape($parentId) . ')');
        }

        $emails = $builder->orderBy('emails.name', 'ASC')->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'DATA PNS');
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $headers = ['No', 'Nama Pegawai', 'Email'];
        $sheet->fromArray($headers, NULL, 'A3');
        $sheet->getStyle('A3:C3')->getFont()->setBold(true);

        $row = 4;
        $no = 1;
        foreach ($emails as $email) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $email['name']);
            $sheet->setCellValue('C' . $row, $email['email']);
            $row++;
        }

        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new XlsxWriter($spreadsheet);
        $filename = 'Data_PNS_' . date('YmdHis') . '.xlsx';
        $filepath = WRITEPATH . 'uploads/' . $filename;
        $writer->save($filepath);

        return ['type' => 'excel', 'path' => $filepath, 'filename' => $filename];
    }

    public function generatePnsCsv($params = [])
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $statusPns = $this->statusAsnModel->where('nama_status_asn', 'PNS')->asArray()->first();
        if (!$statusPns) throw new Exception('Status PNS belum dikonfigurasi.');

        $builder = $this->emailModel->withDetails()->where('emails.status_asn_id', $statusPns['id']);

        if (!empty($params['has_nip'])) {
            if ($params['has_nip'] === 'yes') {
                $builder->where('emails.nip !=', '')->where('emails.nip IS NOT NULL');
            } elseif ($params['has_nip'] === 'no') {
                $builder->groupStart()->where('emails.nip', '')->orWhere('emails.nip', null)->groupEnd();
            }
        }

        if (!empty($params['bsre_status'])) {
            if ($params['bsre_status'] === 'not_synced') {
                $builder->groupStart()->where('emails.bsre_status', null)->orWhere('emails.bsre_status', '')->groupEnd();
            } else {
                $builder->where('emails.bsre_status', $params['bsre_status']);
            }
        }

        if (!empty($params['parent_unit_kerja_id'])) {
            $db = \Config\Database::connect();
            $parentId = $params['parent_unit_kerja_id'];
            $builder->where('(unit_kerja.parent_id = ' . $db->escape($parentId) . ' OR emails.unit_kerja_id = ' . $db->escape($parentId) . ')');
        }

        $emails = $builder->orderBy('emails.name', 'ASC')->findAll();

        $filename = 'Data_PNS_' . date('YmdHis') . '.csv';
        $filepath = WRITEPATH . 'uploads/' . $filename;
        $fp = fopen($filepath, 'w');

        // UTF-8 BOM agar rapi saat dibuka di Excel/Sheets
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($fp, ['No', 'Nama Pegawai', 'NIP', 'Email', 'Jabatan', 'Golongan', 'Pangkat', 'Unit Kerja', 'OPD Induk', 'Status TTE']);

        $no = 1;
        foreach ($emails as $email) {
            fputcsv($fp, [
                $no++,
                $email['name'] ?? '',
                $email['nip'] ? "\t" . $email['nip'] : '',
                $email['email'] ?? '',
                $email['jabatan'] ?? '',
                $email['pangkat_golruang'] ?? '',
                $email['pangkat_nama'] ?? '',
                $email['nama_unit_kerja'] ?? '',
                $email['nama_parent_unit_kerja'] ?? ($email['nama_unit_kerja'] ?? ''),
                $email['bsre_status'] ?? 'Belum Sync',
            ]);
        }

        fclose($fp);

        return ['type' => 'csv', 'path' => $filepath, 'filename' => $filename];
    }

    public function generateUnitKerjaCsv($unitKerjaId, $params = [])
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $unitKerja = $this->unitKerjaModel->find($unitKerjaId);
        if (!$unitKerja) throw new Exception('Unit Kerja not found.');

        $children = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->findAll();
        $childrenIds = array_column($children, 'id');
        $allUnitIds = array_merge([$unitKerjaId], $childrenIds);

        $builder = $this->emailModel->whereIn('unit_kerja_id', $allUnitIds);
        if (!empty($params['search'])) {
            $search = $params['search'];
            $builder->groupStart();
            $cleanSearch = str_replace([' ', '.', '-', '\''], '', $search);
            $builder->like('email', $search)
                ->orLike('name', $search);

            if (is_numeric($cleanSearch) && strlen($cleanSearch) >= 10) {
                $hash = $cleanSearch;
                $builder->orWhere('nik', $hash)
                    ->orWhere('nip', $hash);
            }
            $builder->groupEnd();
        }
        if (!empty($params['status_asn'])) $builder->where('emails.status_asn_id', $params['status_asn']);
        if (!empty($params['bsre_status'])) {
            if ($params['bsre_status'] === 'not_synced') {
                $builder->groupStart()->where('emails.bsre_status', null)->orWhere('emails.bsre_status', '')->groupEnd();
            } else {
                $builder->where('emails.bsre_status', $params['bsre_status']);
            }
        }

        $emails = $builder->findAll();
        $totalEmails = count($emails);
        $limit = 50;
        $unitKerjaName = $unitKerja['nama_unit_kerja'];

        if ($totalEmails <= $limit) {
            $filename = url_title($unitKerjaName, '_', true) . '.csv';
            $path = WRITEPATH . 'uploads/' . $filename;
            $output = fopen($path, 'w');
            fputcsv($output, ['nama', 'emailAddress'], ',');
            foreach ($emails as $email) {
                fputcsv($output, [strtoupper($email['name']), $email['email']], ',');
            }
            fclose($output);
            return ['path' => $path, 'filename' => $filename, 'type' => 'csv'];
        } else {
            $zip = new ZipArchive();
            $zipFileName = url_title($unitKerjaName, '_', true) . '.zip';
            $tempZipPath = WRITEPATH . 'uploads/' . $zipFileName;
            if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                throw new Exception('Cannot create ZIP archive.');
            }

            $chunks = array_chunk($emails, $limit);
            $fileCount = 1;
            foreach ($chunks as $chunk) {
                $csvFileName = url_title($unitKerjaName, '_', true) . '_part_' . $fileCount . '.csv';
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
            return ['path' => $tempZipPath, 'filename' => $zipFileName, 'type' => 'zip'];
        }
    }

    public function generateAndSavePerjanjianKerja($emailId, $unitId)
    {
        $email = $this->emailModel->withDetails()->asArray()->find($emailId);
        if (!$email) throw new Exception('Email not found');

        $statusPppk = $this->statusAsnModel->where('nama_status_asn', 'PPPK')->first();
        $isPppk = $statusPppk && $email['status_asn_id'] == $statusPppk['id'];
        $template = $isPppk ? 'email/exports/perjanjian_kerja_pppk_template' : 'email/exports/perjanjian_kerja_template';
        $subFolder = $isPppk ? 'PPPK' : 'PPPK_PARUH_WAKTU';

        $name = $email['unit_kerja_name'] ?? $email['unit_kerja'] ?? 'N/A';
        $unitKerja = [
            'nama_unit_kerja' => $name
        ];
        if (!empty($email['parent_unit_kerja_name'])) {
            $unitKerja['nama_unit_kerja'] = $unitKerja['nama_unit_kerja'] . ' - ' . $email['parent_unit_kerja_name'];
        }

        $logoSrc = $this->getGarudaLogoSrc();
        $pk_data = $this->pkModel->where('email', $email['email'])->first();

        $data = [
            'email' => $email,
            'unit_kerja' => $unitKerja,
            'logoSrc' => $logoSrc,
            'pk_data' => $pk_data,
        ];

        $html = view($template, $data);
        $dompdf = $this->getDompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        $baseTempDir = WRITEPATH . 'uploads/temp_export_' . $unitId;
        $fullTempDir = $baseTempDir . '/' . $subFolder;
        if (!is_dir($fullTempDir)) {
            mkdir($fullTempDir, 0775, true);
        }

        $filePrefix = $isPppk ? 'pppk_' : 'paruh_waktu_';
        $filename = 'perjanjian_kerja_' . $filePrefix . url_title($email['name'], '_', true) . '_' . ($email['nip'] ?? 'NIP_NONE') . '.pdf';
        file_put_contents($fullTempDir . '/' . $filename, $output);
        
        return true;
    }
}
