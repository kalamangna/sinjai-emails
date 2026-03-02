<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusAsnIdToPkTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pk', [
            'status_asn_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'email',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pk', 'status_asn_id');
    }
}
