<?php

namespace App\Domains\UnitKerja;

use App\Shared\BaseController;
use App\Domains\UnitKerja\UnitKerjaModel;

class UnitKerja extends BaseController
{
    public function manage()
    {
        $unitKerjaModel = new UnitKerjaModel();
        
        // Calculate Statistics
        $data['total_units'] = $unitKerjaModel->countAllResults(false);
        $data['total_parents'] = (new UnitKerjaModel())->where('parent_id', null)->countAllResults();
        $data['total_children'] = (new UnitKerjaModel())->where('parent_id !=', null)->countAllResults();

        $search = $this->request->getGet('search');

        $unitKerjaModel->select('unit_kerja.*, parent.nama_unit_kerja as parent_name')
            ->join('unit_kerja as parent', 'parent.id = unit_kerja.parent_id', 'left');

        if ($search) {
            $unitKerjaModel->groupStart()
                ->like('unit_kerja.nama_unit_kerja', $search)
                ->orLike('parent.nama_unit_kerja', $search)
                ->groupEnd();
        }

        $unit_kerja_list = $unitKerjaModel->findAll();

        // Sort using PHP's natural sort algorithm (case-insensitive)
        usort($unit_kerja_list, function ($a, $b) {
            return strnatcasecmp($a['nama_unit_kerja'], $b['nama_unit_kerja']);
        });

        $data['unit_kerja_list'] = $unit_kerja_list;
        $data['search'] = $search;
        $data['title'] = 'Master Data Unit Kerja';

        return view('unit_kerja/manage', $data);
    }

    public function add()
    {
        $unitKerjaModel = new UnitKerjaModel();
        $data['parent_options'] = $unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['title'] = 'Tambah Unit Kerja';

        return view('unit_kerja/add', $data);
    }

    public function store()
    {
        $unitKerjaModel = new UnitKerjaModel();
        $parentId = $this->request->getPost('parent_id');
        $data = [
            'nama_unit_kerja' => $this->request->getPost('nama_unit_kerja'),
            'parent_id' => !empty($parentId) ? $parentId : null,
        ];
        $unitKerjaModel->insert($data);
        return redirect()->to('unit_kerja/manage');
    }

    public function batch_create()
    {
        $unitKerjaModel = new UnitKerjaModel();
        $data['parent_options'] = $unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['title'] = 'Buat Unit Kerja Massal';

        return view('unit_kerja/batch_create', $data);
    }

    public function batch_store()
    {
        $unitKerjaModel = new UnitKerjaModel();
        $parentId = $this->request->getPost('parent_id');
        $names = $this->request->getPost('unit_kerja_names');

        if (!empty($names)) {
            $namesArray = explode("\n", $names);
            $data = [];
            foreach ($namesArray as $name) {
                $trimmedName = trim($name);
                if (!empty($trimmedName)) {
                    $data[] = [
                        'nama_unit_kerja' => $trimmedName,
                        'parent_id' => !empty($parentId) ? $parentId : null,
                    ];
                }
            }

            if (!empty($data)) {
                $unitKerjaModel->insertBatch($data);
            }
        }

        return redirect()->to('unit_kerja/manage');
    }

    public function edit($id)
    {
        $unitKerjaModel = new UnitKerjaModel();
        $data['unit_kerja'] = $unitKerjaModel->find($id);
        $data['parent_options'] = $unitKerjaModel->where('id !=', $id)->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['title'] = 'Edit Unit Kerja';

        return view('unit_kerja/edit', $data);
    }

    public function update($id)
    {
        $unitKerjaModel = new UnitKerjaModel();
        $parentId = $this->request->getPost('parent_id');
        $data = [
            'nama_unit_kerja' => $this->request->getPost('nama_unit_kerja'),
            'parent_id' => !empty($parentId) ? $parentId : null,
        ];
        $unitKerjaModel->update($id, $data);
        return redirect()->to('unit_kerja/manage');
    }

    public function delete($id)
    {
        $unitKerjaModel = new UnitKerjaModel();
        $unitKerjaModel->delete($id);
        return redirect()->to('unit_kerja/manage');
    }
}