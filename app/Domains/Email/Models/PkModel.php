<?php

namespace App\Domains\Email\Models;

use CodeIgniter\Model;

class PkModel extends Model
{
    protected $table            = 'pk';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'email', 'status_asn_id', 'nomor', 'gaji_nominal', 'gaji_terbilang', 
        'tanggal_kontrak_awal', 'tanggal_kontrak_akhir',
        'tte_status', 'tte_pegawai_at', 'tte_pegawai_file', 'tte_pegawai_ip',
        'tte_bupati_at', 'tte_bupati_file', 'tte_bupati_ip'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Menyimpan atau memperbarui data PK
     */
    public function savePk(array $data): bool
    {
        if (empty($data['email'])) {
            return false;
        }

        $existing = $this->where('email', $data['email'])->first();
        if ($existing) {
            return (bool) $this->update($existing['id'], $data);
        }

        return (bool) $this->insert($data);
    }

    /**
     * Mengambil daftar riwayat kontrak untuk pegawai berdasarkan email
     */
    public function getHistories(string $email): array
    {
        $historyModel = new PkHistoryModel();
        return $historyModel->getHistoryByEmail($email);
    }

    /**
     * Query builder dengan join tabel emails, unit_kerja, dan status_asn
     */
    public function withPegawaiDetails()
    {
        return $this->select('pk.*, emails.name, emails.nip, emails.nik, emails.user, emails.jabatan, emails.unit_kerja_id, unit_kerja.parent_id as unit_kerja_parent_id, unit_kerja.nama_unit_kerja as unit_kerja_name, parent_uk.nama_unit_kerja as parent_unit_kerja_name, status_asn.nama_status_asn as status_asn_name')
            ->join('emails', 'emails.email = pk.email', 'inner')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
            ->join('unit_kerja as parent_uk', 'parent_uk.id = unit_kerja.parent_id', 'left')
            ->join('status_asn', 'status_asn.id = emails.status_asn_id', 'left');
    }
}

