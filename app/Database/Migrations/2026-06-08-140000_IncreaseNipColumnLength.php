<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class IncreaseNipColumnLength extends Migration
{
    public function up()
    {
        $fields = [
            'nip' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ];
        $this->forge->modifyColumn('emails', $fields);
    }

    public function down()
    {
        $fields = [
            'nip' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
        ];
        $this->forge->modifyColumn('emails', $fields);
    }
}
