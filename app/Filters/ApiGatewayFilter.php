<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class ApiGatewayFilter implements FilterInterface
{
    protected string $logFile;

    /**
     * Daftar whitelist IP dan subnet statis VM SinjaiKab
     */
    protected array $staticWhitelist = [
        '103.170.105.101', // IP Publik Server1 / TTE Sinjai
        '36.88.112.133',   // IP Publik Apps / Enikda Sinjai
        '10.91.162.0/24',  // Subnet Privat VM Datacenter Sinjai (server1, apl, apl2)
        '100.64.0.0/10',   // Rentang IP Tailscale CGNAT (seluruh node VM Tailscale SinjaiKab)
    ];

    public function __construct()
    {
        $this->logFile = WRITEPATH . 'logs/api_gateway.log';
    }

    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during normal execution.
     * However, when an abnormal state is found, it should return an instance of
     * ResponseInterface. If it does, script execution will end and that Response
     * will be sent back to the client, allowing for error pages, redirects, etc.
     *
     * @param array|null $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Allow if user is already logged in (Session-based access for browser)
        if (session()->get('isLoggedIn')) {
            return;
        }

        // 2. Validasi Whitelist IP Statis VM SinjaiKab
        $clientIp = $this->getRealClientIp($request);
        if (!$this->isIpAllowed($clientIp)) {
            $this->writeLog($request, 403);
            return Services::response()
                ->setStatusCode(403)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Forbidden: Alamat IP tidak terdaftar dalam whitelist API Gateway VM SinjaiKab.'
                ]);
        }

        $validToken = env('API_GATEWAY_TOKEN');

        if (empty($validToken)) {
            $this->writeLog($request, 500);
            return Services::response()
                ->setStatusCode(500)
                ->setJSON(['status' => 'error', 'message' => 'API Gateway is not properly configured (token missing in .env).']);
        }

        // 3. Check Bearer Token in Header (for external application access)
        $authHeader = $request->getHeaderLine('Authorization');
        if (!empty($authHeader) && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $providedToken = $matches[1];
            if (hash_equals($validToken, $providedToken)) {
                return;
            }
            $this->writeLog($request, 403);
            return Services::response()
                ->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => 'Forbidden: Invalid API Gateway token.']);
        }

        $this->writeLog($request, 401);
        return Services::response()
            ->setStatusCode(401)
            ->setJSON(['status' => 'error', 'message' => 'Unauthorized: Please log in or provide a valid Bearer token in the header.']);
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param array|null $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $statusCode = $response->getStatusCode();
        $this->writeLog($request, $statusCode);
    }

    /**
     * Dapatkan alamat IP koneksi riil klien (kebal pemalsuan header HTTP)
     */
    protected function getRealClientIp(RequestInterface $request): string
    {
        $remoteAddr = $request->getServer('REMOTE_ADDR') ?: ($_SERVER['REMOTE_ADDR'] ?? null);
        if (!empty($remoteAddr) && filter_var($remoteAddr, FILTER_VALIDATE_IP)) {
            return $remoteAddr;
        }

        return $request->getIPAddress();
    }

    /**
     * Periksa apakah alamat IP klien diizinkan berdasarkan whitelist statis VM SinjaiKab
     */
    protected function isIpAllowed(string $clientIp): bool
    {
        $allowedList = $this->staticWhitelist;

        $envCustom = env('API_GATEWAY_WHITELIST');
        if (!empty($envCustom)) {
            $extraIps = array_map('trim', explode(',', $envCustom));
            $allowedList = array_merge($allowedList, $extraIps);
        }

        foreach ($allowedList as $pattern) {
            if ($this->ipMatchesPattern($clientIp, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Pencocokan alamat IP tunggal maupun subnet CIDR (contoh: 10.91.162.0/24)
     */
    protected function ipMatchesPattern(string $ip, string $pattern): bool
    {
        $pattern = trim($pattern);
        if ($ip === $pattern) {
            return true;
        }

        if (str_contains($pattern, '/')) {
            [$subnet, $maskBits] = explode('/', $pattern, 2);
            $maskBits = (int)$maskBits;

            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ipLong     = ip2long($ip);
                $subnetLong = ip2long($subnet);
                if ($maskBits > 0 && $maskBits <= 32) {
                    $mask = (int)(-1 << (32 - $maskBits));
                    return ($ipLong & $mask) === ($subnetLong & $mask);
                }
            }
        }

        return false;
    }

    /**
     * Rekam baris log akses API Gateway
     */
    protected function writeLog(RequestInterface $request, int $statusCode): void
    {
        try {
            $logDir = dirname($this->logFile);
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }

            $timestamp = date('Y-m-d H:i:s');
            $ip        = $request->getIPAddress();
            $method    = strtoupper($request->getMethod());
            
            $uri       = $request->getUri();
            $path      = '/' . ltrim($uri->getPath(), '/');
            if ($uri->getQuery() !== '') {
                $path .= '?' . $uri->getQuery();
            }

            // Format ringkas: YYYY-MM-DD HH:mm:ss | IP | METHOD | PATH | STATUS
            $logLine   = sprintf(
                "%s | %s | %s | %s | %d\n",
                $timestamp,
                $ip,
                $method,
                $path,
                $statusCode
            );

            @file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // Abaikan kegagalan penulisan log agar tidak memutus respon API
        }
    }
}
