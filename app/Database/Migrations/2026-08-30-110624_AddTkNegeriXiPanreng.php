<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTkNegeriXiPanreng extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $exists = $db->table('unit_kerja')
            ->like('nama_unit_kerja', 'PANRENG')
            ->like('nama_unit_kerja', 'TK')
            ->countAllResults();

        if ($exists === 0) {
            $db->table('unit_kerja')->insert([
                'nama_unit_kerja' => 'TK NEGERI XI PANRENG',
                'parent_id'       => 348,
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->table('unit_kerja')->where('nama_unit_kerja', 'TK NEGERI XI PANRENG')->delete();
    }
}
