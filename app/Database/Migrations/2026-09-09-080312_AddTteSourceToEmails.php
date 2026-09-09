<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTteSourceToEmails extends Migration
{
    public function up()
    {
        $this->forge->addColumn('emails', [
            'tte_source' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'default'    => 'email',
                'after'      => 'bsre_status',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('emails', 'tte_source');
    }
}
