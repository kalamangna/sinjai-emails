<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePkHistoriesTable extends Migration
{
    public function up()
    {
        // 1. Buat Tabel pk_histories
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'pk_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'status_asn_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'nomor' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'gaji_nominal' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'gaji_terbilang' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'tanggal_kontrak_awal' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'tanggal_kontrak_akhir' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'default'    => 'Arsip otomatis pembaruan kontrak',
            ],
            'archived_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey('email');
        $this->forge->addKey('tanggal_kontrak_awal');
        $this->forge->createTable('pk_histories', true);

        // 2. Buat Database Trigger untuk Mengarsipkan Otomatis Data Lama Sebelum UPDATE pada tabel pk
        $db = \Config\Database::connect();
        $db->query("DROP TRIGGER IF EXISTS trg_pk_before_update;");
        $triggerSql = "
        CREATE TRIGGER trg_pk_before_update
        BEFORE UPDATE ON pk
        FOR EACH ROW
        BEGIN
            -- Hanya arsipkan jika terdapat data kontrak lama yang valid dan mengalami perubahan
            IF (OLD.nomor IS NOT NULL OR OLD.tanggal_kontrak_awal IS NOT NULL)
               AND (OLD.nomor <=> NEW.nomor = 0 
                    OR OLD.tanggal_kontrak_awal <=> NEW.tanggal_kontrak_awal = 0 
                    OR OLD.tanggal_kontrak_akhir <=> NEW.tanggal_kontrak_akhir = 0) THEN
                
                -- Hindari duplikasi jika data kontrak lama yang sama persis sudah tercatat di riwayat
                IF NOT EXISTS (
                    SELECT 1 FROM pk_histories 
                    WHERE email = OLD.email 
                      AND (nomor <=> OLD.nomor = 1)
                      AND (tanggal_kontrak_awal <=> OLD.tanggal_kontrak_awal = 1)
                      AND (tanggal_kontrak_akhir <=> OLD.tanggal_kontrak_akhir = 1)
                ) THEN
                    INSERT INTO pk_histories (
                        pk_id, email, status_asn_id, nomor, 
                        gaji_nominal, gaji_terbilang, 
                        tanggal_kontrak_awal, tanggal_kontrak_akhir, 
                        keterangan, archived_at, created_at, updated_at
                    ) VALUES (
                        OLD.id, OLD.email, OLD.status_asn_id, OLD.nomor, 
                        OLD.gaji_nominal, OLD.gaji_terbilang, 
                        OLD.tanggal_kontrak_awal, OLD.tanggal_kontrak_akhir, 
                        'Arsip otomatis pembaruan kontrak', NOW(), NOW(), NOW()
                    );
                END IF;
            END IF;
        END;
        ";
        $db->query($triggerSql);
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->query("DROP TRIGGER IF EXISTS trg_pk_before_update;");
        $this->forge->dropTable('pk_histories', true);
    }
}
