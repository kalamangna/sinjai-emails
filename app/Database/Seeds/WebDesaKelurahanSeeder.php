<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Domains\Website\WebDesaKelurahanModel;
use App\Domains\UnitKerja\UnitKerjaModel;
use App\Shared\Models\PlatformModel;
use Config\Services;

class WebDesaKelurahanSeeder extends Seeder
{
    public function run()
    {
        require_once APPPATH . 'Shared/Helpers/TanggalHelper.php';
        $filePath = FCPATH . '../webdesakel.xlsx';

        if (!file_exists($filePath)) {
            echo "File not found: $filePath\n";
            return;
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $model = new WebDesaKelurahanModel();
            $unitKerjaModel = new UnitKerjaModel();
            $platformModel = new PlatformModel();

            // Fetch all Unit Kerja names for matching
            $allUnitKerja = $unitKerjaModel->findAll();
            $unitKerjaNames = [];
            foreach ($allUnitKerja as $uk) {
                $unitKerjaNames[strtoupper(trim($uk['nama_unit_kerja']))] = $uk['nama_unit_kerja'];
            }

            // Fetch platforms for mapping
            $allPlatforms = $platformModel->findAll();
            $platformMap = [];
            foreach ($allPlatforms as $p) {
                $platformMap[strtoupper(trim($p['nama_platform']))] = $p['id'];
            }

            $dataToInsert = [];
            $headerFound = false;

            echo "Processing rows and fetching dates (this may take a minute)...";

            foreach ($rows as $row) {
                if (!$headerFound) {
                    if (
                        isset($row[0]) && isset($row[1]) &&
                        strtoupper(trim($row[0])) === 'NO' && 
                        strtoupper(trim($row[1])) === 'KECAMATAN'
                    ) {
                        $headerFound = true;
                    }
                    continue; 
                }

                if (empty($row[1]) && empty($row[2])) continue;

                $kecamatanRaw = trim($row[1] ?? '');
                $desaRaw      = trim($row[2] ?? '');

                $kecamatanClean = $kecamatanRaw;
                if (isset($unitKerjaNames[strtoupper($kecamatanRaw)])) {
                    $kecamatanClean = $unitKerjaNames[strtoupper($kecamatanRaw)];
                } elseif (isset($unitKerjaNames['KECAMATAN ' . strtoupper($kecamatanRaw)])) {
                    $kecamatanClean = $unitKerjaNames['KECAMATAN ' . strtoupper($kecamatanRaw)];
                }

                $desaClean = $desaRaw;
                $upperDesa = strtoupper($desaRaw);

                if (isset($unitKerjaNames[$upperDesa])) {
                    $desaClean = $unitKerjaNames[$upperDesa];
                } 
                elseif (!str_starts_with($upperDesa, 'DESA') && !str_starts_with($upperDesa, 'KELURAHAN')) {
                     if (isset($unitKerjaNames['DESA ' . $upperDesa])) {
                         $desaClean = $unitKerjaNames['DESA ' . $upperDesa];
                     } elseif (isset($unitKerjaNames['KELURAHAN ' . $upperDesa])) {
                         $desaClean = $unitKerjaNames['KELURAHAN ' . $upperDesa];
                     }
                }

                $domain = trim($row[3] ?? '');
                $tanggalBerakhir = null;

                if (stripos($desaClean, 'KELURAHAN') !== false) {
                    $tanggalBerakhir = '2026-02-01';
                } else {
                    // For Desa, attempt to fetch from API
                    if (!empty($domain)) {
                        $cleanDomain = preg_replace('#^https?://#', '', $domain);
                        $cleanDomain = rtrim($cleanDomain, '/');
                        
                        echo "Fetching date for: $cleanDomain... ";
                        $tanggalBerakhir = $this->fetchPandiExpiration($cleanDomain);
                        if ($tanggalBerakhir) {
                            echo "OK ($tanggalBerakhir)\n";
                        } else {
                            echo "FAILED\n";
                        }
                        
                        // Be polite to the API
                        usleep(500000); // 0.5s delay
                    }
                }

                $sisaHari = null;
                if ($tanggalBerakhir) {
                    $end = new \DateTime($tanggalBerakhir);
                    $now = new \DateTime();
                    $diff = $now->diff($end);
                    $sisaHari = (int)$diff->format('%r%a');
                }

                $statusRaw = strtoupper(trim($row[4] ?? ''));
                $status = ($statusRaw === 'AKTIF') ? 'AKTIF' : 'NONAKTIF';
                
                // Map Platform to ID
                $platformRaw = strtoupper(trim($row[8] ?? ''));
                $normalizedPlatform = null;
                if (strpos($platformRaw, 'OPEN SID') !== false) {
                    $normalizedPlatform = 'OPENSID';
                } elseif (strpos($platformRaw, 'SIDEKA') !== false) {
                    $normalizedPlatform = 'SIDEKA-NG';
                } elseif (strpos($platformRaw, 'PIHAK KETIGA') !== false) {
                    $normalizedPlatform = 'PIHAK KETIGA';
                }
                
                $platformId = null;
                if ($normalizedPlatform && isset($platformMap[$normalizedPlatform])) {
                    $platformId = $platformMap[$normalizedPlatform];
                }

                $dataToInsert[] = [
                    'kecamatan'        => $kecamatanClean,
                    'desa_kelurahan'   => strtoupper($desaClean),
                    'domain'           => $domain,
                    'status'           => $status,
                    'tanggal_berakhir' => $tanggalBerakhir,
                    'sisa_hari'        => $sisaHari,
                    'platform_id'      => $platformId,
                    'dikelola_kominfo' => $row[11] ?? '',
                    'keterangan'       => '', 
                    'created_at'       => untukDatabase('now'),
                    'updated_at'       => untukDatabase('now'),
                ];
            }

            if (!empty($dataToInsert)) {
                $model->truncate(); 
                $model->insertBatch($dataToInsert);
                echo "\n" . count($dataToInsert) . " rows inserted successfully.\n";
            } else {
                echo "No data found to insert.\n";
            }

        } catch (\Exception $e) {
            echo "Error seeding data: " . $e->getMessage() . "\n";
        }
    }

    private function fetchPandiExpiration($domain)
    {
        try {
            $client = Services::curlrequest();
            $response = $client->request('GET', "https://rdap.pandi.id/rdap/domain/{$domain}", [
                'timeout' => 5, 
                'http_errors' => false,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ]);

            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody(), true);
                if (isset($body['events']) && is_array($body['events'])) {
                    foreach ($body['events'] as $event) {
                        if (isset($event['eventAction']) && $event['eventAction'] === 'expiration') {
                            if (isset($event['eventDate'])) {
                                return formatIsiInput($event['eventDate']);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Silence errors during seeding
        }

        return null;
    }

    private function parseDate($dateValue)
    {
        if (empty($dateValue) || $dateValue === '#NAME?') return null;

        if (is_numeric($dateValue)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue)->format('Y-m-d');
        }

        try {
            $date = new \DateTime($dateValue);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
