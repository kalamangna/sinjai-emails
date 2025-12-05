<?php

namespace App\Controllers;

use App\Models\UnitKerjaModel;

class UnitKerja extends BaseController
{
    public function manage()
    {
        $unitKerjaModel = new UnitKerjaModel();
        $search = $this->request->getGet('search');

        if ($search) {
            $unit_kerja_list = $unitKerjaModel->like('nama_unit_kerja', $search)->findAll();
        } else {
            $unit_kerja_list = $unitKerjaModel->findAll();
        }

        // Sort using PHP's natural sort algorithm (case-insensitive)
        usort($unit_kerja_list, function ($a, $b) {
            return strnatcasecmp($a['nama_unit_kerja'], $b['nama_unit_kerja']);
        });

        $data['unit_kerja_list'] = $unit_kerja_list;
        $data['search'] = $search;
        $data['title'] = 'Manage Unit Kerja';

        return view('unit_kerja/manage', $data);
    }

    public function add()
    {
        $unitKerjaModel = new UnitKerjaModel();
        $data = [
            'nama_unit_kerja' => $this->request->getPost('nama_unit_kerja')
        ];
        $unitKerjaModel->insert($data);
        return redirect()->to('unit_kerja/manage');
    }

    public function edit($id)
    {
        $unitKerjaModel = new UnitKerjaModel();
        $data['unit_kerja'] = $unitKerjaModel->find($id);
        $data['title'] = 'Edit Unit Kerja';

        return view('unit_kerja/edit', $data);
    }

    public function update($id)
    {
        $unitKerjaModel = new UnitKerjaModel();
        $data = [
            'nama_unit_kerja' => $this->request->getPost('nama_unit_kerja')
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