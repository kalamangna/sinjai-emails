<?php

namespace App\Domains\Website;

use App\Shared\BaseController;
use App\Domains\Website\WebDesaKelurahanModel;
use App\Shared\Models\PlatformModel;
use CodeIgniter\Files\File;
use Config\Services;

class WebDesaKelurahan extends BaseController
{
    protected $exportService;
    protected $websiteService;

    public function __construct()
    {
        $this->exportService = new \App\Domains\Website\WebMonitoringExportService();
        $this->websiteService = new \App\Domains\Website\WebsiteService();
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

        // Use aggregated counts for stats - single query
        $statsRaw = $model->select("COUNT(id) as total, SUM(CASE WHEN status = 'AKTIF' THEN 1 ELSE 0 END) as aktif, SUM(CASE WHEN status = 'NONAKTIF' THEN 1 ELSE 0 END) as nonaktif")->asArray()->first();
        $total = (int)($statsRaw['total'] ?? 0);
        $aktif = (int)($statsRaw['aktif'] ?? 0);
        $nonaktif = (int)($statsRaw['nonaktif'] ?? 0);

        $stats = [
            'total' => $total,
            'aktif' => $aktif,
            'nonaktif' => $nonaktif,
            'aktif_percentage' => $total > 0 ? (int)(($aktif / $total) * 100) : 0,
            'nonaktif_percentage' => $total > 0 ? (int)(($nonaktif / $total) * 100) : 0,
        ];

        // Platform distribution via aggregation
        $platform_stats_raw = $model->select('platforms.nama_platform, COUNT(web_desa_kelurahan.id) as count')
            ->join('platforms', 'platforms.id = web_desa_kelurahan.platform_id', 'left')
            ->groupBy('platforms.nama_platform')
            ->orderBy('count', 'DESC')
            ->asArray()
            ->findAll();

        $platform_stats = [];
        foreach ($platform_stats_raw as $row) {
            $platform_stats[] = [
                'nama_platform' => $row['nama_platform'] ?: 'N/A',
                'count' => (int)$row['count']
            ];
        }

        // Build Query with Join for the table
        $model->select('web_desa_kelurahan.id, web_desa_kelurahan.desa_kelurahan, web_desa_kelurahan.kecamatan, web_desa_kelurahan.domain, web_desa_kelurahan.status, web_desa_kelurahan.tanggal_berakhir, web_desa_kelurahan.sisa_hari, platforms.nama_platform as platform_name')
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

        $perPage = 100;
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

        $data = [
            'websites' => $websites,
            'pager' => $pager,
            'stats' => $stats,
            'platform_stats' => $platform_stats,
            'kecamatan_list' => $kecamatan_list,
            'platforms' => $platformModel->asArray()->findAll(),
            'title' => 'Website Desa & Kelurahan',
            'search' => $search,
            'filterKecamatan' => $filterKecamatan,
            'filterStatus' => $filterStatus,
            'filterPlatform' => $filterPlatform,
            'filterType' => $filterType,
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

        $data['platforms'] = $platformModel->findAll();
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

        $data = [
            'domain'           => $domain,
            'status'           => $this->request->getPost('status'),
            'tanggal_berakhir' => $expirationDate,
            'platform_id'      => $this->request->getPost('platform_id') ?: null,
            'dikelola_kominfo' => $this->request->getPost('dikelola_kominfo'),
            'keterangan'       => $this->request->getPost('keterangan'),
        ];

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

        // Attempt to fetch date
        $newDate = $this->websiteService->determineExpirationDate($website['desa_kelurahan'], $website['domain'], null);

        if ($newDate) {
            $updateData = [
                'tanggal_berakhir' => $newDate,
                'sisa_hari' => $this->websiteService->calculateDaysRemaining($newDate)
            ];

            $model->update($id, $updateData);

            return $this->response->setJSON([
                'status' => 'success',
                'date' => formatSingkat($newDate),
                'message' => 'Date synced successfully'
            ]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Could not fetch expiration date']);
    }

    private function fetchPandiExpiration($domain)
    {
        return $this->websiteService->fetchPandiExpiration($domain);
    }
}
