<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCategoryToAssistanceTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('assistance', [
            'category' => [
                'type'       => 'ENUM',
                'constraint' => ['Aplikasi SPBE', 'Website Desa/Kelurahan'],
                'null'       => true,
                'after'      => 'agency_name'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('assistance', 'category');
    }
}
