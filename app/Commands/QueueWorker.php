<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Shared\Models\JobModel;

class QueueWorker extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'queue:work';
    protected $description = 'Process background jobs from the queue.';

    protected $options = [
        '--stop-when-empty' => 'Stop the worker when the queue is empty instead of sleeping forever.'
    ];

    public function run(array $params)
    {
        $jobModel = new JobModel();
        $queue = $params['queue'] ?? 'default';
        $stopWhenEmpty = \CodeIgniter\CLI\CLI::getOption('stop-when-empty') !== null;
        $processedTypes = [];

        CLI::write("Queue worker started for queue: [$queue]", 'green');
        if ($stopWhenEmpty) {
            CLI::write("Mode: Stop when empty (Cron mode)", 'yellow');
        }
        
        while (true) {
            $job = $jobModel->getNextJob($queue);

            if ($job) {
                $this->process($job, $jobModel, $processedTypes);
            } else {
                if ($stopWhenEmpty) {
                    CLI::write("Queue is empty. Stopping worker.", 'yellow');
                    
                    // Clear caches because background sync changed data
                    $cache = \Config\Services::cache();
                    $cache->delete('dashboard_summary_data_v3');
                    $cache->delete('email_dashboard_summary');
                    CLI::write("Dashboard caches cleared.", 'green');
                    
                    break;
                }
                // Sleep for 2 seconds if no jobs found to save CPU
                sleep(2);
            }
        }
    }

    private function process($job, $jobModel, &$processedTypes)
    {
        $payload = json_decode($job['payload'], true);
        $type = $payload['type'] ?? 'unknown';
        $processedTypes[$type] = true;

        CLI::write("[" . date('H:i:s') . "] Processing job #{$job['id']} ($type)...", 'yellow');

        try {
            switch ($type) {
                case 'sync_tte_batch':
                    $tteService = new \App\Shared\Services\TteSyncService();
                    $tteService->processBatch($payload['data']);
                    break;
                case 'sync_pegawai_batch':
                    $pegawaiService = new \App\Shared\Services\PegawaiSyncService();
                    $pegawaiService->processBatch($payload['data']);
                    break;
                case 'sync_cpanel':
                    $syncService = new \App\Shared\Services\SyncService();
                    $syncService->syncFromCpanel();
                    break;
                case 'sync_quota_report':
                    $alertService = new \App\Shared\Services\AlertService();
                    $alertService->checkQuotaAlerts();
                    break;
                case 'sync_tte_report':
                    // Panggil logika terpusat dari AlertService (jangan kirim pesan 'aman' jika kosong agar tidak spam)
                    $alertService = new \App\Shared\Services\AlertService();
                    $alertService->checkTteExpiredAlerts(false);
                    break;
                default:
                    CLI::error("Unknown job type: $type");
            }

            // Success: Delete job
            $jobModel->delete($job['id']);
            CLI::write("Job #{$job['id']} completed.", 'green');
            
            // Alerts will be triggered at the end when queue is empty
        } catch (\Throwable $e) {
            CLI::error("Job #{$job['id']} failed: " . $e->getMessage());
            
            // If failed less than 3 times, release back to queue
            if ($job['attempts'] < 3) {
                $jobModel->update($job['id'], [
                    'reserved_at'  => null,
                    'available_at' => date('Y-m-d H:i:s', time() + 60) // Retry in 1 minute
                ]);
            } else {
                // Permanently failed
                CLI::error("Job #{$job['id']} permanently failed after 3 attempts.");
                $telegram = new \App\Shared\Libraries\TelegramLibrary();
                $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
                $builder->setTitle('CRITICAL ERROR: QUEUE WORKER', '🚨')
                        ->addText("Tugas sinkronisasi gagal secara permanen!")
                        ->addDivider()
                        ->addKeyValue('ID Job', "<b>{$job['id']}</b>", '📋')
                        ->addKeyValue('Tipe', "<b>$type</b>", '🔄')
                        ->addKeyValue('Error', $e->getMessage(), '❌');
                
                $telegram->sendMessage($builder->build());
                $jobModel->delete($job['id']);
            }
        }
    }
}
