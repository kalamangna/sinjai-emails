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
        '--daily'   => 'Menjalankan tugas harian (Status TTE Pimpinan)',
        '--weekly'  => 'Menjalankan tugas mingguan (cPanel dan Website)',
        '--monthly' => 'Menjalankan tugas bulanan (Data ASN dan TTE Pegawai)',
        '--pegawai' => 'Hanya menjalankan sinkronisasi data pegawai (Pangkat/Golongan/Jabatan/Mutasi)',
        '--tte'     => 'Hanya menjalankan sinkronisasi status TTE BSrE',
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
        $isPegawai = CLI::getOption('pegawai') !== null;
        $isTte = CLI::getOption('tte') !== null;
        $runAll = !$isDaily && !$isWeekly && !$isMonthly && !$isPegawai && !$isTte;

        $this->telegram = new TelegramLibrary();
        $modeName = $runAll ? 'PENUH' : ($isPegawai ? 'DATA PEGAWAI' : ($isTte ? 'STATUS TTE' : ($isDaily ? 'HARIAN' : ($isWeekly ? 'MINGGUAN' : 'BULANAN'))));
        
        CLI::write("Starting Synchronization Process ($modeName)...", 'blue');
        $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
        $builder->setTitle("SINKRONISASI $modeName DIMULAI", '🔄');

        if ($isDaily) {
            $builder->addKeyValue('Objek', 'TTE Pimpinan', '🎯');
        } elseif ($isWeekly) {
            $builder->addKeyValue('Objek', 'Email & Website', '🎯');
        } elseif ($isMonthly) {
            $builder->addKeyValue('Objek', 'Data & TTE Pegawai', '🎯');
        } elseif ($isPegawai) {
            $builder->addKeyValue('Objek', 'Data Pegawai (SIMPEG)', '🎯');
        } elseif ($isTte) {
            $builder->addKeyValue('Objek', 'Status TTE (BSrE)', '🎯');
        } else {
            $builder->addKeyValue('Objek', 'Semua Data', '🎯');
        }

        $this->telegram->sendMessage($builder->build());

        // Phase: TTE Harian (Khusus Pimpinan jika --daily)
        if ($isDaily) {
            $this->syncTteStatus('pimpinan');
        }
        
        // Phase: cPanel & Website (Mingguan / All)
        if ($runAll || $isWeekly) {
            $this->syncCpanel();
            $this->syncWebExpirations();
        }

        // Phase: Pegawai & TTE Pegawai
        if ($runAll || $isMonthly || $isTte) {
            $this->syncTteStatus('pegawai');
        }

        if ($runAll || $isMonthly || $isPegawai) {
            $this->syncPegawaiData();
        }

        // Phase: Auto Pensiun BUP Check
        if ($runAll || $isMonthly || $isPegawai || $isDaily) {
            $this->detectAutoPensiun();
        }

        // Phase: Cleanup (Setiap kali sinkronisasi)
        $this->cleanupRetiredAccounts();

        // Clear Dashboard Cache so timestamps update immediately
        \App\Shared\Services\CacheService::invalidateDashboard();

        // Push final summary job to queue so it only fires after all batch jobs finish
        $jobModel = new JobModel();
        $jobModel->push('default', [
            'type' => 'sync_summary',
            'data' => [
                'mode'       => $modeName,
                'started_at' => date('Y-m-d H:i:s'),
                'stats'      => $this->syncStats
            ]
        ]);

        CLI::write('Synchronization jobs dispatched and cache cleared!', 'green');
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
            $this->saveLastSyncTime('last_sync_cpanel');
        } catch (\Throwable $e) {
            CLI::error('ERROR in Phase 1: ' . $e->getMessage());
            $this->syncStats['cpanel']['fail'] = 1;
        }
    }



    private function syncTteStatus(string $scope = 'pimpinan')
    {
        $scopeText = $scope === 'pegawai' ? 'Pegawai (Non-Pimpinan)' : ($scope === 'all' ? 'Semua Akun' : 'Pimpinan');
        CLI::write("--- Phase 2: TTE Status Synchronization ($scopeText - Queued) ---", 'yellow');
        $this->syncStats['tte']['executed'] = true;
        $this->syncStats['tte']['scope'] = $scope;
        try {
            $emailModel = new EmailModel();
            $jobModel = new JobModel();

            $builder = $emailModel->select('id, email')
                                  ->where('deleted_at IS NULL')
                                  ->where('unit_kerja_id IS NOT NULL');

            if ($scope === 'pegawai') {
                // Khusus Pegawai Non-Pimpinan yang memiliki NIP dan terdaftar di unit kerja
                $builder->where('(pimpinan = 0 OR pimpinan IS NULL)')
                        ->where('(pimpinan_desa = 0 OR pimpinan_desa IS NULL)')
                        ->where('nip IS NOT NULL')
                        ->where('nip !=', '');
            } elseif ($scope === 'pimpinan') {
                // Khusus Pimpinan & Pimpinan Desa yang terdaftar di unit kerja
                $builder->groupStart()
                            ->where('pimpinan', 1)
                            ->orWhere('pimpinan_desa', 1)
                        ->groupEnd();
            } else {
                // 'all': Seluruh akun (Pimpinan & Pegawai)
                $builder->where('nip IS NOT NULL')
                        ->where('nip !=', '');
            }

            $emails = $builder->findAll();

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



    private function syncPegawaiData()
    {
        CLI::write('--- Phase 3: Pegawai Data Synchronization (Queued) ---', 'yellow');
        $this->syncStats['pegawai']['executed'] = true;
        try {
            $emailModel = new EmailModel();
            $statusAsnModel = new StatusAsnModel();
            $jobModel = new JobModel();

            $statusPns = $statusAsnModel->where('nama_status_asn', 'PNS')->asArray()->first();
            $pnsStatusId = $statusPns['id'] ?? 1;

            $builder = $emailModel->select('nip')
                ->where('emails.nip IS NOT NULL')
                ->where('emails.nip !=', '')
                ->where('emails.status_asn_id', $pnsStatusId);

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
        CLI::write('--- Phase 4: Website Expiration & Hosting Synchronization ---', 'yellow');
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

                $existingIp       = $website['ip_address'] ?? null;
                $existingProvider = $website['hosting_provider'] ?? null;

                $newDate     = $websiteService->determineExpirationDate($website['desa_kelurahan'], $website['domain'], null);
                $hostingInfo = $websiteService->getHostingInfo($website['domain'], $existingIp, $existingProvider);

                $updateData = [];
                if ($newDate) {
                    $updateData['tanggal_berakhir'] = $newDate;
                    $updateData['sisa_hari'] = $websiteService->calculateDaysRemaining($newDate);
                }
                if ($hostingInfo) {
                    $updateData['ip_address'] = $hostingInfo['ip'];
                    $updateData['hosting_provider'] = $hostingInfo['provider'];
                    $updateData['hosting_status'] = $hostingInfo['status'];
                }

                if (!empty($updateData)) {
                    $webDesaModel->update($website['id'], $updateData);
                    CLI::write(($newDate ?: 'No Expir') . " | IP: " . ($hostingInfo['ip'] ?: 'None') . " | ISP: " . ($hostingInfo['provider'] ?: 'None') . " | Port: " . $hostingInfo['status'], 'green');
                    $this->syncStats['website']['success']++;
                } else {
                    CLI::write('FAILED', 'red');
                    $this->syncStats['website']['fail']++;
                }
            }
            CLI::write("Website & Hosting Sync Finished. Success: " . $this->syncStats['website']['success'] . ", Failed: " . $this->syncStats['website']['fail'], 'cyan');
            $this->saveLastSyncTime('last_sync_website');
        } catch (\Throwable $e) {
            CLI::error('ERROR in Phase 4: ' . $e->getMessage());
        }
    }



    private function detectAutoPensiun()
    {
        CLI::write('--- Phase: Auto Pensiun Detection (BUP) ---', 'yellow');
        try {
            $emailModel = new EmailModel();
            $emailService = new \App\Domains\Email\Services\EmailService();

            $accounts = $emailModel->select('emails.*, unit_kerja.nama_unit_kerja as unit_kerja_name')
                                   ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                                   ->where('emails.deleted_at IS NULL')
                                   ->where('emails.pensiun_at IS NULL')
                                   ->where('emails.nip IS NOT NULL')
                                   ->where('emails.nip !=', '')
                                   ->findAll();

            $retiredCount = 0;
            foreach ($accounts as $acc) {
                $bupInfo = $emailService->calculateBupInfo($acc);
                if ($bupInfo['is_pensiun']) {
                    $bupAge = $bupInfo['bup_age'];
                    $tmt = $bupInfo['tmt_pensiun'];
                    $reason = "Mencapai Batas Usia Pensiun (BUP {$bupAge} Thn - TMT {$tmt})";
                    
                    CLI::print("Auto retiring {$acc['email']} (BUP {$bupAge} th, TMT {$tmt})... ");
                    if ($emailService->processAutoPensiun($acc, $reason)) {
                        CLI::write('DONE', 'green');
                        $retiredCount++;
                    } else {
                        CLI::write('FAILED', 'red');
                    }
                }
            }

            if ($retiredCount === 0) {
                CLI::write('No active accounts reached BUP.', 'green');
            } else {
                CLI::write("Auto Pensiun completed. Total retired: {$retiredCount}", 'green');
            }
        } catch (\Throwable $e) {
            CLI::error('ERROR in Auto Pensiun Detection: ' . $e->getMessage());
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
                    // 1. Delete from cPanel (abaikan error jika akun sudah tidak ada di cPanel)
                    try {
                        $cpanelApi->delete_email_account($acc['email']);
                    } catch (\Throwable $cpanelEx) {
                        log_message('notice', "cPanel delete skipped for {$acc['email']}: " . $cpanelEx->getMessage());
                    }
                    
                    // 2. Delete from local DB (Hard Delete)
                    $emailModel->delete($acc['id'], true);
                    
                    $name = $acc['name'] ?: $acc['email'];
                    $deletedList[] = "<b>{$name}</b> (<code>{$acc['email']}</code>)";
                    CLI::write('DONE', 'green');
                } catch (\Throwable $e) {
                    CLI::write('FAILED: ' . $e->getMessage(), 'red');
                }
            }

            if (!empty($deletedList)) {
                $totalDeleted = count($deletedList);
                $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
                $builder->setTitle('PEMBERSIHAN AKUN OTOMATIS', '🧹');
                $builder->addText("🗑️ <b>$totalDeleted Akun Pensiun Dihapus Permanen:</b>");

                foreach (array_slice($deletedList, 0, 5) as $item) {
                    $builder->addBullet($item);
                }
                if ($totalDeleted > 5) {
                    $builder->addItalicText("...dan " . ($totalDeleted - 5) . " akun lainnya.");
                }

                $this->telegram->sendMessage($builder->build());
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
