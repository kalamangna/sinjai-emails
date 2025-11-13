<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddParentIdToUnitKerja extends Migration
{
    public function up()
    {
        $this->forge->addColumn('unit_kerja', [
            'parent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'nama_unit_kerja',
            ],
        ]);

        // Add foreign key constraint
        $this->forge->addForeignKey('parent_id', 'unit_kerja', 'id', 'CASCADE', 'SET NULL');
    }

    public function down()
    {
        // Drop foreign key first
        $this->forge->dropForeignKey('unit_kerja', 'unit_kerja_parent_id_foreign');
        
        // Then drop the column
        $this->forge->dropColumn('unit_kerja', 'parent_id');
    }
}
