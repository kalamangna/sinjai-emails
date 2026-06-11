<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckExpired extends BaseCommand {
    protected $group = 'App';
    protected $name = 'app:checkexpired';
    public function run(array $params) {
        $db = \Config\Database::connect();
        $count = $db->table('emails')->where('bsre_status', 'EXPIRED')->countAllResults();
        CLI::write("Total EXPIRED globally: $count");
        
        $pimpinan = $db->table('emails')->where('bsre_status', 'EXPIRED')->where('(pimpinan = 1 OR pimpinan_desa = 1)')->countAllResults();
        CLI::write("Total EXPIRED Pimpinan: $pimpinan");
        
        $distinct = $db->table('emails')->select('bsre_status, COUNT(*) as count')->groupBy('bsre_status')->get()->getResultArray();
        foreach ($distinct as $d) {
            CLI::write("Status: {$d['bsre_status']}, Count: {$d['count']}");
        }
    }
}
