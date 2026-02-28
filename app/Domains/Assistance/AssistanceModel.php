<?php

namespace App\Domains\Assistance;

use CodeIgniter\Model;

class AssistanceModel extends Model
{
    protected $table            = 'assistance';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tanggal_kegiatan',
        'agency_id',
        'agency_type',
        'agency_name',
        'category',
        'method',
        'services',
        'keterangan'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
