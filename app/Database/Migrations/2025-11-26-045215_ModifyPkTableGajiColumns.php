<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyPkTableGajiColumns extends Migration
{
    public function up()
    {
        // Rename upah_kerja to gaji_nominal
        $this->forge->modifyColumn('pk', [
            'upah_kerja' => [
                'name'       => 'gaji_nominal',
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
        ]);

        // Add gaji_terbilang column
        $this->forge->addColumn('pk', [
            'gaji_terbilang' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'gaji_nominal',
            ],
        ]);
    }

    public function down()
    {
        // Drop gaji_terbilang column
        $this->forge->dropColumn('pk', 'gaji_terbilang');

        // Rename gaji_nominal back to upah_kerja
        $this->forge->modifyColumn('pk', [
            'gaji_nominal' => [
                'name'       => 'upah_kerja',
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
        ]);
    }
}
