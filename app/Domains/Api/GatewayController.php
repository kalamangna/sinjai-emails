<?php

namespace App\Domains\Api;

use App\Shared\BaseController;
use App\Domains\Email\EmailModel;
use App\Shared\Models\StatusAsnModel;
use CodeIgniter\API\ResponseTrait;

class GatewayController extends BaseController
{
    use ResponseTrait;

    protected $emailModel;

    public function __construct()
    {
        $this->emailModel = new EmailModel();
    }

    /**
     * Display API Documentation page for administrators
     */
    public function index()
    {
        $appSettingModel = new \App\Shared\Models\AppSettingModel();
        $token = $appSettingModel->where('key', 'api_gateway_token')->first();
        
        // Fallback to env if not in database yet (though sync:all should handle it)
        $displayToken = $token['value'] ?? env('API_GATEWAY_TOKEN') ?? 'BELUM_DIATUR';

        $unitModel = new \App\Domains\UnitKerja\UnitKerjaModel();
        $units = $unitModel->orderBy('nama_unit_kerja', 'ASC')->findAll();

        $data = [
            'title' => 'API Gateway Documentation',
            'token' => $displayToken,
            'base_url' => base_url('api/v1'),
            'units' => $units
        ];

        return view('api/index', $data);
    }

    /**
     * List all active emails with basic profile info
     */
    public function listEmails()
    {
        $builder = $this->emailModel->select('emails.email, emails.name, emails.nik, emails.nip, emails.jabatan, emails.bsre_status, COALESCE(u1.api_unit_id, u2.api_unit_id) as api_unit_id')
            ->join('unit_kerja u1', 'u1.id = emails.unit_kerja_id', 'left')
            ->join('unit_kerja u2', 'u2.id = u1.parent_id', 'left');

        $builder = $this->applyQueryFilters($builder);
        $emails = $builder->orderBy('emails.email', 'ASC')->findAll();

        return $this->respond([
            'status' => 'success',
            'count'  => count($emails),
            'data'   => $emails
        ]);
    }

    /**
     * List PPPK Penuh Waktu employees
     */
    public function listPppkPenuh()
    {
        $statusAsnModel = new StatusAsnModel();
        $status = $statusAsnModel->where('nama_status_asn', 'PPPK')->first();

        if (!$status) {
            return $this->respond(['status' => 'success', 'count' => 0, 'data' => []]);
        }

        $builder = $this->emailModel->select('emails.email, emails.name, emails.nik, emails.nip, emails.jabatan, emails.bsre_status, COALESCE(u1.api_unit_id, u2.api_unit_id) as api_unit_id')
            ->join('unit_kerja u1', 'u1.id = emails.unit_kerja_id', 'left')
            ->join('unit_kerja u2', 'u2.id = u1.parent_id', 'left')
            ->where('emails.status_asn_id', $status['id']);

        $builder = $this->applyQueryFilters($builder);
        $data = $builder->orderBy('emails.name', 'ASC')->findAll();

        return $this->respond([
            'status' => 'success',
            'count'  => count($data),
            'data'   => $data
        ]);
    }

    /**
     * List PPPK Paruh Waktu employees
     */
    public function listPppkParuh()
    {
        $statusAsnModel = new StatusAsnModel();
        $status = $statusAsnModel->where('nama_status_asn', 'PPPK PARUH WAKTU')->first();

        if (!$status) {
            return $this->respond(['status' => 'success', 'count' => 0, 'data' => []]);
        }

        $builder = $this->emailModel->select('emails.email, emails.name, emails.nik, emails.nip, emails.jabatan, emails.bsre_status, COALESCE(u1.api_unit_id, u2.api_unit_id) as api_unit_id')
            ->join('unit_kerja u1', 'u1.id = emails.unit_kerja_id', 'left')
            ->join('unit_kerja u2', 'u2.id = u1.parent_id', 'left')
            ->where('emails.status_asn_id', $status['id']);

        $builder = $this->applyQueryFilters($builder);
        $data = $builder->orderBy('emails.name', 'ASC')->findAll();

        return $this->respond([
            'status' => 'success',
            'count'  => count($data),
            'data'   => $data
        ]);
    }

