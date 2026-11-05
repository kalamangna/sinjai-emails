<?php

namespace App\Domains\Api\Controllers;

use App\Domains\Bridge\Models\BridgeWhitelistModel;
use App\Shared\BaseController;

class GatewayLogController extends BaseController
{
    protected string $logFile;

    public function __construct()
    {
        $this->logFile = WRITEPATH . 'logs/api_gateway.log';
    }

    /**
     * Tampilan Antarmuka Tabel Log Akses API Gateway
     * GET /api/log
     */
    public function index()
    {
        $search       = trim($this->request->getGet('search') ?? '');
        $filterStatus = trim($this->request->getGet('status') ?? '');
        $filterMethod = strtoupper(trim($this->request->getGet('method') ?? ''));

        $ipAgencyMap = $this->getIpToAgencyMap();
        $rawEntries  = $this->readLogLines($ipAgencyMap);

        // 1. Hitung Ringkasan Metrik Keseluruhan
        $totalRequests  = count($rawEntries);
        $successCount   = 0;
        $rejectedCount  = 0;
        $serverErrCount = 0;

        foreach ($rawEntries as $entry) {
            $code = $entry['status'];
            if ($code >= 200 && $code < 300) {
                $successCount++;
            } elseif ($code >= 400 && $code < 500) {
                $rejectedCount++;
            } elseif ($code >= 500) {
                $serverErrCount++;
            }
        }

        // 2. Terapkan Filter Pencarian
        $filtered = array_filter($rawEntries, function ($row) use ($search, $filterStatus, $filterMethod) {
            if (!empty($search)) {
                $q = strtolower($search);
                $matchSearch = str_contains(strtolower($row['ip']), $q)
                    || (!empty($row['agency']) && str_contains(strtolower($row['agency']), $q))
                    || str_contains(strtolower($row['endpoint']), $q)
                    || str_contains(strtolower($row['method']), $q);

                if (!$matchSearch) {
                    return false;
                }
            }

            if (!empty($filterStatus)) {
                if ($filterStatus === '2xx' && ($row['status'] < 200 || $row['status'] >= 300)) {
                    return false;
                }
                if ($filterStatus === '4xx' && ($row['status'] < 400 || $row['status'] >= 500)) {
                    return false;
                }
                if ($filterStatus === '5xx' && $row['status'] < 500) {
                    return false;
                }
                if (is_numeric($filterStatus) && (int)$row['status'] !== (int)$filterStatus) {
                    return false;
                }
            }

            if (!empty($filterMethod) && $row['method'] !== $filterMethod) {
                return false;
            }

            return true;
        });

        // 3. Paginasi Hasil
        $perPage       = 25;
        $page          = max(1, (int)($this->request->getGet('page') ?? 1));
        $totalFiltered = count($filtered);
        $offset        = ($page - 1) * $perPage;
        $pagedEntries  = array_slice($filtered, $offset, $perPage);

        $pager = service('pager')->makeLinks($page, $perPage, $totalFiltered, 'default_full');

        return view('api/log', [
            'title'          => 'Log API',
            'logs'           => $pagedEntries,
            'pager'          => $pager,
            'totalRequests'  => $totalRequests,
            'successCount'   => $successCount,
            'rejectedCount'  => $rejectedCount,
            'serverErrCount' => $serverErrCount,
            'search'         => $search,
            'filterStatus'   => $filterStatus,
            'filterMethod'   => $filterMethod,
            'page'           => $page,
            'perPage'        => $perPage,
            'totalFiltered'  => $totalFiltered,
        ]);
    }

    /**
     * Unduh Berkas Mentah Log Akses API Gateway
     * GET /api/log/download
     */
    public function download()
    {
        if (!file_exists($this->logFile)) {
            session()->setFlashdata('error', 'Berkas log API Gateway belum tersedia.');
            return redirect()->to(site_url('api/log'));
        }

        $fileName = 'api_gateway_' . date('Y-m-d') . '.log';
        return $this->response->download($this->logFile, null)->setFileName($fileName);
    }

    /**
     * Kosongkan Berkas Log Akses API Gateway
     * POST /api/log/clear
     */
    public function clear()
    {
        if (!in_array(session()->get('role'), ['super_admin', 'admin'])) {
            session()->setFlashdata('error', 'Aksi tidak diizinkan.');
            return redirect()->to(site_url('api/log'));
        }

        if (file_exists($this->logFile)) {
            @file_put_contents($this->logFile, '');
        }

        session()->setFlashdata('success', 'Catatan log API Gateway berhasil dikosongkan.');
        return redirect()->to(site_url('api/log'));
    }

    /**
     * Dapatkan pemetaan alamat IP ke nama instansi jika terdaftar di whitelist
     *
     * @return array<string, string>
     */
    protected function getIpToAgencyMap(): array
    {
        try {
            $whitelistModel = new BridgeWhitelistModel();
            $records = $whitelistModel->select('domain, ip_address, label')->findAll();
        } catch (\Throwable $e) {
            return [];
        }

        $map = [
            '127.0.0.1'       => 'Localhost',
            '::1'             => 'Localhost',
            '103.170.105.101' => 'Server 1 Sinjai (TTE)',
            '36.88.112.133'   => 'Apps Sinjai (Enikda)',
            '10.91.162.2'     => 'VM Server 1',
            '10.91.162.3'     => 'VM Apl (Aplikasi)',
            '10.91.162.7'     => 'VM Apl 2',
            '100.115.30.111'  => 'VM Server 1 (Tailscale)',
            '100.68.83.10'    => 'VM Apl (Tailscale)',
            '100.72.75.85'    => 'VM Apl 2 (Tailscale)',
            '100.68.69.37'    => 'VM Enikda (Tailscale)',
            '100.91.233.34'   => 'VM Cedev (Tailscale)',
        ];
        foreach ($records as $row) {
            $label = trim((string)($row['label'] ?? ''));
            if ($label === '') {
                $label = trim((string)($row['domain'] ?? ''));
            }

            if (!empty($row['ip_address'])) {
                $ips = explode(',', (string)$row['ip_address']);
                foreach ($ips as $ip) {
                    $cleanIp = trim($ip);
                    if ($cleanIp !== '') {
                        $map[$cleanIp] = $label;
                    }
                }
            }

            $domain = trim((string)($row['domain'] ?? ''));
            if ($domain !== '' && filter_var($domain, FILTER_VALIDATE_IP)) {
                $map[$domain] = $label;
            }
        }

        return $map;
    }

    /**
     * Baca baris berkas log dan balik urutan agar data terbaru berada di posisi teratas
     *
     * @param array<string, string> $ipAgencyMap Pemetaan alamat IP ke nama instansi
     * @return array<int, array<string, mixed>>
     */
    protected function readLogLines(array $ipAgencyMap = []): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $lines = @file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return [];
        }

        $lines = array_reverse($lines);

        $parsed = [];
        foreach ($lines as $line) {
            $parts = explode(' | ', trim($line), 5);
            if (count($parts) >= 5) {
                $ip = $parts[1];
                $parsed[] = [
                    'timestamp' => $parts[0],
                    'ip'        => $ip,
                    'agency'    => $ipAgencyMap[$ip] ?? null,
                    'method'    => $parts[2],
                    'endpoint'  => $parts[3],
                    'status'    => (int)$parts[4],
                ];
            }
        }

        return $parsed;
    }
}
