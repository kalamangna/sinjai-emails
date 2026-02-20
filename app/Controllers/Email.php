<?php

namespace App\Controllers;

use App\Libraries\CpanelApi;
use App\Models\EmailModel;
use App\Models\AppSettingModel;
use App\Models\UnitKerjaModel;
use App\Models\StatusAsnModel;
use App\Models\EselonModel;
use App\Services\Exports\EmailExportService;
use App\Services\Features\EmailBatchService;
use App\Services\Features\EmailService;
use App\Services\Features\SyncService;
use CodeIgniter\Controller;
use Dompdf\Dompdf;
use App\Models\PkModel;
use Dompdf\Options;
use Exception;

class Email extends BaseController
{
    private $cpanelApi;
    private $emailModel;
    private $appSettingModel;
    private $unitKerjaModel;
    private $pkModel;
    private $statusAsnModel;
    private $eselonModel;
    private $emailExportService;
    private $syncService;
    private $emailBatchService;
    private $emailService;

    public function __construct()
    {
        $this->cpanelApi = new CpanelApi();
        $this->emailModel = new EmailModel();
        $this->appSettingModel = new AppSettingModel();
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->pkModel = new PkModel();
        $this->statusAsnModel = new StatusAsnModel();
        $this->eselonModel = new EselonModel();
        $this->emailExportService = new EmailExportService();
        $this->syncService = new SyncService();
        $this->emailBatchService = new EmailBatchService();
        $this->emailService = new EmailService();
    }

    public function pimpinan_hub()
    {
        return view('email/pimpinan_hub');
    }

    public function batch_hub()
    {
        return view('email/batch_hub');
    }

