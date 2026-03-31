<?php

namespace App\Shared\Libraries;

use Config\Services;

class PegawaiApi
{
    protected $baseUrl = 'http://apps.sinjaikab.go.id/api/pegawai/data_pegawai/';
    protected $client;

    public function __construct()
    {
        $this->client = Services::curlrequest();
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
            $response = $this->client->get($this->baseUrl, [
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
