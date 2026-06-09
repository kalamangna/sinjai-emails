<?php

namespace App\Domains\Helpdesk;

use CodeIgniter\Model;

class HelpdeskModel extends Model
{
    protected $table            = 'helpdesk';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tiket_id',
        'nama_pemohon',
        'nip_pemohon',
        'kontak_whatsapp',
        'agency_type',
        'agency_id',
        'agency_name',
        'category',
        'service',
        'kategori_layanan',
        'status',
        'admin_notes'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function generateTiketId()
    {
        return 'TTE-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 5));
    }
}
