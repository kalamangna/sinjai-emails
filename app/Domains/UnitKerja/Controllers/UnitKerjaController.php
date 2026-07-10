<?php

namespace App\Domains\UnitKerja\Controllers;

use App\Shared\BaseController;
use App\Domains\UnitKerja\Models\UnitKerjaModel;
use App\Domains\UnitKerja\Services\UnitKerjaService;

class UnitKerjaController extends BaseController
{
    protected $unitKerjaModel;
    protected $unitKerjaService;

    public function __construct()
    {
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->unitKerjaService = new UnitKerjaService();
    }

    public function manage()
    {
        // Calculate Statistics
        $data['total_units'] = $this->unitKerjaModel->countAllResults(false);
        $data['total_parents'] = $this->unitKerjaModel->where('parent_id', null)->countAllResults();
        $data['total_children'] = $this->unitKerjaModel->where('parent_id !=', null)->countAllResults();

        $search = $this->request->getGet('search');
        $parentIdFilter = $this->request->getGet('parent_id');

        $this->unitKerjaModel->select('unit_kerja.*, parent.nama_unit_kerja as parent_name')
            ->join('unit_kerja as parent', 'parent.id = unit_kerja.parent_id', 'left');

        if ($search) {
            $this->unitKerjaModel->groupStart()
                ->like('unit_kerja.nama_unit_kerja', $search)
                ->orLike('parent.nama_unit_kerja', $search)
                ->groupEnd();
        }

        if ($parentIdFilter) {
            $this->unitKerjaModel->where('unit_kerja.parent_id', $parentIdFilter);
        }

        // Apply Pagination
        $unitKerjaList = $this->unitKerjaModel->paginate(100);
        $data['pager'] = $this->unitKerjaModel->pager;

        // Fetch parents that actually have children for the filter
        $parentsWithChildren = $this->unitKerjaModel
            ->select('unit_kerja.id, unit_kerja.nama_unit_kerja')
            ->join('unit_kerja as child', 'child.parent_id = unit_kerja.id')
            ->groupBy('unit_kerja.id')
            ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
            ->asArray()
            ->findAll();

        $data['unitKerjaList'] = $unitKerjaList;
        $data['parents_with_children'] = $parentsWithChildren;
        $data['search'] = $search;
        $data['parent_id_filter'] = $parentIdFilter;
        $data['title'] = 'Unit Kerja';

        return view('unit_kerja/manage', $data);
    }

    public function add()
    {
        $data['parent_options'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['title'] = 'Tambah Unit Kerja';

        return view('unit_kerja/add', $data);
    }

    public function store()
    {
        $parentId = $this->request->getPost('parent_id');
        $parentId = !empty($parentId) ? (int)$parentId : null;
        $name = $this->request->getPost('nama_unit_kerja');

        try {
            $this->unitKerjaService->createUnitKerja($name, $parentId);
            return redirect()->to('unit_kerja/manage')->with('success', 'Unit Kerja berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function batchCreate()
    {
        $data['parent_options'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['title'] = 'Buat Unit Kerja Massal';

        return view('unit_kerja/batchCreate', $data);
    }

    public function batchStore()
    {
        $parentId = $this->request->getPost('parent_id');
        $parentId = !empty($parentId) ? (int)$parentId : null;
        $names = $this->request->getPost('unit_kerja_names');

        if (!empty($names)) {
            $this->unitKerjaService->createUnitKerjaBatchFromText($names, $parentId);
        }

        return redirect()->to('unit_kerja/manage')->with('success', 'Batch pembuatan Unit Kerja selesai.');
    }

    public function processBatchCreate()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $data = $this->request->getJSON(true);
        if (empty($data) || !is_array($data)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No data provided.']);
        }

        $result = $this->unitKerjaService->createUnitKerjaBatchFromJson($data);

        return $this->response->setJSON([
            'success' => true,
            'results' => $result['results'],
            'summary' => $result['summary']
        ]);
    }

    public function edit($id)
    {
        $data['unit_kerja'] = $this->unitKerjaModel->find($id);
        $data['parent_options'] = $this->unitKerjaModel->where('id !=', $id)->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['title'] = 'Edit Unit Kerja';

        return view('unit_kerja/edit', $data);
    }

    public function update($id)
    {
        $parentId = $this->request->getPost('parent_id');
        $parentId = !empty($parentId) ? (int)$parentId : null;
        $name = $this->request->getPost('nama_unit_kerja');

        try {
            $this->unitKerjaService->updateUnitKerja((int)$id, $name, $parentId);
            return redirect()->to('unit_kerja/manage')->with('success', 'Unit Kerja berhasil diperbarui.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $this->unitKerjaService->deleteUnitKerja((int)$id);
            return redirect()->to('unit_kerja/manage')->with('success', 'Unit Kerja berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->to('unit_kerja/manage')->with('error', $e->getMessage());
        }
    }
}
