<?php

namespace App\Domains\Batch;

use App\Shared\BaseController;
use App\Domains\UnitKerja\UnitKerjaModel;
use App\Shared\Models\StatusAsnModel;
use App\Domains\Batch\EmailBatchService;
use Exception;

class BatchController extends BaseController
{
    private $unitKerjaModel;
    private $statusAsnModel;
    private $emailBatchService;

    public function __construct()
    {
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->statusAsnModel = new StatusAsnModel();
        $this->emailBatchService = new EmailBatchService();
    }

    public function index()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
        $data['title'] = 'Buat Akun';
        return view('batch/create', $data);
    }

    public function update()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
        $data['eselon_options'] = (new \App\Shared\Models\EselonModel())->orderBy('nama_eselon', 'ASC')->findAll();
        $data['title'] = 'Edit Akun';
        return view('batch/update', $data);
    }

    public function pk()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
        $data['eselon_options'] = (new \App\Shared\Models\EselonModel())->orderBy('nama_eselon', 'ASC')->findAll();
        $data['title'] = 'Edit PK';
        return view('batch/pk', $data);
    }

    public function process_update()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $data = $this->request->getJSON(true);
        if (empty($data) || !isset($data['identifiers']) || !is_array($data['identifiers'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No identifiers provided.']);
        }

        $results = $this->emailBatchService->processBatchUpdate($data);
        return $this->response->setJSON(['success' => true, 'results' => $results]);
    }

    public function process_create()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/email');
        }

        $data = $this->request->getJSON();
        if (empty($data)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No data provided.']);
        }

        try {
            $results = $this->emailBatchService->processBatchCreate($data);
            return $this->response->setJSON(['success' => true, 'results' => $results]);
        } catch (Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
