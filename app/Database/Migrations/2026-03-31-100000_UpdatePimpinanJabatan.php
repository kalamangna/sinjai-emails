<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdatePimpinanJabatan extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Update Sekretaris Daerah specifically
        $db->table('emails')
            ->where('email', 'jefriantoasapa@sinjaikab.go.id')
            ->update(['jabatan' => 'SEKRETARIS DAERAH']);

        // 2. Update other pimpinan based on unit name patterns
        $pimpinan = $db->table('emails')
            ->select('emails.id, unit_kerja.nama_unit_kerja')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id')
            ->where('emails.pimpinan', 1)
            ->where('emails.email !=', 'jefriantoasapa@sinjaikab.go.id')
            ->get()
            ->getResultArray();

        foreach ($pimpinan as $p) {
            $newJabatan = null;
            $unit = strtoupper($p['nama_unit_kerja']);

            if (strpos($unit, 'DINAS') !== false) {
                $newJabatan = 'KEPALA DINAS';
            } elseif (strpos($unit, 'BADAN') !== false) {
                $newJabatan = 'KEPALA BADAN';
            } elseif (strpos($unit, 'BAGIAN') !== false) {
                $newJabatan = 'KEPALA BAGIAN';
            } elseif (strpos($unit, 'KECAMATAN') !== false) {
                $newJabatan = 'CAMAT';
            } elseif (strpos($unit, 'KELURAHAN') !== false) {
                $newJabatan = 'LURAH';
            }

            if ($newJabatan) {
                $db->table('emails')
                    ->where('id', $p['id'])
                    ->update(['jabatan' => $newJabatan]);
            }
        }
    }

    public function down()
    {
        // Reverting this is difficult as we don't store original jabatan values here.
        // Usually, for data-only migrations, down() might be empty or perform a reverse logic if known.
    }
}
