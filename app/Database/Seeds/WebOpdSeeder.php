<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UnitKerjaModel;
use App\Models\WebOpdModel;

class WebOpdSeeder extends Seeder
{
    public function run()
    {
        $unitKerjaModel = new UnitKerjaModel();
        $webOpdModel = new WebOpdModel();

        // 1. Get all parent units (parent_id IS NULL)
        $parentUnits = $unitKerjaModel->where('parent_id', null)->findAll();

        $dataToInsert = [];

        foreach ($parentUnits as $parent) {
            // Check if already exists in web_opd
            if (!$webOpdModel->where('unit_kerja_id', $parent['id'])->first()) {
                $dataToInsert[] = [
                    'unit_kerja_id' => $parent['id'],
                    'status'        => 'NONAKTIF', // Default to Nonaktif if domain is unknown
                    'domain'        => null,
                    'keterangan'    => 'Initial seed'
                ];
            }

            // 2. Specifically for "Sekretariat Daerah", add all child units
            if (strtoupper(trim($parent['nama_unit_kerja'])) === 'SEKRETARIAT DAERAH') {
                $childUnits = $unitKerjaModel->where('parent_id', $parent['id'])->findAll();
                foreach ($childUnits as $child) {
                    if (!$webOpdModel->where('unit_kerja_id', $child['id'])->first()) {
                        $dataToInsert[] = [
                            'unit_kerja_id' => $child['id'],
                            'status'        => 'NONAKTIF',
                            'domain'        => null,
                            'keterangan'    => 'Initial seed (Child of Sekretariat Daerah)'
                        ];
                    }
                }
            }
        }

        if (!empty($dataToInsert)) {
            $webOpdModel->insertBatch($dataToInsert);
            echo count($dataToInsert) . " records inserted into web_opd table.\n";
        } else {
            echo "No new records to insert into web_opd table.\n";
        }
    }
}