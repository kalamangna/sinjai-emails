<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmailStatsHistoryTable extends Migration
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
            'tanggal' => [
                'type' => 'DATE',
            ],
            'total_akun' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'total_storage_mb' => [
                'type'       => 'FLOAT',
                'constraint' => '12,2',
                'default'    => 0.00,
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
        $this->forge->addUniqueKey('tanggal');
        $this->forge->createTable('email_stats_history');
    }

    public function down()
    {
        $this->forge->dropTable('email_stats_history');
    }
}
