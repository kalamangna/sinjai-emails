<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPimpinanToEmails extends Migration
{
    public function up()
    {
        $fields = [
            'pimpinan' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '0: Not Pimpinan, 1: Pimpinan',
            ],
        ];
        $this->forge->addColumn('emails', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('emails', 'pimpinan');
    }
}