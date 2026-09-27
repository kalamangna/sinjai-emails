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

        if (empty($this->baseUrl) && !$this->isLocalMode()) {
            throw new \RuntimeException('BSRE_BASE_URL is not set in .env');
        }

        $this->client = Services::curlrequest([
            'timeout'  => 30,
            'verify'   => false, // Set true di production jika SSL valid
        ], null, null, false);
    }

    /**
     * Cek apakah sistem sedang berjalan di mode lokal/development
     * di mana API BSrE (intranet) hanya dapat diakses langsung di server production.
     */
    public function isLocalMode(): bool
    {
        $mockEnv = env('BSRE_MOCK_LOCAL');
        if ($mockEnv !== null && $mockEnv !== '') {
            return filter_var($mockEnv, FILTER_VALIDATE_BOOLEAN);
        }

        $ciEnv = env('CI_ENVIRONMENT', defined('ENVIRONMENT') ? ENVIRONMENT : 'production');
        return in_array(strtolower($ciEnv), ['development', 'testing', 'local']);
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
        // Mode Local / Development (Simulasi saat API BSrE hanya bisa diakses di Production)
        if ($this->isLocalMode()) {
            return $this->mockCheckStatus($identifier, $type);
        }

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
        // Mode Local / Development
        if ($this->isLocalMode()) {
            return $this->mockVerifyPdf($fileBase64, $password);
        }

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

    /**
     * Sign PDF (BSrE E-Sign Client Service)
     * Endpoint: /api/sign/pdf
     * 
     * @param string $pdfFilePath Absolute path to PDF file to sign
     * @param string $nik NIK of signer (16 digits)
     * @param string $passphrase Passphrase of signer
     * @param array $options Additional options (tag_koordinat, linkQR, width, height, etc.)
     * @return array
     */
    public function signPdf(string $pdfFilePath, string $nik, string $passphrase, array $options = []): array
    {
        if (!file_exists($pdfFilePath)) {
            return [
                'success' => false,
                'message' => 'Berkas PDF tidak ditemukan.',
                'code'    => 404
            ];
        }

        if (trim($passphrase) === '') {
            return [
                'success' => false,
                'message' => 'Passphrase TTE wajib diisi.',
                'code'    => 400
            ];
        }

        // Mode Local / Development (Simulasi TTE saat API BSrE hanya bisa diakses di Production)
        if ($this->isLocalMode()) {
            return $this->mockSignPdf($pdfFilePath, $nik, $passphrase, $options);
        }

        $tagKoordinat = $options['tag_koordinat'] ?? '${ttd_pengirim1}';
        $linkQr       = $options['linkQR'] ?? base_url('verifikasi-pdf');
        $width        = $options['width'] ?? 100;
        $height       = $options['height'] ?? 100;

        $multipart = [
            'file'          => new \CURLFile($pdfFilePath, 'application/pdf', basename($pdfFilePath)),
            'nik'           => $nik,
            'passphrase'    => $passphrase,
            'tampilan'      => 'visible',
            'image'         => 'false',
            'linkQR'        => $linkQr,
            'tag_koordinat' => $tagKoordinat,
            'width'         => (string) $width,
            'height'        => (string) $height,
        ];

        try {
            $fullUrl = rtrim($this->baseUrl, '/') . '/api/sign/pdf';

            $response = $this->client->request('POST', $fullUrl, [
                'auth'        => [$this->username, $this->password],
                'multipart'   => $multipart,
                'http_errors' => false,
                'timeout'     => 60,
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            $contentType = $response->getHeaderLine('Content-Type');

            // Cek apakah output adalah PDF bertandatangan (200 OK & MIME PDF / magic bytes %PDF)
            if ($statusCode === 200 && (str_contains($contentType, 'application/pdf') || str_starts_with($body, '%PDF-'))) {
                $idDokumen = $response->getHeaderLine('id_dokumen') ?: $response->getHeaderLine('id-dokumen');
                return [
                    'success'     => true,
                    'pdf_content' => $body,
                    'id_dokumen'  => $idDokumen ?: null,
                    'code'        => 200,
                ];
            }

            // Jika bukan PDF, parse error message dari JSON
            $json = json_decode($body, true);
            $msg = $json['message'] ?? $json['error'] ?? 'Gagal melakukan tanda tangan elektronik.';

            log_message('error', "BSrE API Sign Error ({$statusCode}): {$msg}");

            return [
                'success' => false,
                'message' => $msg,
                'code'    => $statusCode,
            ];
        } catch (\Throwable $e) {
            $errorMsg = 'Gagal menghubungi server BSrE: ' . $e->getMessage();
            log_message('error', $errorMsg);

            return [
                'success' => false,
                'message' => $errorMsg,
                'code'    => 500,
            ];
        }
    }

    /**
     * Simulasi Penandatanganan Dokumen Elektronik di Lingkungan Lokal/Dev
     */
    protected function mockSignPdf(string $pdfFilePath, string $nik, string $passphrase, array $options = []): array
    {
        // Jika passphrase bernilai 'salah' atau 'error', simulasikan penolakan otentikasi BSrE (keperluan testing kegagalan)
        if (in_array(strtolower(trim($passphrase)), ['salah', 'error', 'wrong'])) {
            return [
                'success' => false,
                'message' => 'Passphrase TTE yang Anda masukkan salah (Simulasi Lokal).',
                'code'    => 401,
            ];
        }

        $tagKoordinat = $options['tag_koordinat'] ?? '${ttd_pengirim1}';
        $linkQr       = $options['linkQR'] ?? base_url('verifikasi-pdf');
        $username     = $options['user'] ?? null;

        // Jika username tidak dilewatkan langsung, deteksi dari linkQR atau database
        if (empty($username)) {
            if (!empty($options['linkQR']) && preg_match('#/verifikasi/([^/?#]+)#', $options['linkQR'], $m)) {
                $username = $m[1];
            } else {
                try {
                    $db = \Config\Database::connect();
                    $u = $db->table('emails')->select('user')->where('nik', $nik)->get()->getRowArray();
                    if ($u) {
                        $username = $u['user'];
                    }
                } catch (\Throwable $e) {}
            }
        }

        $pdfContent = null;

        // Jika username ditemukan, regenerasi PDF menggunakan EmailExportService dengan visual QR Code
        if (!empty($username)) {
            try {
                $qrDataUri = $this->generateQrDataUri($linkQr);
                $exportService = new \App\Domains\Email\Services\EmailExportService();
                $signOptions = [];

                if ($tagKoordinat === '${ttd_pengirim1}') {
                    // Tahap 1: TTE PPPK (QR PPPK tampil, sisi Bupati tetap teks anchor ${ttd_pengirim2})
                    $signOptions['qr_pppk'] = $qrDataUri;
                    $signOptions['qr_bupati'] = null;
                } else {
                    // Tahap 2: TTE Bupati (kedua pihak tampil visual QR Code)
                    $signOptions['qr_pppk'] = $qrDataUri;
                    $signOptions['qr_bupati'] = $qrDataUri;
                }
                $signOptions['verify_url'] = $linkQr;

                $pdfResult = $exportService->generatePerjanjianKerjaPdf($username, $options['history_id'] ?? null, $signOptions);
                $pdfContent = $pdfResult['dompdf']->output();
            } catch (\Throwable $e) {
                log_message('error', '[BsreApi Local Mock] Gagal render PDF dengan QR Code simulasi: ' . $e->getMessage());
            }
        }

        if ($pdfContent === null) {
            $pdfContent = file_get_contents($pdfFilePath);
        }

        $idDokumen  = 'LOCAL-TTE-' . date('YmdHis') . '-' . substr(md5($nik . microtime(true)), 0, 8);

        log_message('info', "[BsreApi Local Mock] Dokumen {$pdfFilePath} berhasil ditandatangani untuk NIK {$nik} (ID: {$idDokumen}).");

        return [
            'success'     => true,
            'pdf_content' => $pdfContent,
            'id_dokumen'  => $idDokumen,
            'code'        => 200,
            'is_mocked'   => true,
        ];
    }

    /**
     * Generate QR Code Data URI (Base64 PNG) untuk simulasi TTE visual dengan logo Sinjai di tengah
     */
    public function generateQrDataUri(string $url): string
    {
        $qrBaseUrl = env('QR_BASE_URL') ?: 'https://api.qrserver.com';
        // Menggunakan ecc=H (30% error correction) agar logo di tengah tidak mengganggu pembacaan QR
        $apiUrl = rtrim($qrBaseUrl, '/') . '/v1/create-qr-code/?size=300x300&ecc=H&data=' . urlencode($url);

        $imageData = null;
        try {
            $context = stream_context_create([
                'http' => ['timeout' => 3],
                'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false]
            ]);
            $raw = @file_get_contents($apiUrl, false, $context);
            if ($raw !== false && strlen($raw) > 50) {
                $imageData = $raw;
            }
        } catch (\Throwable $e) {
            log_message('warning', '[BsreApi] Gagal fetch QR Code dari API: ' . $e->getMessage());
        }

        // Sematkan logo Sinjai di tengah QR Code
        $brandedPng = $this->embedLogoInQr($imageData, $url);
        if (!empty($brandedPng)) {
            return 'data:image/png;base64,' . base64_encode($brandedPng);
        }

        if ($imageData) {
            return 'data:image/png;base64,' . base64_encode($imageData);
        }

        // Fallback pembuatan visual stempel digital lokal jika jaringan offline
        return $this->generateFallbackQrDataUri($url);
    }

    /**
     * Sematkan Logo Pemkab Sinjai di tengah QR Code
     */
    protected function embedLogoInQr(?string $qrImageData, string $url): ?string
    {
        if (!extension_loaded('gd') || empty($qrImageData)) {
            return null;
        }

        $qr = @imagecreatefromstring($qrImageData);
        if (!$qr) {
            return null;
        }

        $candidates = [
            defined('FCPATH') ? rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'logo.png' : null,
            defined('ROOTPATH') ? rtrim(ROOTPATH, '/\\') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'logo.png' : null,
            realpath(__DIR__ . '/../../../../public/logo.png'),
            realpath(__DIR__ . '/../../../public/logo.png'),
        ];
        $logoPath = null;
        foreach ($candidates as $c) {
            if (!empty($c) && file_exists($c)) {
                $logoPath = $c;
                break;
            }
        }

        if (!$logoPath || !file_exists($logoPath)) {
            ob_start();
            imagepng($qr);
            $res = ob_get_clean();
            imagedestroy($qr);
            return $res;
        }

        $logo = @imagecreatefrompng($logoPath);
        if (!$logo) {
            ob_start();
            imagepng($qr);
            $res = ob_get_clean();
            imagedestroy($qr);
            return $res;
        }

        $qrW = imagesx($qr);
        $qrH = imagesy($qr);

        // Ubah canvas QR dari palette (1-bit/8-bit) ke 24-bit TrueColor agar warna & transparansi logo tidak rusak
        $dest = imagecreatetruecolor($qrW, $qrH);
        imagecopy($dest, $qr, 0, 0, 0, 0, $qrW, $qrH);

        imagealphablending($logo, true);
        imagesavealpha($logo, true);

        $logoW = imagesx($logo);
        $logoH = imagesy($logo);

        // Logo 22% dari ukuran QR Code (proporsional dan aman dari batasan 30% ECL)
        $targetLogoW = (int) round($qrW * 0.22);
        $targetLogoH = (int) round($qrH * 0.22);
        $centerX = (int) round(($qrW - $targetLogoW) / 2);
        $centerY = (int) round(($qrH - $targetLogoH) / 2);

        // Beri bantalan putih (padding) 4px di belakang logo
        $pad = 4;
        $white = imagecolorallocate($dest, 255, 255, 255);
        imagefilledrectangle(
            $dest,
            $centerX - $pad,
            $centerY - $pad,
            $centerX + $targetLogoW + $pad,
            $centerY + $targetLogoH + $pad,
            $white
        );

        // Salin logo Sinjai ke tengah QR
        imagecopyresampled($dest, $logo, $centerX, $centerY, 0, 0, $targetLogoW, $targetLogoH, $logoW, $logoH);

        ob_start();
        imagepng($dest);
        $brandedData = ob_get_clean();

        imagedestroy($qr);
        imagedestroy($dest);
        imagedestroy($logo);

        return $brandedData;
    }

    /**
     * Fallback QR/TTE visual badge menggunakan ekstensi GD jika koneksi internet terputus
     */
    protected function generateFallbackQrDataUri(string $url): string
    {
        if (!extension_loaded('gd')) {
            return '';
        }

        $width = 150;
        $height = 150;
        $img = imagecreatetruecolor($width, $height);

        $bg = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 30, 41, 59);
        $blue = imagecolorallocate($img, 37, 99, 235);

        imagefilledrectangle($img, 0, 0, $width, $height, $bg);
        imagerectangle($img, 0, 0, $width - 1, $height - 1, $black);

        // Gambar 3 kotak sudut khas QR code
        $drawCorner = function($x, $y) use ($img, $black, $bg) {
            imagefilledrectangle($img, $x, $y, $x + 30, $y + 30, $black);
            imagefilledrectangle($img, $x + 5, $y + 5, $x + 25, $y + 25, $bg);
            imagefilledrectangle($img, $x + 10, $y + 10, $x + 20, $y + 20, $black);
        };

        $drawCorner(8, 8);
        $drawCorner(112, 8);
        $drawCorner(8, 112);

        imagestring($img, 2, 45, 60, "TTE SIMULASI", $blue);
        imagestring($img, 1, 48, 75, "BSrE - BSSN", $black);
        imagestring($img, 1, 40, 90, "LOKAL/DEV", $black);

        ob_start();
        imagepng($img);
        $pngData = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($pngData);
    }

    /**
     * Simulasi Pengecekan Status User BSrE di Lingkungan Lokal/Dev
     */
    protected function mockCheckStatus(string $identifier, string $type = 'email'): array
    {
        log_message('info', "[BsreApi Local Mock] Check status for {$type}: {$identifier}");

        $status = 'ISSUE';
        try {
            $db = \Config\Database::connect();
            $column = ($type === 'nik') ? 'nik' : 'email';
            $user = $db->table('emails')->select('bsre_status')->where($column, $identifier)->get()->getRowArray();
            if ($user && !empty($user['bsre_status'])) {
                $status = strtoupper($user['bsre_status']);
            }
        } catch (\Throwable $e) {
            // Gunakan status default jika DB gagal diakses
        }

        return [
            'success'   => true,
            'data'      => [
                'status'  => $status,
                'message' => 'User terdaftar dan sertifikat aktif (Simulasi Lokal)',
            ],
            'code'      => 200,
            'is_mocked' => true,
        ];
    }

    /**
     * Simulasi Verifikasi Dokumen PDF di Lingkungan Lokal/Dev
     */
    protected function mockVerifyPdf(string $fileBase64, ?string $password = null): array
    {
        $decoded = base64_decode($fileBase64);
        if (!$decoded || !str_starts_with($decoded, '%PDF-')) {
            return [
                'success' => false,
                'message' => 'Format berkas PDF tidak valid.',
                'code'    => 400,
            ];
        }

        log_message('info', '[BsreApi Local Mock] Verifikasi berkas PDF di localhost.');

        return [
            'success'   => true,
            'data'      => [
                'conclusion'            => 'VALID',
                'signatureCount'        => 1,
                'signatureInformations' => [
                    [
                        'signerName'           => 'SIMULASI LOCALHOST (BSrE Dev)',
                        'signerCertExpiryDate' => date('Y-m-d H:i:s', strtotime('+1 year')),
                        'signatureDate'        => date('Y-m-d H:i:s'),
                        'signatureFormat'      => 'PKCS7 / Local Mock',
                        'location'             => 'Sinjai (Dev)',
                        'reason'               => 'Simulasi TTE Lingkungan Pengujian',
                        'certValid'            => true,
                        'docTampered'          => false,
                    ]
                ]
            ],
            'code'      => 200,
            'is_mocked' => true,
        ];
    }
}
