<?php

namespace App\Domains\Email;

use App\Shared\BaseController;
use App\Domains\Email\EmailModel;
use App\Domains\Email\EmailService;
use App\Domains\UnitKerja\UnitKerjaModel;
use App\Shared\Models\EselonModel;
use App\Shared\Models\StatusAsnModel;
use Exception;

class EmailList extends BaseController
{
    private $emailModel;
    private $eselonModel;
    private $unitKerjaModel;
    private $statusAsnModel;
    private $emailService;

    public function __construct()
    {
        $this->emailModel = new EmailModel();
        $this->eselonModel = new EselonModel();
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->statusAsnModel = new StatusAsnModel();
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

            $data['title'] = $data['unit_kerja']['nama_unit_kerja'];
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
            $eselon = $this->eselonModel->find($eselonId);
            if (!$eselon) {
                throw new Exception('Eselon not found.');
            }

            $perPage = $this->request->getGet('per_page') ?? 100;
            $search = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');

            // Base query for counting
            $getCountBuilder = function() use ($eselonId, $search, $bsre_status) {
                $builder = $this->emailModel->where('eselon_id', $eselonId);
                if ($search) {
                    $builder->groupStart()
                        ->like('email', $search)
                        ->orLike('name', $search)
                        ->orLike('nik', $search)
                        ->orLike('nip', $search)
                        ->groupEnd();
                }
                if ($bsre_status) {
                    if ($bsre_status === 'not_synced') {
                        $builder->groupStart()->where('bsre_status', null)->orWhere('bsre_status', '')->groupEnd();
                    } else {
                        $builder->where('bsre_status', $bsre_status);
                    }
                }
                return $builder;
            };

            $total_emails = $getCountBuilder()->countAllResults();
            $active_bsre_count = $getCountBuilder()->where('bsre_status', 'ISSUE')->countAllResults();

            // Fresh builder for pagination with details
            $emailBuilder = $this->emailModel
                ->select([
                    'emails.id',
                    'emails.name',
                    'emails.nip',
                    'emails.jabatan',
                    'emails.user',
                    'emails.email',
                    'emails.bsre_status',
                    'unit_kerja.nama_unit_kerja as unit_kerja_name',
                    'parent_unit_kerja.nama_unit_kerja as parent_unit_kerja_name',
                    'status_asn.nama_status_asn as status_asn'
                ])
                ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                ->join('unit_kerja as parent_unit_kerja', 'parent_unit_kerja.id = unit_kerja.parent_id', 'left')
                ->join('status_asn', 'status_asn.id = emails.status_asn_id', 'left')
                ->where('emails.eselon_id', $eselonId);

            if ($search) {
                $emailBuilder->groupStart()
                    ->like('emails.email', $search)
                    ->orLike('emails.name', $search)
                    ->orLike('emails.nik', $search)
                    ->orLike('emails.nip', $search)
                    ->groupEnd();
            }

            if ($bsre_status) {
                if ($bsre_status === 'not_synced') {
                    $emailBuilder->groupStart()
                        ->where('emails.bsre_status', null)
                        ->orWhere('emails.bsre_status', '')
                        ->groupEnd();
                } else {
                    $emailBuilder->where('emails.bsre_status', $bsre_status);
                }
            }

            $emails = $emailBuilder->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
                ->orderBy('emails.jabatan', 'ASC')
                ->orderBy('emails.name', 'ASC')
                ->paginate($perPage);
            $pager = $this->emailModel->pager;

            $bsre_status_options = [
                'ISSUE' => 'ISSUE',
                'EXPIRED' => 'EXPIRED',
                'NO_CERTIFICATE' => 'NO_CERTIFICATE',
                'NOT_REGISTERED' => 'NOT_REGISTERED',
                'not_synced' => 'NOT_SYNCED'
            ];

            $data = [
                'title' => "Eselon " . $eselon['nama_eselon'],
                'eselon' => $eselon,
                'emails' => $emails,
                'total_emails' => $total_emails,
                'active_bsre_count' => $active_bsre_count,
                'pager' => $pager,
                'per_page' => $perPage,
                'search' => $search,
                'bsre_status' => $bsre_status,
                'bsre_status_options' => $bsre_status_options,
                'back_url' => site_url('email'),
            ];

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
            $statusPns = $this->statusAsnModel->where('nama_status_asn', 'PNS')->asArray()->first();

            if (!$statusPns) {
                throw new Exception('Status PNS belum dikonfigurasi di sistem.');
            }

            $hasNip = $this->request->getGet('has_nip');

            $emailBuilder = $this->emailModel
                ->select([
                    'emails.id',
                    'emails.name',
                    'emails.nip',
                    'emails.jabatan',
                    'emails.user',
                    'emails.email',
                    'emails.bsre_status',
                    'unit_kerja.nama_unit_kerja as unit_kerja_name',
                    'parent_unit_kerja.nama_unit_kerja as parent_unit_kerja_name'
                ])
                ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                ->join('unit_kerja as parent_unit_kerja', 'parent_unit_kerja.id = unit_kerja.parent_id', 'left')
                ->where('emails.status_asn_id', $statusPns['id']);

            if ($hasNip === 'yes') {
                $emailBuilder->where('emails.nip !=', '')->where('emails.nip IS NOT NULL');
            } elseif ($hasNip === 'no') {
                $emailBuilder->groupStart()
                    ->where('emails.nip', '')
                    ->orWhere('emails.nip', null)
                    ->groupEnd();
            }

            $emails = $emailBuilder->orderBy('emails.name', 'ASC');

            $totalCountBuilder = $this->emailModel->where('emails.status_asn_id', $statusPns['id']);
            if ($hasNip === 'yes') {
                $totalCountBuilder->where('emails.nip !=', '')->where('emails.nip IS NOT NULL');
            } elseif ($hasNip === 'no') {
                $totalCountBuilder->groupStart()
                    ->where('emails.nip', '')
                    ->orWhere('emails.nip', null)
                    ->groupEnd();
            }

            $data = [
                'title' => 'Daftar PNS',
                'emails' => $emails->paginate(100, 'default'),
                'pager' => $this->emailModel->pager,
                'total_count' => $totalCountBuilder->countAllResults(),
                'has_nip' => $hasNip,
                'back_url' => site_url('email')
            ];

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
            $statusPppk = $this->statusAsnModel->where('nama_status_asn', 'PPPK')->asArray()->first();

            if (!$statusPppk) {
                throw new Exception('Status PPPK belum dikonfigurasi di sistem.');
            }

            $hasNip = $this->request->getGet('has_nip');

            $emailBuilder = $this->emailModel
                ->select([
                    'emails.id',
                    'emails.name',
                    'emails.nip',
                    'emails.jabatan',
                    'emails.user',
                    'emails.email',
                    'emails.bsre_status',
                    'unit_kerja.nama_unit_kerja as unit_kerja_name',
                    'parent_unit_kerja.nama_unit_kerja as parent_unit_kerja_name',
                    'MIN(pk.nomor) as nomor_pk',
                ])
                ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                ->join('unit_kerja as parent_unit_kerja', 'parent_unit_kerja.id = unit_kerja.parent_id', 'left')
                ->join('pk', 'pk.email = emails.email', 'left')
                ->where('emails.status_asn_id', $statusPppk['id']);

            if ($hasNip === 'yes') {
                $emailBuilder->where('emails.nip !=', '')->where('emails.nip IS NOT NULL');
            } elseif ($hasNip === 'no') {
                $emailBuilder->groupStart()
                    ->where('emails.nip', '')
                    ->orWhere('emails.nip', null)
                    ->groupEnd();
            }

            $emails = $emailBuilder->groupBy('emails.id, emails.name, emails.nip, emails.jabatan, emails.user, emails.email, emails.bsre_status, unit_kerja.nama_unit_kerja, parent_unit_kerja.nama_unit_kerja')
                ->orderBy('CAST(MIN(pk.nomor) AS UNSIGNED)', 'ASC');

            $totalCountBuilder = $this->emailModel->where('emails.status_asn_id', $statusPppk['id']);
            if ($hasNip === 'yes') {
                $totalCountBuilder->where('emails.nip !=', '')->where('emails.nip IS NOT NULL');
            } elseif ($hasNip === 'no') {
                $totalCountBuilder->groupStart()
                    ->where('emails.nip', '')
                    ->orWhere('emails.nip', null)
                    ->groupEnd();
            }

            $data = [
                'title' => 'PPPK Penuh Waktu',
                'emails' => $emails->paginate(100, 'default'),
                'pager' => $this->emailModel->pager,
                'total_count' => $totalCountBuilder->countAllResults(),
                'has_nip' => $hasNip,
                'back_url' => site_url('email')
            ];

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
            $statusPppkPw = $this->statusAsnModel->where('nama_status_asn', 'PPPK PARUH WAKTU')->asArray()->first();

            if (!$statusPppkPw) {
                throw new Exception('Status PPPK PARUH WAKTU belum dikonfigurasi di sistem.');
            }

            $hasNip = $this->request->getGet('has_nip');

            $emailBuilder = $this->emailModel
                ->select([
                    'emails.id',
                    'emails.name',
                    'emails.nip',
                    'emails.jabatan',
                    'emails.user',
                    'emails.email',
                    'emails.bsre_status',
                    'unit_kerja.nama_unit_kerja as unit_kerja_name',
                    'parent_unit_kerja.nama_unit_kerja as parent_unit_kerja_name',
                    'MIN(pk.nomor) as nomor_pk'
                ])
                ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                ->join('unit_kerja as parent_unit_kerja', 'parent_unit_kerja.id = unit_kerja.parent_id', 'left')
                ->join('pk', 'pk.email = emails.email', 'left')
                ->where('emails.status_asn_id', $statusPppkPw['id']);

            if ($hasNip === 'yes') {
                $emailBuilder->where('emails.nip !=', '')->where('emails.nip IS NOT NULL');
            } elseif ($hasNip === 'no') {
                $emailBuilder->groupStart()
                    ->where('emails.nip', '')
                    ->orWhere('emails.nip', null)
                    ->groupEnd();
            }

            $emails = $emailBuilder->groupBy('emails.id, emails.name, emails.nip, emails.jabatan, emails.user, emails.email, emails.bsre_status, unit_kerja.nama_unit_kerja, parent_unit_kerja.nama_unit_kerja')
                ->orderBy('CAST(MIN(pk.nomor) AS UNSIGNED)', 'ASC')
                ->orderBy('MIN(pk.nomor)', 'ASC');

            $totalCountBuilder = $this->emailModel->where('emails.status_asn_id', $statusPppkPw['id']);
            if ($hasNip === 'yes') {
                $totalCountBuilder->where('emails.nip !=', '')->where('emails.nip IS NOT NULL');
            } elseif ($hasNip === 'no') {
                $totalCountBuilder->groupStart()
                    ->where('emails.nip', '')
                    ->orWhere('emails.nip', null)
                    ->groupEnd();
            }

            $data = [
                'title' => 'PPPK Paruh Waktu',
                'emails' => $emails->paginate(100, 'default'),
                'pager' => $this->emailModel->pager,
                'total_count' => $totalCountBuilder->countAllResults(),
                'has_nip' => $hasNip,
                'back_url' => site_url('email')
            ];

            return view('email/pppk_pw_list', $data);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }
}
