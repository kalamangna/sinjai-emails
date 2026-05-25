<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropSeStatusFromUsers extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('users', 'se_status');
    }

    public function down()
    {
        $this->forge->addColumn('users', [
            'se_status' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
        ]);
    }
}
