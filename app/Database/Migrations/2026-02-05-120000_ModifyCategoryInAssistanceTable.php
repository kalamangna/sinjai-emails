<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyCategoryInAssistanceTable extends Migration
{
    public function up()
    {
        // Change category from ENUM to TINYINT
        // We will assume data migration isn't strictly required or can be handled if needed, 
        // but for this task, we focus on schema change.
        $this->forge->modifyColumn('assistance', [
            'category' => [
                'name'       => 'category',
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'comment'    => '1: Aplikasi SPBE, 2: Website Desa & Kelurahan',
            ],
        ]);
    }

    public function down()
    {
        // Revert to ENUM (using the old label for safety)
        $this->forge->modifyColumn('assistance', [
            'category' => [
                'name'       => 'category',
                'type'       => 'ENUM',
                'constraint' => ['Aplikasi SPBE', 'Website Desa & Kelurahan'],
                'null'       => true,
            ],
        ]);
    }
}
