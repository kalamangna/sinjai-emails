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

        $data = [
            'title' => 'API Gateway Documentation',
            'token' => $displayToken,
            'base_url' => base_url('email/api/v1')
        ];

        return view('api/index', $data);
    }

    /**
     * List all active emails with basic profile info
     */
    public function listEmails()
    {
        $emails = $this->emailModel->select('email, name, nip, jabatan, humandiskquota, humandiskused, bsre_status')
            ->orderBy('email', 'ASC')
            ->findAll();

        return $this->respond([
            'status' => 'success',
            'count'  => count($emails),
            'data'   => $emails
        ]);
    }

    /**
     * List PPPK employees (Both Full-time and Part-time)
     */
    public function listPppk()
    {
        $statusAsnModel = new StatusAsnModel();
        $pppkStatuses = $statusAsnModel->groupStart()
            ->like('nama_status_asn', 'PPPK', 'after')
            ->groupEnd()
            ->findColumn('id');

        if (empty($pppkStatuses)) {
            return $this->respond([
                'status' => 'success',
                'count'  => 0,
                'data'   => []
            ]);
        }

        $pppk = $this->emailModel->select('email, name, nip, jabatan, humandiskquota, humandiskused, bsre_status, status_asn.nama_status_asn')
            ->join('status_asn', 'status_asn.id = emails.status_asn_id')
            ->whereIn('status_asn_id', $pppkStatuses)
            ->orderBy('name', 'ASC')
            ->findAll();

        return $this->respond([
            'status' => 'success',
            'count'  => count($pppk),
            'data'   => $pppk
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

        $pns = $this->emailModel->select('email, name, nip, jabatan, humandiskquota, humandiskused, bsre_status')
            ->where('status_asn_id', $pnsStatus['id'])
            ->orderBy('name', 'ASC')
            ->findAll();

        return $this->respond([
            'status' => 'success',
            'count'  => count($pns),
            'data'   => $pns
        ]);
    }

    /**
     * List emails by Unit Kerja ID
     */
    public function listByUnit($unitId)
    {
        $emails = $this->emailModel->select('email, name, nip, jabatan, humandiskquota, humandiskused, bsre_status')
            ->where('unit_kerja_id', $unitId)
            ->orderBy('name', 'ASC')
            ->findAll();

        return $this->respond([
            'status' => 'success',
            'count'  => count($emails),
            'unit_id' => $unitId,
            'data'   => $emails
        ]);
    }
}
