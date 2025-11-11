<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNameToEmailsMigration extends Migration
{
    public function up()
    {
        $this->forge->addColumn('emails', [
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('emails', 'name');
    }
}
