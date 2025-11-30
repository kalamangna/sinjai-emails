<?php

namespace App\Models;

use CodeIgniter\Model;

class JenisFormasiModel extends Model
{
    protected $table            = 'jenis_formasi';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['nama_jenis_formasi'];
    protected $useTimestamps    = false;
}
