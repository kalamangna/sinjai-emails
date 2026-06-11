<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class EncryptExistingData extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'app:encryptall';
    protected $description = 'Encrypt all plain-text NIP and NIK in the database.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('emails');
        $encrypter = \Config\Services::encrypter();
        
        $users = $builder->select('id, email, nip, nik, nip_hash, nik_hash')->get()->getResultArray();
        
        $encryptedNipCount = 0;
        $encryptedNikCount = 0;
        
        CLI::write("Memulai proses enkripsi data massal...", 'yellow');
        
        foreach ($users as $user) {
            $update = [];
            
            // Check NIP
            if (!empty($user['nip'])) {
                // If it looks like a plain text NIP (18 digits)
                if (preg_match('/^\d{18}$/', $user['nip'])) {
                    $update['nip'] = base64_encode($encrypter->encrypt($user['nip']));
                    $update['nip_hash'] = hash('sha256', $user['nip']);
                    $encryptedNipCount++;
                }
            }
            
            // Check NIK
            if (!empty($user['nik'])) {
                // If it looks like a plain text NIK (16 digits)
                if (preg_match('/^\d{16}$/', $user['nik'])) {
                    $update['nik'] = base64_encode($encrypter->encrypt($user['nik']));
                    $update['nik_hash'] = hash('sha256', $user['nik']);
                    $encryptedNikCount++;
                }
            }
            
            if (!empty($update)) {
                $builder->where('id', $user['id'])->update($update);
            }
        }
        
        CLI::write("Berhasil mengamankan data (Encrypt & Hash):", 'green');
        CLI::write("- $encryptedNipCount NIP");
        CLI::write("- $encryptedNikCount NIK");
        CLI::write("Selesai!", 'green');
    }
}
