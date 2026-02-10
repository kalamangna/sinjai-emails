<?php

namespace App\Controllers;

use App\Models\WebDesaKelurahanModel;
use App\Models\PlatformModel;
use CodeIgniter\Files\File;
use Config\Services;

class WebDesaKelurahan extends BaseController
{
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

        // Calculate statistics based on filtered data
        $aktif = 0;
        $nonaktif = 0;
        $platform_stats_map = [];

        foreach ($websites as $web) {
            if ($web['status'] === 'AKTIF') $aktif++;
            elseif ($web['status'] === 'NONAKTIF') $nonaktif++;

            $pName = $web['platform_name'] ?: '-';
            if (!isset($platform_stats_map[$pName])) {
                $platform_stats_map[$pName] = 0;
            }
            $platform_stats_map[$pName]++;
        }

        $data['stats'] = [
            'total' => $data['total_filtered'],
            'aktif' => $aktif,
            'nonaktif' => $nonaktif,
        ];

        if ($data['total_filtered'] > 0) {
            $data['stats']['aktif_percentage'] = (int)(($aktif / $data['total_filtered']) * 100);
            $data['stats']['nonaktif_percentage'] = (int)(($nonaktif / $data['total_filtered']) * 100);
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
        helper('time');
        $model = new WebDesaKelurahanModel();

        $search = trim($this->request->getGet('search') ?? '');
        $filterKecamatan = trim($this->request->getGet('kecamatan') ?? '');
        $filterStatus = trim($this->request->getGet('status') ?? '');
        $filterPlatform = trim($this->request->getGet('filter_platform') ?? '');
        $filterType = trim($this->request->getGet('type') ?? '');

        // Build Query
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
            $model->like('web_desa_kelurahan.desa_kelurahan', $filterType, 'after');
        }

        $websites = $model->orderBy('web_desa_kelurahan.kecamatan', 'ASC')
            ->orderBy('web_desa_kelurahan.desa_kelurahan', 'ASC')
            ->findAll();

        $db = \Config\Database::connect();

        $aktif = 0;
        $nonaktif = 0;
        $platform_stats_map = [];

        foreach ($websites as $web) {
            if ($web['status'] === 'AKTIF') $aktif++;
            elseif ($web['status'] === 'NONAKTIF') $nonaktif++;

            $pName = $web['platform_name'] ?: '-';
            if (!isset($platform_stats_map[$pName])) {
                $platform_stats_map[$pName] = 0;
            }
            $platform_stats_map[$pName]++;
        }

        $stats = [
            'total' => count($websites),
            'aktif' => $aktif,
            'nonaktif' => $nonaktif,
        ];

        if ($stats['total'] > 0) {
            $stats['aktif_percentage'] = (int)(($aktif / $stats['total']) * 100);
            $stats['nonaktif_percentage'] = (int)(($nonaktif / $stats['total']) * 100);
        } else {
            $stats['aktif_percentage'] = 0;
            $stats['nonaktif_percentage'] = 0;
        }

        // Platform distribution from filtered data
        $platform_stats = [];
        foreach ($platform_stats_map as $name => $count) {
            $platform_stats[] = [
                'nama_platform' => $name,
                'count' => $count
            ];
        }

        // Sort by count DESC
        usort($platform_stats, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        $logoPath = FCPATH . 'logo.png';
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;

        // Handle chart data from POST request
        $statusChartData = $this->request->getPost('statusChartData');
        $platformChartData = $this->request->getPost('platformChartData');

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml(view('web_desa_kelurahan/pdf_export', [
            'websites' => $websites,
            'stats' => $stats,
            'platform_stats' => $platform_stats,
            'logoSrc' => $logoSrc,
            'current_date' => format_indo_date(date('Y-m-d')),
            'title' => 'DATA WEBSITE DESA & KELURAHAN',
            'subtitle' => 'PEMERINTAH KABUPATEN SINJAI',
            'statusChart' => $statusChartData,
            'platformChart' => $platformChartData,
        ]));
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'Data Website Desa & Kelurahan - ' . format_indo_date(date('Y-m-d'), true) . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
    }

    public function create()
    {
        $platformModel = new PlatformModel();
        $data['platforms'] = $platformModel->findAll();
        $data['title'] = 'Add Website Desa & Kelurahan';
        return view('web_desa_kelurahan/form', $data);
    }

