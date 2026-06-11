<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckPk extends BaseCommand {
    protected $group = 'App';
    protected $name = 'app:checkpk';
    protected $description = 'Check Pk';
    public function run(array $params) {
        $db = \Config\Database::connect();
        $pks = $db->table('pk')->limit(5)->get()->getResultArray();
        foreach ($pks as $pk) {
            CLI::write("PK: " . print_r($pk, true));
        }
    }
}
