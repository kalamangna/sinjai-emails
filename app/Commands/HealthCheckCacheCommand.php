<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Shared\Services\SystemHealthService;

class HealthCheckCacheCommand extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'health:check-cache';
    protected $description = 'Refresh external services health status cache (cPanel, BSrE, Pegawai API).';

    public function run(array $params)
    {
        CLI::write('Refreshing system health cache...', 'yellow');

        $healthService = new SystemHealthService();
        $results = $healthService->refreshCache();

        CLI::write('System health cache successfully refreshed!', 'green');

        foreach ($results as $key => $service) {
            $statusStr = $service['status'] === 'UP' ? 'UP' : 'DOWN';
            $color = $service['status'] === 'UP' ? 'green' : 'red';
            CLI::write(sprintf("- %s: %s (%s)", $service['label'], $statusStr, $service['message']), $color);
        }
    }
}
