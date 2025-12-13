<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBsreStatusToEmailsMigration extends Migration
{
    public function up()
    {
        $this->forge->addColumn('emails', [
            'bsre_status' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'after'      => 'status_asn_id', // Adjust 'after' as needed
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('emails', 'bsre_status');
    }
}
