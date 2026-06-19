<?php

namespace App\Domains\Dashboard\Controllers;

use App\Shared\BaseController;

class Home extends BaseController
{
    public function index(): string
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'dashboard_summary_data_v3';

        if (!$data = $cache->get($cacheKey)) {
            $dashboardService = new \App\Domains\Dashboard\Services\DashboardService();
            $data = $dashboardService->getSummaryData();

            // Cache for 10 minutes (600 seconds)
            $cache->save($cacheKey, $data, 600);
        }

        return view('home/index', $data);
    }
}
