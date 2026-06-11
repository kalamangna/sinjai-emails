<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckDbNames extends BaseCommand {
    protected $group = 'App';
    protected $name = 'app:checkdbnames';
    protected $description = 'Check Db Names';
    public function run(array $params) {
        $db = \Config\Database::connect();
        $user = $db->table('emails')->where('email', 'adamsaputra@sinjaikab.go.id')->get()->getRowArray();
        CLI::write("User: " . print_r($user, true));
    }
}
