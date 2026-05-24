<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPensiunAtToEmails extends Migration
{
    public function up()
    {
        $this->forge->addColumn('emails', [
            'pensiun_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'pimpinan_desa'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('emails', 'pensiun_at');
    }
}
