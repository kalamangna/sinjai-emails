<?php

namespace App\Shared\Services;

use App\Shared\Libraries\CpanelApi;
use App\Shared\Libraries\BsreApi;
use App\Shared\Libraries\PegawaiApi;

class SystemHealthService
{
    public function checkAll()
    {
        return [
            'cpanel'  => $this->checkCpanel(),
            'bsre'    => $this->checkBsre(),
            'pegawai' => $this->checkPegawai(),
        ];
    }

    private function checkCpanel()
    {
        // 1. Bypass pengecekan di mode development/localhost untuk performa instan di local server
        if (env('CI_ENVIRONMENT') === 'development') {
            return [
                'status' => 'DOWN',
                'message' => 'Offline (Local Development)',
            ];
        }

        // 2. Gunakan uji koneksi port jaringan cepat (timeout 1.5 detik) alih-alih mengambil seluruh akun email
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
                return [
                    'status' => 'DOWN',
                    'message' => 'Konfigurasi cPanel host kosong.',
                ];
            }

            $connection = @fsockopen($host, $port, $errno, $errstr, 1.5);
            if (is_resource($connection)) {
                fclose($connection);
                return [
                    'status' => 'UP',
                    'message' => 'Koneksi port cPanel ' . $port . ' berhasil terhubung.',
                ];
            }

            return [
                'status' => 'DOWN',
                'message' => 'Gagal terhubung ke port cPanel: ' . ($errstr ?: 'Timeout'),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'DOWN',
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    private function checkBsre()
    {
        // Bypass pengecekan di mode development/localhost untuk performa instan di local server
        if (env('CI_ENVIRONMENT') === 'development') {
            return [
                'status' => 'DOWN',
                'message' => 'Offline (Local Development)',
            ];
        }

        try {
            $baseUrl = env('BSRE_BASE_URL');
            if (empty($baseUrl)) {
                return [
                    'status' => 'DOWN',
                    'message' => 'BSRE_BASE_URL belum diatur.',
                ];
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

            // Uji port 443 (HTTPS) dengan timeout 1.5 detik
            $connection = @fsockopen($host, 443, $errno, $errstr, 1.5);
            if (is_resource($connection)) {
                fclose($connection);
                return [
                    'status' => 'UP',
                    'message' => 'Koneksi ke API server BSrE berhasil terhubung.',
                ];
            }

            return [
                'status' => 'DOWN',
                'message' => 'Gagal terhubung ke server BSrE: ' . ($errstr ?: 'Timeout'),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'DOWN',
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    private function checkPegawai()
    {
        // Bypass pengecekan di mode development/localhost untuk performa instan di local server
        if (env('CI_ENVIRONMENT') === 'development') {
            return [
                'status' => 'DOWN',
                'message' => 'Offline (Local Development)',
            ];
        }

        try {
            $pegawai = new PegawaiApi();
            
            // Dapatkan host dari PegawaiApi Base URL
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

            // Uji port 443 (HTTPS) dengan timeout 1.5 detik
            $connection = @fsockopen($host, 443, $errno, $errstr, 1.5);
            if (is_resource($connection)) {
                fclose($connection);
                return [
                    'status' => 'UP',
                    'message' => 'Koneksi ke API server Pegawai berhasil terhubung.',
                ];
            }

            return [
                'status' => 'DOWN',
                'message' => 'Gagal terhubung ke server Pegawai: ' . ($errstr ?: 'Timeout'),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'DOWN',
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }
}
