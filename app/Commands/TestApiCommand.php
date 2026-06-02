<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestApiCommand extends BaseCommand {
    protected $group = 'App';
    protected $name = 'test:api';
    protected $description = 'Test API';
    public function run(array $params) {
        $api = new \App\Shared\Libraries\CpanelApi();
        try {
            $res = $api->get_email_accounts_detailed();
            CLI::write("Count: " . count($res));
        } catch (\Exception $e) {
            CLI::error("ERROR: " . $e->getMessage());
        }
    }
}
