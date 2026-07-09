<?php

namespace App\Domains\Website\Controllers;

use App\Shared\BaseController;
use App\Domains\Website\Models\WebDesaKelurahanModel;
use App\Shared\Models\PlatformModel;
use CodeIgniter\Files\File;
use Config\Services;

class WebDesaKelurahan extends BaseController
{
    protected $exportService;
    protected $websiteService;

    public function __construct()
    {
        $this->exportService = new \App\Domains\Website\Services\WebMonitoringExportService();
        $this->websiteService = new \App\Domains\Website\Services\WebsiteService();
    }

    public function index()
    {
        $model = new WebDesaKelurahanModel();
        $platformModel = new PlatformModel();

        $search = trim($this->request->getGet('search') ?? '');
        $filterKecamatan = trim($this->request->getGet('kecamatan') ?? '');
        $filterStatus = trim($this->request->getGet('status') ?? '');
        $filterPlatform = trim($this->request->getGet('filter_platform') ?? '');
        $filterType = trim($this->request->getGet('type') ?? '');

        // Use service for stats and platform distribution
        $stats = $this->websiteService->getDesaKelurahanStats();
        $platform_stats = $this->websiteService->getDesaKelurahanPlatformStats();

        // Build Query with Join for the table
        $model->select('web_desa_kelurahan.id, web_desa_kelurahan.desa_kelurahan, web_desa_kelurahan.kecamatan, web_desa_kelurahan.domain, web_desa_kelurahan.ip_address, web_desa_kelurahan.hosting_provider, web_desa_kelurahan.hosting_status, web_desa_kelurahan.status, web_desa_kelurahan.tanggal_berakhir, web_desa_kelurahan.sisa_hari, web_desa_kelurahan.dikelola_kominfo, web_desa_kelurahan.keterangan, platforms.nama_platform as platform_name')
            ->join('platforms', 'platforms.id = web_desa_kelurahan.platform_id', 'left');

        if ($search !== '') {
            $model->groupStart()
                ->like('web_desa_kelurahan.desa_kelurahan', $search)
                ->orLike('web_desa_kelurahan.kecamatan', $search)
                ->orLike('web_desa_kelurahan.domain', $search)
                ->groupEnd();
        }

        if ($filterKecamatan !== '') {
            $model->where('web_desa_kelurahan.kecamatan', $filterKecamatan);
        }

        if ($filterStatus !== '') {
            $model->where('web_desa_kelurahan.status', $filterStatus);
        }

        if ($filterPlatform !== '') {
            if ($filterPlatform === 'NULL') {
                $model->where('web_desa_kelurahan.platform_id', null);
            } else {
                $model->where('platforms.nama_platform', $filterPlatform);
            }
        }

        if ($filterType !== '') {
            $model->like('web_desa_kelurahan.desa_kelurahan', $filterType, 'after');
        }

        $perPage = 200;
        $websites = $model->orderBy('web_desa_kelurahan.kecamatan', 'ASC')
            ->orderBy('web_desa_kelurahan.desa_kelurahan', 'ASC')
            ->asArray()
            ->paginate($perPage);
        $pager = $model->pager;

        $db = \Config\Database::connect();
        $kecamatan_list = $db->table('web_desa_kelurahan')
            ->select('kecamatan')
            ->distinct()
            ->orderBy('kecamatan', 'ASC')
            ->get()
            ->getResultArray();

        $appSettingModel = new \App\Shared\Models\AppSettingModel();
        $last_sync_website = $appSettingModel->where('key', 'last_sync_website')->first();

        $data = [
            'websites' => $websites,
            'pager' => $pager,
            'stats' => $stats,
            'platform_stats' => $platform_stats,
            'kecamatan_list' => $kecamatan_list,
            'platforms' => $platformModel->orderBy("FIELD(nama_platform, 'KOMINFO', 'SIDEKA-NG', 'OPENSID', 'PIHAK KETIGA')")->asArray()->findAll(),
            'title' => 'Website Desa dan Kelurahan',
            'search' => $search,
            'filterKecamatan' => $filterKecamatan,
            'filterStatus' => $filterStatus,
            'filterPlatform' => $filterPlatform,
            'filterType' => $filterType,
            'last_sync_website' => $last_sync_website['value'] ?? null,
        ];

        return view('web_desa_kelurahan/index', $data);
    }

