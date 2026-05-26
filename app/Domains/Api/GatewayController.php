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
        $emails = $this->emailModel->select('emails.email, emails.name, emails.nik, emails.nip, emails.jabatan, emails.bsre_status, unit_kerja.api_unit_id')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
            ->orderBy('emails.email', 'ASC')
            ->findAll();

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

        $data = $this->emailModel->select('emails.email, emails.name, emails.nik, emails.nip, emails.jabatan, emails.bsre_status, unit_kerja.api_unit_id')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
            ->where('emails.status_asn_id', $status['id'])
            ->orderBy('emails.name', 'ASC')
            ->findAll();

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

        $data = $this->emailModel->select('emails.email, emails.name, emails.nik, emails.nip, emails.jabatan, emails.bsre_status, unit_kerja.api_unit_id')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
            ->where('emails.status_asn_id', $status['id'])
            ->orderBy('emails.name', 'ASC')
            ->findAll();

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

        $pns = $this->emailModel->select('emails.email, emails.name, emails.nik, emails.nip, emails.jabatan, emails.bsre_status, unit_kerja.api_unit_id')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
            ->where('emails.status_asn_id', $pnsStatus['id'])
            ->orderBy('emails.name', 'ASC')
            ->findAll();

        return $this->respond([
            'status' => 'success',
            'count'  => count($pns),
            'data'   => $pns
        ]);
    }

    /**
     * List emails by Unit Kerja ID or External API Unit ID
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

        $emails = $this->emailModel->select('email, name, nik, nip, jabatan, bsre_status')
            ->where('unit_kerja_id', $unit['id'])
            ->orderBy('name', 'ASC')
            ->findAll();

        return $this->respond([
            'status' => 'success',
            'count'  => count($emails),
            'unit_id' => $unit['id'],
            'api_unit_id' => $unit['api_unit_id'],
            'nama_unit_kerja' => $unit['nama_unit_kerja'],
            'data'   => $emails
        ]);
    }
}
