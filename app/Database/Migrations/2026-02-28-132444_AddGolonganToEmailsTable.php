<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGolonganToEmailsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('emails', [
            'golongan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'jabatan',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('emails', 'golongan');
    }
}
