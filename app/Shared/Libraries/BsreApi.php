<?php

namespace App\Shared\Libraries;

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
        $this->baseUrl  = env('BSRE_BASE_URL');
        $this->username = env('BSRE_USERNAME');
        $this->password = env('BSRE_PASSWORD');

        if (empty($this->baseUrl)) {
            throw new \RuntimeException('BSRE_BASE_URL is not set in .env');
        }

        $this->client = Services::curlrequest([
            'timeout'  => 30,
            'verify'   => false, // Set true di production jika SSL valid
        ], null, null, false);
    }

    /**
     * Check Status User (API V2)
     * Endpoint: /api/v2/user/check/status
     * 
     * @param string $identifier NIK or Email
     * @param string $type 'nik' or 'email'
     * @return array
     */
    public function checkStatus(string $identifier, string $type = 'email'): array
    {
        $payload = [];
        if ($type === 'nik') {
            $payload['nik'] = $identifier;
        } else {
            $payload['email'] = $identifier;
        }

        try {
            // Manual URL construction
            $fullUrl = rtrim($this->baseUrl, '/') . '/api/v2/user/check/status';

            $response = $this->client->request('POST', $fullUrl, [
                'auth' => [$this->username, $this->password],
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            $statusCode = $response->getStatusCode();

            // log hasil response untuk debugging
            log_message('info', 'BSrE API Response: ' . print_r($body, true));

            return [
                'success' => true,
                'data'    => $body,
                'code'    => $statusCode
            ];
        } catch (\Throwable $e) {
            $errorMsg = "BSrE API Error. URL: [{$fullUrl}]. Message: " . $e->getMessage();
            log_message('error', $errorMsg);

            return [
                'success' => false,
                'message' => $errorMsg,
                'code'    => 500
            ];
        }
    }
}
