<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ClearAllPasswordsInEmails extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $db->table('emails')->update(['password' => null]);
    }

    public function down()
    {
        // No-op: Passwords cannot be restored once cleared.
    }
}
