<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NormalizeWebStatusValues extends Migration
{
    public function up()
    {
        // Normalize various "inactive" strings to NONAKTIF
        $this->db->query("UPDATE web_desa_kelurahan SET status = 'NONAKTIF' WHERE status IN ('NON AKTIF', 'TIDAK AKTIF', 'SUSPEND', 'EXPIRED', 'OFFLINE') OR status IS NULL OR status = ''");
        
        // Ensure AKTIF is consistent
        $this->db->query("UPDATE web_desa_kelurahan SET status = 'AKTIF' WHERE status = 'AKTIF'");
        
        // Catch anything else as NONAKTIF just in case
        $this->db->query("UPDATE web_desa_kelurahan SET status = 'NONAKTIF' WHERE status NOT IN ('AKTIF', 'NONAKTIF')");
    }

    public function down()
    {
        // No logical way to reverse specific previous states
    }
}