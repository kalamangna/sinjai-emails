<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeUserPasswordNullable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('users', [
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('users', [
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
        ]);
    }
}
