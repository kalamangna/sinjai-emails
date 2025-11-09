<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUnitKerjaTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_unit_kerja' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'unique'     => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('unit_kerja');
    }

    public function down()
    {
        $this->forge->dropTable('unit_kerja');
    }
}
