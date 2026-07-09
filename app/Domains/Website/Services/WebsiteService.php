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

        // Custom sort order: Kominfo, Sideka, OpenSID, Pihak Ketiga, Tidak Terdaftar
        usort($platform_stats, function ($a, $b) {
            $order = [
                'KOMINFO'        => 1,
                'SIDEKA-NG'      => 2,
                'OPENSID'        => 3,
                'PIHAK KETIGA'   => 4,
                'TIDAK TERDAFTAR' => 5,
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

    public function getHostingInfo($domain, $existingIp = null, $existingProvider = null)
    {
        if (empty($domain)) {
            return null;
        }

        $cleanDomain = preg_replace('#^https?://#', '', $domain);
        $cleanDomain = rtrim($cleanDomain, '/');

        // 1. Resolve IP
        $ip = @gethostbyname($cleanDomain);
        if ($ip === $cleanDomain || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return [
                'ip'       => null,
                'provider' => 'TIDAK TERRESOLUSI',
                'status'   => 'NONAKTIF'
            ];
        }

        // 2. Determine Provider
        // Skip IP lookup jika IP tidak berubah dan provider sudah terisi & valid
        $provider = $existingProvider ?: 'UNKNOWN';
        $needsLookup = empty($existingIp)
            || $ip !== $existingIp
            || empty($existingProvider)
            || $existingProvider === 'UNKNOWN';

        if ($needsLookup) {
            $provider = $this->resolveIspProvider($ip, $domain) ?? 'UNKNOWN';
        }

        // 3. Check connectivity
        $status = 'NONAKTIF';
        $connection = @fsockopen($cleanDomain, 443, $errno, $errstr, 1.0);
        if (is_resource($connection)) {
            fclose($connection);
            $status = 'AKTIF';
        } else {
            $connection = @fsockopen($cleanDomain, 80, $errno, $errstr, 1.0);
            if (is_resource($connection)) {
                fclose($connection);
                $status = 'AKTIF';
            }
        }

        return [
            'ip'       => $ip,
            'provider' => $provider,
            'status'   => $status
        ];
    }

    /**
     * Coba resolve ISP/provider dari IP menggunakan beberapa endpoint secara berurutan.
     * Setiap endpoint dicoba dengan mekanisme retry (exponential backoff) sebelum pindah ke fallback.
     *
     * @param  string $ip     Alamat IP yang ingin di-lookup.
     * @param  string $domain Domain asal (untuk keperluan logging).
     * @return string|null    Nama ISP/provider, atau null jika semua endpoint gagal.
     */
    protected function resolveIspProvider(string $ip, string $domain): ?string
    {
        // Berdasarkan uji diagnostik production:
        // - ipwhois.app (HTTPS/443) : ✅ BEKERJA
        // - ip-api.com  (HTTP/80)   : ❌ Port 80 diblokir di server production
        // - ipinfo.io   (HTTPS/443) : ❌ Timeout di server production
        // Hanya menggunakan ipwhois.app dengan 3x retry + exponential backoff.

        $url     = 'https://ipwhois.app/json/' . $ip;
        $client  = Services::curlrequest();
        $timeout = 5;    // timeout per percobaan (detik)
        $maxRetry = 3;   // jumlah maksimal percobaan

        for ($attempt = 1; $attempt <= $maxRetry; $attempt++) {
            try {
                $response = $client->request('GET', $url, [
                    'timeout'     => $timeout,
                    'http_errors' => false,
                ]);

                if ($response->getStatusCode() === 200) {
                    $body   = json_decode($response->getBody(), true);
                    $result = $body['isp'] ?? $body['org'] ?? null;
                    if (!empty($result)) {
                        return $result;
                    }
                    // Respons 200 tapi ISP kosong — tidak perlu retry
                    log_message('warning', sprintf(
                        'IP-API Error for %s [ipwhois.app]: ISP kosong di respons (percobaan %d/%d)',
                        $domain, $attempt, $maxRetry
                    ));
                    break;
                }

                log_message('warning', sprintf(
                    'IP-API Error for %s [ipwhois.app]: HTTP %d (percobaan %d/%d)',
                    $domain, $response->getStatusCode(), $attempt, $maxRetry
                ));

            } catch (\Throwable $e) {
                log_message('warning', sprintf(
                    'IP-API Error for %s [ipwhois.app]: %s (percobaan %d/%d)',
                    $domain, $e->getMessage(), $attempt, $maxRetry
                ));
            }

            // Exponential backoff sebelum retry berikutnya
            if ($attempt < $maxRetry) {
                sleep((int) pow(2, $attempt)); // 2s → 4s
            }
        }

        log_message('warning', 'IP lookup gagal untuk ' . $domain . ' (IP: ' . $ip . ') setelah ' . $maxRetry . ' percobaan');
        return null;
    }
}
