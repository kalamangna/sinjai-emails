<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropEmailStatsHistoryTable extends Migration
{
    public function up()
    {
        $this->forge->dropTable('email_stats_history', true);
    }

    public function down()
    {
        // No going back, but we could recreate the schema if needed. 
        // For now, let's keep it empty as the user wants it gone.
    }
}
