<?php

namespace App\Domains\Website\Services;

use App\Domains\Website\Models\WebDesaKelurahanModel;
use Config\Services;

class WebsiteService
{
    protected $webDesaModel;

    public function __construct()
    {
        $this->webDesaModel = new WebDesaKelurahanModel();
        require_once APPPATH . 'Shared/Helpers/TanggalHelper.php';
    }

    public function determineExpirationDate($desaKelurahan, $domain, $manualDate)
    {
        // Rule for Kelurahan: Expire in 2/1/2027
        if (stripos($desaKelurahan, 'KELURAHAN') !== false) {
            return '2027-02-01';
        }

        // Rule for Desa: Check PANDI RDAP
        if (!empty($domain)) {
            $cleanDomain = preg_replace('#^https?://#', '', $domain);
            $cleanDomain = rtrim($cleanDomain, '/');

            $fetchedDate = $this->fetchPandiExpiration($cleanDomain);
            if ($fetchedDate) {
                return $fetchedDate;
            }
        }

        return $manualDate ?: null;
    }

    public function fetchPandiExpiration($domain)
    {
        try {
            $client = Services::curlrequest();
            $baseUrl = env('PANDI_BASE_URL') ?: 'https://rdap.pandi.id';
            $url = rtrim($baseUrl, '/') . '/rdap/domain/' . $domain;
            
            $response = $client->request('GET', $url, [
                'timeout' => 5,
                'http_errors' => false
            ]);

            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody(), true);
                if (isset($body['events']) && is_array($body['events'])) {
                    foreach ($body['events'] as $event) {
                        if (isset($event['eventAction']) && $event['eventAction'] === 'expiration') {
                            if (isset($event['eventDate'])) {
                                return formatIsiInput($event['eventDate']);
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'PANDI RDAP Error: ' . $e->getMessage());
        }

        return null;
    }

    public function calculateDaysRemaining($date)
    {
        if (!$date) return null;
        
        $end = new \DateTime($date);
        $now = new \DateTime();
        $diff = $now->diff($end);
        return (int)$diff->format('%r%a');
    }

    public function getDesaKelurahanStats()
    {
        $statsRaw = $this->webDesaModel->select("COUNT(id) as total, SUM(CASE WHEN status = 'AKTIF' THEN 1 ELSE 0 END) as aktif, SUM(CASE WHEN status = 'NONAKTIF' THEN 1 ELSE 0 END) as nonaktif")->asArray()->first();
        $total = (int)($statsRaw['total'] ?? 0);
        $aktif = (int)($statsRaw['aktif'] ?? 0);
        $nonaktif = (int)($statsRaw['nonaktif'] ?? 0);

        return [
            'total' => $total,
            'aktif' => $aktif,
            'nonaktif' => $nonaktif,
            'aktif_percentage' => $total > 0 ? (int)(($aktif / $total) * 100) : 0,
            'nonaktif_percentage' => $total > 0 ? (int)(($nonaktif / $total) * 100) : 0,
        ];
    }

    public function getDesaKelurahanPlatformStats()
    {
        $platform_stats_raw = $this->webDesaModel->select('platforms.nama_platform, COUNT(web_desa_kelurahan.id) as count')
            ->join('platforms', 'platforms.id = web_desa_kelurahan.platform_id', 'left')
            ->groupBy('platforms.nama_platform')
            ->orderBy('count', 'DESC')
            ->asArray()
            ->findAll();

        $platform_stats = [];
        foreach ($platform_stats_raw as $row) {
            $platform_stats[] = [
                'nama_platform' => $row['nama_platform'] ?: 'TIDAK TERDAFTAR',
                'count' => (int)$row['count']
            ];
        }

        // Custom sort order: Sideka, OpenSID, Pihak Ketiga, Tidak Terdaftar
        usort($platform_stats, function ($a, $b) {
            $order = [
                'SIDEKA-NG' => 1,
                'OPENSID' => 2,
                'PIHAK KETIGA' => 3,
                'TIDAK TERDAFTAR' => 4
            ];
            
            $nameA = strtoupper($a['nama_platform']);
            $nameB = strtoupper($b['nama_platform']);
            
            $posA = $order[$nameA] ?? 99;
            $posB = $order[$nameB] ?? 99;
            
            if ($posA === $posB) {
                return $b['count'] <=> $a['count'];
            }
            
            return $posA <=> $posB;
        });

        return $platform_stats;
    }

    public function getOpdStats()
    {
        $webOpdModel = new \App\Domains\Website\Models\WebOpdModel();
        $statsRaw = $webOpdModel->select("COUNT(id) as total, SUM(CASE WHEN status = 'AKTIF' THEN 1 ELSE 0 END) as aktif, SUM(CASE WHEN status = 'NONAKTIF' THEN 1 ELSE 0 END) as nonaktif")->asArray()->first();
        $total = (int)($statsRaw['total'] ?? 0);
        $aktif = (int)($statsRaw['aktif'] ?? 0);
        $nonaktif = (int)($statsRaw['nonaktif'] ?? 0);

        return [
            'total' => $total,
            'aktif' => $aktif,
            'nonaktif' => $nonaktif,
            'aktif_percentage' => $total > 0 ? (int)(($aktif / $total) * 100) : 0,
            'nonaktif_percentage' => $total > 0 ? (int)(($nonaktif / $total) * 100) : 0,
        ];
    }
}
