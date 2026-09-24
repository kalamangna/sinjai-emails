<?php

namespace App\Shared\Libraries;

use Config\Services;

class PegawaiApi
{
    protected $baseUrl;
    protected $authUrl;
    protected $client;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('PEGAWAI_BASE_URL') ?: 'https://apps.sinjaikab.go.id/api/pegawai', '/') . '/';
        $this->client = Services::curlrequest([
            'timeout' => 15,
            'verify'  => false
        ]);
    }

    /**
     * Kirim HTTP request dengan penanganan otomatis Rate Limit (429/503/Timeout) & Exponential Backoff
     */
    protected function requestWithRetry(string $url, array $options = [], string $method = 'GET', int $maxRetries = 3): array
    {
        // 1. Cek Circuit Breaker / Global Cooldown di cache
        $cooldownUntil = cache('simpeg_rate_limit_cooldown');
        if (!empty($cooldownUntil) && is_numeric($cooldownUntil)) {
            $now = time();
            if ($cooldownUntil > $now) {
                $waitSec = $cooldownUntil - $now;
                if (is_cli()) {
                    log_message('warning', "SIMPEG API Cooldown Active. Menunggu {$waitSec}s sebelum request {$url}...");
                    sleep($waitSec + 1);
                } else {
                    return [
                        'success'    => false,
                        'statusCode' => 429,
                        'error'      => "Server SIMPEG sedang masa pendinginan rate limit ({$waitSec}s tersisa). Silakan coba sesaat lagi.",
                    ];
                }
            }
        }

        $attempts = 0;
        $delayMs = 3000; // 3 detik initial delay

        while ($attempts <= $maxRetries) {
            $attempts++;
            try {
                $options['http_errors'] = false;
                $response = $this->client->request($method, $url, $options);
                $statusCode = $response->getStatusCode();
                $body = $response->getBody();

                // Deteksi Rate Limit dari status code ATAU teks body response (misal {"error":"Too Many Requests","limit":60})
                $isRateLimited = in_array($statusCode, [429, 503, 504]) 
                    || stripos($body, 'Too Many Requests') !== false 
                    || stripos($body, 'Rate Limit') !== false;

                if ($isRateLimited) {
                    // Set cooldown 15 detik di cache terpusat
                    cache()->save('simpeg_rate_limit_cooldown', time() + 15, 30);

                    if ($attempts <= $maxRetries) {
                        $jitter = rand(500, 1500);
                        $waitMs = $delayMs + $jitter;
                        log_message('warning', "SIMPEG API Rate Limited on {$url}. Retrying in " . round($waitMs / 1000, 2) . "s (Attempt {$attempts}/{$maxRetries})...");
                        usleep($waitMs * 1000);
                        $delayMs = min($delayMs * 2, 12000); // Exponential backoff (3s -> 6s -> 12s)
                        continue;
                    }

                    return [
                        'success'    => false,
                        'statusCode' => 429,
                        'error'      => 'Batas permintaan API SIMPEG terlampaui (Rate Limit: 60 req/menit). Harap tunggu beberapa saat.',
                        'body'       => $body,
                    ];
                }

                return [
                    'success'    => ($statusCode >= 200 && $statusCode < 300),
                    'statusCode' => $statusCode,
                    'body'       => $body,
                ];
            } catch (\Throwable $e) {
                if ($attempts <= $maxRetries) {
                    $jitter = rand(500, 1000);
                    $waitMs = $delayMs + $jitter;
                    log_message('warning', "SIMPEG API Connection Exception on {$url}: " . $e->getMessage() . ". Retrying in " . round($waitMs / 1000, 2) . "s (Attempt {$attempts}/{$maxRetries})...");
                    usleep($waitMs * 1000);
                    $delayMs *= 2;
                    continue;
                }

                return [
                    'success'    => false,
                    'statusCode' => 500,
                    'error'      => $e->getMessage(),
                ];
            }
        }

        return [
            'success'    => false,
            'statusCode' => 429,
            'error'      => 'Batas percobaan terlampaui karena pembatasan request (Rate Limit).',
        ];
    }

    public function getPegawaiData($nip)
    {
        if (empty($nip)) {
            return [
                'success' => false,
                'message' => 'NIP is required'
            ];
        }

        $dataUrl = $this->baseUrl . 'data_pegawai/';
        $res = $this->requestWithRetry($dataUrl, [
            'query' => [
                'nip' => $nip
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
            'timeout' => 12,
        ], 'GET');

        if (!$res['success']) {
            return [
                'success' => false,
                'message' => $res['error'] ?? ('Pegawai API returned status code: ' . ($res['statusCode'] ?? 500)),
                'code'    => $res['statusCode'] ?? 500
            ];
        }

        $body = $res['body'] ?? '';
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'Invalid JSON response from Pegawai API'
            ];
        }

        // Cek jika response JSON adalah error rate limit
        if (is_array($data) && isset($data['error']) && (stripos($data['error'], 'Too Many Requests') !== false || stripos($data['error'], 'Rate Limit') !== false)) {
            cache()->save('simpeg_rate_limit_cooldown', time() + 15, 30);
            return [
                'success' => false,
                'code'    => 429,
                'message' => 'Batas permintaan API SIMPEG terlampaui (Rate Limit: 60 req/menit). Harap tunggu beberapa saat.'
            ];
        }

        // Resolusi otomatis: Tangkap data Plt/Plh jika ada, dan cari Jabatan Definitif
        $jNama = $data['jabatan_nama'] ?? $data['jabatan'] ?? '';
        $statusId = (int)($data['jabatan_status_id'] ?? 1);
        $isPlt = ($statusId === 2) || (stripos($jNama, 'Plt') === 0) || (stripos($jNama, 'Plh') === 0);

        if ($isPlt) {
            $pltJabatan = $jNama;
            $pltUnitId  = $data['unit_id'] ?? null;

            $definitifFound = $this->findDefinitifPosition($nip, $data['unit_id'] ?? null);
            if ($definitifFound) {
                $data = array_merge($data, $definitifFound);
            }

            // Simpan info Plt terpisah agar jabatan definitif tetap bersih
            $data['jabatan_plt'] = $pltJabatan;
            $data['unit_id_plt'] = $pltUnitId;
        } else {
            // Jika profil utama adalah jabatan definitif, cek apakah pegawai sedang ditugaskan sebagai Plt di OPD lain
            $pltAssignment = $this->findPltAssignment($nip);
            if ($pltAssignment) {
                $data['jabatan_plt'] = $pltAssignment['jabatan_nama'] ?? $pltAssignment['jabatan'] ?? null;
                $data['unit_id_plt'] = $pltAssignment['unit_id'] ?? null;
            } else {
                $data['jabatan_plt'] = null;
                $data['unit_id_plt'] = null;
            }
        }

        return [
            'success' => true,
            'data'    => $data
        ];
    }

    /**
     * Mengambil seluruh penugasan Plt aktif di SIMPEG lintas OPD (dicache 6 jam)
     */
    public function getAllPltAssignments(): array
    {
        $cacheKey = 'simpeg_all_plt_assignments';
        $cached = cache($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $db = \Config\Database::connect();
        $units = $db->table('unit_kerja')
            ->select('api_unit_id, nama_unit_kerja')
            ->where('api_unit_id IS NOT NULL')
            ->where('api_unit_id !=', '')
            ->get()
            ->getResultArray();

        $pltMap = [];
        foreach ($units as $u) {
            $res = $this->requestWithRetry($this->baseUrl . 'get_pegawai', [
                'query'   => ['unit_id' => $u['api_unit_id']],
                'headers' => ['Accept' => 'application/json'],
                'timeout' => 5,
            ], 'GET');

            if ($res['success']) {
                $list = json_decode($res['body'] ?? '', true);
                if (is_array($list)) {
                    foreach ($list as $p) {
                        $sId = (int)($p['jabatan_status_id'] ?? 1);
                        $jNama = trim($p['jabatan_nama'] ?? $p['jabatan'] ?? '');
                        if (($sId === 2 || stripos($jNama, 'Plt') === 0 || stripos($jNama, 'Plh') === 0) && !empty($p['nip'])) {
                            $pltMap[$p['nip']] = [
                                'nip'          => $p['nip'],
                                'nama'         => $p['nama'] ?? '',
                                'jabatan_nama' => $jNama,
                                'unit_id'      => $u['api_unit_id'],
                                'unit_nama'    => $u['nama_unit_kerja']
                            ];
                        }
                    }
                }
            } elseif (($res['statusCode'] ?? 0) === 429) {
                log_message('warning', 'getAllPltAssignments dihentikan lebih awal karena rate limit.');
                break;
            }

            // Pacing mikro 150ms antar unit agar aman dari lonjakan burst
            usleep(150000);
        }

        cache()->save($cacheKey, $pltMap, 21600);
        return $pltMap;
    }

    /**
     * Mengambil daftar pegawai dalam suatu unit kerja dari SIMPEG
     */
    public function getPegawaiByUnit(string $unitId): array
    {
        if (empty($unitId)) {
            return ['success' => false, 'message' => 'Unit ID required'];
        }

        $res = $this->requestWithRetry($this->baseUrl . 'get_pegawai', [
            'query'   => ['unit_id' => $unitId],
            'headers' => ['Accept' => 'application/json'],
            'timeout' => 12,
        ], 'GET');

        if (!$res['success']) {
            return [
                'success' => false,
                'message' => $res['error'] ?? 'Gagal mengambil data pegawai unit',
                'code'    => $res['statusCode'] ?? 500
            ];
        }

        $list = json_decode($res['body'] ?? '', true);
        return [
            'success' => true,
            'data'    => is_array($list) ? $list : []
        ];
    }

    /**
     * Mencari apakah seorang pegawai ditugaskan sebagai Plt di OPD lain
     */
    public function findPltAssignment($nip): ?array
    {
        if (empty($nip)) return null;
        $allPlt = $this->getAllPltAssignments();
        return $allPlt[$nip] ?? null;
    }

    /**
     * Mencari jabatan definitif pegawai dari daftar master unit SIMPEG
     */
    public function findDefinitifPosition($nip, $unitId = null)
    {
        try {
            // 1. Coba cari di unit yang sama via get_pegawai
            if (!empty($unitId)) {
                $res = $this->requestWithRetry($this->baseUrl . 'get_pegawai', [
                    'query'   => ['unit_id' => $unitId],
                    'headers' => ['Accept' => 'application/json'],
                    'timeout' => 12,
                ], 'GET');

                if ($res['success']) {
                    $list = json_decode($res['body'] ?? '', true);
                    if (is_array($list)) {
                        foreach ($list as $p) {
                            if (($p['nip'] ?? '') === $nip) {
                                $pStatusId = (int)($p['jabatan_status_id'] ?? 1);
                                $pJNama = trim($p['jabatan_nama'] ?? $p['jabatan'] ?? '');
                                if ($pStatusId === 1 && stripos($pJNama, 'Plt') !== 0 && stripos($pJNama, 'Plh') !== 0) {
                                    return $p;
                                }
                            }
                        }
                    }
                }
            }

            // 2. Jika mutasi Plt lintas OPD (contoh: Staf Ahli Setda yang Plt di BPBD/Dinas), cari di Sekretariat Daerah (730701)
            $resSetda = $this->requestWithRetry($this->baseUrl . 'get_pegawai', [
                'query'   => ['unit_id' => '730701'],
                'headers' => ['Accept' => 'application/json'],
                'timeout' => 12,
            ], 'GET');

            if ($resSetda['success']) {
                $listSetda = json_decode($resSetda['body'] ?? '', true);
                if (is_array($listSetda)) {
                    foreach ($listSetda as $p) {
                        if (($p['nip'] ?? '') === $nip) {
                            $pStatusId = (int)($p['jabatan_status_id'] ?? 1);
                            $pJNama = trim($p['jabatan_nama'] ?? $p['jabatan'] ?? '');
                            if ($pStatusId === 1 && stripos($pJNama, 'Plt') !== 0 && stripos($pJNama, 'Plh') !== 0) {
                                return array_merge($p, ['unit_id' => '730701']);
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Gagal mencari jabatan definitif: ' . $e->getMessage());
        }

        return null;
    }
}
