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
}
