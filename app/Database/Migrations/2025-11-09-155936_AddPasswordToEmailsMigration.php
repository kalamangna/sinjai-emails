<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPasswordToEmailsMigration extends Migration
{
    public function up()
    {
        $this->forge->addColumn('emails', [
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('emails', 'password');
    }
}
