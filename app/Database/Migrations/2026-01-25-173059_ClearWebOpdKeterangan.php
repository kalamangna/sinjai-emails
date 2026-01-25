<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ClearWebOpdKeterangan extends Migration
{
    public function up()
    {
        $this->db->table('web_opd')->update(['keterangan' => null]);
    }

    public function down()
    {
        // No simple way to undo clearing data
    }
}