    public function export_pdf()
    {
        $search = trim($this->request->getGet('search') ?? '');
        $filterPlatform = trim($this->request->getGet('filter_platform') ?? '');
        $filterStatus = trim($this->request->getGet('status') ?? '');
        $filterType = trim($this->request->getGet('type') ?? '');

        $result = $this->exportService->generateWebDesaPdf(
            $search,
            $filterPlatform,
            $filterStatus,
            $filterType
        );

        log_audit('EXPORT', 'WebDesaKelurahan', null, 'Ekspor PDF Website Desa dan Kelurahan');

        $result['dompdf']->stream($result['filename'], ['Attachment' => true]);
    }

    public function edit($id)
    {
        $model = new WebDesaKelurahanModel();
        $platformModel = new PlatformModel();
        $data['website'] = $model->find($id);

        if (!$data['website']) {
            return redirect()->to('web_desa_kelurahan')->with('error', 'Data not found.');
        }

        $data['platforms'] = $platformModel->orderBy("FIELD(nama_platform, 'KOMINFO', 'SIDEKA-NG', 'OPENSID', 'PIHAK KETIGA')")->findAll();
        $data['title'] = 'Edit Website Desa & Kelurahan';
        return view('web_desa_kelurahan/form', $data);
    }

    public function update($id)
    {
        $model = new WebDesaKelurahanModel();
        $website = $model->find($id);

        if (!$website) {
            return redirect()->to('web_desa_kelurahan')->with('error', 'Data not found.');
        }

        $domain = $this->request->getPost('domain');

        $expirationDate = $this->websiteService->determineExpirationDate($website['desa_kelurahan'], $domain, null);
        $hostingInfo = $this->websiteService->getHostingInfo($domain, $website['ip_address'] ?? null, $website['hosting_provider'] ?? null);

        $data = [
            'domain'           => $domain,
            'status'           => $this->request->getPost('status'),
            'tanggal_berakhir' => $expirationDate,
            'platform_id'      => $this->request->getPost('platform_id') ?: null,
            'dikelola_kominfo' => $this->request->getPost('dikelola_kominfo'),
            'keterangan'       => $this->request->getPost('keterangan'),
        ];

        if ($hostingInfo) {
            $data['ip_address'] = $hostingInfo['ip'];
            $data['hosting_provider'] = $hostingInfo['provider'];
            $data['hosting_status'] = $hostingInfo['status'];
        }

        if ($data['tanggal_berakhir']) {
            $data['sisa_hari'] = $this->websiteService->calculateDaysRemaining($data['tanggal_berakhir']);
        }

        $model->update($id, $data);
        return redirect()->to('web_desa_kelurahan')->with('message', 'Data updated successfully.');
    }

    public function sync_expiration($id)
    {
        $model = new WebDesaKelurahanModel();
        $website = $model->find($id);

        if (!$website) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data not found']);
        }

        // 1. Sync Domain Expiration
        $newDate = $this->websiteService->determineExpirationDate($website['desa_kelurahan'], $website['domain'], null);
        
        // 2. Sync Hosting Info
        $hostingInfo = $this->websiteService->getHostingInfo($website['domain'], $website['ip_address'] ?? null, $website['hosting_provider'] ?? null);

        $updateData = [];
        if ($newDate) {
            $updateData['tanggal_berakhir'] = $newDate;
            $updateData['sisa_hari'] = $this->websiteService->calculateDaysRemaining($newDate);
        }
        
        if ($hostingInfo) {
            $updateData['ip_address'] = $hostingInfo['ip'];
            $updateData['hosting_provider'] = $hostingInfo['provider'];
            $updateData['hosting_status'] = $hostingInfo['status'];
        }

        if (!empty($updateData)) {
            $model->update($id, $updateData);
            $updatedWebsite = $model->find($id);

            return $this->response->setJSON([
                'status' => 'success',
                'date' => $newDate ? formatSingkat($newDate) : ($website['tanggal_berakhir'] ? formatSingkat($website['tanggal_berakhir']) : '-'),
                'ip_address' => $updatedWebsite['ip_address'] ?: '-',
                'hosting_provider' => $updatedWebsite['hosting_provider'] ?: '-',
                'hosting_status' => $updatedWebsite['hosting_status'] ?: 'UNKNOWN',
                'message' => 'Data website dan hosting berhasil disinkronkan'
            ]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyinkronkan data website']);
    }

    private function fetchPandiExpiration($domain)
    {
        return $this->websiteService->fetchPandiExpiration($domain);
    }
}
