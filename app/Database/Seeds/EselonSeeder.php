<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EselonSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['nama_eselon' => 'IIa'],
            ['nama_eselon' => 'IIb'],
            ['nama_eselon' => 'IIIa'],
            ['nama_eselon' => 'IIIb'],
            ['nama_eselon' => 'IVa'],
            ['nama_eselon' => 'IVb'],
            ['nama_eselon' => '2a'],
            ['nama_eselon' => '2b'],
            ['nama_eselon' => '3a'],
            ['nama_eselon' => '3b'],
            ['nama_eselon' => '4a'],
            ['nama_eselon' => '4b'],
        ];

        // Using Query Builder
        $this->db->table('eselon')->insertBatch($data);
    }
}