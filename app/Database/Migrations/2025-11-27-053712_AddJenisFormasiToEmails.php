<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJenisFormasiToEmails extends Migration
{
    public function up()
    {
        $fields = [
            'jenis_formasi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ];
        $this->forge->addColumn('emails', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('emails', 'jenis_formasi');
    }
}
