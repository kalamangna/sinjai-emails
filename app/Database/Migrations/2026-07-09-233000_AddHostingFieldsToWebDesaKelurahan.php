<?php
 
namespace App\Database\Migrations;
 
use CodeIgniter\Database\Migration;
 
class AddHostingFieldsToWebDesaKelurahan extends Migration
{
    public function up()
    {
        $this->forge->addColumn('web_desa_kelurahan', [
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'domain',
            ],
            'hosting_provider' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'ip_address',
            ],
            'hosting_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'UNKNOWN',
                'after'      => 'hosting_provider',
            ],
        ]);
    }
 
    public function down()
    {
        $this->forge->dropColumn('web_desa_kelurahan', ['ip_address', 'hosting_provider', 'hosting_status']);
    }
}
