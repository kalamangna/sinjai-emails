<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWebDesaKelurahanTable extends Migration
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
            'kecamatan' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'desa_kelurahan' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'domain' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'tanggal_dibuat' => [
                'type' => 'DATE',
                'null' => true,
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
            'platform' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'hosting' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'dikelola_kominfo' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
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
        $this->forge->createTable('web_desa_kelurahan');
    }

    public function down()
    {
        $this->forge->dropTable('web_desa_kelurahan');
    }
}