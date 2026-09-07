<?php

namespace App\Domains\Dashboard\Controllers;

use App\Shared\BaseController;

class HomeController extends BaseController
{
    /**
     * Halaman Utama (Public Landing Page)
     */
    public function index()
    {
        // Jika sudah login, otomatis alihkan ke Dashboard Internal
        if (session()->get('isLoggedIn')) {
            return redirect()->to('dashboard');
        }

        $data = [
            'title' => 'Sistem Identitas Digital'
        ];

        return view('home/landing', $data);
    }

    /**
     * Portal Internal / Dashboard
     */
    public function dashboard()
    {
        $dashboardService = new \App\Domains\Dashboard\Services\DashboardService();
        $data = $dashboardService->getSummaryData();
        $data['title'] = 'Dashboard';

        return view('home/index', $data);
    }

    /**
     * Trigger Sinkronisasi cPanel secara manual dari Dasbor
     */
    public function syncCpanel()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki izin untuk melakukan sinkronisasi cPanel.'
            ]);
        }

        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }

        try {
            $syncService = new \App\Shared\Services\SyncService();
            $result = $syncService->syncFromCpanel();

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => $result['message'] ?? 'Sinkronisasi cPanel berhasil.',
                'data'    => $result,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Sync cPanel failed via Dashboard: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal sinkronisasi cPanel: ' . $e->getMessage(),
            ]);
        }
    }
}
