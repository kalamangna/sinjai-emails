<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Domains\Email\EmailModel;
use App\Domains\UnitKerja\UnitKerjaModel;

class RecoverNips extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'maintenance:recover-nips';
    protected $description = 'Recover truncated NIP data from API using Unit ID and NIK matching.';

    public function run(array $params)
    {
        $unitModel = new UnitKerjaModel();
        $emailModel = new EmailModel();
        $client = \Config\Services::curlrequest();
        
        $units = $unitModel->where('api_unit_id IS NOT NULL')->where('api_unit_id !=', '')->findAll();
        $totalUnits = count($units);

        if ($totalUnits === 0) {
            CLI::error("No units with api_unit_id found. Please run sync:unit-api first.");
            return;
        }

        CLI::write("Starting NIP recovery for $totalUnits units...", 'yellow');
        $recoveredCount = 0;

        foreach ($units as $index => $unit) {
            $unitName = $unit['nama_unit_kerja'];
            $apiUnitId = $unit['api_unit_id'];
            
            CLI::print("[" . ($index + 1) . "/$totalUnits] Fetching employees for $unitName... ");
            
            try {
                $response = $client->get("https://apps.sinjaikab.go.id/api/pegawai/get_pegawai", [
                    'query' => ['unit_id' => $apiUnitId],
                    'timeout' => 15
                ]);

                if ($response->getStatusCode() !== 200) {
                    CLI::write("FAILED (Status {$response->getStatusCode()})", 'red');
                    continue;
                }

                $pegawaiList = json_decode($response->getBody(), true);
                if (!is_array($pegawaiList)) {
                    CLI::write("EMPTY/INVALID RESPONSE", 'red');
                    continue;
                }

                $unitMatchCount = 0;
                foreach ($pegawaiList as $p) {
                    $apiNip = $p['nip'] ?? null;
                    $apiNik = $p['nik'] ?? null;

                    if (empty($apiNip) || empty($apiNik)) continue;

                    // Clean NIK/NIP for hashing
                    $cleanNik = str_replace([' ', '.', '-', '\''], '', $apiNik);
                    $nikHash = hash('sha256', $cleanNik);

                    // Find local email records matching this NIK hash
                    // We match by NIK hash because NIK was not truncated (VARCHAR 255)
                    $localEmail = $emailModel->allowCallbacks(false)->where('nik_hash', $nikHash)->first();

                    if ($localEmail) {
                        // Update with fresh NIP (which will be hashed and encrypted correctly by the model)
                        // We use the regular update() to trigger hashAndEncrypt callback
                        $emailModel->update($localEmail['id'], [
                            'nip' => $apiNip
                        ]);
                        $unitMatchCount++;
                        $recoveredCount++;
                    }
                }

                CLI::write("DONE (Matched $unitMatchCount)", 'green');

            } catch (\Throwable $e) {
                CLI::write("ERROR: " . $e->getMessage(), 'red');
            }
        }

        CLI::write("\nRecovery process finished! Total NIPs restored: $recoveredCount", 'cyan');
        CLI::write("Please run 'php spark encrypt:data' one last time to ensure all encryption is consistent.", 'yellow');
    }
}
