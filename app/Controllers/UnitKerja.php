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
            $data['unit_kerja_list'] = $unitKerjaModel->like('nama_unit_kerja', $search)->orderBy('nama_unit_kerja', 'ASC')->findAll();
        } else {
            $data['unit_kerja_list'] = $unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        }

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