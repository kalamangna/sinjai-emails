<?php

namespace App\Services\Exports;

use App\Models\AssistanceModel;
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
        helper('tanggal');
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
        if ($filterCategory) {
            $this->assistanceModel->where('category', $filterCategory);
        }

        if ($filterYear) {
            $this->assistanceModel->where('YEAR(tanggal_kegiatan)', $filterYear);
        }

        if ($filterMonth) {
            $this->assistanceModel->where('MONTH(tanggal_kegiatan)', $filterMonth);
        }

        $activities = $this->assistanceModel->orderBy('tanggal_kegiatan', 'ASC')->orderBy('id', 'ASC')->findAll();

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
            'title' => 'Laporan Pendampingan',
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

        $filename = 'Laporan Pendampingan';
        if ($filterCategory && isset(self::CATEGORY_MAP[$filterCategory])) {
            $filename .= ' ' . self::CATEGORY_MAP[$filterCategory];
        }
        $filename .= $filenameSuffix . '.pdf';

        return [
            'dompdf' => $dompdf,
            'filename' => $filename
        ];
    }
}
