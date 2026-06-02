<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddApiUnitIdToUnitKerja extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('api_unit_id', 'unit_kerja')) {
            $this->forge->addColumn('unit_kerja', [
                'api_unit_id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'id',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('unit_kerja', 'api_unit_id');
    }
}
