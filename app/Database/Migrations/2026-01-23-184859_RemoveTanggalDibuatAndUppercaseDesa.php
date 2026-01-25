<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveTanggalDibuatAndUppercaseDesa extends Migration
{
    public function up()
    {
        // 1. Drop the column
        $this->forge->dropColumn('web_desa_kelurahan', 'tanggal_dibuat');

        // 2. Uppercase all existing desa_kelurahan names
        $this->db->query("UPDATE web_desa_kelurahan SET desa_kelurahan = UPPER(desa_kelurahan)");
    }

    public function down()
    {
        $this->forge->addColumn('web_desa_kelurahan', [
            'tanggal_dibuat' => [
                'type' => 'DATE',
                'null' => true,
            ],
        ]);
    }
}