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

    public function run(array $params)
    {
        $jobModel = new JobModel();
        $queue = $params[0] ?? 'default';

        CLI::write("Queue worker started for queue: [$queue]", 'green');
        
        while (true) {
            $job = $jobModel->getNextJob($queue);

            if ($job) {
                $this->process($job, $jobModel);
            } else {
                // Sleep for 2 seconds if no jobs found to save CPU
                sleep(2);
            }
        }
    }

    private function process($job, $jobModel)
    {
        $payload = json_decode($job['payload'], true);
        $type = $payload['type'] ?? 'unknown';

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
                default:
                    CLI::error("Unknown job type: $type");
            }

            // Success: Delete job
            $jobModel->delete($job['id']);
            CLI::write("Job #{$job['id']} completed.", 'green');

            // Check if this was the last job of its type by checking the JSON payload
            $remaining = $jobModel->like('payload', $type)->countAllResults();
            if ($remaining === 0) {
                $alertService = new \App\Shared\Services\AlertService();
                if ($type === 'sync_tte_batch') {
                    CLI::write("All TTE batch jobs completed. Triggering alerts...", 'blue');
                    $alertService->checkTteExpiredAlerts();
                } elseif ($type === 'sync_cpanel') {
                    CLI::write("All cPanel sync jobs completed. Triggering alerts...", 'blue');
                    $alertService->checkQuotaAlerts();
                }
            }
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
                $telegram->sendMessage("🚨 <b>CRITICAL ERROR: QUEUE WORKER</b>\nTugas sinkronisasi gagal secara permanen!\nID Job: {$job['id']}\nTipe: $type\nError: " . $e->getMessage());
                $jobModel->delete($job['id']);
            }
        }
    }

    private function handleSyncTte($emailList)
    {
        $emailModel = new \App\Domains\Email\EmailModel();
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
        $emailModel = new \App\Domains\Email\EmailModel();
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
                    $emailModel->where('nip_hash', hash('sha256', $nip))->set($updateData)->update();
                }
            }
        }
    }

    private function handleSyncCpanel()
    {
        $syncService = new \App\Shared\Services\SyncService();
        $syncService->syncFromCpanel();
    }
}
