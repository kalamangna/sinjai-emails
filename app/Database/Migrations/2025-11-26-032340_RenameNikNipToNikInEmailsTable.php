<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameNikNipToNikInEmailsTable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('emails', [
            'nik_nip' => [
                'name' => 'nik',
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('emails', [
            'nik' => [
                'name' => 'nik_nip',
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);
    }
}