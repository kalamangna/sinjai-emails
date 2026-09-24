<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTteColumnsToPkTable extends Migration
{
    public function up()
    {
        $fields = [
            'tte_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'draft',
                'after'      => 'tanggal_kontrak_akhir',
            ],
            'tte_pegawai_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'after'   => 'tte_status',
            ],
            'tte_pegawai_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'tte_pegawai_at',
            ],
            'tte_pegawai_ip' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'tte_pegawai_file',
            ],
        ];

        $this->forge->addColumn('pk', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('pk', ['tte_status', 'tte_pegawai_at', 'tte_pegawai_file', 'tte_pegawai_ip']);
    }
}
