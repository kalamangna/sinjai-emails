<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateEselonToRomawi extends Migration
{
    public function up()
    {
        $mapping = [
            '2a' => 'IIa',
            '2b' => 'IIb',
            '3a' => 'IIIa',
            '3b' => 'IIIb',
            '4a' => 'IVa',
            '4b' => 'IVb',
        ];

        foreach ($mapping as $old => $new) {
            $this->db->table('eselon')
                ->where('nama_eselon', $old)
                ->update(['nama_eselon' => $new]);
        }
    }

    public function down()
    {
        $mapping = [
            'IIa'  => '2a',
            'IIb'  => '2b',
            'IIIa' => '3a',
            'IIIb' => '3b',
            'IVa'  => '4a',
            'IVb'  => '4b',
        ];

        foreach ($mapping as $new => $old) {
            $this->db->table('eselon')
                ->where('nama_eselon', $new)
                ->update(['nama_eselon' => $old]);
        }
    }
}
