<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckDbRaw extends BaseCommand {
    protected $group = 'App';
    protected $name = 'app:checkdbraw';
    public function run(array $params) {
        $db = \Config\Database::connect();
        $builder = $db->table('emails');
        $users = $builder->where('nip IS NOT NULL')->limit(5)->get()->getResultArray();
        foreach ($users as $u) {
            CLI::write("User " . $u['name'] . " - NIP: " . $u['nip']);
        }
        $countEmptyStr = $builder->where('nip', '')->countAllResults();
        CLI::write("Total nip = '': $countEmptyStr");
        
        $countHash = $builder->where('nip IS NOT NULL')->countAllResults();
        CLI::write("Total nip IS NOT NULL: $countHash");
    }
}
