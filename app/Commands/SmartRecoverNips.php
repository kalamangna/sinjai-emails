<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Domains\Email\EmailModel;
use App\Domains\UnitKerja\UnitKerjaModel;

class SmartRecoverNips extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'maintenance:smart-recover-nips';
    protected $description = 'Advanced global recovery of NIPs using fuzzy name matching across all units.';

    public function run(array $params)
    {
        $unitModel = new UnitKerjaModel();
        $emailModel = new EmailModel();
        $client = \Config\Services::curlrequest();
        
        $units = $unitModel->where('api_unit_id IS NOT NULL')->where('api_unit_id !=', '')->findAll();
        $totalUnits = count($units);

        CLI::write("Starting SMART NIP recovery... Fetching ALL API data first.", 'yellow');
        
        // 1. Fetch ALL API data into a massive global pool
        $globalApiPegawai = [];
        foreach ($units as $index => $unit) {
            try {
                $response = $client->get("https://apps.sinjaikab.go.id/api/pegawai/get_pegawai", [
                    'query' => ['unit_id' => $unit['api_unit_id']],
                    'timeout' => 10
                ]);
                if ($response->getStatusCode() === 200) {
                    $list = json_decode($response->getBody(), true);
                    if (is_array($list)) {
                        $globalApiPegawai = array_merge($globalApiPegawai, $list);
                    }
                }
            } catch (\Throwable $e) {}
        }

        $totalApi = count($globalApiPegawai);
        CLI::write("Successfully fetched $totalApi employees from API.", 'green');

        // 2. Create advanced fuzzy maps
        $cleanNameCore = function($name) {
            $name = explode(',', $name)[0]; // Remove academic titles
            $prefixes = ['H. ', 'Hj. ', 'dr. ', 'Drs. ', 'Ir. ', 'ST. ', 'Siti ', 'Muh. ', 'Andi ', 'Andi', 'Muh ', 'Sitti ', 'Sitti'];
            $name = str_ireplace($prefixes, '', $name);
            $name = preg_replace('/[^a-zA-Z]/', '', $name); // Strip spaces and special chars
            return strtoupper($name); // e.g. "ANDI BUDI S.Kom" -> "BUDI"
        };

        $apiMapExact = [];
        foreach ($globalApiPegawai as $p) {
            $apiNip = $p['nip'] ?? '';
            $apiName = $p['nama'] ?? '';
            if (empty($apiNip) || empty($apiName)) continue;
            
            $core = $cleanNameCore($apiName);
            if (!empty($core)) {
                // Store in an array in case of collisions
                $apiMapExact[$core][] = $p;
            }
        }

        // 3. Find all local accounts that need fixing
        $brokenLocalEmails = [];
        $allLocal = $emailModel->allowCallbacks(false)->findAll();
        foreach ($allLocal as $le) {
            $nip = str_replace([' ', '.', '-', '\''], '', $le['nip'] ?? '');
            if (empty($nip) || strlen($nip) !== 18 || !is_numeric($nip)) {
                // Only care if they are ASN or Pimpinan
                if (in_array($le['status_asn_id'], [1, 2]) || $le['pimpinan'] == 1 || $le['pimpinan_desa'] == 1) {
                    $brokenLocalEmails[] = $le;
                }
            }
        }

        $totalBroken = count($brokenLocalEmails);
        CLI::write("Found $totalBroken local accounts needing NIP recovery.", 'yellow');

        $recovered = 0;
        $unrecovered = [];
        foreach ($brokenLocalEmails as $le) {
            $localCore = $cleanNameCore($le['name']);
            
            $matched = false;
            if (isset($apiMapExact[$localCore])) {
                $candidates = $apiMapExact[$localCore];
                
                if (count($candidates) === 1) {
                    $emailModel->update($le['id'], ['nip' => $candidates[0]['nip']]);
                    $recovered++;
                    $matched = true;
                } else {
                    foreach ($candidates as $cand) {
                        $candUnitId = $cand['unit_id'] ?? '';
                        $localApiUnitId = null;
                        foreach ($units as $u) {
                            if ($u['id'] == $le['unit_kerja_id']) {
                                $localApiUnitId = $u['api_unit_id'];
                                break;
                            }
                        }
                        
                        if ($localApiUnitId == $candUnitId) {
                            $emailModel->update($le['id'], ['nip' => $cand['nip']]);
                            $recovered++;
                            $matched = true;
                            break;
                        }
                    }
                }
            }

            if (!$matched) {
                $unrecovered[] = $le;
            }
        }

        // Pass 2: Levenshtein Distance (Fuzzy Match) for typos
        CLI::write("Pass 2: Attempting Fuzzy Matching for " . count($unrecovered) . " remaining accounts...", 'yellow');
        $fuzzyRecovered = 0;

        // Flatten API map for easier distance calculation
        $flatApi = [];
        foreach ($apiMapExact as $core => $cands) {
            foreach ($cands as $c) {
                $flatApi[$core][] = $c;
            }
        }

        foreach ($unrecovered as $le) {
            $localCore = $cleanNameCore($le['name']);
            if (empty($localCore) || strlen($localCore) < 4) continue; // Skip too short names to avoid false positives

            $bestMatch = null;
            $bestDistance = 3; // Max allowed typo distance (e.g. 3 characters difference)

            foreach ($flatApi as $apiCore => $cands) {
                // Only compare if length is somewhat similar to save CPU
                if (abs(strlen($localCore) - strlen($apiCore)) > 3) continue;

                $distance = levenshtein($localCore, $apiCore);
                if ($distance < $bestDistance) {
                    $bestDistance = $distance;
                    $bestMatch = $cands;
                }
            }

            if ($bestMatch !== null) {
                // If we found a close typo, check if we can isolate to one person
                if (count($bestMatch) === 1) {
                    $emailModel->update($le['id'], ['nip' => $bestMatch[0]['nip']]);
                    $fuzzyRecovered++;
                    $recovered++;
                } else {
                    // Tie-breaker by Unit
                    foreach ($bestMatch as $cand) {
                        $candUnitId = $cand['unit_id'] ?? '';
                        $localApiUnitId = null;
                        foreach ($units as $u) {
                            if ($u['id'] == $le['unit_kerja_id']) {
                                $localApiUnitId = $u['api_unit_id'];
                                break;
                            }
                        }
                        
                        if ($localApiUnitId == $candUnitId) {
                            $emailModel->update($le['id'], ['nip' => $cand['nip']]);
                            $fuzzyRecovered++;
                            $recovered++;
                            break;
                        }
                    }
                }
            }
        }

        CLI::write("Fuzzy Pass finished! Restored: $fuzzyRecovered", 'green');
        CLI::write("SMART Recovery finished! Total successfully restored: $recovered / $totalBroken", 'cyan');
    }
}
