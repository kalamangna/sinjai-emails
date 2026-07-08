<?php

namespace App\Domains\Dashboard\Controllers;

use App\Shared\BaseController;

class Home extends BaseController
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
        $cache = \Config\Services::cache();
        $cacheKey = 'dashboard_summary_data_v3';

        if (!$data = $cache->get($cacheKey)) {
            $dashboardService = new \App\Domains\Dashboard\Services\DashboardService();
            $data = $dashboardService->getSummaryData();

            // Cache for 10 minutes (600 seconds)
            $cache->save($cacheKey, $data, 600);
        }

        $data['title'] = 'Dashboard';

        return view('home/index', $data);
    }
}
