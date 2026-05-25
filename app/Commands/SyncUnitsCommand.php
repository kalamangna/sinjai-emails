<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Domains\UnitKerja\UnitKerjaModel;
use App\Shared\Libraries\PegawaiApi;
use App\Shared\Libraries\TelegramLibrary;

class SyncUnitsCommand extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'sync:units';
    protected $description = 'Synchronize Unit Kerja data from Pegawai API.';
    protected $usage       = 'sync:units';

    public function run(array $params)
    {
        CLI::write('Starting Unit Kerja Synchronization...', 'blue');
        
        $telegram = new TelegramLibrary();
        $unitModel = new UnitKerjaModel();
        $pegawaiApi = new PegawaiApi();

        try {
            // 1. Fetch data from API
            CLI::write('Fetching data from API...', 'yellow');
            
            // We use a custom call here because we need the get_unit endpoint
            $client = \Config\Services::curlrequest([
                'timeout' => 30,
                'http_errors' => false
            ]);
            
            $baseUrl = rtrim(env('PEGAWAI_BASE_URL') ?: 'https://apps.sinjaikab.go.id/api/pegawai', '/');
            $url = $baseUrl . '/get_unit';
            
            $response = $client->get($url);
            
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('API returned status code: ' . $response->getStatusCode());
            }
            
            $apiData = json_decode($response->getBody(), true);
            
            if (empty($apiData)) {
                throw new \Exception('API returned empty data.');
            }

            $totalApi = count($apiData);
            CLI::write("Total units in API: $totalApi", 'cyan');

            $stats = ['added' => 0, 'updated' => 0, 'unchanged' => 0];

            // 2. Process each unit from API
            foreach ($apiData as $index => $unit) {
                $count = $index + 1;
                $apiId = $unit['unit_id'] ?? null;
                $apiName = trim($unit['unit_nama'] ?? '');

                if (!$apiId || !$apiName) continue;

                CLI::print("[$count/$totalApi] Processing: $apiName... ");

                // Try to find by api_unit_id first
                $existing = $unitModel->where('api_unit_id', $apiId)->first();

                // If not found, try to find by name (for mapping legacy data)
                if (!$existing) {
                    $existing = $unitModel->where('nama_unit_kerja', $apiName)->first();
                }

                if ($existing) {
                    // Update ONLY API ID, PRESERVE local name and address
                    if (($existing['api_unit_id'] ?? '') != $apiId) {
                        $unitModel->update($existing['id'], ['api_unit_id' => $apiId]);
                        CLI::write('MAPPED ID', 'green');
                        $stats['updated']++;
                    } else {
                        CLI::write('NO CHANGES', 'blue');
                        $stats['unchanged']++;
                    }
                } else {
                    // Insert new unit only if truly not found
                    $newData = [
                        'api_unit_id' => $apiId,
                        'nama_unit_kerja' => $apiName
                    ];
                    $unitModel->insert($newData);
                    CLI::write('ADDED', 'green');
                    $stats['added']++;
                }
            }

            // 3. Final Report
            $summary = "✅ <b>SINKRONISASI UNIT KERJA SELESAI</b>\n";
            $summary .= "------------------------------------------\n";
            $summary .= "🆕 Baru: <b>{$stats['added']}</b> Unit\n";
            $summary .= "🔄 Update: <b>{$stats['updated']}</b> Unit\n";
            $summary .= "🆗 Tetap: <b>{$stats['unchanged']}</b> Unit\n";
            $summary .= "\n🕒 " . date('d M Y H:i:s');

            CLI::write("\nSync finished!", 'green');
            CLI::write("Added: {$stats['added']}, Updated: {$stats['updated']}, Unchanged: {$stats['unchanged']}", 'cyan');

            $telegram->sendMessage($summary);

        } catch (\Throwable $e) {
            CLI::error('SYNC FAILED: ' . $e->getMessage());
            $telegram->sendMessage("❌ <b>GAGAL SINKRONISASI UNIT KERJA</b>\nError: " . $e->getMessage());
        }
    }
}
