<?php

namespace App\Controllers;

use App\Models\WebOpdModel;
use App\Models\PlatformModel;
use App\Models\UnitKerjaModel;
use CodeIgniter\Files\File;
use Config\Services;

class WebOpd extends BaseController
{
    public function index()
    {
        $model = new WebOpdModel();
        $platformModel = new PlatformModel();
        $unitKerjaModel = new UnitKerjaModel();
        
        $search = trim($this->request->getGet('search') ?? '');
        $filterStatus = trim($this->request->getGet('status') ?? '');
        $filterPlatform = trim($this->request->getGet('filter_platform') ?? '');

        // Build Query with Joins
        $model->select('web_opd.*, platforms.nama_platform as platform_name, unit_kerja.nama_unit_kerja')
              ->join('platforms', 'platforms.id = web_opd.platform_id', 'left')
              ->join('unit_kerja', 'unit_kerja.id = web_opd.unit_kerja_id', 'left');

        if ($search !== '') {
            $model->groupStart()
                  ->like('unit_kerja.nama_unit_kerja', $search)
                  ->orLike('web_opd.domain', $search)
                  ->groupEnd();
        }

        if ($filterStatus !== '') {
            $model->where('web_opd.status', $filterStatus);
        }

        if ($filterPlatform !== '') {
            $model->where('platforms.nama_platform', $filterPlatform);
        }

        $data['websites'] = $model->orderBy('unit_kerja.nama_unit_kerja', 'ASC')->findAll();
        
        $data['total_filtered'] = count($data['websites']);
        
        $db = \Config\Database::connect();
        
        $data['stats'] = [
            'total' => $db->table('web_opd')->countAllResults(),
            'aktif' => $db->table('web_opd')->where('status', 'AKTIF')->countAllResults(),
            'nonaktif' => $db->table('web_opd')->where('status', 'NONAKTIF')->countAllResults(),
        ];
        
        $total = $data['stats']['total'];
        if ($total > 0) {
            $data['stats']['aktif_percentage'] = round(($data['stats']['aktif'] / $total) * 100, 2);
            $data['stats']['nonaktif_percentage'] = round(($data['stats']['nonaktif'] / $total) * 100, 2);
        } else {
            $data['stats']['aktif_percentage'] = 0;
            $data['stats']['nonaktif_percentage'] = 0;
        }

        // Platform distribution
        $data['platform_stats'] = $db->table('web_opd')
            ->select('platforms.nama_platform, COUNT(web_opd.id) as count')
            ->join('platforms', 'platforms.id = web_opd.platform_id', 'left')
            ->groupBy('platforms.nama_platform')
            ->get()
            ->getResultArray();

        $data['platforms'] = $platformModel->findAll();

        $data['title'] = 'Website OPD';
        $data['search'] = $search;
        $data['filterStatus'] = $filterStatus;
        $data['filterPlatform'] = $filterPlatform;

        return view('web_opd/index', $data);
    }

    public function export_pdf()
    {
        helper('time');
        $model = new WebOpdModel();
        
        $search = trim($this->request->getGet('search') ?? '');
        $filterStatus = trim($this->request->getGet('status') ?? '');
        $filterPlatform = trim($this->request->getGet('filter_platform') ?? '');

        // Build Query
        $model->select('web_opd.*, platforms.nama_platform as platform_name, unit_kerja.nama_unit_kerja')
              ->join('platforms', 'platforms.id = web_opd.platform_id', 'left')
              ->join('unit_kerja', 'unit_kerja.id = web_opd.unit_kerja_id', 'left');

        if ($search !== '') {
            $model->groupStart()
                  ->like('unit_kerja.nama_unit_kerja', $search)
                  ->orLike('web_opd.domain', $search)
                  ->groupEnd();
        }

        if ($filterStatus !== '') {
            $model->where('web_opd.status', $filterStatus);
        }

        if ($filterPlatform !== '') {
            $model->where('platforms.nama_platform', $filterPlatform);
        }

        $websites = $model->orderBy('unit_kerja.nama_unit_kerja', 'ASC')->findAll();

        $db = \Config\Database::connect();
        
        $stats = [
            'total' => $db->table('web_opd')->countAllResults(),
            'aktif' => $db->table('web_opd')->where('status', 'AKTIF')->countAllResults(),
            'nonaktif' => $db->table('web_opd')->where('status', 'NONAKTIF')->countAllResults(),
        ];

        $total = $stats['total'];
        if ($total > 0) {
            $stats['aktif_percentage'] = round(($stats['aktif'] / $total) * 100, 2);
            $stats['nonaktif_percentage'] = round(($stats['nonaktif'] / $total) * 100, 2);
        } else {
            $stats['aktif_percentage'] = 0;
            $stats['nonaktif_percentage'] = 0;
        }

        // Platform distribution
        $platform_stats = $db->table('web_opd')
            ->select('platforms.nama_platform, COUNT(web_opd.id) as count')
            ->join('platforms', 'platforms.id = web_opd.platform_id', 'left')
            ->groupBy('platforms.nama_platform')
            ->get()
            ->getResultArray();

        $logoPath = FCPATH . 'logo.png';
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml(view('web_opd/pdf_export', [
            'websites' => $websites,
            'stats' => $stats,
            'platform_stats' => $platform_stats,
            'logoSrc' => $logoSrc,
            'current_date' => format_indo_date(date('Y-m-d')),
        ]));
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        $filename = url_title('data_website_opd_' . format_indo_date(date('Y-m-d'), false), '_', true) . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
    }

    public function create()
    {
        $platformModel = new PlatformModel();
        $unitKerjaModel = new UnitKerjaModel();
        $data['platforms'] = $platformModel->findAll();
        $data['unit_kerja'] = $unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['title'] = 'Add Website OPD';
        return view('web_opd/form', $data);
    }

    public function store()
    {
        $model = new WebOpdModel();
        
        $domain = $this->request->getPost('domain');
        $unitKerjaId = $this->request->getPost('unit_kerja_id');
        $manualDate = $this->request->getPost('tanggal_berakhir');
        
        $expirationDate = $this->determineExpirationDate($domain, $manualDate);

        $data = [
            'unit_kerja_id'    => $unitKerjaId,
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
        return redirect()->to('web_opd')->with('message', 'Data added successfully.');
    }

    public function edit($id)
    {
        $model = new WebOpdModel();
        $platformModel = new PlatformModel();
        $unitKerjaModel = new UnitKerjaModel();
        $data['website'] = $model->find($id);
        
        if (!$data['website']) {
            return redirect()->to('web_opd')->with('error', 'Data not found.');
        }

        $data['platforms'] = $platformModel->findAll();
        $data['unit_kerja'] = $unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['title'] = 'Edit Website OPD';
        return view('web_opd/form', $data);
    }

    public function update($id)
    {
        $model = new WebOpdModel();
        $website = $model->find($id);

        if (!$website) {
            return redirect()->to('web_opd')->with('error', 'Data not found.');
        }

        $domain = $this->request->getPost('domain');
        
        $expirationDate = $this->determineExpirationDate($domain, null);

        $data = [
            'unit_kerja_id'    => $this->request->getPost('unit_kerja_id'),
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
        return redirect()->to('web_opd')->with('message', 'Data updated successfully.');
    }

    public function sync_expiration($id)
    {
        $model = new WebOpdModel();
        $website = $model->find($id);

        if (!$website) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data not found']);
        }

        // Attempt to fetch date
        $newDate = $this->determineExpirationDate($website['domain'], null);

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

    private function determineExpirationDate($domain, $manualDate)
    {
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
