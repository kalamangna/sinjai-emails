<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHelpdeskTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tiket_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'nama_pemohon' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'nip_pemohon' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'kontak_whatsapp' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'agency_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'agency_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true, // opd, desa, or kelurahan
            ],
            'agency_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'kategori_layanan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'deskripsi_kendala' => [
                'type' => 'TEXT',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Menunggu', 'Diproses', 'Selesai', 'Ditolak'],
                'default'    => 'Menunggu',
            ],
            'admin_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tiket_id');
        $this->forge->createTable('helpdesk');
    }

    public function down()
    {
        $this->forge->dropTable('helpdesk');
    }
}
