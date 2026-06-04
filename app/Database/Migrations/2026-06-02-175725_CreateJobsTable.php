<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJobsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'queue' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'default',
            ],
            'payload' => [
                'type' => 'TEXT',
            ],
            'attempts' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'available_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'reserved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('queue');
        $this->forge->addKey('available_at');
        $this->forge->createTable('jobs');
    }

    public function down()
    {
        $this->forge->dropTable('jobs');
    }
}
