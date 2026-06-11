<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Domains\Email\Models\EmailModel;
use App\Domains\UnitKerja\Models\UnitKerjaModel;
use App\Shared\Libraries\BsreApi;
use App\Shared\Models\StatusAsnModel;

class SyncTteUnit extends BaseCommand
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
    protected $name = 'sync:tte-unit';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Synchronize TTE status for all accounts in a specific Unit Kerja manually.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'sync:tte-unit [unit_id] [options]';

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
        $unitId = $params[0] ?? null;

        if (empty($unitId)) {
            CLI::error("Error: Unit ID is required.");
            $this->showUsage();
            return;
        }

        $unitModel = new UnitKerjaModel();
        $unit = $unitModel->find($unitId);

        if (!$unit) {
            CLI::error("Error: Unit Kerja with ID $unitId not found.");
            return;
        }

        CLI::write("Syncing TTE Status for Unit: " . $unit['nama_unit_kerja'], 'yellow');

        // Mengambil semua ID Unit Kerja (Induk + Anak)
        $unitIds = [$unitId];
        $childUnits = $unitModel->where('parent_id', $unitId)->findAll();
        foreach ($childUnits as $child) {
            $unitIds[] = $child['id'];
        }

        if (count($unitIds) > 1) {
            CLI::write("Including " . (count($unitIds) - 1) . " child units.", 'cyan');
        }

        $asnFilter = CLI::getOption('asn');
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
        }

        $emails = $builder->findAll();

        if (empty($emails)) {
            CLI::write("No email accounts found for this unit.", 'cyan');
            return;
        }

        $total = count($emails);
        CLI::write("Found $total accounts. Starting sync...\n", 'cyan');

        $bsreApi = new BsreApi();
        $successCount = 0;
        $failCount = 0;

        foreach ($emails as $index => $email) {
            $curr = $index + 1;
            CLI::print("[$curr/$total] Checking {$email['email']}... ");

            try {
                $result = $bsreApi->checkStatus($email['email'], 'email');
                
                if ($result['success']) {
                    $responseBody = $result['data'];
                    // Logic based on BsreApi implementation
                    $statusFromBsre = $responseBody['status'] ?? ($responseBody['data']['status'] ?? 'UNKNOWN');
                    
                    $emailModel->update($email['id'], ['bsre_status' => $statusFromBsre]);
                    
                    CLI::write($statusFromBsre, 'green');
                    $successCount++;
                } else {
                    CLI::write("FAILED (" . ($result['message'] ?? 'Unknown Error') . ")", 'red');
                    $failCount++;
                }
            } catch (\Throwable $e) {
                CLI::write("ERROR: " . $e->getMessage(), 'red');
                $failCount++;
            }
        }

        CLI::write("\nSync Completed!", 'green');
        CLI::write("Success: $successCount", 'green');
        CLI::write("Failed: $failCount", 'red');
    }
}
