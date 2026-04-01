<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CapitalizeJabatanExceptDan extends Migration
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
            
            // 1. Convert to Title Case
            $converted = mb_convert_case($original, MB_CASE_TITLE, "UTF-8");

            // 2. Fix the word "Dan" to "dan"
            // We split by spaces and check each word.
            $words = explode(' ', $converted);
            foreach ($words as $index => &$word) {
                // Skip the first word, and check if subsequent words are "Dan"
                if ($index > 0 && strtolower($word) === 'dan') {
                    $word = 'dan';
                }
            }
            $final = implode(' ', $words);

            if ($final !== $original) {
                $batchData[] = [
                    'id' => $row['id'],
                    'jabatan' => $final
                ];
            }
        }

        if (!empty($batchData)) {
            $this->db->table('emails')->updateBatch($batchData, 'id');
        }
    }

    public function down()
    {
        // Formatting change is not reversible without data loss
    }
}
