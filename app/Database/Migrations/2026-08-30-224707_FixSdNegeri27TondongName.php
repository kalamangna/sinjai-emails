<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixSdNegeri27TondongName extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $db->table('unit_kerja')
            ->where('nama_unit_kerja', 'SD NEG. NO. 27 TONDONG SINJAI TIMUR')
            ->update(['nama_unit_kerja' => 'SD NEG. NO. 27 TONDONG']);
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->table('unit_kerja')
            ->where('nama_unit_kerja', 'SD NEG. NO. 27 TONDONG')
            ->update(['nama_unit_kerja' => 'SD NEG. NO. 27 TONDONG SINJAI TIMUR']);
    }
}
