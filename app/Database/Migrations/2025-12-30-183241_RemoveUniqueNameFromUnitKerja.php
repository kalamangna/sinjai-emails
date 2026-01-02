<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveUniqueNameFromUnitKerja extends Migration
{
    public function up()
    {
        // Dropping the unique index on nama_unit_kerja
        // The error message "Duplicate entry ... for key 'nama_unit_kerja'" confirms the key name.
        $this->db->query('ALTER TABLE unit_kerja DROP INDEX nama_unit_kerja');
    }

    public function down()
    {
        // Re-adding the unique constraint
        $this->db->query('ALTER TABLE unit_kerja ADD UNIQUE (nama_unit_kerja)');
    }
}