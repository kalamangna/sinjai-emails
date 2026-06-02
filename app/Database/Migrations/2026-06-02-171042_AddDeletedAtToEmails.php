<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtToEmails extends Migration
{
    public function up()
    {
        $this->forge->addColumn('emails', [
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('emails', 'deleted_at');
    }
}
