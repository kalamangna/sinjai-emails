<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPerformanceIndexes extends Migration
{
    public function up()
    {
        // emails table indexes
        $this->forge->addColumn('emails', [
            'INDEX unit_kerja_id_idx (unit_kerja_id)',
            'INDEX status_asn_id_idx (status_asn_id)',
            'INDEX eselon_id_idx (eselon_id)',
            'INDEX bsre_status_idx (bsre_status)',
            'INDEX email_idx (email)',
            'INDEX user_idx (user)',
            'INDEX nik_idx (nik)',
            'INDEX nip_idx (nip)',
            'INDEX pimpinan_idx (pimpinan)',
            'INDEX pimpinan_desa_idx (pimpinan_desa)',
            'INDEX created_at_idx (created_at)',
        ]);

        // assistance table indexes
        $this->forge->addColumn('assistance', [
            'INDEX category_idx (category)',
            'INDEX agency_id_idx (agency_id)',
            'INDEX tanggal_kegiatan_idx (tanggal_kegiatan)',
        ]);

        // unit_kerja table indexes
        $this->forge->addColumn('unit_kerja', [
            'INDEX parent_id_idx (parent_id)',
        ]);

        // web_opd table indexes
        $this->forge->addColumn('web_opd', [
            'INDEX unit_kerja_id_idx (unit_kerja_id)',
        ]);

        // web_desa_kelurahan table indexes
        $this->forge->addColumn('web_desa_kelurahan', [
            'INDEX platform_id_idx (platform_id)',
        ]);
    }

    public function down()
    {
        // To be safe, we just remove the indexes we added
        $this->db->query('ALTER TABLE emails DROP INDEX unit_kerja_id_idx, DROP INDEX status_asn_id_idx, DROP INDEX eselon_id_idx, DROP INDEX bsre_status_idx, DROP INDEX email_idx, DROP INDEX user_idx, DROP INDEX nik_idx, DROP INDEX nip_idx, DROP INDEX pimpinan_idx, DROP INDEX pimpinan_desa_idx, DROP INDEX created_at_idx');
        $this->db->query('ALTER TABLE assistance DROP INDEX category_idx, DROP INDEX agency_id_idx, DROP INDEX tanggal_kegiatan_idx');
        $this->db->query('ALTER TABLE unit_kerja DROP INDEX parent_id_idx');
        $this->db->query('ALTER TABLE web_opd DROP INDEX unit_kerja_id_idx');
        $this->db->query('ALTER TABLE web_desa_kelurahan DROP INDEX platform_id_idx');
    }
}
