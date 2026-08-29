<?php

namespace App\Domains\Email\Controllers;

use App\Shared\BaseController;
use App\Domains\Email\Services\EmailService;
use App\Domains\Email\Models\EmailModel;
use App\Domains\UnitKerja\Models\UnitKerjaModel;
use App\Shared\Models\EselonModel;
use Exception;

class EmailListController extends BaseController
{
    private $eselonModel;
    private $unitKerjaModel;
    private $emailService;
    private $emailModel;

    public function __construct()
    {
        $this->eselonModel = new EselonModel();
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->emailService = new EmailService();
        $this->emailModel = new EmailModel();
    }

    public function eselonList()
    {
        $data['eselons'] = $this->eselonModel->orderBy('nama_eselon', 'ASC')->findAll();
        $data['title'] = 'Eselon';
        return view('email/eselon_list', $data);
    }

    public function unitKerjaList()
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

    public function unitKerjaDetail($unitKerjaId)
    {
        try {
            $params = [
                'per_page' => $this->request->getGet('per_page'),
                'search' => $this->request->getGet('search'),
                'status_asn' => $this->request->getGet('status_asn'),
                'bsre_status' => $this->request->getGet('bsre_status'),
                'pimpinan_desa' => $this->request->getGet('pimpinan_desa'),
                'password_status' => $this->request->getGet('password_status'),
            ];

            $data = $this->emailService->getUnitKerjaDetail($unitKerjaId, $params);

            $data['title'] = 'Detail Unit Kerja';
            $data['per_page'] = $params['per_page'] ?? 100;
            $data['search'] = $params['search'];
            $data['status_asn'] = $params['status_asn'];
            $data['bsre_status'] = $params['bsre_status'];
            $data['pimpinan_desa'] = $params['pimpinan_desa'] ?? 1;
            $data['password_status'] = $params['password_status'];
            $data['back_url'] = site_url('email');

            return view('email/unit_kerja_detail', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function apiBatchUpdatePassword()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $payload  = $this->request->getJSON(true);
        $email    = $payload['email'] ?? '';
        $password = $payload['password'] ?? '';

        if (empty($email) || empty($password)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Email dan password harus diisi.']);
        }

        if (strlen($password) < 8) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Password minimal 8 karakter.']);
        }

        try {
            $username = strstr($email, '@', true);
            $emailService = new \App\Domains\Email\Services\EmailService();
            $emailService->updatePassword($username, $password);

            helper('audit');
            log_audit('CHANGE_PASSWORD', 'Email', null, 'Password diubah untuk akun: ' . $email);

            return $this->response->setJSON(['success' => true, 'message' => 'Password berhasil diperbarui.']);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function eselonDetail($eselonId)
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

    public function pnsList()
    {
        try {
            $params = [
                'has_nip' => $this->request->getGet('has_nip'),
                'bsre_status' => $this->request->getGet('bsre_status'),
                'parent_unit_kerja_id' => $this->request->getGet('parent_unit_kerja_id'),
                'use_pk_join' => false,
                'per_page' => 100,
            ];

            $data = $this->emailService->getAsnList('PNS', $params);

            $data['parent_unit_kerjas'] = $this->unitKerjaModel->where('parent_id', null)->orderBy('nama_unit_kerja', 'ASC')->findAll();

            $data['bsre_status_options'] = [
                'ISSUE'          => 'ISSUE',
                'EXPIRED'        => 'EXPIRED',
                'NO_CERTIFICATE' => 'NO_CERTIFICATE',
                'NOT_REGISTERED' => 'NOT_REGISTERED',
                'not_synced'     => 'BELUM SYNC',
            ];

            $data['title'] = 'PNS';
            $data['has_nip'] = $params['has_nip'];
            $data['bsre_status'] = $params['bsre_status'];
            $data['parent_unit_kerja_id'] = $params['parent_unit_kerja_id'];
            $data['back_url'] = site_url('email');

            return view('email/pns_list', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function pppkList()
    {
        try {
            $params = [
                'has_nip' => $this->request->getGet('has_nip'),
                'use_pk_join' => true,
                'per_page' => 100,
            ];

            $data = $this->emailService->getAsnList('PPPK', $params);

            $data['title'] = 'PPPK';
            $data['has_nip'] = $params['has_nip'];
            $data['back_url'] = site_url('email');

            return view('email/pppk_list', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function pppkPwList()
    {
        try {
            $params = [
                'has_nip' => $this->request->getGet('has_nip'),
                'use_pk_join' => true,
                'per_page' => 100,
            ];

            $data = $this->emailService->getAsnList('PPPK PARUH WAKTU', $params);

            $data['title'] = 'PPPK PW';
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
