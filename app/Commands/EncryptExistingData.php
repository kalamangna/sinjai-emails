<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Domains\Email\EmailModel;

class EncryptExistingData extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'encrypt:data';
    protected $description = 'Encrypt existing NIK, NIP, and Password in the emails table.';

    public function run(array $params)
    {
        $emailModel = new EmailModel();
        
        // Disable callbacks temporarily to handle process manually
        $emailModel->setAllowedFields(array_merge($emailModel->allowedFields, ['id']));
        
        $emails = $emailModel->allowCallbacks(false)->findAll();
        $total = count($emails);
        
        CLI::write("Starting encryption for $total records...", 'yellow');
        
        $encrypter = \Config\Services::encrypter();
        $success = 0;
        
        foreach ($emails as $index => $row) {
            $count = $index + 1;
            $updateData = [];
            
            // Check if already encrypted (very basic check by looking at base64/length)
            // Or just re-encrypt everything since we also need to generate hashes
            
            if (!empty($row['nik'])) {
                $updateData['nik_hash'] = hash('sha256', $row['nik']);
                $updateData['nik'] = base64_encode($encrypter->encrypt($row['nik']));
            }
            
            if (!empty($row['nip'])) {
                $updateData['nip_hash'] = hash('sha256', $row['nip']);
                $updateData['nip'] = base64_encode($encrypter->encrypt($row['nip']));
            }
            
            if (!empty($row['password'])) {
                $updateData['password'] = base64_encode($encrypter->encrypt($row['password']));
            }
            
            if (!empty($updateData)) {
                $emailModel->allowCallbacks(false)->update($row['id'], $updateData);
                $success++;
            }
            
            CLI::showProgress($count, $total);
        }
        
        CLI::write("\nEncryption complete! $success records updated.", 'green');
    }
}
