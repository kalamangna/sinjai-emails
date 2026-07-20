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
            $fullUrl = rtrim($this->baseUrl, '/') . '/api/v2/user/check/status';

            $response = $this->client->request('POST', $fullUrl, [
                'auth' => [$this->username, $this->password],
                'json' => $payload,
                'http_errors' => false,
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            $statusCode = $response->getStatusCode();

            log_message('info', 'BSrE API Response (Check Status): ' . print_r($body, true));

            if ($statusCode >= 200 && $statusCode < 300) {
                return [
                    'success' => true,
                    'data'    => $body,
                    'code'    => $statusCode
                ];
            } else {
                $msg = $body['message'] ?? $body['error'] ?? 'Gagal mengambil status dari BSrE';
                return [
                    'success' => false,
                    'message' => $msg,
                    'code'    => $statusCode
                ];
            }
        } catch (\Throwable $e) {
            $errorMsg = "BSrE API Error (Check Status). URL: [{$fullUrl}]. Message: " . $e->getMessage();
            log_message('error', $errorMsg);

            return [
                'success' => false,
                'message' => $errorMsg,
                'code'    => 500
            ];
        }
    }

    /**
     * Register User Baru (API V2)
     * Endpoint: /api/v2/user/registration
     * 
     * @param string $nama Nama Lengkap
     * @param string $email Email Dinas/Resmi
     * @return array
     */
    public function registerUser(string $nama, string $email, string $nik): array
    {
        $payload = [
            'nik'   => $nik,
            'nama'  => $nama,
            'email' => $email
        ];

        try {
            $fullUrl = rtrim($this->baseUrl, '/') . '/api/v2/user/registration';

            $response = $this->client->request('POST', $fullUrl, [
                'auth' => [$this->username, $this->password],
                'json' => $payload,
                'http_errors' => false,
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            $statusCode = $response->getStatusCode();

            log_message('info', 'BSrE API Response (Register User): ' . print_r($body, true));

            if ($statusCode >= 200 && $statusCode < 300) {
                return [
                    'success' => true,
                    'data'    => $body,
                    'code'    => $statusCode
                ];
            } else {
                $msg = $body['message'] ?? $body['error'] ?? $body['error_description'] ?? 'Gagal mendaftarkan user ke BSrE';
                if ($msg === 'Gagal mendaftarkan user ke BSrE') {
                    $msg .= '. Raw Response: ' . $response->getBody();
                }
                return [
                    'success' => false,
                    'message' => $msg,
                    'code'    => $statusCode
                ];
            }
        } catch (\Throwable $e) {
            $errorMsg = "BSrE API Error (Register User). URL: [{$fullUrl}]. Message: " . $e->getMessage();
            log_message('error', $errorMsg);

            return [
                'success' => false,
                'message' => $errorMsg,
                'code'    => 500
            ];
        }
    }

    /**
     * Verify PDF (API V2)
     * Endpoint: /api/v2/verify/pdf
     * 
     * @param string $fileBase64 File PDF dikodekan dalam Base64
     * @param string|null $password Sandi enkripsi PDF jika ada
     * @return array
     */
    public function verifyPdf(string $fileBase64, ?string $password = null): array
    {
        $payload = [
            'file' => $fileBase64
        ];

        if (!empty($password)) {
            $payload['password'] = $password;
        }

        try {
            $fullUrl = rtrim($this->baseUrl, '/') . '/api/v2/verify/pdf';

            $response = $this->client->request('POST', $fullUrl, [
                'auth' => [$this->username, $this->password],
                'json' => $payload,
                'http_errors' => false,
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            $statusCode = $response->getStatusCode();

            log_message('info', 'BSrE API Response (Verify PDF): ' . print_r($body, true));

            if ($statusCode >= 200 && $statusCode < 300) {
                return [
                    'success' => true,
                    'data'    => $body,
                    'code'    => $statusCode
                ];
            } else {
                $msg = $body['message'] ?? $body['error'] ?? 'Gagal melakukan verifikasi PDF';
                return [
                    'success' => false,
                    'message' => $msg,
                    'code'    => $statusCode
                ];
            }
        } catch (\Throwable $e) {
            $errorMsg = "BSrE API Error (Verify PDF). URL: [{$fullUrl}]. Message: " . $e->getMessage();
            log_message('error', $errorMsg);

            return [
                'success' => false,
                'message' => $errorMsg,
                'code'    => 500
            ];
        }
    }
}
