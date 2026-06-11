<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ListTables extends BaseCommand {
    protected $group = 'App';
    protected $name = 'app:listtables';
    protected $description = 'List Tables';
    public function run(array $params) {
        $db = \Config\Database::connect();
        $tables = $db->listTables();
        foreach ($tables as $table) {
            CLI::write($table);
            $fields = $db->getFieldNames($table);
            CLI::write("  - " . implode(", ", $fields));
        }
    }
}
