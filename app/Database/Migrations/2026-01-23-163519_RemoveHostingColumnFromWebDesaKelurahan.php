<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveHostingColumnFromWebDesaKelurahan extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('web_desa_kelurahan', 'hosting');
    }

    public function down()
    {
        $this->forge->addColumn('web_desa_kelurahan', [
            'hosting' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
        ]);
    }
}