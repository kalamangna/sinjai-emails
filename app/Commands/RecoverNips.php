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

        CLI::write("Starting Smart NIP recovery (Name + Jabatan matching) for $totalUnits units...", 'yellow');
        $recoveredCount = 0;
        $ambiguousCount = 0;

        $cleanName = function($name) {
            $name = explode(',', $name)[0];
            $prefixes = ['H. ', 'Hj. ', 'dr. ', 'Drs. ', 'Ir. ', 'ST. ', 'Siti ', 'Muh. '];
            $name = str_ireplace($prefixes, '', $name);
            $name = strtoupper(trim($name));
            return preg_replace('/\s+/', ' ', $name);
        };

        $cleanJabatan = function($jabatan) {
            $jabatan = strtoupper(trim($jabatan));
            $removals = ['KANTOR ', 'DINAS ', 'BADAN ', 'BAGIAN ', 'SUB ', 'PELAKSANA ', 'PENGADMINISTRASI ', 'FUNGSIONAL '];
            $jabatan = str_replace($removals, '', $jabatan);
            return preg_replace('/\s+/', ' ', $jabatan);
        };

        foreach ($units as $index => $unit) {
            $unitName = $unit['nama_unit_kerja'];
            $apiUnitId = $unit['api_unit_id'];
            $unitId = $unit['id'];
            
            CLI::print("[" . ($index + 1) . "/$totalUnits] Processing $unitName... ");
            
            try {
                $response = $client->get("https://apps.sinjaikab.go.id/api/pegawai/get_pegawai", [
                    'query' => ['unit_id' => $apiUnitId],
                    'timeout' => 15
                ]);

                if ($response->getStatusCode() !== 200) {
                    CLI::write("FAILED (API Error)", 'red');
                    continue;
                }

                $pegawaiList = json_decode($response->getBody(), true);
                if (!is_array($pegawaiList)) {
                    CLI::write("INVALID JSON", 'red');
                    continue;
                }

                // Map local emails by cleaned name
                $localEmails = $emailModel->allowCallbacks(false)->where('unit_kerja_id', $unitId)->findAll();
                $localMap = [];
                foreach ($localEmails as $le) {
                    $normLocal = $cleanName($le['name']);
                    if (!empty($normLocal)) {
                        $localMap[$normLocal][] = $le;
                    }
                }

                $unitMatchCount = 0;
                foreach ($pegawaiList as $p) {
                    $apiNip = $p['nip'] ?? null;
                    $apiNameRaw = $p['nama'] ?? null;
                    $apiJabatanRaw = $p['jabatan_nama'] ?? '';

                    if (empty($apiNip) || empty($apiNameRaw)) continue;

                    $normApiName = $cleanName($apiNameRaw);
                    $normApiJabatan = $cleanJabatan($apiJabatanRaw);

                    if (isset($localMap[$normApiName])) {
                        $candidates = $localMap[$normApiName];
                        $matchedLocalId = null;

                        if (count($candidates) === 1) {
                            $matchedLocalId = $candidates[0]['id'];
                        } else {
                            // Multiple candidates with same name, try matching Jabatan
                            foreach ($candidates as $cand) {
                                $normLocalJabatan = $cleanJabatan($cand['jabatan'] ?? '');
                                if (!empty($normLocalJabatan) && (strpos($normApiJabatan, $normLocalJabatan) !== false || strpos($normLocalJabatan, $normApiJabatan) !== false)) {
                                    $matchedLocalId = $cand['id'];
                                    break;
                                }
                            }
                        }

                        if ($matchedLocalId) {
                            $emailModel->update($matchedLocalId, ['nip' => $apiNip]);
                            $unitMatchCount++;
                            $recoveredCount++;
                        } else {
                            $ambiguousCount++;
                        }
                    }
                }

                CLI::write("DONE (Restored $unitMatchCount)", 'green');

            } catch (\Throwable $e) {
                CLI::write("ERROR: " . $e->getMessage(), 'red');
            }
        }

        CLI::write("\nRecovery process finished!", 'cyan');
        CLI::write("- Total NIPs restored: $recoveredCount", 'green');
        if ($ambiguousCount > 0) {
            CLI::write("- Skipped ambiguous names: $ambiguousCount (Requires manual check)", 'yellow');
        }
        CLI::write("\nFinal step: Please run 'php spark encrypt:data' to finalize encryption.", 'yellow');
    }
}
