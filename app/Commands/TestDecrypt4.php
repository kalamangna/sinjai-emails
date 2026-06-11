<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestDecrypt4 extends BaseCommand {
    protected $group = 'App';
    protected $name = 'app:testdb4';
    protected $description = 'Test DB 4';
    public function run(array $params) {
        $encrypter = \Config\Services::encrypter();
        $str = "8kaAAy48A6S3OcjTYoD5FvDn0v9/mT7RF9XyAZ+dDHy7Fr/ssp";
        
        // Add padding
        $mod = strlen($str) % 4;
        if ($mod > 0) {
            $str .= str_repeat('=', 4 - $mod);
        }
        
        try {
            $dec = $encrypter->decrypt(base64_decode($str));
            CLI::write("Decrypted: " . $dec);
            CLI::write("Length: " . strlen($dec));
        } catch (\Exception $e) {
            CLI::write("Failed: " . $e->getMessage());
        }
    }
}
