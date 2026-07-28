<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SimplifyPlatformsToKominfoAndMandiri extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Ensure MANDIRI platform exists
        $mandiri = $db->table('platforms')->where('nama_platform', 'MANDIRI')->get()->getRowArray();
        if (!$mandiri) {
            $db->table('platforms')->insert([
                'nama_platform' => 'MANDIRI',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
            $mandiriId = $db->insertID();
        } else {
            $mandiriId = $mandiri['id'];
        }

        // 2. Ensure KOMINFO platform exists
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

        // 3. Map existing web_desa_kelurahan platform_id entries:
        // SIDEKA-NG becomes KOMINFO
        $sideka = $db->table('platforms')->where('nama_platform', 'SIDEKA-NG')->get()->getRowArray();
        if ($sideka) {
            $db->table('web_desa_kelurahan')
                ->where('platform_id', $sideka['id'])
                ->update(['platform_id' => $kominfoId]);
        }

        // OPENSID, PIHAK KETIGA, and any other non-KOMINFO/MANDIRI become MANDIRI
        $mandiriPlatforms = $db->table('platforms')
            ->whereNotIn('nama_platform', ['KOMINFO', 'MANDIRI'])
            ->get()
            ->getResultArray();

        $mandiriIds = array_column($mandiriPlatforms, 'id');
        if (!empty($mandiriIds)) {
            $db->table('web_desa_kelurahan')
                ->whereIn('platform_id', $mandiriIds)
                ->update(['platform_id' => $mandiriId]);

            // 4. Delete old platform records
            $db->table('platforms')
                ->whereIn('id', $mandiriIds)
                ->delete();
        }
    }

    public function down()
    {
        // Re-adding previous platforms if rolled back
        $db = \Config\Database::connect();
        $existing = $db->table('platforms')->whereIn('nama_platform', ['SIDEKA-NG', 'OPENSID', 'PIHAK KETIGA'])->get()->getResultArray();
        if (empty($existing)) {
            $db->table('platforms')->insertBatch([
                ['nama_platform' => 'SIDEKA-NG', 'created_at' => date('Y-m-d H:i:s')],
                ['nama_platform' => 'OPENSID', 'created_at' => date('Y-m-d H:i:s')],
                ['nama_platform' => 'PIHAK KETIGA', 'created_at' => date('Y-m-d H:i:s')],
            ]);
        }
    }
}
