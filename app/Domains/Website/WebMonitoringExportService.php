<?php

namespace App\Domains\Website;

use App\Domains\Website\WebOpdModel;
use App\Domains\Website\WebDesaKelurahanModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class WebMonitoringExportService
{
    protected $webOpdModel;
    protected $webDesaModel;

    public function __construct()
    {
        $this->webOpdModel = new WebOpdModel();
        $this->webDesaModel = new WebDesaKelurahanModel();
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

    public function generateWebOpdPdf($search = '', $filterStatus = '', $statusChartData = null)
    {
        $this->webOpdModel->select('web_opd.*, unit_kerja.nama_unit_kerja')
            ->join('unit_kerja', 'unit_kerja.id = web_opd.unit_kerja_id', 'left');

        if ($search !== '') {
            $this->webOpdModel->groupStart()
                ->like('unit_kerja.nama_unit_kerja', $search)
                ->orLike('web_opd.domain', $search)
                ->groupEnd();
        }

        if ($filterStatus !== '') {
            $this->webOpdModel->where('web_opd.status', $filterStatus);
        }

        $websites = $this->webOpdModel->orderBy('unit_kerja.nama_unit_kerja', 'ASC')->findAll();

        $aktif = 0;
        $nonaktif = 0;
        foreach ($websites as $web) {
            if ($web['status'] === 'AKTIF') $aktif++;
            elseif ($web['status'] === 'NONAKTIF') $nonaktif++;
        }

        $stats = [
            'total' => count($websites),
            'aktif' => $aktif,
            'nonaktif' => $nonaktif,
        ];

        if ($stats['total'] > 0) {
            $stats['aktif_percentage'] = round(($aktif / $stats['total']) * 100);
            $stats['nonaktif_percentage'] = round(($nonaktif / $stats['total']) * 100);
        } else {
            $stats['aktif_percentage'] = 0;
            $stats['nonaktif_percentage'] = 0;
        }

        $data = [
            'websites' => $websites,
            'stats' => $stats,
            'logoSrc' => $this->getLogoSrc(),
            'current_date' => formatTanggal('now'),
            'title' => 'DATA WEBSITE ORGANISASI PERANGKAT DAERAH (OPD)',
            'subtitle' => 'PEMERINTAH KABUPATEN SINJAI',
            'statusChart' => $statusChartData,
        ];

        $dompdf = $this->getDompdf();
        $dompdf->loadHtml(view('web_opd/exports/pdf_export', $data));
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return [
            'dompdf' => $dompdf,
            'filename' => 'Data Website OPD - ' . formatTanggal('now') . '.pdf'
        ];
    }

    public function generateWebDesaPdf($search = '', $filterPlatform = '', $filterStatus = '', $statusChartData = null, $platformChartData = null, $filterType = '')
    {
        $this->webDesaModel->select('web_desa_kelurahan.*, platforms.nama_platform as platform_name')
            ->join('platforms', 'platforms.id = web_desa_kelurahan.platform_id', 'left');

        if ($search !== '') {
            $this->webDesaModel->groupStart()
                ->like('desa_kelurahan', $search)
                ->orLike('kecamatan', $search)
                ->orLike('domain', $search)
                ->groupEnd();
        }

        if ($filterPlatform !== '') {
            if ($filterPlatform === 'NULL') {
                $this->webDesaModel->where('platform_id', null);
            } else {
                $this->webDesaModel->where('platforms.nama_platform', $filterPlatform);
            }
        }

        if ($filterStatus !== '') {
            $this->webDesaModel->where('status', $filterStatus);
        }

        if ($filterType !== '') {
            $this->webDesaModel->like('web_desa_kelurahan.desa_kelurahan', $filterType, 'after');
        }

        $websites = $this->webDesaModel->orderBy('kecamatan', 'ASC')->orderBy('desa_kelurahan', 'ASC')->findAll();

        $aktif = 0;
        $nonaktif = 0;
        $platform_stats_map = [];

        foreach ($websites as $web) {
            if ($web['status'] === 'AKTIF') $aktif++;
            elseif ($web['status'] === 'NONAKTIF') $nonaktif++;

            $pName = $web['platform_name'] ?: '-';
            if (!isset($platform_stats_map[$pName])) {
                $platform_stats_map[$pName] = 0;
            }
            $platform_stats_map[$pName]++;
        }

        $stats = [
            'total' => count($websites),
            'aktif' => $aktif,
            'nonaktif' => $nonaktif,
        ];

        if ($stats['total'] > 0) {
            $stats['aktif_percentage'] = (int)(($aktif / $stats['total']) * 100);
            $stats['nonaktif_percentage'] = (int)(($nonaktif / $stats['total']) * 100);
        } else {
            $stats['aktif_percentage'] = 0;
            $stats['nonaktif_percentage'] = 0;
        }

        $platform_stats = [];
        foreach ($platform_stats_map as $name => $count) {
            $platform_stats[] = [
                'nama_platform' => $name,
                'count' => $count
            ];
        }

        usort($platform_stats, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        $data = [
            'websites' => $websites,
            'stats' => $stats,
            'platform_stats' => $platform_stats,
            'logoSrc' => $this->getLogoSrc(),
            'current_date' => formatTanggal('now'),
            'title' => 'DATA WEBSITE DESA & KELURAHAN',
            'subtitle' => 'PEMERINTAH KABUPATEN SINJAI',
            'statusChart' => $statusChartData,
            'platformChart' => $platformChartData,
        ];

        $dompdf = $this->getDompdf();
        $dompdf->loadHtml(view('web_desa_kelurahan/exports/pdf_export', $data));
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return [
            'dompdf' => $dompdf,
            'filename' => 'Data Website Desa & Kelurahan - ' . formatTanggal('now') . '.pdf'
        ];
    }
}
