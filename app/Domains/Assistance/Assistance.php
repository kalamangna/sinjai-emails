<?php

namespace App\Domains\Assistance;

use App\Shared\BaseController;
use App\Domains\Assistance\AssistanceModel;
use App\Domains\UnitKerja\UnitKerjaModel;
use App\Domains\Website\WebDesaKelurahanModel;
use Dompdf\Dompdf;

class Assistance extends BaseController
{
    protected $desaModel;
    protected $exportService;
    protected $assistanceModel;
    protected $unitKerjaModel;

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
            'Migrasi / Setup Hosting',
            'Troubleshooting / Perbaikan'
        ]
    ];

    public function __construct()
    {
        $this->assistanceModel = new AssistanceModel();
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->desaModel = new WebDesaKelurahanModel();
        $this->exportService = new \App\Domains\Assistance\AssistanceExportService();
    }

    public function index()
    {
        $filterCategory = $this->request->getGet('category');

        // Default to current month and year on first visit (null)
        // If empty string (explicit "Semua"), it remains empty
        $filterMonth = $this->request->getGet('month');
        if ($filterMonth === null) {
            $filterMonth = \bulanSekarang();
        }

        $filterYear = $this->request->getGet('year');
        if ($filterYear === null) {
            $filterYear = \tahunSekarang();
        }

        // Get available years for filter BEFORE applying other filters to the model
        $years = $this->assistanceModel->select('YEAR(tanggal_kegiatan) as year')
            ->distinct()
            ->orderBy('year', 'DESC')
            ->findAll();

        $yearOptions = array_column($years, 'year');
        if (empty($yearOptions)) {
            $yearOptions = [\tahunSekarang()];
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

        $perPage = 100;
        $activities = $builder->orderBy('tanggal_kegiatan', 'ASC')->orderBy('id', 'ASC')->asArray()->paginate($perPage);
        $pager = $this->assistanceModel->pager;
        $totalActivities = $this->assistanceModel->countAllResults(false);

        foreach ($activities as &$activity) {
            $activity['category_label'] = self::CATEGORY_MAP[$activity['category']] ?? 'Tidak Diketahui';
            $activity['services'] = json_decode($activity['services'], true);
        }

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
            'title' => 'Log Layanan',
            'activities' => $activities,
            'pager' => $pager,
            'totalActivities' => $totalActivities,
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
            'title' => 'Tambah Log Layanan',
            'agencies' => $this->exportService->getAgencyOptions(),
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
            'title' => 'Edit Log Layanan',
            'activity' => $activity,
            'agencies' => $this->exportService->getAgencyOptions(),
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

    public function export_pdf()
    {
        $filterCategory = $this->request->getGet('category');

        $filterMonth = $this->request->getGet('month');
        if ($filterMonth === null) {
            $filterMonth = \bulanSekarang();
        }

        $filterYear = $this->request->getGet('year');
        if ($filterYear === null) {
            $filterYear = \tahunSekarang();
        }

        $result = $this->exportService->generateReportPdf($filterCategory, $filterMonth, $filterYear);
        $result['dompdf']->stream($result['filename'], ['Attachment' => true]);
    }
}
