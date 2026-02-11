<?php

namespace App\Controllers;

use App\Models\WebOpdModel;
use App\Models\UnitKerjaModel;
use CodeIgniter\Files\File;
use Config\Services;

class WebOpd extends BaseController
{
    protected $exportService;

    public function __construct()
    {
        $this->exportService = new \App\Services\Exports\WebMonitoringExportService();
    }

    public function index()
    {
        $model = new WebOpdModel();
        $unitKerjaModel = new UnitKerjaModel();

        $search = trim($this->request->getGet('search') ?? '');
        $filterStatus = trim($this->request->getGet('status') ?? '');

        // Build Query with Joins
        $model->select('web_opd.*, unit_kerja.nama_unit_kerja')
            ->join('unit_kerja', 'unit_kerja.id = web_opd.unit_kerja_id', 'left');

        if ($search !== '') {
            $model->groupStart()
                ->like('unit_kerja.nama_unit_kerja', $search)
                ->orLike('web_opd.domain', $search)
                ->groupEnd();
        }

        if ($filterStatus !== '') {
            $model->where('web_opd.status', $filterStatus);
        }

        $websites = $model->orderBy('unit_kerja.nama_unit_kerja', 'ASC')->findAll();

        $data['websites'] = $websites;
        $data['total_filtered'] = count($websites);

        // Calculate statistics based on filtered data
        $aktif = 0;
        $nonaktif = 0;

        foreach ($websites as $web) {
            if ($web['status'] === 'AKTIF') $aktif++;
            elseif ($web['status'] === 'NONAKTIF') $nonaktif++;
        }

        $data['stats'] = [
            'total' => $data['total_filtered'],
            'aktif' => $aktif,
            'nonaktif' => $nonaktif,
        ];

        if ($data['total_filtered'] > 0) {
            $data['stats']['aktif_percentage'] = (int)(($aktif / $data['total_filtered']) * 100);
            $data['stats']['nonaktif_percentage'] = (int)(($nonaktif / $data['total_filtered']) * 100);
        } else {
            $data['stats']['aktif_percentage'] = 0;
            $data['stats']['nonaktif_percentage'] = 0;
        }

        $data['title'] = 'Website OPD';
        $data['search'] = $search;
        $data['filterStatus'] = $filterStatus;

        return view('web_opd/index', $data);
    }

    public function export_pdf()
    {
        $search = trim($this->request->getGet('search') ?? '');
        $filterStatus = trim($this->request->getGet('status') ?? '');
        $statusChartData = $this->request->getPost('statusChartData');

        $result = $this->exportService->generateWebOpdPdf($search, $filterStatus, $statusChartData);
        $result['dompdf']->stream($result['filename'], ['Attachment' => true]);
    }

    public function create()
    {
        $unitKerjaModel = new UnitKerjaModel();
        $data['unit_kerja'] = $unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['title'] = 'Add Website OPD';
        return view('web_opd/form', $data);
    }

    public function store()
    {
        $model = new WebOpdModel();

        $data = [
            'unit_kerja_id'    => $this->request->getPost('unit_kerja_id'),
            'domain'           => $this->request->getPost('domain'),
            'status'           => $this->request->getPost('status'),
            'keterangan'       => $this->request->getPost('keterangan'),
        ];

        $model->insert($data);
        return redirect()->to('web_opd')->with('message', 'Data added successfully.');
    }

    public function edit($id)
    {
        $model = new WebOpdModel();
        $unitKerjaModel = new UnitKerjaModel();
        $data['website'] = $model->find($id);

        if (!$data['website']) {
            return redirect()->to('web_opd')->with('error', 'Data not found.');
        }

        // Fetch the unit_kerja name for display
        $unitKerja = $unitKerjaModel->find($data['website']['unit_kerja_id']);
        $data['unit_kerja_name'] = $unitKerja['nama_unit_kerja'] ?? 'N/A';

        // For create mode, we need all unit_kerja options
        $data['unit_kerja'] = $unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['title'] = 'Edit Website OPD';
        return view('web_opd/form', $data);
    }

    public function update($id)
    {
        $model = new WebOpdModel();
        $website = $model->find($id);

        if (!$website) {
            return redirect()->to('web_opd')->with('error', 'Data not found.');
        }

        $data = [
            'unit_kerja_id'    => $this->request->getPost('unit_kerja_id'),
            'domain'           => $this->request->getPost('domain'),
            'status'           => $this->request->getPost('status'),
            'keterangan'       => $this->request->getPost('keterangan'),
        ];

        $model->update($id, $data);
        return redirect()->to('web_opd')->with('message', 'Data updated successfully.');
    }
}
