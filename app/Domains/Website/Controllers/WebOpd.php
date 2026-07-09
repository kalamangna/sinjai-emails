<?php

namespace App\Domains\Website\Controllers;

use App\Shared\BaseController;
use App\Domains\Website\Models\WebOpdModel;
use App\Domains\UnitKerja\Models\UnitKerjaModel;
use CodeIgniter\Files\File;
use Config\Services;

class WebOpd extends BaseController
{
    protected $exportService;
    protected $websiteService;

    public function __construct()
    {
        $this->exportService = new \App\Domains\Website\Services\WebMonitoringExportService();
        $this->websiteService = new \App\Domains\Website\Services\WebsiteService();
    }

    public function index()
    {
        $model = new WebOpdModel();
        
        $search = trim($this->request->getGet('search') ?? '');
        $filterStatus = trim($this->request->getGet('status') ?? '');

        // Use service for stats
        $stats = $this->websiteService->getOpdStats();

        // Build Query with Joins for paginated list
        $model->select('web_opd.id, web_opd.domain, web_opd.status, web_opd.unit_kerja_id, web_opd.keterangan, unit_kerja.nama_unit_kerja')
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

        $perPage = 200;
        $websites = $model->orderBy('unit_kerja.nama_unit_kerja', 'ASC')->asArray()->paginate($perPage);
        $pager = $model->pager;

        $data = [
            'websites' => $websites,
            'pager' => $pager,
            'stats' => $stats,
            'title' => 'Website OPD',
            'search' => $search,
            'filterStatus' => $filterStatus,
        ];

        return view('web_opd/index', $data);
    }

    public function export_pdf()
    {
        $search = trim($this->request->getGet('search') ?? '');
        $filterStatus = trim($this->request->getGet('status') ?? '');

        $result = $this->exportService->generateWebOpdPdf($search, $filterStatus);
        log_audit('EXPORT', 'WebOpd', null, 'Ekspor PDF Website OPD');
        $result['dompdf']->stream($result['filename'], ['Attachment' => true]);
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
