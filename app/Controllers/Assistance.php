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

    public function __construct()
    {
        $this->assistanceModel = new AssistanceModel();
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->desaModel = new WebDesaKelurahanModel();
    }

    public function index()
    {
        $filterCategory = $this->request->getGet('category');

        if ($filterCategory) {
            $this->assistanceModel->where('category', $filterCategory);
        }

        $data = [
            'title' => 'Assistance Activities',
            'activities' => $this->assistanceModel->orderBy('tanggal_kegiatan', 'DESC')->findAll(),
            'filterCategory' => $filterCategory,
            'categoryMap' => self::CATEGORY_MAP
        ];

        return view('assistance/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Add New Assistance',
            'agencies' => $this->getAgencies(),
            'categoryMap' => self::CATEGORY_MAP
        ];

        return view('assistance/form', $data);
    }

    public function store()
    {
        // Parse agency_info "TYPE-ID-NAME"
        $agencyInfo = $this->request->getPost('agency_info');
        list($type, $id, $name) = explode('|', $agencyInfo);

        $services = $this->request->getPost('services');

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

        $services = $this->request->getPost('services');

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
        $desas = $this->desaModel->orderBy('desa_kelurahan', 'ASC')->findAll();

        $options = [];

        // Group OPD
        foreach ($opds as $opd) {
            // Value format: TYPE|ID|NAME
            $options[] = (object)[
                'value' => 'OPD|' . $opd['id'] . '|' . $opd['nama_unit_kerja'],
                'label' => $opd['nama_unit_kerja'],
                'group' => 'OPD'
            ];
        }

        // Group Desa & Kelurahan
        foreach ($desas as $desa) {
            $type = (stripos($desa['desa_kelurahan'], 'KELURAHAN') !== false) ? 'KELURAHAN' : 'DESA';
            $group = ($type === 'KELURAHAN') ? 'Kelurahan' : 'Desa';
            
            $options[] = (object)[
                'value' => $type . '|' . $desa['id'] . '|' . $desa['desa_kelurahan'],
                'label' => $desa['desa_kelurahan'] . ' (Kec. ' . $desa['kecamatan'] . ')',
                'group' => $group
            ];
        }

        return $options;
    }

    public function export_pdf()
    {
        helper('time');
        $filterCategory = $this->request->getGet('category');

        if ($filterCategory) {
            $this->assistanceModel->where('category', $filterCategory);
        }

        $activities = $this->assistanceModel->orderBy('tanggal_kegiatan', 'ASC')->findAll();

        $logoPath = FCPATH . 'logo.png';
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;

        $categoryLabel = $filterCategory && isset(self::CATEGORY_MAP[$filterCategory]) 
            ? self::CATEGORY_MAP[$filterCategory] 
            : 'Semua Kategori';

        $data = [
            'title' => 'Laporan Pendampingan',
            'subtitle' => 'Kategori: ' . $categoryLabel,
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
        $filename .= ' - ' . format_indo_date(date('Y-m-d'), true) . '.pdf';

        $dompdf->stream($filename, ['Attachment' => true]);
    }
}
