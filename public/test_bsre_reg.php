<?php
require_once '../app/Config/Paths.php';
$paths = new Config\Paths();
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app = Config\Services::codeigniter();
$app->initialize();

use App\Shared\Libraries\BsreApi;

$api = new BsreApi();
// Try to register anitar to see the exact API response
$result = $api->registerUser('Anita', 'anitar@sinjaikab.go.id');

echo "Result:\n";
print_r($result);
