<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTkNegeriIISinjaiUtaraAndBontolempangan extends Migration
{
    private array $units = [
        'TK NEGERI II SINJAI UTARA',
        'TK NEGERI BONTOLEMPANGAN SINJAI BARAT',
    ];

    public function up()
    {
        $db = \Config\Database::connect();
        foreach ($this->units as $nama) {
            $exists = $db->table('unit_kerja')
                ->where('nama_unit_kerja', $nama)
                ->countAllResults();

            if ($exists === 0) {
                $db->table('unit_kerja')->insert([
                    'nama_unit_kerja' => $nama,
                    'parent_id'       => 348,
                ]);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        foreach ($this->units as $nama) {
            $db->table('unit_kerja')->where('nama_unit_kerja', $nama)->delete();
        }
    }
}
