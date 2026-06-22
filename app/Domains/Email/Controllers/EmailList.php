<?php

namespace App\Domains\Email\Controllers;

use App\Shared\BaseController;
use App\Domains\Email\Services\EmailService;
use App\Domains\UnitKerja\Models\UnitKerjaModel;
use App\Shared\Models\EselonModel;
use Exception;

class EmailList extends BaseController
{
    private $eselonModel;
    private $unitKerjaModel;
    private $emailService;

    public function __construct()
    {
        $this->eselonModel = new EselonModel();
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->emailService = new EmailService();
    }

    public function eselon_list()
    {
        $data['eselons'] = $this->eselonModel->orderBy('nama_eselon', 'ASC')->findAll();
        $data['title'] = 'Eselon';
        return view('email/eselon_list', $data);
    }

    public function unit_kerja_list()
    {
        try {
            $navData = $this->emailService->getGlobalNavigationData();
            $data = [
                'title' => 'Unit Kerja',
                'unit_kerja' => $navData['unit_kerja_nav'],
                'back_url' => site_url('/')
            ];
            return view('email/unit_kerja_list', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function unit_kerja_detail($unitKerjaId)
    {
        try {
            $params = [
                'per_page' => $this->request->getGet('per_page'),
                'search' => $this->request->getGet('search'),
                'status_asn' => $this->request->getGet('status_asn'),
                'bsre_status' => $this->request->getGet('bsre_status'),
                'pimpinan_desa' => $this->request->getGet('pimpinan_desa'),
            ];

            $data = $this->emailService->getUnitKerjaDetail($unitKerjaId, $params);

            $data['title'] = 'Detail Unit Kerja';
            $data['per_page'] = $params['per_page'] ?? 100;
            $data['search'] = $params['search'];
            $data['status_asn'] = $params['status_asn'];
            $data['bsre_status'] = $params['bsre_status'];
            $data['pimpinan_desa'] = $params['pimpinan_desa'] ?? 1;
            $data['back_url'] = site_url('email');

            return view('email/unit_kerja_detail', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function eselon_detail($eselonId)
    {
        try {
            $params = [
                'per_page' => $this->request->getGet('per_page'),
                'search' => $this->request->getGet('search'),
                'bsre_status' => $this->request->getGet('bsre_status')
            ];

            $data = $this->emailService->getEselonDetail($eselonId, $params);

            $bsre_status_options = [
                'ISSUE' => 'ISSUE',
                'EXPIRED' => 'EXPIRED',
                'NO_CERTIFICATE' => 'NO_CERTIFICATE',
                'NOT_REGISTERED' => 'NOT_REGISTERED',
                'not_synced' => 'NOT_SYNCED'
            ];

            $data['title'] = "Eselon " . $data['eselon']['nama_eselon'];
            $data['per_page'] = $params['per_page'] ?? 100;
            $data['search'] = $params['search'];
            $data['bsre_status'] = $params['bsre_status'];
            $data['bsre_status_options'] = $bsre_status_options;
            $data['back_url'] = site_url('email');

            return view('email/eselon_detail', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function pns_list()
    {
        try {
            $params = [
                'has_nip' => $this->request->getGet('has_nip'),
                'parent_unit_kerja_id' => $this->request->getGet('parent_unit_kerja_id'),
                'use_pk_join' => false,
                'per_page' => 100,
            ];

            $data = $this->emailService->getAsnList('PNS', $params);

            $data['parent_unit_kerjas'] = $this->unitKerjaModel->where('parent_id', null)->orderBy('nama_unit_kerja', 'ASC')->findAll();

            $data['title'] = 'Daftar PNS';
            $data['has_nip'] = $params['has_nip'];
            $data['parent_unit_kerja_id'] = $params['parent_unit_kerja_id'];
            $data['back_url'] = site_url('email');

            return view('email/pns_list', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function pppk_list()
    {
        try {
            $params = [
                'has_nip' => $this->request->getGet('has_nip'),
                'use_pk_join' => true,
                'per_page' => 100,
            ];

            $data = $this->emailService->getAsnList('PPPK', $params);

            $data['title'] = 'PPPK Penuh Waktu';
            $data['has_nip'] = $params['has_nip'];
            $data['back_url'] = site_url('email');

            return view('email/pppk_list', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function pppk_pw_list()
    {
        try {
            $params = [
                'has_nip' => $this->request->getGet('has_nip'),
                'use_pk_join' => true,
                'per_page' => 100,
            ];

            $data = $this->emailService->getAsnList('PPPK PARUH WAKTU', $params);

            $data['title'] = 'PPPK Paruh Waktu';
            $data['has_nip'] = $params['has_nip'];
            $data['back_url'] = site_url('email');

            return view('email/pppk_pw_list', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }
}
