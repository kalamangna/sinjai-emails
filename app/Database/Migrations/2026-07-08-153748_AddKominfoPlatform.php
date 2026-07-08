<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKominfoPlatform extends Migration
{
    public function up()
    {
        $this->db->table('platforms')->insert([
            'nama_platform' => 'KOMINFO',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->db->table('platforms')
            ->where('nama_platform', 'KOMINFO')
            ->delete();
    }
}
