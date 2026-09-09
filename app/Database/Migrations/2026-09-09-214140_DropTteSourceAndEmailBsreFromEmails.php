<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropTteSourceAndEmailBsreFromEmails extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('email_bsre', 'emails')) {
            $this->forge->dropColumn('emails', 'email_bsre');
        }
        if ($this->db->fieldExists('tte_source', 'emails')) {
            $this->forge->dropColumn('emails', 'tte_source');
        }
    }

    public function down()
    {
        // No-op
    }
}
