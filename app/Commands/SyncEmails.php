<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Controllers\Email;

class SyncEmails extends BaseCommand
{
    protected $group       = 'email';
    protected $name        = 'email:sync';
    protected $description = 'Synchronizes email accounts from cPanel.';

    public function run(array $params)
    {
        CLI::write('Starting email synchronization...');

        $emailController = new Email();
        $result = $emailController->sync();

        if ($result['success']) {
            CLI::write(CLI::color('Synchronization completed successfully.', 'green'));
            CLI::write($result['message']);
        } else {
            CLI::write(CLI::color('Synchronization failed.', 'red'));
            CLI::write($result['message']);
        }
    }
}
