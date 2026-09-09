<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailBsreToEmails extends Migration
{
    public function up()
    {
        $this->forge->addColumn('emails', [
            'email_bsre' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => true,
                'default'    => null,
                'after'      => 'tte_source',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('emails', 'email_bsre');
    }
}
