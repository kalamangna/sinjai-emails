<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Domains\Email\Models\EmailModel;
use App\Domains\Email\Services\EmailService;

class ProcessNonUsulanCommand extends BaseCommand
{
    protected $group = 'App';
    protected $name = 'email:process-non-usulan';
    protected $description = 'Menangguhkan akun PPPK Paruh Waktu yang tidak diusulkan perpanjangan kontraknya.';
    protected $usage = 'email:process-non-usulan [options]';
    protected $options = [
        '--dry-run' => 'Simulasi pengecekan tanpa suspend cPanel atau update database.',
    ];

    public function run(array $params)
    {
        $isDryRun = array_key_exists('dry-run', $params) || CLI::getOption('dry-run');

        CLI::write('====================================================', 'yellow');
        CLI::write(' PENANGGUHAN AKUN NON-USULAN PERPANJANGAN KONTRAK  ', 'yellow');
        CLI::write('====================================================', 'yellow');

        if ($isDryRun) {
            CLI::write('MODE: DRY-RUN (Simulasi saja, tidak ada data diubah)', 'cyan');
        }

        $targetList = [
            ['nip' => '197202022025212043', 'nama' => 'SUTRAWATI', 'alasan' => 'mengundurkan diri (sakit) (proses)'],
            ['nip' => '198001142025211044', 'nama' => 'KHOMENI S. PUTRA', 'alasan' => 'meninggal'],
            ['nip' => '198112012025211085', 'nama' => 'ANDI BAKRI SYAM', 'alasan' => 'mengundurkan diri (tk)'],
            ['nip' => '199501172025212100', 'nama' => 'NURAMNISA', 'alasan' => 'mengundurkan diri'],
            ['nip' => '199504122025212104', 'nama' => 'CAHAYA MASGANDASARI', 'alasan' => 'mengundurkan diri'],
            ['nip' => '197909172025211073', 'nama' => 'ANDI RAHMATULLAH', 'alasan' => 'mengundurkan diri (proses)'],
            ['nip' => '199511272025211108', 'nama' => 'ARI HANDAYANI ARHAM', 'alasan' => 'mengundurkan diri (proses)'],
            ['nip' => '197008252025211026', 'nama' => 'ARDI', 'alasan' => 'meninggal'],
            ['nip' => '199911282025212071', 'nama' => 'KHAFIFA WAHDANIAH', 'alasan' => 'mengundurkan diri (proses)'],
            ['nip' => '198904132025212131', 'nama' => 'NIAR FATMASARI', 'alasan' => 'mengundurkan diri'],
            ['nip' => '198909082025212128', 'nama' => 'KASMAWATI', 'alasan' => 'mengundurkan diri (tk)'],
            ['nip' => '198312232025211109', 'nama' => 'JUSMIN', 'alasan' => 'mengundurkan diri (tk)'],
            ['nip' => '198612102025211154', 'nama' => 'SUPRIADI HAMMA', 'alasan' => 'mengundurkan diri (tk)'],
            ['nip' => '198106162025211104', 'nama' => 'IRVAN. M', 'alasan' => 'mengundurkan diri (proses)'],
            ['nip' => '198012072025211088', 'nama' => 'AMBO TUO', 'alasan' => 'meninggal'],
            ['nip' => '197312312025211325', 'nama' => 'BAHRI', 'alasan' => 'meninggal'],
            ['nip' => '199612312025211145', 'nama' => 'M.RIDWAN', 'alasan' => 'meninggal'],
            ['nip' => '198910272025212070', 'nama' => 'ANDI INDRA ASTUTI', 'alasan' => 'mengundurkan diri (proses)'],
            ['nip' => '199003152025211119', 'nama' => 'MUZAKKIR', 'alasan' => 'mengundurkan diri (proses)'],
            ['nip' => '198908052025212104', 'nama' => 'SUSI SUSANTI', 'alasan' => 'mengundurkan diri (proses)'],
            ['nip' => '199504072025212142', 'nama' => 'RAHMI', 'alasan' => 'mengundurkan diri (proses)'],
            ['nip' => '199609032025212118', 'nama' => 'NURMILA', 'alasan' => 'mengundurkan diri (proses)'],
            ['nip' => '199502032025212097', 'nama' => 'RUKMANA', 'alasan' => 'mengundurkan diri'],
            ['nip' => '199704282025211091', 'nama' => 'HERIANTO', 'alasan' => 'mengundurkan diri'],
            ['nip' => '196807122025212006', 'nama' => 'JAWARIAH', 'alasan' => 'pensiun'],
            ['nip' => '196808152025212016', 'nama' => 'SITTI ASIAH', 'alasan' => 'pensiun'],
            ['nip' => '198201012025212105', 'nama' => 'RAHMANIAH', 'alasan' => 'tdak pernh mlkasankan tugas'],
            ['nip' => '198206012025212074', 'nama' => 'NURLIA', 'alasan' => 'meninggal ( masi ad diusulan)'],
            ['nip' => '199004022025212124', 'nama' => 'ERNAWATI', 'alasan' => 'meninggal'],
            ['nip' => '198010242025212037', 'nama' => 'ANDI SUKMAWATI', 'alasan' => 'mengundurkan diri ( masih ad disulan)'],
            ['nip' => '197201012025212039', 'nama' => 'NAJEMAWATI', 'alasan' => 'mengundurkan diri (proses)'],
        ];

        $emailModel = new EmailModel();
        $emailService = new EmailService();

        $total = count($targetList);
        CLI::write("Memeriksa {$total} akun dari daftar non-usulan...\n", 'white');

        $successCount = 0;
        $failedCount = 0;
        $notFoundCount = 0;

        foreach ($targetList as $idx => $item) {
            $nip = $item['nip'];
            $alasan = $item['alasan'];
            $no = $idx + 1;

            $acc = $emailModel->select('emails.*, unit_kerja.nama_unit_kerja as unit_kerja_name')
                              ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                              ->where('emails.nip', $nip)
                              ->first();

            if (!$acc) {
                CLI::write("[{$no}/{$total}] NIP: {$nip} ({$item['nama']}) - TIDAK DITEMUKAN DI DATABASE!", 'red');
                $notFoundCount++;
                continue;
            }

            $nama = $acc['name'] ?: $acc['email'];
            $emailAddr = $acc['email'];
            $unit = $acc['unit_kerja_name'] ?: '-';

            CLI::write("[{$no}/{$total}] {$nama} ({$emailAddr})", 'white');
            CLI::write("    NIP: {$nip} | Unit: {$unit}", 'light_gray');
            CLI::write("    Alasan: {$alasan}", 'yellow');

            if ($acc['deleted_at'] !== null) {
                CLI::write("    Status: Akun SUDAH berada di kotak sampah (deleted_at: {$acc['deleted_at']})", 'cyan');
                continue;
            }

            if ($isDryRun) {
                CLI::write("    Status: [DRY-RUN] Siap ditangguhkan", 'cyan');
                continue;
            }

            CLI::print("    Memproses penangguhan (cPanel suspend, soft delete & notifikasi)... ");
            $res = $emailService->processAutoPensiun($acc, $alasan);

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
            CLI::write("Simulasi selesai. Total akun target: {$total}", 'cyan');
        } else {
            CLI::write("Eksekusi selesai. Sukses: {$successCount} | Gagal: {$failedCount} | Tidak Ditemukan: {$notFoundCount}", 'green');
        }
        CLI::write("====================================================\n", 'yellow');
    }
}
