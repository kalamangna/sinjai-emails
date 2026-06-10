<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Shared\Services\SyncService;
use App\Shared\Libraries\BsreApi;
use App\Shared\Libraries\PegawaiApi;
use App\Shared\Libraries\TelegramLibrary;
use App\Domains\Email\EmailModel;
use App\Shared\Models\StatusAsnModel;
use App\Shared\Models\EselonModel;
use App\Shared\Models\JobModel;

use App\Domains\Website\WebDesaKelurahanModel;
use App\Domains\Website\WebsiteService;

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
        $this->telegram->sendMessage("🔄 <b>Sinkronisasi $modeName Dimulai</b>\nSistem sedang memperbarui data...");

        // Phase: TTE (Harian / All)
        if ($runAll || $isDaily) {
            $this->syncTteStatus();
            $this->checkTteExpiredAlerts();
        }
        
        // Phase: cPanel (Mingguan / All)
        if ($runAll || $isWeekly) {
            $this->syncCpanel();
            $this->checkQuotaAlerts();
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
        $msg = "✅ <b>Sinkronisasi $mode Selesai</b>\n\n";

        if (isset($this->syncStats['cpanel']['executed'])) {
            $status = $this->syncStats['cpanel']['success'] > 0 ? "🟢 Berhasil" : "🔴 Gagal";
            $msg .= "📧 <b>cPanel Sync</b>: $status\n";
        }

        if (isset($this->syncStats['tte']['executed'])) {
            $msg .= "✍️ <b>TTE Sync</b>: " . $this->syncStats['tte']['success'] . " Berhasil, " . $this->syncStats['tte']['fail'] . " Gagal\n";
        }

        if (isset($this->syncStats['pegawai']['executed'])) {
            $msg .= "👥 <b>Pegawai Sync</b>: " . $this->syncStats['pegawai']['success'] . " Update, " . $this->syncStats['pegawai']['skipped'] . " Tetap, " . $this->syncStats['pegawai']['fail'] . " Gagal\n";
        }

        if (isset($this->syncStats['website']['executed'])) {
            $msg .= "🌐 <b>Website Sync</b>: " . $this->syncStats['website']['success'] . " Berhasil, " . $this->syncStats['website']['fail'] . " Gagal\n";
        }

        $msg .= "\n🕒 " . date('d M Y H:i:s');
        $this->telegram->sendMessage($msg);
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
            CLI::write('SUCCESS: Job dispatched to queue.', 'green');
            $this->syncStats['cpanel']['success'] = 1;
        } catch (\Throwable $e) {
            CLI::error('ERROR in Phase 1: ' . $e->getMessage());
            $this->syncStats['cpanel']['fail'] = 1;
        }
    }

    private function checkQuotaAlerts()
    {
        CLI::write('Checking for High Quota Usage Alerts...', 'yellow');
        try {
            $emailModel = new EmailModel();
            $highUsageAccounts = $emailModel->select('emails.*, unit_kerja.nama_unit_kerja as unit_name')
                                            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                                            ->where('emails.diskusedpercent_float >=', 90)
                                            ->orderBy('emails.diskusedpercent_float', 'DESC')
                                            ->findAll();
            
            if (!empty($highUsageAccounts)) {
                $count = count($highUsageAccounts);
                CLI::write("Found $count accounts with high usage (>90%)", 'red');
                
                $msg = "⚠️ <b>PERINGATAN KUOTA EMAIL</b>\n";
                $msg .= "Ditemukan <b>$count</b> akun dengan penggunaan > 90%:\n";
                $msg .= "------------------------------------------\n\n";
                
                foreach (array_slice($highUsageAccounts, 0, 10) as $acc) {
                    $msg .= "👤 " . $acc['name'] . " (" . ($acc['nip'] ?: '-') . ")\n";
                    $msg .= "💼 " . ($acc['jabatan'] ?: '-') . "\n";
                    $msg .= "🏛️ " . ($acc['unit_name'] ?: '-') . "\n";
                    $msg .= "📧 " . $acc['email'] . "\n";
                    $msg .= "📊 Penggunaan: <b>" . $acc['humandiskused'] . "</b> (" . round($acc['diskusedpercent_float'], 1) . "%)\n\n";
                }
                
                if ($count > 10) {
                    $msg .= "...dan " . ($count - 10) . " akun lainnya.";
                }
                
                $this->telegram->sendMessage($msg);
            } else {
                CLI::write('No high usage accounts found.', 'green');
            }
        } catch (\Throwable $e) {
            CLI::error('Error checking quota alerts: ' . $e->getMessage());
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

            CLI::write("SUCCESS: " . count($chunks) . " jobs dispatched to queue.", 'green');
            $this->syncStats['tte']['success'] = $total;
            $this->saveLastSyncTime('last_sync_tte');
        } catch (\Throwable $e) {
            CLI::error('ERROR in Phase 2: ' . $e->getMessage());
        }
    }

    private function checkTteExpiredAlerts()
    {
        CLI::write('Checking for Expired TTE Status Alerts...', 'yellow');
        try {
            $emailModel = new EmailModel();
            
            // 1. Get TOTAL count of all leadership expired accounts
            $totalExpiredCount = $emailModel->where('bsre_status', 'EXPIRED')
                ->groupStart()
                    ->where('pimpinan', 1)
                    ->orWhere('pimpinan_desa', 1)
                ->groupEnd()
                ->countAllResults();
            
            if ($totalExpiredCount === 0) {
                CLI::write('No expired TTE accounts found.', 'green');
                return;
            }

            // 2. Get detailed data for LEADERSHIP only
            $expiredPimpinan = $emailModel->select('emails.email, emails.name, emails.nip, emails.jabatan, unit_kerja.nama_unit_kerja as unit_name')
                                          ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                                          ->where('emails.bsre_status', 'EXPIRED')
                                          ->groupStart()
                                              ->where('emails.pimpinan', 1)
                                              ->orWhere('emails.pimpinan_desa', 1)
                                          ->groupEnd()
                                          ->findAll();
            
            $pimpinanCount = count($expiredPimpinan);
            CLI::write("Total Expired: $totalExpiredCount, Pimpinan Expired: $pimpinanCount", 'cyan');
            
            // 3. Construct Telegram Message
            $msg = "🔔 <b>LAPORAN TTE PIMPINAN</b>\n\n";
            $msg .= "📊 Pimpinan Expired: <b>$totalExpiredCount</b> Akun\n";
            $msg .= "------------------------------------------\n\n";

            if ($pimpinanCount > 0) {
                $msg .= "⚠️ <b>DETAIL PIMPINAN EXPIRED</b>\n";
                $msg .= "Ditemukan <b>$pimpinanCount</b> pimpinan:\n\n";
                
                foreach (array_slice($expiredPimpinan, 0, 10) as $acc) {
                    $msg .= "👤 " . $acc['name'] . " (" . ($acc['nip'] ?: '-') . ")\n";
                    $msg .= "💼 " . ($acc['jabatan'] ?: '-') . "\n";
                    $msg .= "🏛️ " . ($acc['unit_name'] ?: '-') . "\n";
                    $msg .= "📧 " . $acc['email'] . "\n\n";
                }
                
                if ($pimpinanCount > 10) {
                    $msg .= "...dan " . ($pimpinanCount - 10) . " pimpinan lainnya.";
                }
            } else {
                $msg .= "✅ Tidak ada data pimpinan yang expired.";
            }
            
            $this->telegram->sendMessage($msg);

        } catch (\Throwable $e) {
            CLI::error('Error checking TTE expired alerts: ' . $e->getMessage());
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
                ->where('emails.nip !=', '');

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
            $this->checkWebExpirationAlerts();
        } catch (\Throwable $e) {
            CLI::error('ERROR in Phase 4: ' . $e->getMessage());
        }
    }

    private function checkWebExpirationAlerts()
    {
        CLI::write('Checking for Expiring Website Domains...', 'yellow');
        try {
            $webDesaModel = new WebDesaKelurahanModel();
            
            // Look for domains expiring in 30 days or less
            $expiringWebs = $webDesaModel->where('sisa_hari <=', 30)
                                         ->orderBy('sisa_hari', 'ASC')
                                         ->findAll();
            
            if (!empty($expiringWebs)) {
                $count = count($expiringWebs);
                CLI::write("Found $count website domains expiring soon", 'red');
                
                $msg = "🌐 <b>PERINGATAN MASA AKTIF WEBSITE</b>\n";
                $msg .= "Ditemukan <b>$count</b> domain desa/kelurahan yang akan kadaluwarsa dalam 30 hari:\n\n";
                
                foreach (array_slice($expiringWebs, 0, 10) as $web) {
                    $msg .= "💻 <b>" . $web['domain'] . "</b>\n";
                    $msg .= "🏛️ " . $web['desa_kelurahan'] . "\n";
                    $msg .= "⏳ Sisa: <b>" . $web['sisa_hari'] . " Hari</b> (s.d " . date('d M Y', strtotime($web['tanggal_berakhir'])) . ")\n\n";
                }
                
                if ($count > 10) {
                    $msg .= "...dan " . ($count - 10) . " domain lainnya.";
                }
                
                $this->telegram->sendMessage($msg);
            } else {
                CLI::write('No expiring website domains found.', 'green');
            }
        } catch (\Throwable $e) {
            CLI::error('Error checking website expiration alerts: ' . $e->getMessage());
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
            $toDelete = $emailModel->where('pensiun_at IS NOT NULL')
                                   ->where('pensiun_at <=', $thirtyDaysAgo)
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
                    
                    // 2. Delete from local DB
                    $emailModel->delete($acc['id']);
                    
                    $deletedList[] = "• " . ($acc['name'] ?: $acc['email']);
                    CLI::write('DONE', 'green');
                } catch (\Throwable $e) {
                    CLI::write('FAILED: ' . $e->getMessage(), 'red');
                }
            }

            if (!empty($deletedList)) {
                $msg = "🧹 <b>LAPORAN PEMBERSIHAN PENSIUN</b>\n";
                $msg .= "Akun berikut telah dihapus permanen (melewati masa tunggu 30 hari):\n\n";
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