    /**
     * List PNS employees
     */
    public function listPns()
    {
        $statusAsnModel = new StatusAsnModel();
        $pnsStatus = $statusAsnModel->where('nama_status_asn', 'PNS')->first();

        if (!$pnsStatus) {
            return $this->respond(['status' => 'success', 'count' => 0, 'data' => []]);
        }

        $builder = $this->emailModel->select('emails.email, emails.name, emails.nik, emails.nip, emails.jabatan, emails.bsre_status, COALESCE(u1.api_unit_id, u2.api_unit_id) as api_unit_id')
            ->join('unit_kerja u1', 'u1.id = emails.unit_kerja_id', 'left')
            ->join('unit_kerja u2', 'u2.id = u1.parent_id', 'left')
            ->where('emails.status_asn_id', $pnsStatus['id']);

        $builder = $this->applyQueryFilters($builder);
        $pns = $builder->orderBy('emails.name', 'ASC')->findAll();

        return $this->respond([
            'status' => 'success',
            'count'  => count($pns),
            'data'   => $pns
        ]);
    }

    /**
     * List emails by Unit Kerja ID or External API Unit ID (Including Descendants)
     */
    public function listByUnit($unitId)
    {
        $unitModel = new \App\Domains\UnitKerja\UnitKerjaModel();
        
        // 1. Try to find the unit by api_unit_id (External)
        $unit = $unitModel->where('api_unit_id', $unitId)->first();
        
        // 2. Fallback to local ID if not found by api_unit_id
        if (!$unit) {
            $unit = $unitModel->find($unitId);
        }

        if (!$unit) {
            return $this->failNotFound('Unit kerja tidak ditemukan.');
        }

        // 3. Find all child units
        $childIds = $unitModel->where('parent_id', $unit['id'])->select('id')->findAll();
        $targetIds = array_column($childIds, 'id');
        $targetIds[] = $unit['id']; // Include the main unit itself

        $builder = $this->emailModel->select('emails.email, emails.name, emails.nik, emails.nip, emails.jabatan, emails.bsre_status, COALESCE(u1.api_unit_id, u2.api_unit_id) as api_unit_id')
            ->join('unit_kerja u1', 'u1.id = emails.unit_kerja_id', 'left')
            ->join('unit_kerja u2', 'u2.id = u1.parent_id', 'left')
            ->whereIn('emails.unit_kerja_id', $targetIds);

        $builder = $this->applyQueryFilters($builder);
        $emails = $builder->orderBy('emails.name', 'ASC')->findAll();

        return $this->respond([
            'status' => 'success',
            'count'  => count($emails),
            'unit_id' => $unit['id'],
            'api_unit_id' => $unit['api_unit_id'],
            'nama_unit_kerja' => $unit['nama_unit_kerja'],
            'data'   => $emails
        ]);
    }

    /**
     * Apply URL query filters to the builder
     */
    private function applyQueryFilters($builder)
    {
        $request = \Config\Services::request();
        
        if ($name = $request->getGet('name')) {
            $builder->like('emails.name', $name);
        }
        if ($email = $request->getGet('email')) {
            $builder->like('emails.email', $email);
        }
        if ($nip = $request->getGet('nip')) {
            $builder->where('emails.nip', $nip);
        }
        if ($nik = $request->getGet('nik')) {
            $builder->where('emails.nik', $nik);
        }
        if ($jabatan = $request->getGet('jabatan')) {
            $builder->like('emails.jabatan', $jabatan);
        }
        if ($status = $request->getGet('bsre_status')) {
            $builder->where('emails.bsre_status', $status);
        }
        if ($apiUnitId = $request->getGet('api_unit_id')) {
            $builder->groupStart()
                    ->where('u1.api_unit_id', $apiUnitId)
                    ->orWhere('u2.api_unit_id', $apiUnitId)
                    ->groupEnd();
        }

        return $builder;
    }
}
