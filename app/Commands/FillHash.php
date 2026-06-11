<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class FillHash extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'app:fillhash';
    protected $description = 'Populate nip_hash and nik_hash without encrypting the data.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('emails');
        
        $users = $builder->select('id, nip, nik')->get()->getResultArray();
        
        $hashNipCount = 0;
        $hashNikCount = 0;
        
        CLI::write("Memulai pembuatan Hash Index...", 'yellow');
        
        foreach ($users as $user) {
            $update = [];
            
            // Check NIP
            if (!empty($user['nip'])) {
                $cleanNip = str_replace([' ', '.', '-', '\''], '', $user['nip']);
                $update['nip_hash'] = hash('sha256', $cleanNip);
                $hashNipCount++;
            }
            
            // Check NIK
            if (!empty($user['nik'])) {
                $cleanNik = str_replace([' ', '.', '-', '\''], '', $user['nik']);
                $update['nik_hash'] = hash('sha256', $cleanNik);
                $hashNikCount++;
            }
            
            if (!empty($update)) {
                $builder->where('id', $user['id'])->update($update);
            }
        }
        
        CLI::write("Berhasil membuat Hash untuk:", 'green');
        CLI::write("- $hashNipCount NIP");
        CLI::write("- $hashNikCount NIK");
        CLI::write("Pencarian dan Sinkronisasi sekarang akan berjalan 100x lebih cepat!", 'green');
    }
}
