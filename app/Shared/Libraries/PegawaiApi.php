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

    public function getPegawaiData($nip)
    {
        if (empty($nip)) {
            return [
                'success' => false,
                'message' => 'NIP is required'
            ];
        }

        try {
            $dataUrl = $this->baseUrl . 'data_pegawai/';
            $response = $this->client->get($dataUrl, [
                'query' => [
                    'nip' => $nip
                ],
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'timeout' => 10,
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();

            if ($statusCode === 200) {
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
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'Pegawai API returned status code: ' . $statusCode
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error connecting to Pegawai API: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mencari jabatan definitif pegawai dari daftar master unit SIMPEG
     */
    public function findDefinitifPosition($nip, $unitId = null)
    {
        try {
            // 1. Coba cari di unit yang sama via get_pegawai
            if (!empty($unitId)) {
                $response = $this->client->get($this->baseUrl . 'get_pegawai', [
                    'query' => ['unit_id' => $unitId],
                    'headers' => ['Accept' => 'application/json'],
                    'timeout' => 10,
                ]);
                if ($response->getStatusCode() === 200) {
                    $list = json_decode($response->getBody(), true);
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
            $responseSetda = $this->client->get($this->baseUrl . 'get_pegawai', [
                'query' => ['unit_id' => '730701'],
                'headers' => ['Accept' => 'application/json'],
                'timeout' => 10,
            ]);
            if ($responseSetda->getStatusCode() === 200) {
                $listSetda = json_decode($responseSetda->getBody(), true);
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
