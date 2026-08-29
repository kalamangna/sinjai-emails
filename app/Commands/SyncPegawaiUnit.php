<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Domains\Email\Models\EmailModel;
use App\Domains\UnitKerja\Models\UnitKerjaModel;
use App\Shared\Services\PegawaiSyncService;
use App\Shared\Models\StatusAsnModel;

class SyncPegawaiUnit extends BaseCommand
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
    protected $name = 'sync:pegawai-unit';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Synchronize Pegawai data (Pangkat/Golongan/Jabatan) for all accounts in a specific Unit Kerja manually.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'sync:pegawai-unit [unit_id] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'unit_id' => 'The ID of the Unit Kerja',
    ];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--asn' => 'Filter by ASN status (e.g., --asn=PNS or --asn="PPPK PARUH WAKTU")',
    ];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $unitInput = $params[0] ?? CLI::getOption('unit') ?? null;

        if (empty($unitInput)) {
            CLI::error("Error: Unit ID atau Nama Unit Kerja wajib diisi.");
            CLI::write("Contoh ID   : php spark sync:pegawai-unit 343", 'yellow');
            CLI::write("Contoh Nama : php spark sync:pegawai-unit \"Dinas Komunikasi\"", 'yellow');
            return;
        }

        $unitModel = new UnitKerjaModel();
        if (is_numeric($unitInput)) {
            $unit = $unitModel->find($unitInput);
        } else {
            $unit = $unitModel->like('nama_unit_kerja', $unitInput)->first();
        }

        if (!$unit) {
            CLI::error("Error: Unit Kerja '$unitInput' tidak ditemukan.");
            return;
        }

        $unitId = $unit['id'];
        CLI::write("Syncing Pegawai Data for Unit: " . $unit['nama_unit_kerja'] . " (ID: $unitId)", 'yellow');

        // Mengambil semua ID Unit Kerja (Induk + Anak)
        $unitIds = [$unitId];
        $childUnits = $unitModel->where('parent_id', $unitId)->findAll();
        foreach ($childUnits as $child) {
            $unitIds[] = $child['id'];
        }

        if (count($unitIds) > 1) {
            CLI::write("Including " . (count($unitIds) - 1) . " child units.", 'cyan');
        }

        // CI4 CLI parser sometimes parses --asn=PNS as key 'asn=PNS' with null value.
        // Or properly as key 'asn' with value 'PNS'.
        $asnFilter = CLI::getOption('asn') ?? $params['asn'] ?? null;
        if (empty($asnFilter)) {
            // Check for key 'asn=X' inside $params
            foreach ($params as $key => $val) {
                if (strpos($key, 'asn=') === 0) {
                    $asnFilter = substr($key, 4);
                    break;
                }
            }
        }
        
        $emailModel = new EmailModel();
        $builder = $emailModel->whereIn('unit_kerja_id', $unitIds);

        if ($asnFilter) {
            $statusAsnModel = new StatusAsnModel();
            $statusAsn = $statusAsnModel->where('nama_status_asn', strtoupper($asnFilter))->first();

            if (!$statusAsn) {
                CLI::error("Error: Status ASN '$asnFilter' not found.");
                CLI::write("Available statuses: PNS, PPPK, PPPK PARUH WAKTU", 'yellow');
                return;
            }

            $builder->where('status_asn_id', $statusAsn['id']);
            CLI::write("Filtering by ASN Status: " . strtoupper($asnFilter), 'cyan');
        } else {
            $statusAsnModel = new StatusAsnModel();
            $statusPppk = $statusAsnModel->where('nama_status_asn', 'PPPK')->first();
            $statusPppkPw = $statusAsnModel->where('nama_status_asn', 'PPPK PARUH WAKTU')->first();
            $excludeIds = array_filter([
                $statusPppk['id'] ?? null,
                $statusPppkPw['id'] ?? null,
            ]);

            if (!empty($excludeIds)) {
                $builder->whereNotIn('status_asn_id', $excludeIds);
            }
            CLI::write("Target: Seluruh akun kecuali PPPK & PPPK Paruh Waktu", 'cyan');
        }

        $emails = $builder->findAll();

        if (empty($emails)) {
            CLI::write("No email accounts found for this unit.", 'cyan');
            return;
        }

        $nips = array_filter(array_column($emails, 'nip'));

        if (empty($nips)) {
            CLI::write("No valid NIPs found in the matched accounts.", 'yellow');
            return;
        }

        $totalNips = count($nips);
        CLI::write("Found $totalNips accounts with NIP. Starting Pegawai sync...", 'cyan');

        $pegawaiSyncService = new PegawaiSyncService();
        
        $successCount = 0;
        $failCount = 0;

        // Pass a callback to print progress for each NIP
        $pegawaiSyncService->processBatch($nips, function($curr, $total, $nip, $success, $statusMessage) use (&$successCount, &$failCount) {
            CLI::print("[$curr/$total] Syncing NIP $nip... ");
            if ($success) {
                CLI::write($statusMessage, 'green');
                $successCount++;
            } else {
                CLI::write($statusMessage, 'red');
                $failCount++;
            }
        });

        CLI::write("\nSync Completed for $totalNips NIPs!", 'green');
        CLI::write("Success: $successCount", 'green');
        CLI::write("Failed: $failCount", 'red');
    }
}
