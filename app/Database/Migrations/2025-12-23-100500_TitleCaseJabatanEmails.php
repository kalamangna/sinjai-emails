<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TitleCaseJabatanEmails extends Migration
{
    public function up()
    {
        // 1. Fetch all records where jabatan is not null
        $rows = $this->db->table('emails')
            ->select('id, jabatan')
            ->where('jabatan IS NOT NULL')
            ->get()
            ->getResultArray();

        $batchData = [];

        foreach ($rows as $row) {
            $currentJabatan = $row['jabatan'];
            
            // Only process if the string is all uppercase (ignoring non-letters)
            // This is a simple heuristic: if the uppercased version equals the original, 
            // and the lowercased version does NOT equal the original (meaning it has some letters), it's all caps.
            // Or simpler: if it equals its uppercased self, we assume it's "fully uppercase" style and want to fix it.
            // However, the request says "Convert all values... that are fully uppercase".
            if ($currentJabatan === mb_strtoupper($currentJabatan)) {
                
                // Convert to Title Case
                // ucwords(strtolower($string)) is a common way, but for names/titles 
                // mb_convert_case with MB_CASE_TITLE is better for handling unicode if needed.
                $newJabatan = mb_convert_case($currentJabatan, MB_CASE_TITLE, "UTF-8");

                // If the new value is different, add to batch
                if ($newJabatan !== $currentJabatan) {
                    $batchData[] = [
                        'id' => $row['id'],
                        'jabatan' => $newJabatan
                    ];
                }
            }
        }

        // 2. Update in batches
        if (!empty($batchData)) {
            $this->db->table('emails')->updateBatch($batchData, 'id');
        }
    }

    public function down()
    {
        // Reverting this specific text formatting change is complex/impossible without a backup
        // because we don't know which ones were originally uppercase vs already title case.
        // Leaving empty.
    }
}