    public function index()
    {
        helper('time');

        try {
            $perPage = $this->request->getGet('per_page') ?? 100;
            $search = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');

            $data = $this->emailService->getEmailDashboardData($search, $bsre_status, $perPage);
            
            $lastSync = $this->appSettingModel->where('key', 'last_sync_time')->first();

            $data['search'] = $search;
            $data['bsre_status'] = $bsre_status;
            $data['per_page'] = $perPage;
            $data['last_sync_time'] = $lastSync['value'] ?? null;
            $data['pagination'] = $data['pager'];
            // filtered_count is now provided by the service
            $data['bsre_status_options'] = $data['bsre_status_labels'];

            return view('email/index', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function unit_kerja_list()
    {
        try {
            $navData = $this->emailService->getGlobalNavigationData();
            $data = [
                'title' => 'Daftar Perangkat Daerah',
                'unit_kerja' => $navData['unit_kerja_nav'],
                'back_url' => site_url('/')
            ];
            return view('email/unit_kerja_list', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function batch()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
        return view('email/batch', $data);
    }

    public function batch_update()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
        return view('email/batch_update', $data);
    }

    public function batch_perjanjian_kerja()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
        return view('email/batch_perjanjian_kerja', $data);
    }

    public function batch_update_process()
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

    public function batch_create()
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

    public function sync()
    {
        try {
            $result = $this->syncService->syncFromCpanel();
            if (is_cli()) {
                return $result;
            }
            return $this->response->setJSON($result);
        } catch (Exception $e) {
            $result = ['success' => false, 'message' => 'Failed to synchronize: ' . $e->getMessage()];
            if (is_cli()) {
                return $result;
            }
            return $this->response->setStatusCode(500)->setJSON($result);
        }
    }

    public function detail($username)
    {
        try {
            $data = $this->emailService->getEmailDetail($username);
            $data['back_url'] = site_url('email');
            return view('email/detail', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function update_details($username)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('email/detail/' . $username)->with('error', 'Invalid request method.');
        }

        $email = $this->emailModel->where('user', $username)->first();
        if (!$email) {
            return redirect()->to('email')->with('error', 'Email account not found.');
        }

        $statusAsnId = $this->request->getPost('status_asn');
        $eselonId = $this->request->getPost('eselon');
        $unitKerjaId = $this->request->getPost('unit_kerja_id');
        $pimpinan = $this->request->getPost('pimpinan');
        $pimpinanDesa = $this->request->getPost('pimpinan_desa');

        $updateData = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'gelar_depan' => $this->request->getPost('gelar_depan'),
            'gelar_belakang' => $this->request->getPost('gelar_belakang'),
            'nik' => $this->request->getPost('nik'),
            'nip' => $this->request->getPost('nip'),
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            'pendidikan' => $this->request->getPost('pendidikan'),
            'jabatan' => $this->request->getPost('jabatan'),
            'status_asn_id' => !empty($statusAsnId) ? $statusAsnId : null,
            'eselon_id' => !empty($eselonId) ? $eselonId : null,
            'unit_kerja_id' => !empty($unitKerjaId) ? $unitKerjaId : null,
            'pimpinan' => $pimpinan,
            'pimpinan_desa' => $pimpinanDesa,
        ];

        $tanggalLahir = $this->request->getPost('tanggal_lahir');
        $updateData['tanggal_lahir'] = !empty($tanggalLahir) ? $tanggalLahir : null;

        $newPassword = $this->request->getPost('password');
        if (!empty($newPassword) && $newPassword !== $email['password']) {
            try {
                $this->cpanelApi->change_password($email['email'], $newPassword);
                $updateData['password'] = $newPassword;
            } catch (Exception $e) {
                log_message('error', 'Error updating password on cPanel: ' . $e->getMessage());
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update password on cPanel: ' . $e->getMessage());
            }
        }

        try {
            $updated = $this->emailModel->update($email['id'], $updateData);
            if ($updated) {
                return redirect()->to('email/detail/' . $username)->with('success', 'Email details have been updated successfully.');
            } else {
                return redirect()->to('email/detail/' . $username)->with('info', 'No changes were detected.');
            }
        } catch (Exception $e) {
            log_message('error', 'Database error during email details update: ' . $e->getMessage());
            return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update email details due to a database error.');
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
            
            $data['per_page'] = $params['per_page'] ?? 100;
            $data['search'] = $params['search'];
            $data['status_asn'] = $params['status_asn'];
            $data['bsre_status'] = $params['bsre_status'];
            $data['pimpinan_desa'] = $params['pimpinan_desa'] ?? 1;
            $data['back_url'] = site_url('email');

            return view('email/unit_kerja_detail', $data);
        } catch (Exception $e) {
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

            $emailBuilder = $this->emailModel
                ->select('emails.*, uk.nama_unit_kerja as unit_kerja_name, parent_uk.nama_unit_kerja as parent_unit_kerja_name')
                ->join('unit_kerja as uk', 'uk.id = emails.unit_kerja_id', 'left')
                ->join('unit_kerja as parent_uk', 'parent_uk.id = uk.parent_id', 'left')
                ->where('eselon_id', $eselonId);

            if ($search) {
                $emailBuilder->groupStart()
                    ->like('email', $search)
                    ->orLike('name', $search)
                    ->orLike('nik', $search)
                    ->orLike('nip', $search)
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

            $total_emails = $emailBuilder->countAllResults(false);

            $emails = $emailBuilder->orderBy('uk.nama_unit_kerja', 'ASC')
                ->orderBy('jabatan', 'ASC')
                ->orderBy('name', 'ASC')
                ->paginate($perPage);
            $pager = $this->emailModel->pager;

            $bsre_status_options = [
                'ISSUE' => 'ISSUE',
                'EXPIRED' => 'EXPIRED',
                'RENEW' => 'RENEW',
                'WAITING_FOR_VERIFICATION' => 'WAITING_FOR_VERIFICATION',
                'NEW' => 'NEW',
                'NO_CERTIFICATE' => 'NO_CERTIFICATE',
                'NOT_REGISTERED' => 'NOT_REGISTERED',
                'SUSPEND' => 'SUSPEND',
                'REVOKE' => 'REVOKE',
                'not_synced' => 'NOT SYNCED'
            ];

            $data = [
                'eselon' => $eselon,
                'emails' => $emails,
                'total_emails' => $total_emails,
                'pagination' => $pager,
                'per_page' => $perPage,
                'search' => $search,
                'bsre_status' => $bsre_status,
                'bsre_status_options' => $bsre_status_options,
                'back_url' => site_url('email'),
            ];

            return view('email/eselon_detail', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function pimpinan()
    {
        try {
            $perPage = $this->request->getGet('per_page') ?? 100;
            $search = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');

            $emailBuilder = $this->emailModel
                ->allowCallbacks(false)
                ->select('emails.*, unit_kerja.nama_unit_kerja as unit_kerja_name, parent_unit_kerja.nama_unit_kerja as parent_unit_kerja_name, status_asn.nama_status_asn as status_asn')
                ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                ->join('unit_kerja as parent_unit_kerja', 'parent_unit_kerja.id = unit_kerja.parent_id', 'left')
                ->join('status_asn', 'status_asn.id = emails.status_asn_id', 'left')
                ->where('pimpinan', 1);

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

            $total_emails = $emailBuilder->countAllResults(false);

            $emails = $emailBuilder
                ->orderBy('emails.eselon_id', 'ASC')
                ->orderBy('COALESCE(parent_unit_kerja.nama_unit_kerja, unit_kerja.nama_unit_kerja)', 'ASC', false)
                ->orderBy('unit_kerja.parent_id IS NOT NULL', 'ASC', false)
                ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
                ->orderBy('emails.jabatan', 'ASC')
                ->orderBy('emails.name', 'ASC')
                ->paginate($perPage);

            $pager = $this->emailModel->pager;

            $bsre_status_options = [
                'ISSUE' => 'ISSUE',
                'EXPIRED' => 'EXPIRED',
                'RENEW' => 'RENEW',
                'WAITING_FOR_VERIFICATION' => 'WAITING_FOR_VERIFICATION',
                'NEW' => 'NEW',
                'NO_CERTIFICATE' => 'NO_CERTIFICATE',
                'NOT_REGISTERED' => 'NOT_REGISTERED',
                'SUSPEND' => 'SUSPEND',
                'REVOKE' => 'REVOKE',
                'not_synced' => 'NOT SYNCED'
            ];

            $data = [
                'title' => 'Daftar Email Pimpinan',
                'emails' => $emails,
                'total_emails' => $total_emails,
                'pagination' => $pager,
                'per_page' => $perPage,
                'search' => $search,
                'bsre_status' => $bsre_status,
                'bsre_status_options' => $bsre_status_options,
                'back_url' => site_url('email'),
            ];

            return view('email/pimpinan', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function pimpinan_desa()
    {
        try {
            $perPage = $this->request->getGet('per_page') ?? 100;
            $search = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');

            $emailBuilder = $this->emailModel
                ->allowCallbacks(false)
                ->select('emails.*, unit_kerja.nama_unit_kerja as unit_kerja_name, parent_unit_kerja.nama_unit_kerja as parent_unit_kerja_name, status_asn.nama_status_asn as status_asn')
                ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                ->join('unit_kerja as parent_unit_kerja', 'parent_unit_kerja.id = unit_kerja.parent_id', 'left')
                ->join('status_asn', 'status_asn.id = emails.status_asn_id', 'left')
                ->where('pimpinan_desa', 1)
                ->where('unit_kerja.nama_unit_kerja NOT LIKE', '%Kelurahan%');

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

            $total_emails = $emailBuilder->countAllResults(false);

            $emails = $emailBuilder
                ->orderBy('emails.eselon_id', 'ASC')
                ->orderBy('COALESCE(parent_unit_kerja.nama_unit_kerja, unit_kerja.nama_unit_kerja)', 'ASC', false)
                ->orderBy('unit_kerja.parent_id IS NOT NULL', 'ASC', false)
                ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
                ->orderBy('emails.jabatan', 'ASC')
                ->orderBy('emails.name', 'ASC')
                ->paginate($perPage);

            $pager = $this->emailModel->pager;

            $bsre_status_options = [
                'ISSUE' => 'ISSUE',
                'EXPIRED' => 'EXPIRED',
                'RENEW' => 'RENEW',
                'WAITING_FOR_VERIFICATION' => 'WAITING_FOR_VERIFICATION',
                'NEW' => 'NEW',
                'NO_CERTIFICATE' => 'NO_CERTIFICATE',
                'NOT_REGISTERED' => 'NOT_REGISTERED',
                'SUSPEND' => 'SUSPEND',
                'REVOKE' => 'REVOKE',
                'not_synced' => 'NOT SYNCED'
            ];

            $data = [
                'title' => 'Daftar Email Pimpinan Desa',
                'emails' => $emails,
                'total_emails' => $total_emails,
                'pagination' => $pager,
                'per_page' => $perPage,
                'search' => $search,
                'bsre_status' => $bsre_status,
                'bsre_status_options' => $bsre_status_options,
                'back_url' => site_url('email'),
            ];

            return view('email/pimpinan_desa', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function export_pimpinan_pdf()
    {
        try {
            $search = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');

            $dompdf = $this->emailExportService->generatePimpinanPdf($search, $bsre_status);

            $filename = 'Email & TTE Pimpinan - ' . format_indo_date(date('Y-m-d'), true) . '.pdf';
            $dompdf->stream($filename, ["Attachment" => true]);
            exit();
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function export_pimpinan_desa_pdf()
    {
        try {
            $search = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');

            $dompdf = $this->emailExportService->generatePimpinanDesaPdf($search, $bsre_status);

            $filename = 'Email & TTE Pimpinan Desa - ' . format_indo_date(date('Y-m-d'), true) . '.pdf';
            $dompdf->stream($filename, ["Attachment" => true]);
            exit();
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function api_unit_emails($unitKerjaId)
    {
        $unitKerja = $this->unitKerjaModel->find($unitKerjaId);
        if (!$unitKerja) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unit Kerja not found']);
        }

        $statusParuhWaktu = $this->statusAsnModel->where('nama_status_asn', 'PPPK PARUH WAKTU')->first();
        if (!$statusParuhWaktu) {
            return $this->response->setJSON(['success' => true, 'emails' => [], 'message' => 'Status PPPK PARUH WAKTU not configured.']);
        }

        $children = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->findAll();
        $childrenIds = array_column($children, 'id');
        $allUnitIds = array_merge([$unitKerjaId], $childrenIds);

        $search = $this->request->getGet('search');
        $bsre_status = $this->request->getGet('bsre_status');

        $builder = $this->emailModel->whereIn('unit_kerja_id', $allUnitIds);
        $builder->where('emails.status_asn_id', $statusParuhWaktu['id']);

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
                $builder->groupStart()
                    ->where('emails.bsre_status', null)
                    ->orWhere('emails.bsre_status', '')
                    ->groupEnd();
            } else {
                $builder->where('emails.bsre_status', $bsre_status);
            }
        }

        $emails = $builder
            ->orderBy('emails.eselon_id IS NULL', 'ASC', false)
            ->orderBy('emails.eselon_id', 'ASC')
            ->orderBy('emails.status_asn_id IS NULL', 'ASC', false)
            ->orderBy('emails.status_asn_id', 'ASC')
            ->orderBy('emails.jabatan IS NULL', 'ASC', false)
            ->orderBy('emails.jabatan', 'ASC')
            ->orderBy('emails.name', 'ASC')
            ->findAll();

        return $this->response->setJSON(['success' => true, 'emails' => $emails]);
    }

    public function api_generate_pdf()
    {
        $unitId = $this->request->getPost('unit_id');
        $emailId = $this->request->getPost('email_id');

        if (!$unitId || !$emailId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid parameters']);
        }

        try {
            $email = $this->emailModel->find($emailId);
            if (!$email) {
                throw new Exception('Email not found');
            }

            $statusParuhWaktu = $this->statusAsnModel->where('nama_status_asn', 'PPPK PARUH WAKTU')->first();
            if (!$statusParuhWaktu || $email['status_asn_id'] != $statusParuhWaktu['id']) {
                throw new Exception('Perjanjian Kerja hanya tersedia untuk PPPK PARUH WAKTU.');
            }

            $unitKerja = $this->unitKerjaModel->find($unitId);
            if ($unitKerja && !empty($unitKerja['parent_id'])) {
                $parentUnit = $this->unitKerjaModel->find($unitKerja['parent_id']);
                if ($parentUnit) {
                    $unitKerja['nama_unit_kerja'] = $unitKerja['nama_unit_kerja'] . '-' . $parentUnit['nama_unit_kerja'];
                }
            }

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);
            $logoPath = FCPATH . 'garuda.png';
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;

            $pk_data = $this->pkModel->where('email', $email['email'])->first();

            $data = [
                'email' => $email,
                'unit_kerja' => $unitKerja,
                'logoSrc' => $logoSrc,
                'pk_data' => $pk_data,
            ];

            $html = view('email/exports/perjanjian_kerja_template', $data);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $output = $dompdf->output();
            $tempDir = WRITEPATH . 'uploads/temp_export_' . $unitId;
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0775, true);
            }

            $filename = 'perjanjian_kerja_' . url_title($email['name'], '_', true) . '_' . $email['user'] . '.pdf';
            file_put_contents($tempDir . '/' . $filename, $output);

            return $this->response->setJSON(['success' => true]);
        } catch (Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function api_download_zip($unitId)
    {
        set_time_limit(0);
        $unitKerja = $this->unitKerjaModel->find($unitId);
        if (!$unitKerja) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unit Kerja not found']);
        }

        $tempDir = WRITEPATH . 'uploads/temp_export_' . $unitId;
        if (!is_dir($tempDir)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No files generated to zip.']);
        }

        $files = scandir($tempDir);
        $pdfFiles = [];
        $addedUsers = [];

        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            if (preg_match('/_([^_]+)\.pdf$/', $file, $matches)) {
                $username = $matches[1];
                if (in_array($username, $addedUsers)) continue;
                $addedUsers[] = $username;
            }
            $pdfFiles[] = $file;
        }

        if (empty($pdfFiles)) {
             return $this->response->setJSON(['success' => false, 'message' => 'Temp folder is empty.']);
        }

        $limit = 250;
        $chunks = array_chunk($pdfFiles, $limit);
        $generatedZips = [];
        $baseName = url_title($unitKerja['nama_unit_kerja'], '_', true);

        foreach ($chunks as $index => $chunk) {
            $zip = new \ZipArchive();
            $partSuffix = (count($chunks) > 1) ? '_part_' . ($index + 1) : '';
            $zipFileName = 'perjanjian_kerja_' . $baseName . $partSuffix . '.zip';
            $zipFilePath = WRITEPATH . 'uploads/' . $zipFileName;

            if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                log_message('error', 'Failed to create zip: ' . $zipFileName);
                continue;
            }

            foreach ($chunk as $file) {
                $zip->addFile($tempDir . '/' . $file, $file);
            }
            $zip->close();
            $generatedZips[] = $zipFileName;
        }

        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            unlink($tempDir . '/' . $file);
        }
        rmdir($tempDir);

        return $this->response->setJSON(['success' => true, 'files' => $generatedZips]);
    }

    public function download_zip_file($filename)
    {
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            throw new \Exception('Invalid filename');
        }

        $path = WRITEPATH . 'uploads/' . $filename;
        if (file_exists($path)) {
            return $this->response->download($path, null);
        } else {
            throw new \CodeIgniter\Exceptions\PageNotFoundException($filename . ' not found');
        }
    }

    public function export_unit_kerja_csv($unitKerjaId)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $unitKerja = $this->unitKerjaModel->find($unitKerjaId);
            if (!$unitKerja) {
                throw new Exception('Unit Kerja not found.');
            }

            $children = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->findAll();
            $childrenIds = array_column($children, 'id');
            $allUnitIds = array_merge([$unitKerjaId], $childrenIds);

            $search = $this->request->getGet('search');
            $status_asn = $this->request->getGet('status_asn');
            $bsre_status = $this->request->getGet('bsre_status');

            $builder = $this->emailModel->whereIn('unit_kerja_id', $allUnitIds);
            if ($search) {
                $builder->groupStart()
                    ->like('email', $search)
                    ->orLike('name', $search)
                    ->orLike('nik', $search)
                    ->orLike('nip', $search)
                    ->groupEnd();
            }
            if ($status_asn) $builder->where('emails.status_asn_id', $status_asn);
            if ($bsre_status) {
                if ($bsre_status === 'not_synced') {
                    $builder->groupStart()->where('emails.bsre_status', null)->orWhere('emails.bsre_status', '')->groupEnd();
                } else {
                    $builder->where('emails.bsre_status', $bsre_status);
                }
            }

            $emails = $builder->findAll();
            $totalEmails = count($emails);
            $limit = 50;
            $unitKerjaName = $unitKerja['nama_unit_kerja'];

            if ($totalEmails <= $limit) {
                $filename = url_title($unitKerjaName, '_', true) . '.csv';
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                $output = fopen('php://output', 'w');
                fputcsv($output, ['nama', 'emailAddress'], ',');
                foreach ($emails as $email) {
                    fputcsv($output, [strtoupper($email['name']), $email['email']], ',');
                }
                fclose($output);
                exit();
            } else {
                $zip = new \ZipArchive();
                $zipFileName = url_title($unitKerjaName, '_', true) . '.zip';
                $tempZipPath = WRITEPATH . 'uploads/' . $zipFileName;
                if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                    throw new Exception('Cannot create ZIP archive.');
                }

                $chunks = array_chunk($emails, $limit);
                $fileCount = 1;
                foreach ($chunks as $chunk) {
                    $csvFileName = url_title($unitKerjaName, '_', true) . '_part_' . $fileCount . '.csv';
                    $stream = fopen('php://memory', 'w+');
                    fputcsv($stream, ['nama', 'emailAddress'], ',');
                    foreach ($chunk as $email) {
                        fputcsv($stream, [strtoupper($email['name']), $email['email']], ',');
                    }
                    rewind($stream);
                    $csvContent = stream_get_contents($stream);
                    fclose($stream);
                    $zip->addFromString($csvFileName, $csvContent);
                    $fileCount++;
                }
                $zip->close();
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
                header('Content-Length: ' . filesize($tempZipPath));
                readfile($tempZipPath);
                unlink($tempZipPath);
                exit();
            }
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function export_single_perjanjian_kerja_pdf($username)
    {
        try {
            $result = $this->emailExportService->generatePerjanjianKerjaPdf($username);
            $result['dompdf']->stream($result['filename'], ["Attachment" => true]);
            exit();
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function export_perjanjian_kerja_pdf($unitKerjaId)
    {
        try {
            $result = $this->emailExportService->generatePerjanjianKerjaZip($unitKerjaId);
            
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
            header('Content-Length: ' . filesize($result['path']));
            readfile($result['path']);
            unlink($result['path']);
            exit();
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function export_unit_kerja_pdf($unitKerjaId)
    {
        try {
            $search = $this->request->getGet('search');
            $status_asn = $this->request->getGet('status_asn');
            $bsre_status = $this->request->getGet('bsre_status');
            $pimpinan_desa = $this->request->getGet('pimpinan_desa') ?? 1;

            $result = $this->emailExportService->generateUnitKerjaPdf(
                $unitKerjaId, $search, $status_asn, $bsre_status, $pimpinan_desa
            );

            $result['dompdf']->stream($result['filename'], ["Attachment" => true]);
            exit();
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function export_account_detail_pdf($unitKerjaId)
    {
        try {
            $search = $this->request->getGet('search');
            $status_asn = $this->request->getGet('status_asn');
            $bsre_status = $this->request->getGet('bsre_status');
            $pimpinan_desa = $this->request->getGet('pimpinan_desa') ?? 1;

            $result = $this->emailExportService->generateAccountDetailPdf(
                $unitKerjaId, $search, $status_asn, $bsre_status, $pimpinan_desa
            );

            $result['dompdf']->stream($result['filename'], ["Attachment" => true]);
            exit();
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function delete($id)
    {
        try {
            $email = $this->emailModel->find($id);
            if (!$email) return redirect()->to('email')->with('error', 'Email account not found.');
            $this->cpanelApi->delete_email_account($email['email']);
            $this->emailModel->delete($id);
            return redirect()->back()->with('success', 'Email account ' . $email['email'] . ' has been deleted successfully.');
        } catch (Exception $e) {
            log_message('error', 'Failed to delete email: ' . $e->getMessage());
            $this->emailModel->delete($id);
            return redirect()->back()->with('error', 'Failed to delete email account from cPanel, but removed from local list. Please check cPanel manually.');
        }
    }

    public function create_single_email()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $data = $this->request->getJSON(true);
        if (empty($data) || !isset($data['email'])) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No data provided.']);
        }

        try {
            $existing_email = $this->emailModel->where('email', $data['email'])->first();
            if ($existing_email) return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'Email already exists in local database.']);

            $this->cpanelApi->create_email_account($data['email'], $data['password'], $data['quota'] ?? 1024);
            $unitKerjaId = null;
            if (!empty($data['unitKerja'])) {
                $unit = $this->unitKerjaModel->where('nama_unit_kerja', $data['unitKerja'])->first();
                if ($unit) $unitKerjaId = $unit['id'];
            }

            $this->emailModel->insert([
                'email'      => $data['email'],
                'user'       => explode('@', $data['email'])[0],
                'domain'     => explode('@', $data['email'])[1],
                'unit_kerja_id' => $unitKerjaId,
                'password'   => $data['password'] ?? null,
                'nik'        => $data['nik'] ?? null,
                'nip'        => $data['nip'] ?? null,
                'name'       => $data['name'] ?? null,
                'jabatan'    => $data['jabatan'] ?? null,
                'status_asn_id' => $data['jenisFormasi'] ?? null,
            ]);

            return $this->response->setJSON(['success' => true, 'email' => $data['email']]);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, 'already exists') !== false) return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'Email already exists on cPanel.']);
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $errorMessage]);
        }
    }
}