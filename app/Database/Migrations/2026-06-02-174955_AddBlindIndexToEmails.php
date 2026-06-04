<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBlindIndexToEmails extends Migration
{
    public function up()
    {
        $fields = [
            'nik_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'nik',
            ],
            'nip_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'nip',
            ],
        ];
        $this->forge->addColumn('emails', $fields);

        // Add index for hash columns to speed up exact match searches
        $this->db->query("CREATE INDEX idx_nik_hash ON emails(nik_hash)");
        $this->db->query("CREATE INDEX idx_nip_hash ON emails(nip_hash)");
    }

    public function down()
    {
        $this->forge->dropColumn('emails', 'nik_hash');
        $this->forge->dropColumn('emails', 'nip_hash');
    }
}
