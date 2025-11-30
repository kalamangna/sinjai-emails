<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefactorJenisFormasi extends Migration
{
    public function up()
    {
        // 1. Create 'jenis_formasi' table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_jenis_formasi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('jenis_formasi');

        // 2. Insert default values
        $data = [
            ['nama_jenis_formasi' => 'PNS'],
            ['nama_jenis_formasi' => 'PPPK'],
            ['nama_jenis_formasi' => 'PPPK PARUH WAKTU'],
        ];
        $this->db->table('jenis_formasi')->insertBatch($data);

        // 3. Add 'jenis_formasi_id' column to 'emails' table
        $fields = [
            'jenis_formasi_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ];
        $this->forge->addColumn('emails', $fields);

        // 4. Migrate existing data
        // Get all emails
        $emails = $this->db->table('emails')->get()->getResultArray();
        foreach ($emails as $email) {
            if (!empty($email['jenis_formasi'])) {
                $jenis = $this->db->table('jenis_formasi')
                    ->where('nama_jenis_formasi', $email['jenis_formasi'])
                    ->get()->getRowArray();
                
                if ($jenis) {
                    $this->db->table('emails')
                        ->where('id', $email['id'])
                        ->update(['jenis_formasi_id' => $jenis['id']]);
                }
            }
        }

        // 5. Drop 'jenis_formasi' string column
        $this->forge->dropColumn('emails', 'jenis_formasi');
    }

    public function down()
    {
        // 1. Add 'jenis_formasi' column back
        $fields = [
            'jenis_formasi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ];
        $this->forge->addColumn('emails', $fields);

        // 2. Migrate data back
        $emails = $this->db->table('emails')->get()->getResultArray();
        foreach ($emails as $email) {
            if (!empty($email['jenis_formasi_id'])) {
                $jenis = $this->db->table('jenis_formasi')
                    ->where('id', $email['jenis_formasi_id'])
                    ->get()->getRowArray();
                
                if ($jenis) {
                    $this->db->table('emails')
                        ->where('id', $email['id'])
                        ->update(['jenis_formasi' => $jenis['nama_jenis_formasi']]);
                }
            }
        }

        // 3. Drop 'jenis_formasi_id' column
        $this->forge->dropColumn('emails', 'jenis_formasi_id');

        // 4. Drop 'jenis_formasi' table
        $this->forge->dropTable('jenis_formasi');
    }
}
