<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPersonalDataToEmails extends Migration
{
    public function up()
    {
        $fields = [
            'nip' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'tempat_lahir' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'tanggal_lahir' => [
                'type'       => 'DATE',
                'null'       => true,
            ],
            'pendidikan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'jabatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ];
        $this->forge->addColumn('emails', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('emails', ['nip', 'tempat_lahir', 'tanggal_lahir', 'pendidikan', 'jabatan']);
    }
}