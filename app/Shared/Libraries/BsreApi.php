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
     * Kirim HTTP request dengan penanganan otomatis Rate Limit (429/503/Timeout) & Exponential Backoff
     */
    protected function requestWithRetry(string $url, array $options = [], string $method = 'POST', int $maxRetries = 3): array
    {
        $attempts = 0;
        $delayMs = 1500;

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
                    log_message('warning', "BSrE API Rate Limited ({$statusCode}). Retrying in " . round($waitMs / 1000, 2) . "s (Attempt {$attempts}/{$maxRetries})...");
                    usleep($waitMs * 1000);
                    $delayMs *= 2;
                    continue;
                }

                $body = json_decode($response->getBody(), true);
                return [
                    'success'    => ($statusCode >= 200 && $statusCode < 300),
                    'statusCode' => $statusCode,
                    'body'       => $body,
                ];
            } catch (\Throwable $e) {
                if ($attempts <= $maxRetries) {
                    $jitter = rand(100, 500);
                    $waitMs = $delayMs + $jitter;
                    log_message('warning', "BSrE API Connection Exception on {$url}: " . $e->getMessage() . ". Retrying in " . round($waitMs / 1000, 2) . "s (Attempt {$attempts}/{$maxRetries})...");
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
            'error'      => 'Batas percobaan terlampaui karena pembatasan request (BSrE Rate Limit).',
        ];
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

        $fullUrl = rtrim($this->baseUrl, '/') . '/api/v2/user/check/status';

        $res = $this->requestWithRetry($fullUrl, [
            'auth' => [$this->username, $this->password],
            'json' => $payload,
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'timeout' => 15,
        ], 'POST');

        if ($res['success']) {
            $body = $res['body'] ?? [];
            log_message('info', 'BSrE API Response (Check Status): ' . print_r($body, true));

            return [
                'success' => true,
                'data'    => $body,
                'code'    => $res['statusCode'] ?? 200
            ];
        } else {
            $body = $res['body'] ?? [];
            $msg = $body['message'] ?? $body['error'] ?? $res['error'] ?? 'Gagal mengambil status dari BSrE';
            log_message('error', "BSrE API Error (Check Status). URL: [{$fullUrl}]. Message: {$msg}");

            return [
                'success' => false,
                'message' => $msg,
                'code'    => $res['statusCode'] ?? 500
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
