<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTkPertiwiBontosalama extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $exists = $db->table('unit_kerja')
            ->where('nama_unit_kerja', 'TK PERTIWI BONTOSALAMA SINJAI BARAT')
            ->countAllResults();

        if ($exists === 0) {
            $db->table('unit_kerja')->insert([
                'nama_unit_kerja' => 'TK PERTIWI BONTOSALAMA SINJAI BARAT',
                'parent_id'       => 348,
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->table('unit_kerja')->where('nama_unit_kerja', 'TK PERTIWI BONTOSALAMA SINJAI BARAT')->delete();
    }
}
