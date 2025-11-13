<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSubUnitKerjaToEmails extends Migration
{
    public function up()
    {
        $fields = [
            'sub_unit_kerja' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'unit_kerja',
            ],
        ];
        $this->forge->addColumn('emails', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('emails', 'sub_unit_kerja');
    }
}