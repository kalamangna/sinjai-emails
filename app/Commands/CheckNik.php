<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckNik extends BaseCommand {
    protected $group = 'App';
    protected $name = 'app:checknik';
    public function run(array $params) {
        $db = \Config\Database::connect();
        $countNikHash = $db->table('emails')->where('nik_hash IS NOT NULL')->countAllResults();
        CLI::write("Total nik_hash IS NOT NULL: $countNikHash");
    }
}
