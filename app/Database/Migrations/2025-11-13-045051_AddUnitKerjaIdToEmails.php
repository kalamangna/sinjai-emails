<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUnitKerjaIdToEmails extends Migration
{
    public function up()
    {
        $this->forge->addColumn('emails', [
            'unit_kerja_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'sub_unit_kerja',
            ],
        ]);

        $this->forge->addForeignKey('unit_kerja_id', 'unit_kerja', 'id', 'CASCADE', 'SET NULL');
    }

    public function down()
    {
        $this->forge->dropForeignKey('emails', 'emails_unit_kerja_id_foreign');
        $this->forge->dropColumn('emails', 'unit_kerja_id');
    }
}
