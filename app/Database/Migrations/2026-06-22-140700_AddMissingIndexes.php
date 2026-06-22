<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingIndexes extends Migration
{
    public function up()
    {
        // Add index for soft deletes since it's heavily used by CI4 Models
        $this->forge->addColumn('emails', [
            'INDEX deleted_at_idx (deleted_at)',
        ]);

        // Add index for website statuses
        $this->forge->addColumn('web_opd', [
            'INDEX status_idx (status)',
        ]);

        // Add index for web desa kelurahan status and kecamatan
        $this->forge->addColumn('web_desa_kelurahan', [
            'INDEX status_idx (status)',
            'INDEX kecamatan_idx (kecamatan)',
        ]);
        
        // Add composite index for tte queries
        $this->db->query('ALTER TABLE emails ADD INDEX bsre_status_deleted_at_idx (bsre_status, deleted_at)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE emails DROP INDEX deleted_at_idx');
        $this->db->query('ALTER TABLE emails DROP INDEX bsre_status_deleted_at_idx');
        $this->db->query('ALTER TABLE web_opd DROP INDEX status_idx');
        $this->db->query('ALTER TABLE web_desa_kelurahan DROP INDEX status_idx');
        $this->db->query('ALTER TABLE web_desa_kelurahan DROP INDEX kecamatan_idx');
    }
}
