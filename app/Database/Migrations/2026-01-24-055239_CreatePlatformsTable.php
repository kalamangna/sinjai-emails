<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlatformsTable extends Migration
{
    public function up()
    {
        // 1. Create platforms table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_platform' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
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
        $this->forge->createTable('platforms');

        // 2. Insert initial data
        $db = \Config\Database::connect();
        $db->table('platforms')->insertBatch([
            ['nama_platform' => 'SIDEKA-NG', 'created_at' => date('Y-m-d H:i:s')],
            ['nama_platform' => 'OPENSID', 'created_at' => date('Y-m-d H:i:s')],
            ['nama_platform' => 'PIHAK KETIGA', 'created_at' => date('Y-m-d H:i:s')],
        ]);

        // 3. Add platform_id to web_desa_kelurahan
        $this->forge->addColumn('web_desa_kelurahan', [
            'platform_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'platform'
            ],
        ]);

        // 4. Data Migration: Map VARCHAR platform to INT platform_id
        $platforms = $db->table('platforms')->get()->getResultArray();
        foreach ($platforms as $p) {
            $name = $p['nama_platform'];
            $id = $p['id'];
            // Normalize slightly to catch variations
            $db->query("UPDATE web_desa_kelurahan SET platform_id = $id WHERE platform LIKE '%$name%'");
        }

        // 5. Remove old platform column
        $this->forge->dropColumn('web_desa_kelurahan', 'platform');
        
        // 6. Add Foreign Key
        $db->query("ALTER TABLE web_desa_kelurahan ADD CONSTRAINT fk_platform FOREIGN KEY (platform_id) REFERENCES platforms(id) ON DELETE SET NULL ON UPDATE CASCADE");
    }

    public function down()
    {
        $this->forge->dropTable('platforms');
    }
}