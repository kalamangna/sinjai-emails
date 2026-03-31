<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPangkatToEmailsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('emails', [
            'pangkat_golruang' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'golongan',
            ],
            'pangkat_nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'pangkat_golruang',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('emails', ['pangkat_golruang', 'pangkat_nama']);
    }
}
