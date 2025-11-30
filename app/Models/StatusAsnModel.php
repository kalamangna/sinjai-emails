<?php

namespace App\Models;

use CodeIgniter\Model;

class StatusAsnModel extends Model
{
    protected $table            = 'status_asn';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['nama_status_asn'];
    protected $useTimestamps    = false;
}
