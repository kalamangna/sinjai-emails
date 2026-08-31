<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJabatanPltToEmails extends Migration
{
    public function up()
    {
        $fields = [
            'jabatan_plt' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'jabatan',
            ],
            'unit_kerja_plt_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'unit_kerja_id',
            ],
        ];

        $this->forge->addColumn('emails', $fields);

        // Add index on unit_kerja_plt_id for performance
        $this->db->query("ALTER TABLE emails ADD INDEX idx_emails_unit_kerja_plt_id (unit_kerja_plt_id)");
    }

    public function down()
    {
        $this->forge->dropColumn('emails', ['jabatan_plt', 'unit_kerja_plt_id']);
    }
}
