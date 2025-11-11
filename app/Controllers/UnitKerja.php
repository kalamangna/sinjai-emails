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
            $data['unit_kerja_list'] = $unitKerjaModel->like('nama_unit_kerja', $search)->findAll();
        } else {
            $data['unit_kerja_list'] = $unitKerjaModel->findAll();
        }

        $data['search'] = $search;

        return view('templates/header', ['title' => 'Manage Unit Kerja'])
            . view('unit_kerja/manage', $data)
            . view('templates/footer');
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

        return view('templates/header', ['title' => 'Edit Unit Kerja'])
            . view('unit_kerja/edit', $data)
            . view('templates/footer');
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