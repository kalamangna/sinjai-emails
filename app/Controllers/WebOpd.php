<?php

namespace App\Controllers;

use App\Models\WebOpdModel;
use App\Models\UnitKerjaModel;
use CodeIgniter\Files\File;
use Config\Services;

class WebOpd extends BaseController
{
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

        $data['websites'] = $model->orderBy('unit_kerja.nama_unit_kerja', 'ASC')->findAll();
        
        $data['total_filtered'] = count($data['websites']);
        
        $db = \Config\Database::connect();
        
        $data['stats'] = [
            'total' => $db->table('web_opd')->countAllResults(),
            'aktif' => $db->table('web_opd')->where('status', 'AKTIF')->countAllResults(),
            'nonaktif' => $db->table('web_opd')->where('status', 'NONAKTIF')->countAllResults(),
        ];
        
        $total = $data['stats']['total'];
        if ($total > 0) {
            $data['stats']['aktif_percentage'] = round(($data['stats']['aktif'] / $total) * 100, 2);
            $data['stats']['nonaktif_percentage'] = round(($data['stats']['nonaktif'] / $total) * 100, 2);
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
        helper('time');
        $model = new WebOpdModel();
        
        $search = trim($this->request->getGet('search') ?? '');
        $filterStatus = trim($this->request->getGet('status') ?? '');

        // Build Query
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

        $db = \Config\Database::connect();
        
        $stats = [
            'total' => $db->table('web_opd')->countAllResults(),
            'aktif' => $db->table('web_opd')->where('status', 'AKTIF')->countAllResults(),
            'nonaktif' => $db->table('web_opd')->where('status', 'NONAKTIF')->countAllResults(),
        ];

        $total = $stats['total'];
        if ($total > 0) {
            $stats['aktif_percentage'] = round(($stats['aktif'] / $total) * 100, 2);
            $stats['nonaktif_percentage'] = round(($stats['nonaktif'] / $total) * 100, 2);
        } else {
            $stats['aktif_percentage'] = 0;
            $stats['nonaktif_percentage'] = 0;
        }

        $logoPath = FCPATH . 'logo.png';
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml(view('web_opd/pdf_export', [
            'websites' => $websites,
            'stats' => $stats,
            'logoSrc' => $logoSrc,
            'current_date' => format_indo_date(date('Y-m-d')),
        ]));
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        $filename = url_title('data_website_opd_' . format_indo_date(date('Y-m-d'), false), '_', true) . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
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