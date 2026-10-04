<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPerformanceIndexesPhase2 extends Migration
{
    public function up()
    {
        // 1. Table emails
        $this->db->query('ALTER TABLE emails ADD INDEX idx_emails_nip (nip)');
        $this->db->query('ALTER TABLE emails ADD INDEX idx_emails_user (user)');
        $this->db->query('ALTER TABLE emails ADD INDEX idx_emails_unit_kerja_id (unit_kerja_id)');
        $this->db->query('ALTER TABLE emails ADD INDEX idx_emails_status_asn_id (status_asn_id)');
        $this->db->query('ALTER TABLE emails ADD INDEX idx_emails_pensiun_at (pensiun_at)');

        // 2. Table pk
        $this->db->query('ALTER TABLE pk ADD INDEX idx_pk_tte_status (tte_status)');
        $this->db->query('ALTER TABLE pk ADD INDEX idx_pk_status_asn_id (status_asn_id)');
        $this->db->query('ALTER TABLE pk ADD INDEX idx_pk_nomor (nomor)');

        // 3. Table audit_logs
        $this->db->query('ALTER TABLE audit_logs ADD INDEX idx_audit_logs_created_at (created_at)');
        $this->db->query('ALTER TABLE audit_logs ADD INDEX idx_audit_logs_entity_entity_id (entity, entity_id)');
    }

    public function down()
    {
        // Drop from emails
        $this->db->query('ALTER TABLE emails DROP INDEX idx_emails_nip');
        $this->db->query('ALTER TABLE emails DROP INDEX idx_emails_user');
        $this->db->query('ALTER TABLE emails DROP INDEX idx_emails_unit_kerja_id');
        $this->db->query('ALTER TABLE emails DROP INDEX idx_emails_status_asn_id');
        $this->db->query('ALTER TABLE emails DROP INDEX idx_emails_pensiun_at');

        // Drop from pk
        $this->db->query('ALTER TABLE pk DROP INDEX idx_pk_tte_status');
        $this->db->query('ALTER TABLE pk DROP INDEX idx_pk_status_asn_id');
        $this->db->query('ALTER TABLE pk DROP INDEX idx_pk_nomor');

        // Drop from audit_logs
        $this->db->query('ALTER TABLE audit_logs DROP INDEX idx_audit_logs_created_at');
        $this->db->query('ALTER TABLE audit_logs DROP INDEX idx_audit_logs_entity_entity_id');
    }
}
