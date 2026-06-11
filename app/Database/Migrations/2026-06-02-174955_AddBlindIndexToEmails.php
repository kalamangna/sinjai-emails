<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBlindIndexToEmails extends Migration
{
    public function up()
    {
        $fields = [
            'nik' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'nik',
            ],
            'nip' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'nip',
            ],
        ];
        $this->forge->addColumn('emails', $fields);

        // Add index for hash columns to speed up exact match searches
        $this->db->query("CREATE INDEX idx_nik ON emails(nik)");
        $this->db->query("CREATE INDEX idx_nip ON emails(nip)");
    }

    public function down()
    {
        $this->forge->dropColumn('emails', 'nik');
        $this->forge->dropColumn('emails', 'nip');
    }
}
