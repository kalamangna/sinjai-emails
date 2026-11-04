<?php

namespace App\Domains\Bridge\Controllers;

use App\Shared\BaseController;
use CURLFile;

class BridgeController extends BaseController
{
    protected string $backendUrl;
    protected int $timeout;
    protected string $logFile;
    protected string $accessLogFile;
    protected int $maxFileSizeBytes;

    public function __construct()
    {
        $this->backendUrl = rtrim(env('BSRE_BASE_URL') ?: 'http://10.91.162.6', '/');
        $this->timeout = 120;
        $this->logFile = WRITEPATH . 'logs/tte.log';
        $this->accessLogFile = WRITEPATH . 'logs/bridge_access.log';
        $this->maxFileSizeBytes = 25 * 1024 * 1024; // Maksimal 25 MB
    }

    /**
     * Halaman Utama Status Bridge TTE
     * GET /bridge
     */
    public function index()
    {
        $startTime = microtime(true);
        $action = $this->request->getGet('action');

        // Jika parameter action adalah panduan/docs
        if ($action && in_array(strtolower($action), ['panduan', 'docs'])) {
            return $this->panduan();
        }

        // Jika parameter action untuk pemanggilan API legacy
        if ($action && in_array(strtolower($action), ['status', 'user_status', 'sign', 'verify'])) {
            return $this->proxy('');
        }

        // Cek jika pemanggil meminta format JSON
        $accept = $this->request->getHeaderLine('Accept');
        if ($accept && stripos($accept, 'application/json') !== false) {
            $this->logAccess(200, (microtime(true) - $startTime) * 1000, null);

            return $this->response->setJSON([
                'status'  => true,
                'message' => 'Layanan Bridge TTE Sinjai aktif'
            ]);
        }

        $this->logAccess(200, (microtime(true) - $startTime) * 1000, null);

        return view('bridge/index', [
            'title'    => 'Bridge TTE',
            'docsUrl'  => site_url('bridge/panduan'),
            'logoUrl'  => base_url('logo.png'),
            'cssUrl'   => base_url('css/output.css'),
        ]);
    }

    /**
     * Halaman Panduan Integrasi Teknis
     * GET /bridge/panduan
     */
    public function panduan()
    {
        $startTime = microtime(true);
        $this->logAccess(200, (microtime(true) - $startTime) * 1000, null);

        return view('bridge/panduan', [
            'title'          => 'Panduan Integrasi Bridge TTE',
            'bridgeUrl'      => site_url('bridge'),
            'baseUrlExample' => site_url('bridge'),
            'logoUrl'        => base_url('logo.png'),
            'cssUrl'         => base_url('css/output.css'),
        ]);
    }

