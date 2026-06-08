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

        CLI::write("Starting NIP recovery via Name Matching for $totalUnits units...", 'yellow');
        $recoveredCount = 0;

        $cleanName = function($name) {
            // Remove everything after the first comma (academic titles)
            $name = explode(',', $name)[0];
            
            // Remove common prefixes
            $prefixes = ['H. ', 'Hj. ', 'dr. ', 'Drs. ', 'Ir. ', 'ST. '];
            $name = str_ireplace($prefixes, '', $name);
            
            // Normalize
            $name = strtoupper(trim($name));
            // Remove double spaces
            $name = preg_replace('/\s+/', ' ', $name);
            
            return $name;
        };

        foreach ($units as $index => $unit) {
            $unitName = $unit['nama_unit_kerja'];
            $apiUnitId = $unit['api_unit_id'];
            $unitId = $unit['id'];
            
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

                // Fetch all local emails for this unit to match in memory (faster)
                $localEmails = $emailModel->allowCallbacks(false)->where('unit_kerja_id', $unitId)->findAll();
                $localMap = [];
                foreach ($localEmails as $le) {
                    $normLocal = $cleanName($le['name']);
                    if (!empty($normLocal)) {
                        $localMap[$normLocal] = $le;
                    }
                }

                $unitMatchCount = 0;
                foreach ($pegawaiList as $p) {
                    $apiNip = $p['nip'] ?? null;
                    $apiNameRaw = $p['nama'] ?? null;

                    if (empty($apiNip) || empty($apiNameRaw)) continue;

                    $normApiName = $cleanName($apiNameRaw);

                    if (isset($localMap[$normApiName])) {
                        $localEmail = $localMap[$normApiName];
                        
                        // Update with fresh NIP
                        $emailModel->update($localEmail['id'], [
                            'nip' => $apiNip
                        ]);
                        $unitMatchCount++;
                        $recoveredCount++;
                        
                        // Remove from map to prevent double matching
                        unset($localMap[$normApiName]);
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
