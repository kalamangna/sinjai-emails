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
    protected $exportService;

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
        $this->exportService = new \App\Services\Exports\AssistanceExportService();
    }

    public function index()
    {
        $filterCategory = $this->request->getGet('category');

        // Default to current month and year on first visit (null)
        // If empty string (explicit "Semua"), it remains empty
        $filterMonth = $this->request->getGet('month');
        if ($filterMonth === null) {
            $filterMonth = date('n');
        }

        $filterYear = $this->request->getGet('year');
        if ($filterYear === null) {
            $filterYear = date('Y');
        }

        // Get available years for filter BEFORE applying other filters to the model
        $years = $this->assistanceModel->select('YEAR(tanggal_kegiatan) as year')
            ->distinct()
            ->orderBy('year', 'DESC')
            ->findAll();

        $yearOptions = array_column($years, 'year');
        if (empty($yearOptions)) {
            $yearOptions = [date('Y')];
        }

        // Build the main query
        $builder = $this->assistanceModel;

        if ($filterCategory) {
            $builder->where('category', $filterCategory);
        }

        if ($filterYear) {
            $builder->where('YEAR(tanggal_kegiatan)', $filterYear);
        }

        if ($filterMonth) {
            $builder->where('MONTH(tanggal_kegiatan)', $filterMonth);
        }

        $activities = $builder->orderBy('tanggal_kegiatan', 'ASC')->orderBy('id', 'ASC')->findAll();

        $monthNames = [
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

        $data = [
            'title' => 'Assistance Activities',
            'activities' => $activities,
            'filterCategory' => $filterCategory,
            'filterMonth' => $filterMonth,
            'filterYear' => $filterYear,
            'yearOptions' => $yearOptions,
            'monthNames' => $monthNames,
            'categoryMap' => self::CATEGORY_MAP,
            'servicesMap' => self::SERVICES_MAP,
            'keteranganMap' => self::KETERANGAN_BY_SERVICE_MAP,
        ];

        return view('assistance/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Log Pendampingan Teknis',
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
        $filterCategory = $this->request->getGet('category');
        
        $filterMonth = $this->request->getGet('month');
        if ($filterMonth === null) {
            $filterMonth = date('n');
        }

        $filterYear = $this->request->getGet('year');
        if ($filterYear === null) {
            $filterYear = date('Y');
        }

        $result = $this->exportService->generateReportPdf($filterCategory, $filterMonth, $filterYear);
        $result['dompdf']->stream($result['filename'], ['Attachment' => true]);
    }
}
