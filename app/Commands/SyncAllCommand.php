<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Shared\Services\SyncService;
use App\Shared\Libraries\BsreApi;
use App\Shared\Libraries\PegawaiApi;
use App\Shared\Libraries\TelegramLibrary;
use App\Domains\Email\Models\EmailModel;
use App\Shared\Models\StatusAsnModel;
use App\Shared\Models\EselonModel;
use App\Shared\Models\JobModel;

use App\Domains\Website\Models\WebDesaKelurahanModel;
use App\Domains\Website\Services\WebsiteService;

class SyncAllCommand extends BaseCommand
{
    protected $telegram;
    protected $syncStats = [
        'cpanel' => ['success' => 0, 'fail' => 0],
        'tte'    => ['success' => 0, 'fail' => 0],
        'pegawai' => ['success' => 0, 'fail' => 0, 'skipped' => 0],
        'website' => ['success' => 0, 'fail' => 0],
    ];

    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'sync:all';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Synchronize cPanel, TTE status, Pegawai data, and Website expirations automatically.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'sync:all';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--daily'   => 'Menjalankan tugas harian (Status TTE)',
        '--weekly'  => 'Menjalankan tugas mingguan (cPanel)',
        '--monthly' => 'Menjalankan tugas bulanan (Pegawai dan Website)',
    ];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $isDaily = CLI::getOption('daily') !== null;
        $isWeekly = CLI::getOption('weekly') !== null;
        $isMonthly = CLI::getOption('monthly') !== null;
        $runAll = !$isDaily && !$isWeekly && !$isMonthly;

        $this->telegram = new TelegramLibrary();
        $modeName = $runAll ? 'PENUH' : ($isDaily ? 'HARIAN' : ($isWeekly ? 'MINGGUAN' : 'BULANAN'));
        
        CLI::write("Starting Synchronization Process ($modeName)...", 'blue');
        $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
        $builder->setTitle('SINKRONISASI SISTEM BERJALAN', '🔄')
                ->addText("Sistem mengeksekusi sinkronisasi $modeName...")
                ->addDivider();
                
        $this->telegram->sendMessage($builder->build());

        // Phase: TTE (Harian / All)
        if ($runAll || $isDaily) {
            $this->syncTteStatus();
        }
        
        // Phase: cPanel (Mingguan / All)
        if ($runAll || $isWeekly) {
            $this->syncCpanel();
        }

        // Phase: Pegawai & Website (Bulanan / All)
        if ($runAll || $isMonthly) {
            $this->syncPegawaiData();
            $this->syncWebExpirations();
        }

        // Phase: Cleanup (Setiap kali sinkronisasi)
        $this->cleanupRetiredAccounts();

        CLI::write('Synchronization process completed!', 'green');
        $this->sendTelegramSummary($modeName);
    }

    private function sendTelegramSummary($mode)
    {
        $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
        $builder->setTitle("SINKRONISASI $mode SELESAI", '✅')
                ->addDivider();

        if (isset($this->syncStats['cpanel']['executed'])) {
            $status = $this->syncStats['cpanel']['success'] > 0 ? "🟢 Berhasil" : "🔴 Gagal";
            $builder->addKeyValue('cPanel Sync', $status, '📧');
        }

        if (isset($this->syncStats['tte']['executed'])) {
            $builder->addKeyValue('TTE Sync', $this->syncStats['tte']['success'] . " Berhasil, " . $this->syncStats['tte']['fail'] . " Gagal", '✍️');
        }

        if (isset($this->syncStats['pegawai']['executed'])) {
            $builder->addKeyValue('Pegawai Sync', $this->syncStats['pegawai']['success'] . " Update, " . $this->syncStats['pegawai']['skipped'] . " Tetap, " . $this->syncStats['pegawai']['fail'] . " Gagal", '👥');
        }

        if (isset($this->syncStats['website']['executed'])) {
            $builder->addKeyValue('Website Sync', $this->syncStats['website']['success'] . " Berhasil, " . $this->syncStats['website']['fail'] . " Gagal", '🌐');
        }

        $builder->addText("\n🕒 " . date('d M Y, H:i:s'));
        $this->telegram->sendMessage($builder->build());
    }

    private function syncCpanel()
    {
        CLI::write('--- Phase 1: cPanel Synchronization (Queued) ---', 'yellow');
        $this->syncStats['cpanel']['executed'] = true;
        try {
            $jobModel = new JobModel();
            $jobModel->push('default', [
                'type' => 'sync_cpanel',
                'data' => []
            ]);
            $jobModel->push('default', [
                'type' => 'sync_quota_report',
                'data' => []
            ]);
            CLI::write('SUCCESS: Job dispatched to queue.', 'green');
            $this->syncStats['cpanel']['success'] = 1;
        } catch (\Throwable $e) {
            CLI::error('ERROR in Phase 1: ' . $e->getMessage());
            $this->syncStats['cpanel']['fail'] = 1;
        }
    }



    private function syncTteStatus()
    {
        CLI::write('--- Phase 2: TTE Status Synchronization (Queued) ---', 'yellow');
        $this->syncStats['tte']['executed'] = true;
        try {
            $emailModel = new EmailModel();
            $jobModel = new JobModel();

            $emails = $emailModel->select('id, email')
                ->groupStart()
                    ->where('pimpinan', 1)
                    ->orWhere('pimpinan_desa', 1)
                ->groupEnd()
                ->findAll();

            $total = count($emails);
            CLI::write("Total accounts to queue: $total");

            // Chunk emails into groups of 50 per job
            $chunks = array_chunk($emails, 50);
            foreach ($chunks as $chunk) {
                $jobModel->push('default', [
                    'type' => 'sync_tte_batch',
                    'data' => $chunk
                ]);
            }

            // Push a final job to generate and send the Telegram report for EXPIRED TTEs
            $jobModel->push('default', [
                'type' => 'sync_tte_report',
                'data' => []
            ]);

            CLI::write("SUCCESS: " . count($chunks) . " jobs dispatched to queue, plus 1 report job.", 'green');
            $this->syncStats['tte']['success'] = $total;
            $this->saveLastSyncTime('last_sync_tte');
        } catch (\Throwable $e) {
            CLI::error('ERROR in Phase 2: ' . $e->getMessage());
        }
    }



    private function syncPegawaiData()
    {
        CLI::write('--- Phase 3: Pegawai Data Synchronization (Queued) ---', 'yellow');
        $this->syncStats['pegawai']['executed'] = true;
        try {
            $emailModel = new EmailModel();
            $statusAsnModel = new StatusAsnModel();
            $jobModel = new JobModel();

            $statusPppkPw = $statusAsnModel->where('nama_status_asn', 'PPPK PARUH WAKTU')->asArray()->first();
            $pppkPwId = $statusPppkPw['id'] ?? null;

            $builder = $emailModel->select('nip')
                ->where('emails.nip IS NOT NULL')
                ->where('emails.nip !=', '')
                ->where('(emails.pimpinan = 0 OR emails.pimpinan IS NULL)')
                ->where('(emails.pimpinan_desa = 0 OR emails.pimpinan_desa IS NULL)');

            if ($pppkPwId) {
                $builder->where('emails.status_asn_id !=', $pppkPwId);
            }

            $nips = array_column($builder->findAll(), 'nip');
            $nips = array_unique($nips);
            $total = count($nips);

            CLI::write("Total NIPs to queue: $total");

            // Chunk NIPs into groups of 50 per job
            $chunks = array_chunk($nips, 50);
            foreach ($chunks as $chunk) {
                $jobModel->push('default', [
                    'type' => 'sync_pegawai_batch',
                    'data' => $chunk
                ]);
            }

            CLI::write("SUCCESS: " . count($chunks) . " jobs dispatched to queue.", 'green');
            $this->syncStats['pegawai']['success'] = $total;
            $this->saveLastSyncTime('last_sync_pegawai');
        } catch (\Throwable $e) {
            CLI::error('ERROR in Phase 3: ' . $e->getMessage());
        }
    }

    private function syncWebExpirations()
    {
        CLI::write('--- Phase 4: Website Expiration Synchronization ---', 'yellow');
        $this->syncStats['website']['executed'] = true;
        try {
            $webDesaModel = new WebDesaKelurahanModel();
            $websiteService = new WebsiteService();
            
            $websites = $webDesaModel->findAll();
            $total = count($websites);
            CLI::write("Total websites to sync: $total");
            
            foreach ($websites as $index => $website) {
                $count = $index + 1;
                CLI::print("[$count/$total] Syncing {$website['domain']}... ");
                
                $newDate = $websiteService->determineExpirationDate($website['desa_kelurahan'], $website['domain'], null);
                
                if ($newDate) {
                    $updateData = [
                        'tanggal_berakhir' => $newDate,
                        'sisa_hari' => $websiteService->calculateDaysRemaining($newDate)
                    ];
                    $webDesaModel->update($website['id'], $updateData);
                    CLI::write($newDate, 'green');
                    $this->syncStats['website']['success']++;
                } else {
                    CLI::write('FAILED', 'red');
                    $this->syncStats['website']['fail']++;
                }
            }
            CLI::write("Website Expiration Sync Finished. Success: " . $this->syncStats['website']['success'] . ", Failed: " . $this->syncStats['website']['fail'], 'cyan');
            $this->saveLastSyncTime('last_sync_website');

            // Check for expiring website domains alerts
            (new \App\Shared\Services\AlertService())->checkWebExpirationAlerts();
        } catch (\Throwable $e) {
            CLI::error('ERROR in Phase 4: ' . $e->getMessage());
        }
    }



    private function cleanupRetiredAccounts()
    {
        CLI::write('--- Phase 5: Cleanup Retired Accounts (30 Days Rule) ---', 'yellow');
        try {
            $emailModel = new EmailModel();
            $cpanelApi = new \App\Shared\Libraries\CpanelApi();
            
            // Look for accounts marked as retired more than 30 days ago
            $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));
            $toDelete = $emailModel->withDeleted()
                                   ->groupStart()
                                       ->groupStart()
                                           ->where('pensiun_at IS NOT NULL')
                                           ->where('pensiun_at <=', $thirtyDaysAgo)
                                       ->groupEnd()
                                       ->orGroupStart()
                                           ->where('deleted_at IS NOT NULL')
                                           ->where('deleted_at <=', $thirtyDaysAgo)
                                       ->groupEnd()
                                   ->groupEnd()
                                   ->findAll();
            
            if (empty($toDelete)) {
                CLI::write('No retired accounts to cleanup.', 'green');
                return;
            }

            $deletedList = [];
            foreach ($toDelete as $acc) {
                CLI::print("Deleting retired account: {$acc['email']}... ");
                try {
                    // 1. Delete from cPanel
                    $cpanelApi->delete_email_account($acc['email']);
                    
                    // 2. Delete from local DB (Hard Delete)
                    $emailModel->delete($acc['id'], true);
                    
                    $deletedList[] = "• " . ($acc['name'] ?: $acc['email']);
                    CLI::write('DONE', 'green');
                } catch (\Throwable $e) {
                    CLI::write('FAILED: ' . $e->getMessage(), 'red');
                }
            }

            if (!empty($deletedList)) {
                $msg = "🧹 <b>LAPORAN PEMBERSIHAN OTOMATIS</b>\n";
                $msg .= "Akun berikut telah dihapus permanen (melewati masa tunggu 30 hari):\n";
                $msg .= "------------------------------------------\n\n";
                $msg .= implode("\n", $deletedList);
                $this->telegram->sendMessage($msg);
            }

        } catch (\Throwable $e) {
            CLI::error('Error during cleanup: ' . $e->getMessage());
        }
    }

    private function saveLastSyncTime($key)
    {
        require_once APPPATH . 'Shared/Helpers/TanggalHelper.php';
        $now = untukDatabase('now');
        $appSettingModel = new \App\Shared\Models\AppSettingModel();

        $appSettingModel->where('key', $key)->set(['value' => $now])->update();
        if ($appSettingModel->affectedRows() == 0) {
            $appSettingModel->insert(['key' => $key, 'value' => $now]);
        }
    }
}
