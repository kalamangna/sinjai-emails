<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameBagianKeuanganDanPerencanaan extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $db->table('unit_kerja')
            ->where('id', 673)
            ->update(['nama_unit_kerja' => 'BAGIAN PERENCANAAN DAN KEUANGAN']);
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->table('unit_kerja')
            ->where('id', 673)
            ->update(['nama_unit_kerja' => 'BAGIAN KEUANGAN DAN PERENCANAAN']);
    }
}
