<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveDescriptionFromHelpdesk extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('helpdesk', 'deskripsi_kendala');
    }

    public function down()
    {
        $fields = [
            'deskripsi_kendala' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'kategori_layanan'
            ],
        ];
        $this->forge->addColumn('helpdesk', $fields);
    }
}
