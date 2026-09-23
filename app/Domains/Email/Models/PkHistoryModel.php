<?php

namespace App\Domains\Email\Models;

use CodeIgniter\Model;

class PkHistoryModel extends Model
{
    protected $table            = 'pk_histories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'pk_id',
        'email',
        'status_asn_id',
        'nomor',
        'gaji_nominal',
        'gaji_terbilang',
        'tanggal_kontrak_awal',
        'tanggal_kontrak_akhir',
        'keterangan',
        'archived_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mengambil riwayat PK berdasarkan email pegawai
     */
    public function getHistoryByEmail(string $email): array
    {
        return $this->where('email', $email)
                    ->orderBy('tanggal_kontrak_awal', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }
}
