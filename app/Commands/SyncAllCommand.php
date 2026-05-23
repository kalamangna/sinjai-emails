<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Shared\Services\SyncService;
use App\Shared\Libraries\BsreApi;
use App\Shared\Libraries\PegawaiApi;
use App\Domains\Email\EmailModel;
use App\Shared\Models\StatusAsnModel;
use App\Shared\Models\EselonModel;

use App\Domains\Website\WebDesaKelurahanModel;
use App\Domains\Website\WebsiteService;

class SyncAllCommand extends BaseCommand
{
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
        $isDaily = isset($params['daily']);
        $isMonthly = isset($params['monthly']);
        $runAll = !$isDaily && !$isMonthly;

        CLI::write('Starting Synchronization Process...', 'blue');
        
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
        
        CLI::write('Synchronization process completed!', 'green');
    }

    private function syncCpanel()
    {
        CLI::write('--- Phase 1: cPanel Synchronization ---', 'yellow');
        try {
            $syncService = new SyncService();
            $result = $syncService->syncFromCpanel();
            if ($result['success']) {
                CLI::write('SUCCESS: ' . $result['message'], 'green');
            } else {
                CLI::error('FAILED: ' . $result['message']);
            }
        } catch (\Throwable $e) {
            CLI::error('ERROR in Phase 1: ' . $e->getMessage());
        }
    }

    private function syncTteStatus()
    {
        CLI::write('--- Phase 2: TTE Status Synchronization ---', 'yellow');
        try {
            $emailModel = new EmailModel();
            $bsreApi = new BsreApi();
            
            $emails = $emailModel->select('id, email')->findAll();
            $total = count($emails);
            CLI::write("Total accounts to check: $total");
            
            $successCount = 0;
            $failCount = 0;
            
            foreach ($emails as $index => $email) {
                $count = $index + 1;
                CLI::print("[$count/$total] Checking {$email['email']}... ");
                
                $result = $bsreApi->checkStatus($email['email'], 'email');
                if ($result['success']) {
                    $responseBody = $result['data'];
                    $statusFromBsre = $responseBody['status'] ?? ($responseBody['data']['status'] ?? 'UNKNOWN');
                    $emailModel->update($email['id'], ['bsre_status' => $statusFromBsre]);
                    CLI::write($statusFromBsre, 'green');
                    $successCount++;
                } else {
                    CLI::write('FAILED', 'red');
                    $failCount++;
                }
            }
            
            CLI::write("TTE Sync Finished. Success: $successCount, Failed: $failCount", 'cyan');
        } catch (\Throwable $e) {
            CLI::error('ERROR in Phase 2: ' . $e->getMessage());
        }
    }

    private function syncPegawaiData()
    {
        CLI::write('--- Phase 3: Pegawai Data Synchronization ---', 'yellow');
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
            
            $successCount = 0;
            $failCount = 0;
            
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
                        $failCount++;
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
                        $successCount++;
                    } else {
                        CLI::write('NO CHANGES', 'blue');
                    }
                } else {
                    CLI::write('API FAILED', 'red');
                    $failCount++;
                }
            }
            
            CLI::write("Pegawai Sync Finished. Success: $successCount, Failed: $failCount", 'cyan');
        } catch (\Throwable $e) {
            CLI::error('ERROR in Phase 3: ' . $e->getMessage());
        }
    }

    private function syncWebExpirations()
    {
        CLI::write('--- Phase 4: Website Expiration Synchronization ---', 'yellow');
        try {
            $webDesaModel = new WebDesaKelurahanModel();
            $websiteService = new WebsiteService();
            
            $websites = $webDesaModel->findAll();
            $total = count($websites);
            CLI::write("Total websites to sync: $total");
            
            $successCount = 0;
            $failCount = 0;
            
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
                    $successCount++;
                } else {
                    CLI::write('FAILED', 'red');
                    $failCount++;
                }
            }
            
            CLI::write("Website Expiration Sync Finished. Success: $successCount, Failed: $failCount", 'cyan');
        } catch (\Throwable $e) {
            CLI::error('ERROR in Phase 4: ' . $e->getMessage());
        }
    }
}
