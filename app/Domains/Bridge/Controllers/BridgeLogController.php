<?php

namespace App\Domains\Bridge\Controllers;

use App\Shared\BaseController;

class BridgeLogController extends BaseController
{
    protected string $logFile;

    public function __construct()
    {
        $this->logFile = WRITEPATH . 'logs/bridge_access.log';
    }

    /**
     * Tampilan Antarmuka Tabel Log Koneksi Bridge
     * GET /admin/bridge-log
     */
    public function index()
    {
        $search       = trim($this->request->getGet('search') ?? '');
        $filterStatus = trim($this->request->getGet('status') ?? '');
        $filterMethod = strtoupper(trim($this->request->getGet('method') ?? ''));

        $rawEntries = $this->readLogLines();

        // 1. Hitung Ringkasan Metrik Keseluruhan
        $totalRequests = count($rawEntries);
        $successCount  = 0;
        $rejectedCount = 0;
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
                    || str_contains(strtolower($row['user']), $q)
                    || str_contains(strtolower($row['endpoint']), $q)
                    || str_contains(strtolower($row['user_agent']), $q);

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
        $perPage = 25;
        $page = max(1, (int)($this->request->getGet('page') ?? 1));
        $totalFiltered = count($filtered);
        $offset = ($page - 1) * $perPage;
        $pagedEntries = array_slice($filtered, $offset, $perPage);

        $pager = service('pager')->makeLinks($page, $perPage, $totalFiltered, 'default_full');

        return view('bridge/log', [
            'title'          => 'Log Bridge',
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
     * Unduh Berkas Mentah Log Akses Bridge
     * GET /admin/bridge-log/download
     */
    public function download()
    {
        if (!file_exists($this->logFile)) {
            session()->setFlashdata('error', 'Berkas log koneksi bridge belum tersedia.');
            return redirect()->to(site_url('admin/bridge-log'));
        }

        $fileName = 'bridge_access_' . date('Y-m-d') . '.log';
        return $this->response->download($this->logFile, null)->setFileName($fileName);
    }

    /**
     * Kosongkan Berkas Log Akses Bridge (Khusus Super Admin)
     * POST /admin/bridge-log/clear
     */
    public function clear()
    {
        if (session()->get('role') !== 'super_admin') {
            session()->setFlashdata('error', 'Aksi hanya diizinkan untuk Super Admin.');
            return redirect()->to(site_url('admin/bridge-log'));
        }

        if (file_exists($this->logFile)) {
            @file_put_contents($this->logFile, '');
        }

        session()->setFlashdata('success', 'Catatan log koneksi bridge berhasil dikosongkan.');
        return redirect()->to(site_url('admin/bridge-log'));
    }

    /**
     * Baca baris berkas log dan balik urutan agar data terbaru berada di posisi teratas
     *
     * @return array<int, array<string, mixed>>
     */
    protected function readLogLines(): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $lines = @file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return [];
        }

        // Balik urutan agar riwayat paling baru muncul pertama
        $lines = array_reverse($lines);

        $parsed = [];
        foreach ($lines as $line) {
            $parts = explode(' | ', trim($line), 8);
            if (count($parts) >= 8) {
                $parsed[] = [
                    'timestamp'  => $parts[0],
                    'ip'         => $parts[1],
                    'user'       => $parts[2],
                    'method'     => $parts[3],
                    'endpoint'   => $parts[4],
                    'status'     => (int)$parts[5],
                    'duration'   => $parts[6],
                    'user_agent' => $parts[7],
                ];
            }
        }

        return $parsed;
    }
}
