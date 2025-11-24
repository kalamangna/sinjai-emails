<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSeStatusToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'se_status' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'after'      => 'user_email',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'se_status');
    }
}
