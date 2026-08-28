<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Shared\Models\JobModel;

class QueueFlush extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'queue:flush';
    protected $description = 'Flush/clear all pending background jobs from the queue.';

    public function run(array $params)
    {
        $jobModel = new JobModel();
        $count = $jobModel->countAllResults();
        
        $jobModel->truncate();
        
        CLI::write("Successfully flushed {$count} pending jobs from queue.", 'green');
    }
}
