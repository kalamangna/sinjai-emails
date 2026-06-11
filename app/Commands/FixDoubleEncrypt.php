<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class FixDoubleEncrypt extends BaseCommand {
    protected $group = 'App';
    protected $name = 'app:fixdb';
    protected $description = 'Fix Double Encrypted NIP/NIK';
    public function run(array $params) {
        $model = new \App\Domains\Email\EmailModel();
        $encrypter = \Config\Services::encrypter();
        
        // Disable model events temporarily to prevent infinite loops or double encryption on save
        // Actually, EmailModel has $beforeUpdate = ['hashAndEncrypt'];
        // If we want to save the TRUE plain string, we CAN use the model and let it hashAndEncrypt,
        // BUT we must provide the plain string!
        
        $emails = $model->findAll();
        $fixed = 0;
        
        foreach ($emails as $email) {
            $changed = false;
            $updateData = [];
            
            // NIP
            if (!empty($email['nip']) && !is_numeric(str_replace([' ', '.', '-', '\''], '', $email['nip']))) {
                // Try to decrypt it one more time!
                try {
                    $plainNip = $encrypter->decrypt(base64_decode($email['nip']));
                    if (is_numeric(str_replace([' ', '.', '-', '\''], '', $plainNip))) {
                        $updateData['nip'] = $plainNip;
                        $changed = true;
                        CLI::write("Fixed NIP for {$email['email']} -> $plainNip");
                    }
                } catch (\Throwable $e) {}
            }
            
            // NIK
            if (!empty($email['nik']) && !is_numeric(str_replace([' ', '.', '-', '\''], '', $email['nik']))) {
                try {
                    $plainNik = $encrypter->decrypt(base64_decode($email['nik']));
                    if (is_numeric(str_replace([' ', '.', '-', '\''], '', $plainNik))) {
                        $updateData['nik'] = $plainNik;
                        $changed = true;
                        CLI::write("Fixed NIK for {$email['email']} -> $plainNik");
                    }
                } catch (\Throwable $e) {}
            }
            
            if ($changed) {
                $model->update($email['id'], $updateData);
                $fixed++;
            }
        }
        
        CLI::write("Fixed $fixed accounts.");
    }
}
