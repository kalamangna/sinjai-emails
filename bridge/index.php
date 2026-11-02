<?php
/**
 * TTE BSrE Bridge Router - Pemkab Sinjai
 * Endpoint: /tte/
 * Menjembatani seluruh pemanggilan API TTE agar server TTE (10.91.162.6)
 * melihat request murni berasal dari IP internal (10.91.162.3).
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');

$config = require __DIR__ . '/config.php';

// Helper Logging
function tte_log($config, $message) {
    if (!empty($config['log']['enabled'])) {
        $timestamp = date('Y-m-d H:i:s');
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'UNKNOWN';
        $logLine = "[$timestamp] [$ip] $message" . PHP_EOL;
        @file_put_contents($config['log']['file'], $logLine, FILE_APPEND);
    }
}

// Ambil Header (kompatibel FastCGI / PHP-FPM / Apache Rewrites)
function get_custom_header($name) {
    $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    if (!empty($_SERVER[$key])) {
        return $_SERVER[$key];
    }
    if (!empty($_SERVER['REDIRECT_' . $key])) {
        return $_SERVER['REDIRECT_' . $key];
    }
    if (!empty($_SERVER['REDIRECT_REDIRECT_' . $key])) {
        return $_SERVER['REDIRECT_REDIRECT_' . $key];
    }
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $k => $v) {
            if (strcasecmp($k, $name) === 0) {
                return $v;
            }
        }
    }
    return null;
}

// 1. Tentukan Path Target API Terlebih Dahulu
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Hapus prefix direktori (seperti /bridge atau /tte) secara dinamis
$scriptDir = isset($_SERVER['SCRIPT_NAME']) ? rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') : '';
if (!empty($scriptDir) && strpos($uri, $scriptDir) === 0) {
    $relativePath = substr($uri, strlen($scriptDir));
} else {
    $relativePath = preg_replace('#^/(tte|bridge)/?#', '', $uri);
}
$relativePath = ltrim($relativePath, '/');

$backend = rtrim($config['tte']['base_url'], '/');
$timeout = isset($config['tte']['timeout']) ? $config['tte']['timeout'] : 120;

// Jika diakses root direktori tanpa path API dan tanpa parameter action
if (empty($relativePath) && !isset($_GET['action'])) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>TTE Bridge Service - Pemkab Sinjai</title>
        <style>
            body { font-family: sans-serif; background: #f4f6f9; color: #333; padding: 40px; text-align: center; }
            .card { background: #fff; max-width: 600px; margin: 0 auto; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h2 { color: #0d6efd; margin-top: 0; }
            .status { display: inline-block; padding: 6px 12px; background: #d1e7dd; color: #0f5132; border-radius: 4px; font-weight: bold; }
            code { background: #eee; padding: 3px 6px; border-radius: 3px; }
        </style>
    </head>
    <body>
        <div class="card">
            <h2>TTE BSrE Bridge Active</h2>
            <p><span class="status">✓ Berjalan Normal</span></p>
            <p>Layanan API Bridge Tanda Tangan Elektronik (BSrE BSSN) Pemkab Sinjai siap digunakan.</p>
            <hr>
            <p style="font-size:13px; text-align:left;">
                <strong>Endpoint Siap Pakai:</strong><br>
                • <code>/tte/api/user/status/{nik}</code> (Cek status sertifikat NIK)<br>
                • <code>/tte/api/v2/user/check/status</code> (Cek status sertifikat JSON V2)<br>
                • <code>/tte/api/sign/pdf</code> (Tanda tangan dokumen PDF)<br>
                • <code>/tte/api/v2/verify/pdf</code> (Verifikasi dokumen PDF JSON V2)<br>
                • <code>/tte/api/sign/verify</code> (Verifikasi dokumen PDF Multipart)
            </p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 2. Ekstraksi Autentikasi (Basic Auth atau API Key)
$incomingAuth = null;
if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
    $incomingAuth = $_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW'];
} else {
    $authHeader = get_custom_header('Authorization');
    if ($authHeader && stripos($authHeader, 'Basic ') === 0) {
        $decoded = base64_decode(substr($authHeader, 6));
        if ($decoded && strpos($decoded, ':') !== false) {
            $incomingAuth = $decoded;
        }
    }
}

$providedApiKey = get_custom_header('X-Api-Key');
if (!$providedApiKey && isset($_REQUEST['api_key'])) {
    $providedApiKey = $_REQUEST['api_key'];
}

// Cek jika API Key diwajibkan
if (!empty($config['require_api_key'])) {
    if (empty($providedApiKey) || $providedApiKey !== $config['api_key']) {
        tte_log($config, "AKSES DITOLAK: API Key tidak valid.");
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => false, 'error' => 'Forbidden: API Key tidak valid.']);
        exit;
    }
}

// Wajibkan Kredensial Basic Auth dari Pengakses (Matikan Fallback Default)
if (!$incomingAuth) {
    tte_log($config, "AKSES DITOLAK: Header Authorization Basic Auth tidak disertakan.");
    http_response_code(401);
    header('WWW-Authenticate: Basic realm="TTE Bridge Pemkab Sinjai"');
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'  => false,
        'error'   => 'Unauthorized',
        'message' => 'Kredensial HTTP Basic Auth wajib disertakan untuk mengakses API TTE.'
    ]);
    exit;
}

$authToSend = $incomingAuth;

// 3. Parsing Action dari Path atau Query Param
$targetEndpoint = '';
if (strpos($relativePath, 'api/') === 0) {
    $targetEndpoint = '/' . $relativePath;
} elseif (isset($_GET['action'])) {
    $act = strtolower($_GET['action']);
    if ($act === 'status') {
        $targetEndpoint = '/api/status';
    } elseif ($act === 'user_status') {
        $nik = isset($_GET['nik']) ? preg_replace('/\D/', '', $_GET['nik']) : '';
        $targetEndpoint = '/api/user/status/' . $nik;
    } elseif ($act === 'sign') {
        $targetEndpoint = '/api/sign/pdf';
    } elseif ($act === 'verify') {
        $targetEndpoint = '/api/sign/verify';
    }
} else {
    $targetEndpoint = '/' . ltrim($relativePath, '/');
}

$targetUrl = $backend . $targetEndpoint;
tte_log($config, "FORWARD: " . $_SERVER['REQUEST_METHOD'] . " $targetUrl");

// 4. Eksekusi cURL ke Server TTE Lokal (10.91.162.6)
$ch = curl_init($targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, $authToSend);
curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

// Handle HTTP Method & Payload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    curl_setopt($ch, CURLOPT_POST, true);

    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : (isset($_SERVER['HTTP_CONTENT_TYPE']) ? $_SERVER['HTTP_CONTENT_TYPE'] : '');

    if (stripos($contentType, 'application/json') !== false) {
        $rawInput = file_get_contents('php://input');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $rawInput);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($rawInput)
        ]);
    } else {
        $postData = $_POST;
        unset($postData['api_key']);

        // Handle File Uploads
        if (!empty($_FILES)) {
            foreach ($_FILES as $key => $file) {
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $postData[$key] = new CURLFile(
                        $file['tmp_name'],
                        $file['type'] ? $file['type'] : 'application/octet-stream',
                        $file['name']
                    );
                }
            }
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }
}

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$resContentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    tte_log($config, "ERROR cURL: $curlError");
    http_response_code(502);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => false, 'error' => "Gagal menghubungi backend TTE lokal: $curlError"]);
    exit;
}

tte_log($config, "RESPONSE: HTTP $httpCode");

// Kirimkan balik respons ke Klien
http_response_code($httpCode);
if ($resContentType) {
    header('Content-Type: ' . $resContentType);
}
echo $response;
