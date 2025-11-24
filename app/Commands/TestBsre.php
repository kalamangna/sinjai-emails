<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\BsreApi;
use Exception;

class TestBsre extends BaseCommand
{
    protected $group = 'BSRE';
    protected $name = 'bsre:test';
    protected $description = 'Test the connection to the BSRE API.';

    public function run(array $params)
    {
        $email = $params[0] ?? 'dzul@sinjaikab.go.id';

        CLI::write('Testing BSRE API connection with email: ' . $email);

        try {
            $bsreApi = new BsreApi();
            $response = $bsreApi->checkStatus($email);

            CLI::write('API Response:');
            CLI::print(print_r($response, true));
        } catch (Exception $e) {
            CLI::error('An error occurred: ' . $e->getMessage());
        }
    }
}
