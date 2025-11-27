<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnsToEmailsAndPk extends Migration
{
    public function up()
    {
        // Add columns to emails table
        $this->forge->addColumn('emails', [
            'gelar_depan' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'after'      => 'name',
            ],
            'gelar_belakang' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'after'      => 'gelar_depan',
            ],
        ]);

        // Add columns to pk table
        $this->forge->addColumn('pk', [
            'tanggal_kontrak_awal' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'gaji_terbilang',
            ],
            'tanggal_kontrak_akhir' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'tanggal_kontrak_awal',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('emails', ['gelar_depan', 'gelar_belakang']);
        $this->forge->dropColumn('pk', ['tanggal_kontrak_awal', 'tanggal_kontrak_akhir']);
    }
}
