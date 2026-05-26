<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SyncUnitApi extends BaseCommand
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
    protected $name = 'sync:unit-api';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Sync api_unit_id from external API based on unit name matching.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'sync:unit-api';

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $client = \Config\Services::curlrequest();
        $url = 'https://apps.sinjaikab.go.id/api/pegawai/get_unit';

        CLI::write("Fetching data from API...", 'yellow');
        $response = $client->get($url);

        if ($response->getStatusCode() !== 200) {
            CLI::error("Error: Failed to fetch data from API (Status: " . $response->getStatusCode() . ")");
            return;
        }

        $apiData = json_decode($response->getBody(), true);
        if (!$apiData) {
            CLI::error("Error: Invalid JSON response from API");
            return;
        }

        $unitModel = new \App\Domains\UnitKerja\UnitKerjaModel();
        $localUnits = $unitModel->findAll();

        CLI::write("Processing " . count($apiData) . " API records...", 'yellow');

        $updateCount = 0;
        foreach ($apiData as $apiUnit) {
            $apiId = $apiUnit['unit_id'];
            $apiNameRaw = trim($apiUnit['unit_nama']);
            $apiName = strtoupper($apiNameRaw);
            
            $matched = false;
            foreach ($localUnits as $localUnit) {
                $localName = strtoupper(trim($localUnit['nama_unit_kerja']));
                
                // Advanced Normalization for matching
                $apiNorm = strtoupper(trim(preg_replace('/[^A-Z0-9]/', '', $apiName)));
                $localNorm = strtoupper(trim(preg_replace('/[^A-Z0-9]/', '', $localName)));
                
                // Handle specific variations on the local side to match the API
                $localNorm = str_replace('PERMUKIMAN', 'PEMUKIMAN', $localNorm);
                $localNorm = str_replace('HORTIKULTURA', 'HOLTIKULTURA', $localNorm);
                $localNorm = str_replace('USAHAMIKROKECIL', 'USAHAKECIL', $localNorm);
                $localNorm = str_replace('PEMERINTAHKABUPATENSINJAI', 'PEMERINTAHDAERAHKABUPATENSINJAI', $localNorm);
                
                // Direct or normalized match
                if ($apiName === $localName || $apiNorm === $localNorm) {
                    $matched = $localUnit;
                    break;
                }

                // Normalization (e.g. remove "KANTOR KECAMATAN" prefix if needed, or check contains)
                $apiNameClean = str_replace(['KANTOR KECAMATAN ', 'KECAMATAN '], '', $apiName);
                $localNameClean = str_replace(['KANTOR KECAMATAN ', 'KECAMATAN '], '', $localName);

                if ($apiNameClean === $localNameClean) {
                    $matched = $localUnit;
                    break;
                }

                // Fuzzy match for common cases like "Inspektorat" vs "INSPEKTORAT DAERAH"
                if (strpos($localName, $apiName) !== false || strpos($apiName, $localName) !== false) {
                     if (strlen($apiName) > 5 && strlen($localName) > 5) {
                         $matched = $localUnit;
                         break;
                     }
                }
            }

            if ($matched) {
                CLI::write("Matching: [{$apiId}] {$apiNameRaw}  ==>  {$matched['nama_unit_kerja']}", 'green');
                $unitModel->update($matched['id'], ['api_unit_id' => $apiId]);
                $updateCount++;
            } else {
                CLI::write("No match for: [{$apiId}] {$apiNameRaw}", 'red');
            }
        }

        CLI::write("\nSynchronization finished. Total updated: $updateCount", 'cyan');
    }
}
