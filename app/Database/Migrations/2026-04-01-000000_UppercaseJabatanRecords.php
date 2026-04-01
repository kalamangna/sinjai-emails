<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UppercaseJabatanRecords extends Migration
{
    public function up()
    {
        $rows = $this->db->table('emails')
            ->select('id, jabatan')
            ->where('jabatan IS NOT NULL')
            ->where('jabatan !=', '')
            ->get()
            ->getResultArray();

        $batchData = [];

        foreach ($rows as $row) {
            $original = $row['jabatan'];
            $converted = mb_strtoupper($original, 'UTF-8');

            if ($converted !== $original) {
                $batchData[] = [
                    'id' => $row['id'],
                    'jabatan' => $converted
                ];
            }
        }

        if (!empty($batchData)) {
            $this->db->table('emails')->updateBatch($batchData, 'id');
        }
    }

    public function down()
    {
        // Formatting change is not easily reversible to Title Case without logic.
    }
}
