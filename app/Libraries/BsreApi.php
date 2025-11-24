<?php

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

class BsreApi
{
    protected $client;
    protected $baseUrl;
    protected $username;
    protected $password;

    public function __construct()
    {
        // Pastikan konfigurasi ini ada di .env Anda
        $this->baseUrl  = getenv('BSRE_BASE_URL');
        $this->username = getenv('BSRE_USERNAME');
        $this->password = getenv('BSRE_PASSWORD');

        $this->client = Services::curlrequest([
            'base_uri' => $this->baseUrl,
            'timeout'  => 30,
            'verify'   => false, // Set true di production jika SSL valid
        ]);
    }

    /**
     * Cek Status User (API V2)
     * Referensi Dokumen: Halaman 34, Poin 6.7 
     * Endpoint: /api/v2/user/check/status [cite: 548]
     * * @param string $identifier NIK atau Email pengguna
     * @param string $type 'nik' atau 'email'
     */
    public function checkStatus($identifier, $type = 'email')
    {
        // Tentukan payload JSON berdasarkan tipe input (NIK atau Email)
        // [cite: 550, 554]
        $payload = [];
        if ($type === 'nik') {
            $payload['nik'] = $identifier;
        } else {
            $payload['email'] = $identifier;
        }

        try {
            $response = $this->client->request('POST', '/api/v2/user/check/status', [
                'auth' => [$this->username, $this->password], // Basic Auth [cite: 309]
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            // Decode response JSON
            $body = json_decode($response->getBody(), true);

            // Kembalikan data mentah untuk diproses controller
            return [
                'success' => true,
                'data'    => $body,
                'code'    => $response->getStatusCode()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
