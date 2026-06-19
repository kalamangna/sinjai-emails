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
                    $this->handleSyncTte($payload['data']);
                    break;
                case 'sync_pegawai_batch':
                    $this->handleSyncPegawai($payload['data']);
                    break;
                case 'sync_cpanel':
                    $this->handleSyncCpanel();
                    break;
                case 'sync_quota_report':
                    $alertService = new \App\Shared\Services\AlertService();
                    $alertService->checkQuotaAlerts();
                    break;
                case 'sync_tte_report':
                    $this->handleSyncTteReport();
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
                $msg = "🚨 <b>CRITICAL ERROR: QUEUE WORKER</b>\n";
                $msg .= "Tugas sinkronisasi gagal secara permanen!\n";
                $msg .= "------------------------------------------\n";
                $msg .= "📋 ID Job: <b>{$job['id']}</b>\n";
                $msg .= "🔄 Tipe: <b>$type</b>\n";
                $msg .= "❌ Error: " . $e->getMessage();
                $telegram->sendMessage($msg);
                $jobModel->delete($job['id']);
            }
        }
    }

    private function handleSyncTte($emailList)
    {
        $emailModel = new \App\Domains\Email\Models\EmailModel();
        $bsreApi = new \App\Shared\Libraries\BsreApi();
        
        foreach ($emailList as $email) {
            $result = $bsreApi->checkStatus($email['email'], 'email');
            if ($result['success']) {
                $responseBody = $result['data'];
                $statusFromBsre = $responseBody['status'] ?? ($responseBody['data']['status'] ?? 'UNKNOWN');
                $emailModel->update($email['id'], ['bsre_status' => $statusFromBsre]);
            }
        }
    }

    private function handleSyncPegawai($nipList)
    {
        $emailModel = new \App\Domains\Email\Models\EmailModel();
        $pegawaiApi = new \App\Shared\Libraries\PegawaiApi();
        
        foreach ($nipList as $nip) {
            $result = $pegawaiApi->getPegawaiData($nip);
            if ($result['success']) {
                $data = $result['data'];
                $source = (is_array($data) && isset($data[0])) ? $data[0] : $data;
                
                if (isset($source['pangkat_nama']) || isset($source['pangkat_golruang'])) {
                    $updateData = [
                        'pangkat_nama' => $source['pangkat_nama'] ?? null,
                        'pangkat_golruang' => $source['pangkat_golruang'] ?? null
                    ];
                    if (isset($source['jabatan'])) {
                        $updateData['jabatan'] = mb_strtoupper($source['jabatan'], 'UTF-8');
                    }
                    $emailModel->where('nip', $nip)->set($updateData)->update();
                }
            }
        }
    }

    private function handleSyncCpanel()
    {
        $syncService = new \App\Shared\Services\SyncService();
        $syncService->syncFromCpanel();
    }

    private function handleSyncTteReport()
    {
        // Panggil logika terpusat dari AlertService (jangan kirim pesan 'aman' jika kosong agar tidak spam)
        $alertService = new \App\Shared\Services\AlertService();
        $alertService->checkTteExpiredAlerts(false);
    }
}
