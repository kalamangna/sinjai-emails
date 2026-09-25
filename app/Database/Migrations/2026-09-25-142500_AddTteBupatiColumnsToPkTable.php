<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTteBupatiColumnsToPkTable extends Migration
{
    public function up()
    {
        $fields = [
            'tte_bupati_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'after'   => 'tte_pegawai_ip',
            ],
            'tte_bupati_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'tte_bupati_at',
            ],
            'tte_bupati_ip' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'tte_bupati_file',
            ],
        ];

        $this->forge->addColumn('pk', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('pk', ['tte_bupati_at', 'tte_bupati_file', 'tte_bupati_ip']);
    }
}
