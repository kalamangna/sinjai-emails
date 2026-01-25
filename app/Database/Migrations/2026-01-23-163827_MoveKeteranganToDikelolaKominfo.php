<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MoveKeteranganToDikelolaKominfo extends Migration
{
    public function up()
    {
        // Move data from keterangan to dikelola_kominfo
        $this->db->query("UPDATE web_desa_kelurahan SET dikelola_kominfo = keterangan");
        
        // Optionally clear keterangan if it was just a temporary placeholder during seed
        $this->db->query("UPDATE web_desa_kelurahan SET keterangan = ''");
    }

    public function down()
    {
        // Reverse if needed: move data back to keterangan
        $this->db->query("UPDATE web_desa_kelurahan SET keterangan = dikelola_kominfo");
        $this->db->query("UPDATE web_desa_kelurahan SET dikelola_kominfo = ''");
    }
}