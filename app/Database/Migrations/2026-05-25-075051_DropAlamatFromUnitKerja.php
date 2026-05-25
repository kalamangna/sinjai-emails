<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropAlamatFromUnitKerja extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('unit_kerja', 'alamat');
    }

    public function down()
    {
        $this->forge->addColumn('unit_kerja', [
            'alamat' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
    }
}
