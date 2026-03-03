<?php

namespace App\Domains\Assistance;

use App\Domains\Assistance\AssistanceModel;
use App\Domains\UnitKerja\UnitKerjaModel;
use App\Domains\Website\WebDesaKelurahanModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class AssistanceExportService
{
    protected $assistanceModel;

    const CATEGORY_MAP = [
        1 => 'Aplikasi SPBE',
        2 => 'Website Desa & Kelurahan'
    ];

    public function __construct()
    {
        $this->assistanceModel = new AssistanceModel();
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

    public function generateReportPdf($filterCategory = null, $filterMonth = null, $filterYear = null)
    {
        $this->assistanceModel->select('*');

        if ($filterCategory) {
            $this->assistanceModel->where('category', $filterCategory);
        }

        if ($filterYear) {
            $this->assistanceModel->where('YEAR(tanggal_kegiatan)', $filterYear);
        }

        if ($filterMonth) {
            $this->assistanceModel->where('MONTH(tanggal_kegiatan)', $filterMonth);
        }

        $activities = $this->assistanceModel->orderBy('tanggal_kegiatan', 'ASC')->orderBy('id', 'ASC')->asArray()->findAll();

        $categoryLabel = $filterCategory && isset(self::CATEGORY_MAP[$filterCategory])
            ? self::CATEGORY_MAP[$filterCategory]
            : 'Semua Kategori';

        $subtitle = 'Kategori: ' . $categoryLabel;
        $filenameSuffix = '';

        $monthsIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        if ($filterMonth && $filterYear) {
            $subtitle .= ' | Periode: ' . $monthsIndo[(int)$filterMonth] . ' ' . $filterYear;
            $filenameSuffix = ' - ' . $monthsIndo[(int)$filterMonth] . ' ' . $filterYear;
        } elseif ($filterYear) {
            $subtitle .= ' | Tahun: ' . $filterYear;
            $filenameSuffix = ' - Tahun ' . $filterYear;
        } elseif ($filterMonth) {
            $subtitle .= ' | Bulan: ' . $monthsIndo[(int)$filterMonth];
            $filenameSuffix = ' - ' . $monthsIndo[(int)$filterMonth];
        } else {
            $subtitle .= ' | Semua Periode';
        }

        $data = [
            'title' => 'LOG LAYANAN',
            'subtitle' => $subtitle,
            'activities' => $activities,
            'current_date' => formatTanggal('now'),
            'logoSrc' => $this->getLogoSrc(),
            'categoryMap' => self::CATEGORY_MAP
        ];

        $html = view('assistance/exports/pdf_export', $data);
        $dompdf = $this->getDompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Log Layanan';
        if ($filterCategory && isset(self::CATEGORY_MAP[$filterCategory])) {
            $filename .= ' ' . self::CATEGORY_MAP[$filterCategory];
        }
        $filename .= $filenameSuffix . '.pdf';

        return [
            'dompdf' => $dompdf,
            'filename' => $filename
        ];
    }

    public function getAgencyOptions()
    {
        $unitKerjaModel = new \App\Domains\UnitKerja\UnitKerjaModel();
        $desaModel = new \App\Domains\Website\WebDesaKelurahanModel();

        // 1. Get OPDs (Only those present in web_opd)
        $opds = $unitKerjaModel
            ->select('unit_kerja.*')
            ->join('web_opd', 'web_opd.unit_kerja_id = unit_kerja.id')
            ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
            ->findAll();

        // 2. Get Desa/Kelurahan
        $allDesas = $desaModel->orderBy('desa_kelurahan', 'ASC')->findAll();

        $kelurahans = [];
        $desas = [];

        foreach ($allDesas as $row) {
            if (stripos($row['desa_kelurahan'], 'KELURAHAN') !== false) {
                $kelurahans[] = $row;
            } else {
                $desas[] = $row;
            }
        }

        $options = [];

        // Group 1: OPD
        foreach ($opds as $opd) {
            $options[] = (object)[
                'value' => 'OPD|' . $opd['id'] . '|' . $opd['nama_unit_kerja'],
                'label' => $opd['nama_unit_kerja'],
                'group' => 'OPD'
            ];
        }

        // Group 2: Kelurahan
        foreach ($kelurahans as $kel) {
            $options[] = (object)[
                'value' => 'KELURAHAN|' . $kel['id'] . '|' . $kel['desa_kelurahan'],
                'label' => $kel['desa_kelurahan'] . ' (' . $kel['kecamatan'] . ')',
                'group' => 'Kelurahan'
            ];
        }

        // Group 3: Desa
        foreach ($desas as $desa) {
            $options[] = (object)[
                'value' => 'DESA|' . $desa['id'] . '|' . $desa['desa_kelurahan'],
                'label' => $desa['desa_kelurahan'] . ' (' . $desa['kecamatan'] . ')',
                'group' => 'Desa'
            ];
        }

        return $options;
    }
}
