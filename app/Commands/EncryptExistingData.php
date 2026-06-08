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
        
        CLI::write("Starting encryption and data recovery for $total records...", 'yellow');
        
        $encrypter = \Config\Services::encrypter();
        $success = 0;

        $processField = function($value) use ($encrypter) {
            if (empty($value)) return null;
            $plainText = $value;
            
            // Loop to deeply decrypt if it was accidentally double or triple encrypted
            for ($i = 0; $i < 5; $i++) {
                try {
                    $decrypted = $encrypter->decrypt(base64_decode($plainText));
                    if ($decrypted !== false && $decrypted !== '') {
                        $plainText = $decrypted;
                        continue;
                    }
                } catch (\Throwable $e) {
                    // Decryption failed, meaning we've reached the plain text
                }
                break;
            }
            
            // Normalize (remove spaces, dots, etc.) before returning plain text
            return str_replace([' ', '.', '-', '\''], '', $plainText);
        };
        
        foreach ($emails as $index => $row) {
            $count = $index + 1;
            $updateData = [];
            
            if (!empty($row['nik'])) {
                $plain = $processField($row['nik']);
                $updateData['nik_hash'] = hash('sha256', $plain);
                $updateData['nik'] = base64_encode($encrypter->encrypt($plain));
            }
            
            if (!empty($row['nip'])) {
                $plain = $processField($row['nip']);
                $updateData['nip_hash'] = hash('sha256', $plain);
                $updateData['nip'] = base64_encode($encrypter->encrypt($plain));
            }
            
            if (!empty($row['password'])) {
                $plain = $processField($row['password']);
                $updateData['password'] = base64_encode($encrypter->encrypt($plain));
            }
            
            if (!empty($updateData)) {
                $emailModel->allowCallbacks(false)->update($row['id'], $updateData);
                $success++;
            }
            
            CLI::showProgress($count, $total);
        }
        
        CLI::write("\nEncryption and recovery complete! $success records updated.", 'green');
    }
}
