<?php

namespace App\Domains\Email;

use CodeIgniter\Model;

class PkModel extends Model
{
    protected $table            = 'pk';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['email', 'nomor', 'gaji_nominal', 'gaji_terbilang', 'tanggal_kontrak_awal', 'tanggal_kontrak_akhir'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
