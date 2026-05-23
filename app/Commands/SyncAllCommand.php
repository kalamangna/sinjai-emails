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
use App\Shared\Models\EmailStatsHistoryModel;

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
        '--daily'   => 'Menjalankan tugas harian (cPanel dan TTE)',
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
        $isMonthly = CLI::getOption('monthly') !== null;
        $runAll = !$isDaily && !$isMonthly;

        $this->telegram = new TelegramLibrary();
        $modeName = $runAll ? 'PENUH' : ($isDaily ? 'HARIAN' : 'BULANAN');
        
        CLI::write("Starting Synchronization Process ($modeName)...", 'blue');
        $this->telegram->sendMessage("🔄 <b>Sinkronisasi $modeName Dimulai</b>\nSistem sedang memperbarui data...");

        if ($runAll || $isDaily) {
            // 1. cPanel Synchronization
            $this->syncCpanel();
            
            // 2. TTE Status Synchronization
            $this->syncTteStatus();
        }
        
        if ($runAll || $isMonthly) {
            // 3. Pegawai Data Synchronization
            $this->syncPegawaiData();

            // 4. Website Expiration Synchronization
            $this->syncWebExpirations();
        }
        
        // Record stats history after daily or full sync
        if ($runAll || $isDaily) {
            $this->recordStatsHistory();
        }

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
        CLI::write('--- Phase 1: cPanel Synchronization ---', 'yellow');
        $this->syncStats['cpanel']['executed'] = true;
        try {
            $syncService = new SyncService();
            $result = $syncService->syncFromCpanel();
            if ($result['success']) {
                CLI::write('SUCCESS: ' . $result['message'], 'green');
                $this->syncStats['cpanel']['success'] = 1;
                
                // Check for high quota usage alerts
                $this->checkQuotaAlerts();
            } else {
                CLI::error('FAILED: ' . $result['message']);
                $this->syncStats['cpanel']['fail'] = 1;
            }
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
            $highUsageAccounts = $emailModel->where('diskusedpercent_float >=', 90)
                                            ->orderBy('diskusedpercent_float', 'DESC')
                                            ->findAll();
            
            if (!empty($highUsageAccounts)) {
                $count = count($highUsageAccounts);
                CLI::write("Found $count accounts with high usage (>90%)", 'red');
                
                $msg = "⚠️ <b>PERINGATAN KUOTA EMAIL</b>\n";
                $msg .= "Ditemukan <b>$count</b> akun dengan penggunaan > 90%:\n\n";
                
                foreach (array_slice($highUsageAccounts, 0, 10) as $acc) {
                    $msg .= "📧 " . $acc['email'] . "\n";
                    $msg .= "📊 Digunakan: <b>" . $acc['humandiskused'] . "</b> (" . round($acc['diskusedpercent_float'], 1) . "%)\n\n";
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

    private function recordStatsHistory()
    {
        CLI::write('Recording Daily Stats History...', 'yellow');
        try {
            $emailModel = new EmailModel();
            $historyModel = new EmailStatsHistoryModel();
            
            $totalAkun = $emailModel->countAllResults();
            $totalStorageResult = $emailModel->selectSum('diskused', 'total')->first();
            $totalStorageMb = ($totalStorageResult['total'] ?? 0) / (1024 * 1024);
            
            $today = date('Y-m-d');
            
            $existing = $historyModel->where('tanggal', $today)->first();
            $data = [
                'tanggal' => $today,
                'total_akun' => $totalAkun,
                'total_storage_mb' => $totalStorageMb
            ];
            
            if ($existing) {
                $historyModel->update($existing['id'], $data);
            } else {
                $historyModel->insert($data);
            }
            CLI::write('Stats history recorded successfully.', 'green');
        } catch (\Throwable $e) {
            CLI::error('Error recording stats history: ' . $e->getMessage());
        }
    }

    private function syncTteStatus()
    {
        CLI::write('--- Phase 2: TTE Status Synchronization ---', 'yellow');
        $this->syncStats['tte']['executed'] = true;
        try {
            $emailModel = new EmailModel();
            $bsreApi = new BsreApi();
            
            $emails = $emailModel->select('id, email')->findAll();
            $total = count($emails);
            CLI::write("Total accounts to check: $total");
            
            foreach ($emails as $index => $email) {
                $count = $index + 1;
                CLI::print("[$count/$total] Checking {$email['email']}... ");
                
                $result = $bsreApi->checkStatus($email['email'], 'email');
                if ($result['success']) {
                    $responseBody = $result['data'];
                    $statusFromBsre = $responseBody['status'] ?? ($responseBody['data']['status'] ?? 'UNKNOWN');
                    $emailModel->update($email['id'], ['bsre_status' => $statusFromBsre]);
                    CLI::write($statusFromBsre, 'green');
                    $this->syncStats['tte']['success']++;
                } else {
                    CLI::write('FAILED', 'red');
                    $this->syncStats['tte']['fail']++;
                }
            }
            CLI::write("TTE Sync Finished. Success: " . $this->syncStats['tte']['success'] . ", Failed: " . $this->syncStats['tte']['fail'], 'cyan');
            $this->saveLastSyncTime('last_sync_tte');
        } catch (\Throwable $e) {
            CLI::error('ERROR in Phase 2: ' . $e->getMessage());
        }
    }

    private function syncPegawaiData()
    {
        CLI::write('--- Phase 3: Pegawai Data Synchronization ---', 'yellow');
        $this->syncStats['pegawai']['executed'] = true;
        try {
            $emailModel = new EmailModel();
            $pegawaiApi = new PegawaiApi();
            $statusAsnModel = new StatusAsnModel();
            $eselonModel = new EselonModel();
            
            $statusPppkPw = $statusAsnModel->where('nama_status_asn', 'PPPK PARUH WAKTU')->asArray()->first();
            $pppkPwId = $statusPppkPw['id'] ?? null;
            
            // Get all emails with NIP, excluding PPPK PW
            $builder = $emailModel->select('emails.*, unit_kerja.nama_unit_kerja')
                ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                ->where('emails.nip IS NOT NULL')
                ->where('emails.nip !=', '');
            
            if ($pppkPwId) {
                $builder->where('emails.status_asn_id !=', $pppkPwId);
            }
            
            $emails = $builder->findAll();
            $total = count($emails);
            CLI::write("Total NIPs to sync: $total");
            
            // To avoid redundant API calls for same NIP
            $nipResults = [];
            
            foreach ($emails as $index => $currentEmail) {
                $count = $index + 1;
                $nip = $currentEmail['nip'];
                CLI::print("[$count/$total] Syncing NIP $nip ({$currentEmail['email']})... ");
                
                if (isset($nipResults[$nip])) {
                    $result = $nipResults[$nip];
                } else {
                    $result = $pegawaiApi->getPegawaiData($nip);
                    $nipResults[$nip] = $result;
                }
                
                if ($result['success']) {
                    $data = $result['data'];
                    $source = (is_array($data) && isset($data[0])) ? $data[0] : $data;
                    
                    $hasActualData = isset($source['jabatan_nama']) || 
                                     isset($source['jabatan']) || 
                                     isset($source['pangkat_nama']) || 
                                     isset($source['pangkat_golruang']);

                    if (empty($data) || !$hasActualData) {
                        CLI::write('DATA NOT FOUND', 'red');
                        $this->syncStats['pegawai']['fail']++;
                        continue;
                    }
                    
                    $isPimpinan = ($currentEmail['pimpinan'] ?? 0) == 1;
                    $updateData = [];
                    
                    // 1. Sync Jabatan
                    if (!$isPimpinan) {
                        $newJabatan = $source['jabatan_nama'] ?? ($source['jabatan'] ?? null);
                        if ($newJabatan) {
                            $newJabatanUpper = mb_strtoupper($newJabatan, 'UTF-8');
                            if (stripos($newJabatanUpper, 'PLT') === false) {
                                if (strpos($newJabatanUpper, 'SEKRETARIS') !== false) {
                                    $unitName = strtoupper($currentEmail['nama_unit_kerja'] ?? '');
                                    if (strpos($unitName, 'DINAS') !== false) $newJabatanUpper = 'SEKRETARIS DINAS';
                                    elseif (strpos($unitName, 'BADAN') !== false) $newJabatanUpper = 'SEKRETARIS BADAN';
                                    elseif (strpos($unitName, 'KECAMATAN') !== false) $newJabatanUpper = 'SEKRETARIS KECAMATAN';
                                    elseif (strpos($unitName, 'KELURAHAN') !== false) $newJabatanUpper = 'SEKRETARIS KELURAHAN';
                                }
                                $updateData['jabatan'] = $newJabatanUpper;

                                if (!empty($source['jabatan_jenis_eselon'])) {
                                    $eselonStr = str_replace(['.', ' '], '', $source['jabatan_jenis_eselon']);
                                    $eselon = $eselonModel->where('nama_eselon', $eselonStr)->first();
                                    if ($eselon) $updateData['eselon_id'] = $eselon['id'];
                                }
                            }
                        }
                    }

                    // 2. Sync Pangkat & Golongan
                    if (isset($source['pangkat_nama'])) $updateData['pangkat_nama'] = $source['pangkat_nama'];
                    if (isset($source['pangkat_golruang'])) $updateData['pangkat_golruang'] = $source['pangkat_golruang'];

                    if (!empty($updateData)) {
                        $emailModel->update($currentEmail['id'], $updateData);
                        CLI::write('SUCCESS', 'green');
                        $this->syncStats['pegawai']['success']++;
                    } else {
                        CLI::write('NO CHANGES', 'blue');
                        $this->syncStats['pegawai']['skipped']++;
                    }
                } else {
                    CLI::write('API FAILED', 'red');
                    $this->syncStats['pegawai']['fail']++;
                }
            }
            CLI::write("Pegawai Sync Finished. Success: " . $this->syncStats['pegawai']['success'] . ", Failed: " . $this->syncStats['pegawai']['fail'], 'cyan');
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
        } catch (\Throwable $e) {
            CLI::error('ERROR in Phase 4: ' . $e->getMessage());
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
