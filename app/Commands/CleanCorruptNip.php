<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CleanCorruptNip extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'app:cleandb';
    protected $description = 'Membersihkan data NIP dan NIK yang korup (enkripsi ganda/hash) dari database.';

    public function run(array $params)
    {
        CLI::write("Memulai pembersihan data NIP/NIK yang korup...");
        
        $db = \Config\Database::connect();
        $builder = $db->table('emails');
        
        // Ambil semua data email
        $emails = $builder->get()->getResultArray();
        $fixed = 0;
        
        foreach ($emails as $email) {
            $update = [];
            
            // NIP atau NIK yang valid seharusnya 16 atau 18 digit (kurang dari 20 karakter).
            // Jika string lebih dari 20 karakter, itu berarti string tersebut adalah base64/hash yang korup.
            if (!empty($email['nip']) && strlen($email['nip']) > 20) {
                $update['nip'] = null;
                $update['nip_hash'] = null;
            }
            if (!empty($email['nik']) && strlen($email['nik']) > 20) {
                $update['nik'] = null;
                $update['nik_hash'] = null;
            }
            
            if (!empty($update)) {
                $builder->where('id', $email['id'])->update($update);
                $fixed++;
            }
        }
        
        CLI::write("Berhasil membersihkan $fixed akun yang memiliki NIP/NIK korup.", "green");
        CLI::write("Akun-akun tersebut sekarang berstatus 'Tanpa NIP'.", "yellow");
    }
}
