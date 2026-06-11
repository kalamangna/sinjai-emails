<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckDuplicateNames extends BaseCommand {
    protected $group = 'App';
    protected $name = 'app:checkdups';
    protected $description = 'Check Duplicate Names';
    public function run(array $params) {
        $db = \Config\Database::connect();
        $query = $db->query("SELECT name, COUNT(*) as count FROM emails WHERE deleted_at IS NULL AND name != '' AND name IS NOT NULL GROUP BY name HAVING count > 1");
        $results = $query->getResultArray();
        CLI::write("Total duplicate names: " . count($results));
    }
}
