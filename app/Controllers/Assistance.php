<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AssistanceModel;
use App\Models\UnitKerjaModel;
use App\Models\WebDesaKelurahanModel;
use Dompdf\Dompdf;

class Assistance extends BaseController
{
    protected $desaModel;

    const CATEGORY_MAP = [
        1 => 'Aplikasi SPBE',
        2 => 'Website Desa & Kelurahan'
    ];

    const SERVICES_MAP = [

        1 => [ // Aplikasi SPBE

            'Website OPD',

            'Email Resmi',

            'Tanda Tangan Elektronik',

            'Aplikasi Srikandi'

        ],

        2 => [ // Website Desa & Kelurahan

            'Bimtek Website',

            'Domain Hosting Website'

        ]

    ];



    const KETERANGAN_BY_SERVICE_MAP = [

        'Website OPD' => [
            'Konsultasi',
            'Registrasi Domain',
            'Migrasi / Setup Hosting',
            'Troubleshooting / Perbaikan'
        ],

        'Email Resmi' => [
            'Pembuatan Akun',
            'Reset Password'
        ],

        'Tanda Tangan Elektronik' => [
            'Aktivasi TTE',
            'Pembaruan TTE',
            'Reset Passphrase'
        ],

        'Aplikasi Srikandi' => [
            'Konsultasi',
            'Pendampingan Teknis',
            'Bimtek / Sosialisasi'
        ],

        'Bimtek Website' => [
            'Konsultasi',
            'Bimtek / Sosialisasi'
        ],

        'Domain Hosting Website' => [
            'Registrasi Domain',
            'Migrasi / Setup Hosting'
        ]

    ];

    public function __construct()
    {
        $this->assistanceModel = new AssistanceModel();
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->desaModel = new WebDesaKelurahanModel();
    }

    public function index()
    {
        $filterCategory = $this->request->getGet('category');
        $filterMonth = $this->request->getGet('month') ?: date('Y-m');

        if ($filterCategory) {
            $this->assistanceModel->where('category', $filterCategory);
        }

        if ($filterMonth) {
            $parts = explode('-', $filterMonth);
            $year = $parts[0];
            $month = $parts[1];
            $this->assistanceModel->where('YEAR(tanggal_kegiatan)', $year);
            $this->assistanceModel->where('MONTH(tanggal_kegiatan)', $month);
        }

        // Flatten keterangan options for filter/index view compatibility if needed
        $allKeterangan = [];
        foreach (self::KETERANGAN_BY_SERVICE_MAP as $opts) {
            $allKeterangan = array_merge($allKeterangan, $opts);
        }
        $allKeterangan = array_unique($allKeterangan);

        $data = [
            'title' => 'Assistance Activities',
            'activities' => $this->assistanceModel->orderBy('tanggal_kegiatan', 'ASC')->orderBy('id', 'ASC')->findAll(),
            'filterCategory' => $filterCategory,
            'filterMonth' => $filterMonth,
            'categoryMap' => self::CATEGORY_MAP,
            'servicesMap' => self::SERVICES_MAP,
            'keteranganMap' => self::KETERANGAN_BY_SERVICE_MAP, // Pass this for potential use
            'keteranganOptions' => $allKeterangan
        ];

        return view('assistance/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Add New Assistance',
            'agencies' => $this->getAgencies(),
            'categoryMap' => self::CATEGORY_MAP,
            'servicesMap' => self::SERVICES_MAP,
            'keteranganByServiceMap' => self::KETERANGAN_BY_SERVICE_MAP
        ];

        return view('assistance/form', $data);
    }

    public function edit($id)
    {
        $activity = $this->assistanceModel->find($id);

        if (!$activity) {
            return redirect()->to('/assistance')->with('error', 'Activity not found.');
        }

        // Decode services JSON string to array for the view
        $activity['services'] = json_decode($activity['services'], true);

        $data = [
            'title' => 'Edit Assistance',
            'activity' => $activity,
            'agencies' => $this->getAgencies(),
            'categoryMap' => self::CATEGORY_MAP,
            'servicesMap' => self::SERVICES_MAP,
            'keteranganByServiceMap' => self::KETERANGAN_BY_SERVICE_MAP
        ];

        return view('assistance/form', $data);
    }

