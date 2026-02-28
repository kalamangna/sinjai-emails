<?php

namespace App\Domains\Website;

use App\Shared\BaseController;
use App\Domains\Website\WebOpdModel;
use App\Domains\UnitKerja\UnitKerjaModel;
use CodeIgniter\Files\File;
use Config\Services;

class WebOpd extends BaseController
{
    protected $exportService;

    public function __construct()
    {
        $this->exportService = new \App\Domains\Website\WebMonitoringExportService();
    }

    public function index()
    {
        $model = new WebOpdModel();
        
        $search = trim($this->request->getGet('search') ?? '');
        $filterStatus = trim($this->request->getGet('status') ?? '');

        // Use aggregated counts for stats - single query
        $statsRaw = $model->select("COUNT(id) as total, SUM(CASE WHEN status = 'AKTIF' THEN 1 ELSE 0 END) as aktif, SUM(CASE WHEN status = 'NONAKTIF' THEN 1 ELSE 0 END) as nonaktif")->asArray()->first();
        $total = (int)($statsRaw['total'] ?? 0);
        $aktif = (int)($statsRaw['aktif'] ?? 0);
        $nonaktif = (int)($statsRaw['nonaktif'] ?? 0);

        $stats = [
            'total' => $total,
            'aktif' => $aktif,
            'nonaktif' => $nonaktif,
            'aktif_percentage' => $total > 0 ? (int)(($aktif / $total) * 100) : 0,
            'nonaktif_percentage' => $total > 0 ? (int)(($nonaktif / $total) * 100) : 0,
        ];

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

        $perPage = 100;
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
