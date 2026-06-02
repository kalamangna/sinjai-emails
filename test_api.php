<?php
define('FCPATH', __DIR__ . '/public' . DIRECTORY_SEPARATOR);
require 'vendor/autoload.php';

// Bootstrap CodeIgniter
$app = require_once 'app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/Boot.php';
\CodeIgniter\Boot::bootWeb($paths);

$api = new \App\Shared\Libraries\CpanelApi();
try {
    $res = $api->get_email_accounts_detailed();
    echo "Count: " . count($res) . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
