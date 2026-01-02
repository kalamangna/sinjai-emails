<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPimpinanDesaToEmails extends Migration
{
    public function up()
    {
        $fields = [
            'pimpinan_desa' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'pimpinan',
                'comment'    => '0: Not Pimpinan Desa, 1: Pimpinan Desa',
            ],
        ];
        $this->forge->addColumn('emails', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('emails', 'pimpinan_desa');
    }
}
