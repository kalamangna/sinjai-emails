<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixDikelolaKominfoDataMapping extends Migration
{
    public function up()
    {
        // Move data from keterangan to dikelola_kominfo (overwriting existing data in dikelola_kominfo)
        $this->db->query("UPDATE web_desa_kelurahan SET dikelola_kominfo = keterangan");
        
        // Clear keterangan
        $this->db->query("UPDATE web_desa_kelurahan SET keterangan = ''");
    }

    public function down()
    {
        // No easy way to undo unless we assume dikelola_kominfo had 'AKTIF'/'EXPIRED' etc.
    }
}