    /**
     * Reverse Proxy Handler untuk API TTE BSrE
     * GET/POST /bridge/(:any)
     */
    public function proxy(...$params)
    {
        $startTime = microtime(true);
        $subpath = !empty($params) ? implode('/', $params) : '';

        // 1. Validasi Whitelist IP / Domain Server Pemanggil
        $clientIp = $this->getRealClientIp();
        $whitelistModel = new \App\Domains\Bridge\Models\BridgeWhitelistModel();

        if (!$whitelistModel->isIpAllowed($clientIp)) {
            $this->logTte("AKSES DITOLAK: IP [{$clientIp}] tidak terdaftar pada whitelist domain bridge.");
            $this->logAccess(403, (microtime(true) - $startTime) * 1000, null);

            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status'  => false,
                    'error'   => 'Forbidden',
                    'message' => 'Akses ditolak: Alamat IP server tidak terdaftar dalam whitelist bridge.'
                ]);
        }

        // 2. Ekstraksi Autentikasi HTTP Basic Auth
        $authToSend = $this->extractBasicAuth();
        $username = $authToSend ? explode(':', $authToSend, 2)[0] : null;

        if (!$authToSend) {
            $this->logTte('AKSES DITOLAK: Header Authorization Basic Auth tidak disertakan.');
            $this->logAccess(401, (microtime(true) - $startTime) * 1000, null);

            return $this->response
                ->setStatusCode(401)
                ->setHeader('WWW-Authenticate', 'Basic realm="TTE Bridge Pemkab Sinjai"')
                ->setJSON([
                    'status'  => false,
                    'error'   => 'Unauthorized',
                    'message' => 'Kredensial HTTP Basic Auth wajib disertakan untuk mengakses API TTE.'
                ]);
        }

        // 2. Validasi & Resolusi Endpoint Target (Perlindungan SSRF & Path Traversal)
        $targetEndpoint = $this->resolveEndpoint($subpath);

        if (!$targetEndpoint) {
            $this->logTte("AKSES DITOLAK: Percobaan akses endpoint tidak sah [{$subpath}].");
            $this->logAccess(403, (microtime(true) - $startTime) * 1000, $username);

            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status'  => false,
                    'error'   => 'Forbidden',
                    'message' => 'Akses ditolak: Endpoint yang diizinkan hanya rute API TTE resmi.'
                ]);
        }

        // 3. Validasi Berkas Unggahan (Khusus Request POST Multipart)
        if (strtoupper($this->request->getMethod()) === 'POST' && !empty($_FILES)) {
            foreach ($_FILES as $key => $file) {
                if (isset($file['error'])) {
                    // Validasi batas ukuran berkas dari form atau konfigurasi sistem
                    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE || ($file['size'] ?? 0) > $this->maxFileSizeBytes) {
                        $this->logTte("AKSES DITOLAK: Ukuran berkas melebihi 25 MB.");
                        $this->logAccess(413, (microtime(true) - $startTime) * 1000, $username);

                        return $this->response
                            ->setStatusCode(413)
                            ->setJSON([
                                'status'  => false,
                                'error'   => 'Payload Too Large',
                                'message' => 'Ukuran berkas melebihi batas maksimal 25 MB.'
                            ]);
                    }

                    if ($file['error'] === UPLOAD_ERR_OK) {
                        // Validasi ekstensi berkas untuk pengamanan
                        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        if ($ext !== 'pdf' && !empty($file['name'])) {
                            $this->logTte("AKSES DITOLAK: Format berkas tidak diizinkan [{$file['name']}].");
                            $this->logAccess(400, (microtime(true) - $startTime) * 1000, $username);

                            return $this->response
                                ->setStatusCode(400)
                                ->setJSON([
                                    'status'  => false,
                                    'error'   => 'Bad Request',
                                    'message' => 'Format berkas tidak valid. Hanya berkas PDF yang diizinkan.'
                                ]);
                        }
                    }
                }
            }
        }

        $targetUrl = $this->backendUrl . $targetEndpoint;
        $method = $this->request->getMethod();
        $this->logTte("FORWARD: {$method} {$targetUrl}");

        // 4. Eksekusi cURL ke Server Backend BSrE
        $ch = curl_init($targetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $authToSend);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        // Pengerasan Keamanan Protokol cURL
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);

            $contentType = $this->request->getHeaderLine('Content-Type');

            if (stripos($contentType, 'application/json') !== false) {
                $rawBody = $this->request->getBody();
                curl_setopt($ch, CURLOPT_POSTFIELDS, $rawBody);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($rawBody)
                ]);
            } else {
                $postData = $this->request->getPost();

                // Unggah berkas biner tervalidasi
                if (!empty($_FILES)) {
                    foreach ($_FILES as $key => $file) {
                        if ($file['error'] === UPLOAD_ERR_OK) {
                            $postData[$key] = new CURLFile(
                                $file['tmp_name'],
                                $file['type'] ?: 'application/pdf',
                                $file['name']
                            );
                        }
                    }
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            }
        }

        $rawResponse = curl_exec($ch);
        $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resType     = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $curlError   = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $this->logTte("ERROR cURL: {$curlError}");
            $this->logAccess(502, (microtime(true) - $startTime) * 1000, $username);

            return $this->response
                ->setStatusCode(502)
                ->setJSON([
                    'status' => false,
                    'error'  => "Gagal menghubungi backend TTE lokal: {$curlError}"
                ]);
        }

        $this->logTte("RESPONSE: HTTP {$httpCode}");
        $this->logAccess($httpCode, (microtime(true) - $startTime) * 1000, $username);

        $response = $this->response->setStatusCode($httpCode);

        if ($resType) {
            $response->setContentType($resType);
        }

        return $response->setBody($rawResponse);
    }

    /**
     * Ekstraksi kredensial HTTP Basic Auth dari berbagai kemungkinan header server
     */
    protected function extractBasicAuth(): ?string
    {
        if ($this->request->getServer('PHP_AUTH_USER') && $this->request->getServer('PHP_AUTH_PW')) {
            return $this->request->getServer('PHP_AUTH_USER') . ':' . $this->request->getServer('PHP_AUTH_PW');
        }

        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader) {
            $authHeader = $this->request->getServer('HTTP_AUTHORIZATION')
                ?: $this->request->getServer('REDIRECT_HTTP_AUTHORIZATION')
                ?: $this->request->getServer('REDIRECT_REDIRECT_HTTP_AUTHORIZATION');
        }

        if ($authHeader && stripos($authHeader, 'Basic ') === 0) {
            $decoded = base64_decode(substr($authHeader, 6));
            if ($decoded && strpos($decoded, ':') !== false) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * Resolusi dan validasi endpoint path (Perlindungan Path Traversal & SSRF)
     */
    protected function resolveEndpoint(string $subpath): ?string
    {
        if (!empty($subpath)) {
            // Tolak percobaan Path Traversal dan karakter berbahaya
            if (str_contains($subpath, '..') || str_contains($subpath, "\\") || str_contains($subpath, "\0")) {
                return null;
            }

            // Normalisasi jalur URL
            $clean = trim(preg_replace('#/+#', '/', $subpath), '/');

            // Whitelist Ketat: Hanya izinkan rute resmi berawalan 'api/'
            if (!str_starts_with($clean, 'api/')) {
                return null;
            }

            return '/' . $clean;
        }

        $action = strtolower($this->request->getGet('action') ?? '');

        if ($action === 'status') {
            return '/api/status';
        }

        if ($action === 'user_status') {
            $nik = preg_replace('/\D/', '', $this->request->getGet('nik') ?? '');
            return !empty($nik) ? '/api/user/status/' . $nik : null;
        }

        if ($action === 'sign') {
            return '/api/sign/pdf';
        }

        if ($action === 'verify') {
            return '/api/sign/verify';
        }

        return null;
    }

    /**
     * Dapatkan alamat IP koneksi riil klien (kebal pemalsuan header HTTP)
     */
    protected function getRealClientIp(): string
    {
        $remoteAddr = $this->request ? $this->request->getServer('REMOTE_ADDR') : ($_SERVER['REMOTE_ADDR'] ?? null);
        if (!empty($remoteAddr) && filter_var($remoteAddr, FILTER_VALIDATE_IP)) {
            return $remoteAddr;
        }

        return $this->request ? $this->request->getIPAddress() : ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    /**
     * Catat log transaksi bridge dengan sanitasi anti-injeksi log dan rotasi berkas
     */
    protected function logTte(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $this->getRealClientIp();

        // Sanitasi karakter baris baru untuk mencegah Log Injection (CRLF)
        $sanitizedMessage = str_replace(["\r", "\n"], ' ', $message);
        $line = "[{$timestamp}] [{$ip}] {$sanitizedMessage}" . PHP_EOL;

        // Rotasi log jika berkas melebihi 10 MB
        if (file_exists($this->logFile) && filesize($this->logFile) > 10 * 1024 * 1024) {
            @rename($this->logFile, $this->logFile . '.' . date('Ymd_His') . '.bak');
        }

        @file_put_contents($this->logFile, $line, FILE_APPEND);
    }

    /**
     * Catat log koneksi masuk dari luar ke berkas bridge_access.log
     */
    protected function logAccess(int $statusCode, float $durationMs, ?string $username = null): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $this->getRealClientIp();
        $user = $username ?: '-';
        $method = strtoupper($this->request->getMethod());
        $uri = '/' . ltrim($this->request->getUri()->getPath(), '/');
        $duration = number_format($durationMs, 1, '.', '');
        $rawAgent = $this->request->getUserAgent()->getAgentString() ?: '-';

        // Sanitasi pemisah dan karakter baris baru
        $cleanUser = str_replace(['|', "\r", "\n"], ' ', $user);
        $cleanUri = str_replace(['|', "\r", "\n"], ' ', $uri);
        $cleanAgent = str_replace(['|', "\r", "\n"], ' ', $rawAgent);

        $line = "{$timestamp} | {$ip} | {$cleanUser} | {$method} | {$cleanUri} | {$statusCode} | {$duration}ms | {$cleanAgent}" . PHP_EOL;

        // Rotasi log jika berkas melebihi 10 MB
        if (file_exists($this->accessLogFile) && filesize($this->accessLogFile) > 10 * 1024 * 1024) {
            @rename($this->accessLogFile, $this->accessLogFile . '.' . date('Ymd_His') . '.bak');
        }

        @file_put_contents($this->accessLogFile, $line, FILE_APPEND);
    }
}
