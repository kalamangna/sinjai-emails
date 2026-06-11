<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ListAmbiguous3 extends BaseCommand {
    protected $group = 'App';
    protected $name = 'app:ambiguous3';
    protected $description = 'List ambiguous 3';
    public function run(array $params) {
        $model = new \App\Domains\Email\EmailModel();
        
        $all_target_emails = $model->select('emails.id, emails.user, emails.name, emails.email, emails.nip, emails.jabatan, unit_kerja.nama_unit_kerja')
                                       ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                                       ->groupStart()
                                           ->whereIn('emails.status_asn_id', [1, 2]) // PNS or PPPK
                                           ->orWhere('emails.pimpinan', 1) // ASN Leaders
                                       ->groupEnd()
                                       ->where('emails.pimpinan_desa', 0) // Kepala Desa excluded
                                       ->findAll();
        
        $count = 0;
        foreach ($all_target_emails as $email) {
            $nip = $email['nip'] ?? '';
            $cleanNip = str_replace([' ', '.', '-', '\''], '', $nip);

            if (!empty($cleanNip) && (strlen($cleanNip) !== 18 || !is_numeric($cleanNip))) {
                $count++;
            }
        }
        CLI::write("Total ambiguous: $count");
    }
}
