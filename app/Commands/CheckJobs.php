<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckJobs extends BaseCommand {
    protected $group = 'App';
    protected $name = 'app:checkjobs';
    public function run(array $params) {
        $db = \Config\Database::connect();
        $count = $db->table('jobs')->where("payload LIKE '%sync_tte_batch%'")->countAllResults();
        CLI::write("Remaining sync_tte_batch jobs: $count");
        
        $jobs = $db->table('jobs')->get()->getResultArray();
        foreach ($jobs as $job) {
            CLI::write("Job ID: {$job['id']}, Queue: {$job['queue']}, Attempts: {$job['attempts']}, Available At: {$job['available_at']}");
        }
    }
}
