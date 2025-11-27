<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyPkTable extends Migration
{
    public function up()
    {
        // Add upah_kerja column
        $this->forge->addColumn('pk', [
            'upah_kerja' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'nomor',
            ],
        ]);

        // Drop unwanted columns
        $this->forge->dropColumn('pk', [
            'nama',
            'nip',
            'tempat_lahir',
            'tanggal_lahir',
            'pendidikan',
            'jabatan',
            'unit_kerja',
        ]);
    }

    public function down()
    {
        // Add back the dropped columns
        $fields = [
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'nip' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'tempat_lahir' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'tanggal_lahir' => [
                'type'       => 'DATE',
                'null'       => true,
            ],
            'pendidikan' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'jabatan' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'unit_kerja' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
        ];
        $this->forge->addColumn('pk', $fields);

        // Drop upah_kerja column
        $this->forge->dropColumn('pk', 'upah_kerja');
    }
}
