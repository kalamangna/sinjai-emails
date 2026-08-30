<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixTkNegeriXiPanrengName extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $db->table('unit_kerja')
            ->where('nama_unit_kerja', 'TK NEGERI XI PANRENG')
            ->update(['nama_unit_kerja' => 'TK NEGERI XI PANRENG SINJAI UTARA']);
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->table('unit_kerja')
            ->where('nama_unit_kerja', 'TK NEGERI XI PANRENG SINJAI UTARA')
            ->update(['nama_unit_kerja' => 'TK NEGERI XI PANRENG']);
    }
}
