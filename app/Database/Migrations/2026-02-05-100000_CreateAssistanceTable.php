<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssistanceTable extends Migration
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
            'tanggal_kegiatan' => [
                'type' => 'DATE',
            ],
            'agency_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'agency_type' => [
                'type'       => 'ENUM',
                'constraint' => ['OPD', 'DESA', 'KELURAHAN'],
                'null'       => true,
            ],
            // Stores the raw name if needed, or used for display cache
            'agency_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'method' => [
                'type'       => 'ENUM',
                'constraint' => ['Online', 'Offline'],
                'default'    => 'Offline',
            ],
            'services' => [
                'type' => 'TEXT', // Will store JSON array
                'null' => true,
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
        $this->forge->createTable('assistance');
    }

    public function down()
    {
        $this->forge->dropTable('assistance');
    }
}
