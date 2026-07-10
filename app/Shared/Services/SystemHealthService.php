<?php

namespace App\Shared\Services;

use App\Shared\Libraries\CpanelApi;
use App\Shared\Libraries\BsreApi;
use App\Shared\Libraries\PegawaiApi;

class SystemHealthService
{
    /**
     * Get health status from cache or trigger refresh on-demand.
     */
    public function getHealthStatus()
    {
        $cacheKey = 'system_health_status_v3';
        $cache = \Config\Services::cache();
        
        $cachedData = $cache->get($cacheKey);
        if ($cachedData !== null) {
            return $cachedData;
        }
        
        // Fallback On-Demand: run check and cache the result
        return $this->refreshCache();
    }

    /**
     * Force refresh the health check cache (used by cron job / CLI).
     */
    public function refreshCache()
    {
        $cacheKey = 'system_health_status_v3';
        $cache = \Config\Services::cache();
        
        $results = $this->checkAll();
        $cache->save($cacheKey, $results, 300); // Cache for 5 minutes
        return $results;
    }

    /**
     * Run health checks for all services.
     */
    public function checkAll()
    {
        return [
            'cpanel'  => $this->checkCpanel(),
            'bsre'    => $this->checkBsre(),
            'pegawai' => $this->checkPegawai(),
        ];
    }

    /**
     * Standard response helper.
     */
    private function buildResponse($key, $label, $status, $text, $message, $isMocked = false)
    {
        return [
            'key'       => $key,
            'label'     => $label,
            'status'    => $status,
            'text'      => $text,
            'message'   => $message,
            'is_mocked' => $isMocked,
        ];
    }

    private function checkCpanel()
    {
        $key = 'cpanel';
        $label = 'cPanel UAPI';

        // 1. Bypass check in development mode
        if (env('CI_ENVIRONMENT') === 'development') {
            return $this->buildResponse($key, $label, 'UP', 'Online (Dev)', 'Simulasi koneksi lokal cPanel berhasil.', true);
        }

        // 2. Perform fast TCP port ping
        try {
            $config = config('Cpanel');
            $rawHost = $config->cpanel_host ?? '';
            $port = (string)($config->cpanel_port ?? '2083');

            $host = trim(str_replace(['"', "'"], '', $rawHost));
            if (preg_match('/^https?:\/\//', $host)) {
                $parsedUrl = parse_url($host);
                $host = $parsedUrl['host'] ?? $host;
            }
            $host = explode('/', $host)[0];
            if (strpos($host, ':') !== false) {
                list($host, ) = explode(':', $host, 2);
            }

            if (empty($host)) {
                return $this->buildResponse($key, $label, 'DOWN', 'Offline', 'Konfigurasi cPanel host kosong.');
            }

            $connection = @fsockopen($host, $port, $errno, $errstr, 1.5);
            if (is_resource($connection)) {
                fclose($connection);
                return $this->buildResponse($key, $label, 'UP', 'Online', 'Koneksi port cPanel ' . $port . ' berhasil terhubung.');
            }

            return $this->buildResponse($key, $label, 'DOWN', 'Offline', 'Gagal terhubung ke port cPanel: ' . ($errstr ?: 'Timeout'));
        } catch (\Throwable $e) {
            return $this->buildResponse($key, $label, 'DOWN', 'Offline', 'Error: ' . $e->getMessage());
        }
    }

    private function checkBsre()
    {
        $key = 'bsre';
        $label = 'BSrE Status API';

        // 1. Bypass check in development mode
        if (env('CI_ENVIRONMENT') === 'development') {
            return $this->buildResponse($key, $label, 'UP', 'Online (Dev)', 'Simulasi koneksi lokal BSrE API berhasil.', true);
        }

        try {
            $baseUrl = env('BSRE_BASE_URL');
            if (empty($baseUrl)) {
                return $this->buildResponse($key, $label, 'DOWN', 'Offline', 'BSRE_BASE_URL belum diatur.');
            }

            $host = $baseUrl;
            if (preg_match('/^https?:\/\//', $host)) {
                $parsedUrl = parse_url($host);
                $host = $parsedUrl['host'] ?? $host;
            }
            $host = explode('/', $host)[0];
            if (strpos($host, ':') !== false) {
                list($host, ) = explode(':', $host, 2);
            }

            // Test port 443 (HTTPS) with 1.5s timeout
            $connection = @fsockopen($host, 443, $errno, $errstr, 1.5);
            if (is_resource($connection)) {
                fclose($connection);
                return $this->buildResponse($key, $label, 'UP', 'Online', 'Koneksi ke API server BSrE berhasil terhubung.');
            }

            return $this->buildResponse($key, $label, 'DOWN', 'Offline', 'Gagal terhubung ke server BSrE: ' . ($errstr ?: 'Timeout'));
        } catch (\Throwable $e) {
            return $this->buildResponse($key, $label, 'DOWN', 'Offline', 'Error: ' . $e->getMessage());
        }
    }

    private function checkPegawai()
    {
        $key = 'pegawai';
        $label = 'Pegawai API';

        // 1. Bypass check in development mode
        if (env('CI_ENVIRONMENT') === 'development') {
            return $this->buildResponse($key, $label, 'UP', 'Online (Dev)', 'Simulasi koneksi lokal Pegawai API berhasil.', true);
        }

        try {
            $pegawai = new PegawaiApi();
            
            // Get host from PegawaiApi Base URL using reflection
            $reflection = new \ReflectionClass($pegawai);
            $property = $reflection->getProperty('baseUrl');
            $property->setAccessible(true);
            $baseUrl = $property->getValue($pegawai);

            $host = $baseUrl;
            if (preg_match('/^https?:\/\//', $host)) {
                $parsedUrl = parse_url($host);
                $host = $parsedUrl['host'] ?? $host;
            }
            $host = explode('/', $host)[0];
            if (strpos($host, ':') !== false) {
                list($host, ) = explode(':', $host, 2);
            }

            // Test port 443 (HTTPS) with 1.5s timeout
            $connection = @fsockopen($host, 443, $errno, $errstr, 1.5);
            if (is_resource($connection)) {
                fclose($connection);
                return $this->buildResponse($key, $label, 'UP', 'Online', 'Koneksi ke API server Pegawai berhasil terhubung.');
            }

            return $this->buildResponse($key, $label, 'DOWN', 'Offline', 'Gagal terhubung ke server Pegawai: ' . ($errstr ?: 'Timeout'));
        } catch (\Throwable $e) {
            return $this->buildResponse($key, $label, 'DOWN', 'Offline', 'Error: ' . $e->getMessage());
        }
    }
}
