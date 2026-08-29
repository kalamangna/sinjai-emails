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

    public function authenticate($nip, $password)
    {
        if (empty($nip) || empty($password)) {
            return [
                'success' => false,
                'message' => 'NIP dan Password wajib diisi.'
            ];
        }

        try {
            $authUrl = $this->baseUrl . 'user_auth';
            $response = $this->client->post($authUrl, [
                'query' => [
                    'username' => $nip,
                    'password' => $password
                ],
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'timeout' => 10,
            ]);

            $statusCode = $response->getStatusCode();
            $body = trim($response->getBody());

            log_message('debug', "PegawaiAuth Response ($statusCode): " . $body);

            if ($statusCode === 200) {
                // Handle plain text response '1' or 'success'
                if ($body === '1' || strtolower($body) === 'success' || $body === 'true') {
                    return [
                        'success' => true,
                        'data' => ['status' => $body]
                    ];
                }

                $data = json_decode($body, true);
                
                // Flexible success check for JSON responses
                $isSuccess = false;
                if (is_array($data) && isset($data['status'])) {
                    $status = $data['status'];
                    if ($status === 'success' || $status === true || $status === 1 || $status === '1') {
                        $isSuccess = true;
                    }
                }

                if ($isSuccess) {
                    return [
                        'success' => true,
                        'data' => $data
                    ];
                }

                return [
                    'success' => false,
                    'message' => (is_array($data) ? ($data['message'] ?? $data['error'] ?? null) : null) ?: 'Kredensial eksternal tidak valid.'
                ];
            }

            return [
                'success' => false,
                'message' => 'API Otentikasi memberikan respon status: ' . $statusCode
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke API Otentikasi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Kirim HTTP request dengan penanganan otomatis Rate Limit (429/503/Timeout) & Exponential Backoff
     */
    protected function requestWithRetry(string $url, array $options = [], string $method = 'GET', int $maxRetries = 3): array
    {
        $attempts = 0;
        $delayMs = 1500; // 1.5 detik initial delay

        while ($attempts <= $maxRetries) {
            $attempts++;
            try {
                $options['http_errors'] = false;
                $response = $this->client->request($method, $url, $options);
                $statusCode = $response->getStatusCode();

                // Jika rate limit (429) atau server overload (503/504), lakukan backoff & retry
                if (in_array($statusCode, [429, 503, 504]) && $attempts <= $maxRetries) {
                    $jitter = rand(100, 500);
                    $waitMs = $delayMs + $jitter;
                    log_message('warning', "SIMPEG API Rate Limited ({$statusCode}) on {$url}. Retrying in " . round($waitMs / 1000, 2) . "s (Attempt {$attempts}/{$maxRetries})...");
                    usleep($waitMs * 1000);
                    $delayMs *= 2; // Exponential backoff (1.5s -> 3s -> 6s)
                    continue;
                }

                return [
                    'success'    => ($statusCode >= 200 && $statusCode < 300),
                    'statusCode' => $statusCode,
                    'body'       => $response->getBody(),
                ];
            } catch (\Throwable $e) {
                if ($attempts <= $maxRetries) {
                    $jitter = rand(100, 500);
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

        // Resolusi otomatis: Gunakan Jabatan Definitif saja jika API mengembalikan data Plt/Plh
        $jNama = $data['jabatan_nama'] ?? $data['jabatan'] ?? '';
        $statusId = (int)($data['jabatan_status_id'] ?? 1);
        $isPlt = ($statusId === 2) || (stripos($jNama, 'Plt') === 0) || (stripos($jNama, 'Plh') === 0);

        if ($isPlt) {
            $definitifFound = $this->findDefinitifPosition($nip, $data['unit_id'] ?? null);
            if ($definitifFound) {
                $data = array_merge($data, $definitifFound);
            }
        }

        return [
            'success' => true,
            'data'    => $data
        ];
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
