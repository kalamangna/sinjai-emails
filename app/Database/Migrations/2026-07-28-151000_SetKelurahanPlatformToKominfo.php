<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SetKelurahanPlatformToKominfo extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Ensure KOMINFO platform exists
        $kominfo = $db->table('platforms')->where('nama_platform', 'KOMINFO')->get()->getRowArray();
        if (!$kominfo) {
            $db->table('platforms')->insert([
                'nama_platform' => 'KOMINFO',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
            $kominfoId = $db->insertID();
        } else {
            $kominfoId = $kominfo['id'];
        }

        // 2. Explicitly update all Kelurahan records to KOMINFO platform
        $db->table('web_desa_kelurahan')
            ->like('desa_kelurahan', 'KELURAHAN')
            ->update(['platform_id' => $kominfoId]);
    }

    public function down()
    {
        // No revert needed
    }
}
