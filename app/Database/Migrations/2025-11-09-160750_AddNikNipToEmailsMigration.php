<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNikNipToEmailsMigration extends Migration
{
    public function up()
    {
        $this->forge->addColumn('emails', [
            'nik_nip' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('emails', 'nik_nip');
    }
}