    public function store()
    {
        $model = new WebDesaKelurahanModel();

        $domain = $this->request->getPost('domain');
        $desaKelurahan = $this->request->getPost('desa_kelurahan');
        $manualDate = $this->request->getPost('tanggal_berakhir');

        $expirationDate = $this->determineExpirationDate($desaKelurahan, $domain, $manualDate);

        $data = [
            'kecamatan'        => $this->request->getPost('kecamatan'),
            'desa_kelurahan'   => strtoupper($desaKelurahan),
            'domain'           => $domain,
            'status'           => $this->request->getPost('status'),
            'tanggal_berakhir' => $expirationDate,
            'platform_id'      => $this->request->getPost('platform_id') ?: null,
            'dikelola_kominfo' => $this->request->getPost('dikelola_kominfo'),
            'keterangan'       => $this->request->getPost('keterangan'),
        ];

        if ($data['tanggal_berakhir']) {
            $end = new \DateTime($data['tanggal_berakhir']);
            $now = new \DateTime();
            $diff = $now->diff($end);
            $data['sisa_hari'] = (int)$diff->format('%r%a');
        }

        $model->insert($data);
        return redirect()->to('web_desa_kelurahan')->with('message', 'Data added successfully.');
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

        $expirationDate = $this->determineExpirationDate($website['desa_kelurahan'], $domain, null);

        $data = [
            'domain'           => $domain,
            'status'           => $this->request->getPost('status'),
            'tanggal_berakhir' => $expirationDate,
            'platform_id'      => $this->request->getPost('platform_id') ?: null,
            'dikelola_kominfo' => $this->request->getPost('dikelola_kominfo'),
            'keterangan'       => $this->request->getPost('keterangan'),
        ];

        if ($data['tanggal_berakhir']) {
            $end = new \DateTime($data['tanggal_berakhir']);
            $now = new \DateTime();
            $diff = $now->diff($end);
            $data['sisa_hari'] = (int)$diff->format('%r%a');
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
        $newDate = $this->determineExpirationDate($website['desa_kelurahan'], $website['domain'], null);

        if ($newDate) {
            $updateData = ['tanggal_berakhir' => $newDate];

            // Calculate sisa_hari
            $end = new \DateTime($newDate);
            $now = new \DateTime();
            $diff = $now->diff($end);
            $sisaHari = (int)$diff->format('%r%a');
            $updateData['sisa_hari'] = $sisaHari;

            $model->update($id, $updateData);

            return $this->response->setJSON([
                'status' => 'success',
                'date' => date('d-m-Y', strtotime($newDate)),
                'message' => 'Date synced successfully'
            ]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Could not fetch expiration date']);
    }

    private function determineExpirationDate($desaKelurahan, $domain, $manualDate)
    {
        // Rule for Kelurahan: Expire in 2/1/2026
        if (stripos($desaKelurahan, 'KELURAHAN') !== false) {
            return '2026-02-01';
        }

        // Rule for Desa: Check PANDI RDAP
        // Only if domain is present
        if (!empty($domain)) {
            // Remove protocol if present for clean domain check (though PANDI might handle it, safer to send raw domain)
            $cleanDomain = preg_replace('#^https?://#', '', $domain);
            $cleanDomain = rtrim($cleanDomain, '/');

            $fetchedDate = $this->fetchPandiExpiration($cleanDomain);
            if ($fetchedDate) {
                return $fetchedDate;
            }
        }

        // Fallback to manual date if rules don't apply or fail
        return $manualDate ?: null;
    }

    private function fetchPandiExpiration($domain)
    {
        try {
            $client = Services::curlrequest();
            $response = $client->request('GET', "https://rdap.pandi.id/rdap/domain/{$domain}", [
                'timeout' => 5,
                'http_errors' => false
            ]);

            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody(), true);
                if (isset($body['events']) && is_array($body['events'])) {
                    foreach ($body['events'] as $event) {
                        if (isset($event['eventAction']) && $event['eventAction'] === 'expiration') {
                            // Date format usually: "2024-05-18T03:57:33Z"
                            if (isset($event['eventDate'])) {
                                return date('Y-m-d', strtotime($event['eventDate']));
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error or ignore
            log_message('error', 'PANDI RDAP Error: ' . $e->getMessage());
        }

        return null;
    }
}
