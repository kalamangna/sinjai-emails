<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckAudit extends BaseCommand {
    protected $group = 'App';
    protected $name = 'app:checkaudit';
    protected $description = 'Check Audit';
    public function run(array $params) {
        $db = \Config\Database::connect();
        $logs = $db->table('audit_logs')->limit(5)->get()->getResultArray();
        foreach ($logs as $log) {
            CLI::write("Log: " . print_r($log, true));
        }
    }
}
