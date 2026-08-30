<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Domains\Email\Models\EmailModel;
use App\Domains\Email\Services\EmailService;

class AutoPensiunCommand extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'email:auto-pensiun';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Otomatis mendeteksi dan menangguhkan akun ASN yang telah mencapai Batas Usia Pensiun (BUP).';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'email:auto-pensiun [options]';

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--dry-run' => 'Simulasi pengecekan tanpa melakukan suspend atau soft delete ke akun.',
    ];

    /**
     * Actually run the command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $isDryRun = array_key_exists('dry-run', $params) || CLI::getOption('dry-run');

        CLI::write('====================================================', 'yellow');
        CLI::write('   SISTEM IDENTITAS DIGITAL — AUTO PENSIUN BUP      ', 'yellow');
        CLI::write('====================================================', 'yellow');

        if ($isDryRun) {
            CLI::write('MODE: DRY-RUN (Simulasi saja, tidak ada data diubah)', 'cyan');
        }

        $emailModel = new EmailModel();
        $emailService = new EmailService();

        // Ambil seluruh akun aktif berstatus PNS (status_asn_id = 1) yang memiliki NIP dan belum pensiun
        $accounts = $emailModel->select('emails.*, unit_kerja.nama_unit_kerja as unit_kerja_name')
                               ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                               ->where('emails.deleted_at IS NULL')
                               ->where('emails.pensiun_at IS NULL')
                               ->where('emails.status_asn_id', 1) // Khusus PNS
                               ->where('emails.nip IS NOT NULL')
                               ->where('emails.nip !=', '')
                               ->findAll();

        $totalChecked = count($accounts);
        CLI::write("Memeriksa {$totalChecked} akun PNS aktif...\n", 'white');

        $retiredAccounts = [];

        foreach ($accounts as $acc) {
            $bupInfo = $emailService->calculateBupInfo($acc);

            if ($bupInfo['is_pensiun']) {
                $retiredAccounts[] = [
                    'account' => $acc,
                    'bup'     => $bupInfo
                ];
            }
        }

        $totalRetired = count($retiredAccounts);

        if ($totalRetired === 0) {
            CLI::write('Tidak ada akun yang telah mencapai Batas Usia Pensiun (BUP).', 'green');
            return;
        }

        CLI::write("Ditemukan {$totalRetired} akun yang telah mencapai Batas Usia Pensiun (BUP):\n", 'yellow');

        $successCount = 0;
        $failedCount = 0;

        foreach ($retiredAccounts as $idx => $item) {
            $acc = $item['account'];
            $bup = $item['bup'];
            $no = $idx + 1;

            $nama = $acc['name'] ?: $acc['email'];
            $nip = $acc['nip'];
            $jabatan = $acc['jabatan'] ?: '-';
            $unit = $acc['unit_kerja_name'] ?: '-';
            $tmt = $bup['tmt_pensiun'];
            $bupAge = $bup['bup_age'];

            CLI::write("[{$no}/{$totalRetired}] {$nama} ({$acc['email']})", 'white');
            CLI::write("    NIP: {$nip} | BUP: {$bupAge} Thn | TMT Pensiun: {$tmt}", 'light_gray');
            CLI::write("    Jabatan: {$jabatan} | Unit: {$unit}", 'light_gray');

            if ($isDryRun) {
                CLI::write("    Status: [DRY-RUN] Siap ditangguhkan otomatis", 'cyan');
                continue;
            }

            CLI::print("    Memproses penangguhan (cPanel suspend & soft delete)... ");
            $reason = "Mencapai Batas Usia Pensiun (BUP {$bupAge} Thn - TMT {$tmt})";
            $res = $emailService->processAutoPensiun($acc, $reason);

            if ($res) {
                CLI::write("BERHASIL", 'green');
                $successCount++;
            } else {
                CLI::write("GAGAL", 'red');
                $failedCount++;
            }
        }

        CLI::write("\n====================================================", 'yellow');
        if ($isDryRun) {
            CLI::write("Simulasi selesai. Total akun mencapai BUP: {$totalRetired}", 'cyan');
        } else {
            CLI::write("Eksekusi selesai. Sukses: {$successCount} | Gagal: {$failedCount}", 'green');
        }
        CLI::write("====================================================\n", 'yellow');
    }
}
