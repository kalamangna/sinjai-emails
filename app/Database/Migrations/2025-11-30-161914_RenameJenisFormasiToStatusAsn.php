<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameJenisFormasiToStatusAsn extends Migration
{
    public function up()
    {
        // 1. Rename table
        $this->forge->renameTable('jenis_formasi', 'status_asn');

        // 2. Rename column in status_asn table
        $fields = [
            'nama_jenis_formasi' => [
                'name' => 'nama_status_asn',
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
        ];
        $this->forge->modifyColumn('status_asn', $fields);

        // 3. Rename column in emails table
        $fields = [
            'jenis_formasi_id' => [
                'name' => 'status_asn_id',
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
        ];
        $this->forge->modifyColumn('emails', $fields);
    }

    public function down()
    {
        // 1. Revert column rename in emails table
        $fields = [
            'status_asn_id' => [
                'name' => 'jenis_formasi_id',
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
        ];
        $this->forge->modifyColumn('emails', $fields);

        // 2. Revert column rename in status_asn table
        $fields = [
            'nama_status_asn' => [
                'name' => 'nama_jenis_formasi',
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
        ];
        $this->forge->modifyColumn('status_asn', $fields);

        // 3. Revert table rename
        $this->forge->renameTable('status_asn', 'jenis_formasi');
    }
}
