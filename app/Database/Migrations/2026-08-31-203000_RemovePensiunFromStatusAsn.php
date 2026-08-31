<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemovePensiunFromStatusAsn extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Nullify any references in emails just in case
        $db->table('emails')->where('status_asn_id', 4)->update(['status_asn_id' => null]);
        
        // Nullify any references in pk table if exists
        if ($db->tableExists('pk')) {
            $db->table('pk')->where('status_asn_id', 4)->update(['status_asn_id' => null]);
        }

        // Delete PENSIUN from status_asn table
        $db->table('status_asn')->where('id', 4)->orWhere('nama_status_asn', 'PENSIUN')->delete();
    }

    public function down()
    {
        $db = \Config\Database::connect();
        
        // Re-insert PENSIUN if rolled back
        $exists = $db->table('status_asn')->where('id', 4)->orWhere('nama_status_asn', 'PENSIUN')->countAllResults();
        if ($exists === 0) {
            $db->table('status_asn')->insert([
                'id' => 4,
                'nama_status_asn' => 'PENSIUN'
            ]);
        }
    }
}
