<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCategoryServiceToHelpdesk extends Migration
{
    public function up()
    {
        $this->forge->addColumn('helpdesk', [
            'category' => [
                'type' => 'INT',
                'null' => true,
                'after' => 'agency_name'
            ],
            'service' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'category'
            ]
        ]);
        
        // Update existing records to default to TTE
        $this->db->query("UPDATE helpdesk SET category = 1, service = 'Tanda Tangan Elektronik' WHERE category IS NULL");
    }

    public function down()
    {
        $this->forge->dropColumn('helpdesk', ['category', 'service']);
    }
}
