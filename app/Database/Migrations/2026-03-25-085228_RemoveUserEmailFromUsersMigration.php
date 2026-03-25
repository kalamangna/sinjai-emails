<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveUserEmailFromUsersMigration extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('users', 'user_email');
    }

    public function down()
    {
        $fields = [
            'user_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'unique'     => true,
                'after'      => 'username'
            ],
        ];
        $this->forge->addColumn('users', $fields);
    }
}
