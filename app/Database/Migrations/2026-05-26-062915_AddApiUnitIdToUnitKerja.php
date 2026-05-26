<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddApiUnitIdToUnitKerja extends Migration
{
    public function up()
    {
        $this->forge->addColumn('unit_kerja', [
            'api_unit_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('unit_kerja', 'api_unit_id');
    }
}
