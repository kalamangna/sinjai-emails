<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEselonTable extends Migration
{
    public function up()
    {
        // Create eselon table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nama_eselon' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('eselon');

        // Add eselon_id to emails table
        $this->forge->addColumn('emails', [
            'eselon_id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'null' => true,
                'after' => 'status_asn_id',
            ],
        ]);

        // Add foreign key constraint
        $this->forge->addForeignKey('eselon_id', 'eselon', 'id', 'CASCADE', 'SET NULL');
    }

    public function down()
    {
        // Drop foreign key
        $this->forge->dropForeignKey('emails', 'emails_eselon_id_foreign');

        // Drop eselon_id column
        $this->forge->dropColumn('emails', 'eselon_id');

        // Drop eselon table
        $this->forge->dropTable('eselon');
    }
}