    public function store()
    {
        // Parse agency_info "TYPE-ID-NAME"
        $agencyInfo = $this->request->getPost('agency_info');
        list($type, $id, $name) = explode('|', $agencyInfo);

        $serviceInput = $this->request->getPost('service');
        // Ensure services is stored as an array for backward compatibility
        $services = [$serviceInput];

        $data = [
            'tanggal_kegiatan' => $this->request->getPost('tanggal_kegiatan'),
            'agency_type'      => $type,
            'agency_id'        => $id,
            'agency_name'      => $name, // Store name for easier display/search
            'category'         => $this->request->getPost('category'),
            'method'           => $this->request->getPost('method'),
            'services'         => json_encode($services),
            'keterangan'       => $this->request->getPost('keterangan'),
        ];

        $this->assistanceModel->save($data);

        return redirect()->to('/assistance')->with('message', 'Activity added successfully.');
    }

    public function update($id)
    {
        $agencyInfo = $this->request->getPost('agency_info');
        list($type, $id_agency, $name) = explode('|', $agencyInfo);

        $serviceInput = $this->request->getPost('service');
        // Ensure services is stored as an array for backward compatibility
        $services = [$serviceInput];

        $data = [
            'id'               => $id,
            'tanggal_kegiatan' => $this->request->getPost('tanggal_kegiatan'),
            'agency_type'      => $type,
            'agency_id'        => $id_agency,
            'agency_name'      => $name,
            'category'         => $this->request->getPost('category'),
            'method'           => $this->request->getPost('method'),
            'services'         => json_encode($services),
            'keterangan'       => $this->request->getPost('keterangan'),
        ];

        $this->assistanceModel->save($data);

        return redirect()->to('/assistance')->with('message', 'Activity updated successfully.');
    }

    public function delete($id)
    {
        $this->assistanceModel->delete($id);
        return redirect()->to('/assistance')->with('message', 'Activity deleted successfully.');
    }

    private function getAgencies()
    {
        // 1. Get OPDs (Only those present in web_opd)
        $opds = $this->unitKerjaModel
            ->select('unit_kerja.*')
            ->join('web_opd', 'web_opd.unit_kerja_id = unit_kerja.id')
            ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
            ->findAll();

        // 2. Get Desa/Kelurahan
        $allDesas = $this->desaModel->orderBy('desa_kelurahan', 'ASC')->findAll();
        
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

    public function export_pdf()
    {
        helper('time');
        $filterCategory = $this->request->getGet('category');
        $filterMonth = $this->request->getGet('month') ?: date('Y-m');

        if ($filterCategory) {
            $this->assistanceModel->where('category', $filterCategory);
        }

        if ($filterMonth) {
            $parts = explode('-', $filterMonth);
            $year = $parts[0];
            $month = $parts[1];
            $this->assistanceModel->where('YEAR(tanggal_kegiatan)', $year);
            $this->assistanceModel->where('MONTH(tanggal_kegiatan)', $month);
        }

        $activities = $this->assistanceModel->orderBy('tanggal_kegiatan', 'ASC')->orderBy('id', 'ASC')->findAll();

        $logoPath = FCPATH . 'logo.png';
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;

        $categoryLabel = $filterCategory && isset(self::CATEGORY_MAP[$filterCategory])
            ? self::CATEGORY_MAP[$filterCategory]
            : 'Semua Kategori';

        $subtitle = 'Kategori: ' . $categoryLabel;
        if ($filterMonth) {
            $parts = explode('-', $filterMonth);
            $monthNum = (int)$parts[1];
            $yearNum = $parts[0];
            $monthsIndo = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember'
            ];
            $subtitle .= ' | Bulan: ' . $monthsIndo[$monthNum] . ' ' . $yearNum;
        }

        $data = [
            'title' => 'Laporan Pendampingan',
            'subtitle' => $subtitle,
            'activities' => $activities,
            'current_date' => format_indo_date(date('Y-m-d')),
            'logoSrc' => $logoSrc,
            'categoryMap' => self::CATEGORY_MAP
        ];
        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('assistance/pdf_export', $data));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Laporan Pendampingan';
        if ($filterCategory && isset(self::CATEGORY_MAP[$filterCategory])) {
            $filename .= ' ' . self::CATEGORY_MAP[$filterCategory];
        }

        if ($filterMonth) {
            $parts = explode('-', $filterMonth);
            $monthNum = (int)$parts[1];
            $yearNum = $parts[0];
            $monthsIndo = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $filename .= ' - ' . $monthsIndo[$monthNum] . ' ' . $yearNum;
        }

        $dompdf->stream($filename . '.pdf', ['Attachment' => true]);
    }
}
