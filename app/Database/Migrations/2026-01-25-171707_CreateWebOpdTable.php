<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWebOpdTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'unit_kerja_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'domain' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'AKTIF',
            ],
            'tanggal_berakhir' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'sisa_hari' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'platform_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'dikelola_kominfo' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'YA',
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('unit_kerja_id', 'unit_kerja', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('platform_id', 'platforms', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('web_opd');
    }

    public function down()
    {
        $this->forge->dropTable('web_opd');
    }
}