<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddApiFieldsToUnitKerja extends Migration
{
    public function up()
    {
        $this->forge->addColumn('unit_kerja', [
            'api_unit_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'after'      => 'id',
            ],
            'alamat' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'nama_unit_kerja',
            ],
        ]);
        
        // Add index to api_unit_id for faster lookup
        $this->db->query("CREATE INDEX idx_api_unit_id ON unit_kerja(api_unit_id)");
    }

    public function down()
    {
        $this->forge->dropColumn('unit_kerja', ['api_unit_id', 'alamat']);
    }
}
