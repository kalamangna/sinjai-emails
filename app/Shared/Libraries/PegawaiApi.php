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
}
