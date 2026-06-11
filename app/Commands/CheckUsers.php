<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckUsers extends BaseCommand {
    protected $group = 'App';
    protected $name = 'app:checkusers';
    protected $description = 'Check Users';
    public function run(array $params) {
        $db = \Config\Database::connect();
        $users = $db->table('users')->limit(5)->get()->getResultArray();
        foreach ($users as $u) {
            CLI::write("User: " . print_r($u, true));
        }
    }
}
