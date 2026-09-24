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
        'tte_status', 'tte_pegawai_at', 'tte_pegawai_file', 'tte_pegawai_ip'
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
}

