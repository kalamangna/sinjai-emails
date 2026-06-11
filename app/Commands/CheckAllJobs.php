<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckAllJobs extends BaseCommand {
    protected $group = 'App';
    protected $name = 'app:checkalljobs';
    public function run(array $params) {
        $db = \Config\Database::connect();
        $jobs = $db->table('jobs')->get()->getResultArray();
        CLI::write("Total jobs in queue: " . count($jobs));
        foreach ($jobs as $job) {
            CLI::write("ID: {$job['id']}, Type: " . json_decode($job['payload'], true)['type']);
        }
    }
}
