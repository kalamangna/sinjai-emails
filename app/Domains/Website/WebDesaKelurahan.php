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

        // Build Query with Join for the table
        $model->select('web_desa_kelurahan.*, platforms.nama_platform as platform_name')
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
            // Filter by prefix in desa_kelurahan column
            $model->like('web_desa_kelurahan.desa_kelurahan', $filterType, 'after');
        }

        $websites = $model->orderBy('web_desa_kelurahan.kecamatan', 'ASC')
            ->orderBy('web_desa_kelurahan.desa_kelurahan', 'ASC')
            ->findAll();

        $data['websites'] = $websites;
        $data['total_filtered'] = count($websites);

        // Calculate statistics based on complete dataset (ignore filters)
        $allWebsites = (new WebDesaKelurahanModel())
            ->select('web_desa_kelurahan.*, platforms.nama_platform as platform_name')
            ->join('platforms', 'platforms.id = web_desa_kelurahan.platform_id', 'left')
            ->findAll();

        $aktif = 0;
        $nonaktif = 0;
        $platform_stats_map = [];

        foreach ($allWebsites as $web) {
            if ($web['status'] === 'AKTIF') $aktif++;
            elseif ($web['status'] === 'NONAKTIF') $nonaktif++;

            $pName = $web['platform_name'] ?: 'N/A';
            if (!isset($platform_stats_map[$pName])) {
                $platform_stats_map[$pName] = 0;
            }
            $platform_stats_map[$pName]++;
        }

        $data['stats'] = [
            'total' => count($allWebsites),
            'aktif' => $aktif,
            'nonaktif' => $nonaktif,
        ];

        if ($data['stats']['total'] > 0) {
            $data['stats']['aktif_percentage'] = (int)(($aktif / $data['stats']['total']) * 100);
            $data['stats']['nonaktif_percentage'] = (int)(($nonaktif / $data['stats']['total']) * 100);
        } else {
            $data['stats']['aktif_percentage'] = 0;
            $data['stats']['nonaktif_percentage'] = 0;
        }

        $data['platform_stats'] = [];
        foreach ($platform_stats_map as $name => $count) {
            $data['platform_stats'][] = [
                'nama_platform' => $name,
                'count' => $count
            ];
        }

        // Sort by count DESC
        usort($data['platform_stats'], function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        $db = \Config\Database::connect();
        // Get distinct kecamatan for filter (remain global)
        $data['kecamatan_list'] = $db->table('web_desa_kelurahan')
            ->select('kecamatan')
            ->distinct()
            ->orderBy('kecamatan', 'ASC')
            ->get()
            ->getResultArray();

        $data['platforms'] = $platformModel->findAll();

        $data['title'] = 'Website Desa & Kelurahan';
        $data['search'] = $search;
        $data['filterKecamatan'] = $filterKecamatan;
        $data['filterStatus'] = $filterStatus;
        $data['filterPlatform'] = $filterPlatform;
        $data['filterType'] = $filterType;

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
