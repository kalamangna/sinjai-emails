<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropBlindIndexFromEmails extends Migration
{
    public function up()
    {
        // Drop index first if exists
        try {
            $this->db->query("DROP INDEX idx_nip_hash ON emails");
            $this->db->query("DROP INDEX idx_nik_hash ON emails");
        } catch (\Throwable $e) {
            // Index might not exist or name is different
        }

        try {
            $this->forge->dropColumn('emails', 'nip_hash');
            $this->forge->dropColumn('emails', 'nik_hash');
        } catch (\Throwable $e) {
            
        }
    }

    public function down()
    {
        // Cannot revert
    }
}
