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
}
