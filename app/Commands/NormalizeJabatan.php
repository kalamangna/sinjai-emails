<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Domains\Email\Models\EmailModel;
use App\Shared\Models\StatusAsnModel;
use App\Shared\Services\CacheService;

class NormalizeJabatan extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'email:normalize-jabatan';
    protected $description = 'Menormalkan dan merapikan teks nama jabatan PNS (huruf kapital, hapus trailing dot/koma, spasi ganda, dan nomenklatur sekretaris).';
    protected $usage       = 'email:normalize-jabatan [options]';
    protected $arguments   = [];
    protected $options     = [
        '--apply' => 'Terapkan pembaruan data secara langsung ke database.',
    ];

    public function run(array $params)
    {
        $apply = CLI::getOption('apply') !== null || in_array('--apply', $params);

        CLI::write("==========================================================", 'yellow');
        CLI::write("  NORMALISASI TEKS JABATAN PEGAWAI PNS SINJAI", 'white');
        CLI::write("  Mode: " . ($apply ? "EKSEKUSI LANGSUNG (--apply)" : "SIMULASI / DRY-RUN"), $apply ? 'green' : 'cyan');
        CLI::write("==========================================================", 'yellow');

        $statusAsnModel = new StatusAsnModel();
        $statusPns = $statusAsnModel->where('nama_status_asn', 'PNS')->first();
        $pnsId = $statusPns['id'] ?? 1;

        $emailModel = new EmailModel();
        $emails = $emailModel
            ->select('emails.id, emails.email, emails.name, emails.nip, emails.jabatan, emails.pimpinan, unit_kerja.nama_unit_kerja')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
            ->where('emails.status_asn_id', $pnsId)
            ->where('emails.jabatan !=', '')
            ->where('emails.jabatan IS NOT NULL')
            ->orderBy('emails.name', 'ASC')
            ->findAll();

        $totalEvaluated = count($emails);
        $changedCount   = 0;
        $changesList    = [];

        CLI::write("Total akun PNS dengan jabatan ditemukan: $totalEvaluated", 'cyan');
        CLI::newLine();

        foreach ($emails as $email) {
            $oldJabatan = $email['jabatan'];
            $newJabatan = $this->cleanJabatan($oldJabatan, $email['nama_unit_kerja'] ?? '');

            if ($oldJabatan !== $newJabatan) {
                $changedCount++;
                $changesList[] = [
                    'id'          => $email['id'],
                    'email'       => $email['email'],
                    'name'        => $email['name'],
                    'nip'         => $email['nip'],
                    'unit'        => $email['nama_unit_kerja'] ?? '-',
                    'old_jabatan' => $oldJabatan,
                    'new_jabatan' => $newJabatan,
                ];

                if ($apply) {
                    $emailModel->update($email['id'], ['jabatan' => $newJabatan]);
                }
            }
        }

        if (empty($changesList)) {
            CLI::write("✅ Seluruh nama jabatan PNS sudah bersih dan terstandarisasi dengan baik.", 'green');
            return;
        }

        CLI::write("--- [📋 RINCIAN PERUBAHAN JABATAN ($changedCount Akun)] ---", 'yellow');
        foreach ($changesList as $idx => $item) {
            $num = $idx + 1;
            CLI::write("[$num] {$item['name']} ({$item['email']})", 'white');
            CLI::write("    Unit Kerja : {$item['unit']}", 'cyan');
            CLI::write("    Sebelum    : \"{$item['old_jabatan']}\"", 'red');
            CLI::write("    Sesudah    : \"{$item['new_jabatan']}\"", 'green');
            CLI::newLine();
        }

        CLI::write("==========================================================", 'yellow');
        CLI::write("Ringkasan Hasil:", 'white');
        CLI::write("• Total Akun Dievaluasi : $totalEvaluated", 'white');
        CLI::write("• Jabatan Dinormalkan   : $changedCount", $changedCount > 0 ? 'green' : 'white');
        CLI::write("• Status Eksekusi       : " . ($apply ? "BERHASIL DIPERBARUI DI DATABASE ✅" : "HANYA SIMULASI (Gunakan --apply untuk menyimpan)"), $apply ? 'green' : 'yellow');
        CLI::write("==========================================================", 'yellow');

        if ($apply) {
            CacheService::invalidateDashboard();
            helper('audit');
            log_audit('UPDATE', 'Email', null, "Normalisasi nama jabatan massal ($changedCount akun PNS)");
        }
    }

    private function cleanJabatan(string $jabatan, string $unitKerjaName = ''): string
    {
        $jab = mb_strtoupper(trim($jabatan), 'UTF-8');
        
        // Hapus karakter liar di akhir (titik ganda, titik koma, titik satu di ujung)
        $jab = preg_replace('/[,\.]+\s*$/', '', $jab);
        
        // Spasi sebelum/setelah tanda baca titik dan koma
        $jab = preg_replace('/\s+([,\.])/', '$1', $jab);
        $jab = preg_replace('/([,\.])\s+/', '$1 ', $jab);

        // Standarisasi slash (III/a tanpa spasi)
        $jab = preg_replace('/\s*\/\s*/', '/', $jab);

        // Standarisasi tanda hubung spasi bersih e.g. " - "
        $jab = preg_replace('/\s*-\s*/', ' - ', $jab);

        // Hapus spasi ganda atau tab liar
        $jab = preg_replace('/\s+/', ' ', trim($jab));

        // Penyesuaian khusus jika hanya tertulis "SEKRETARIS"
        if ($jab === 'SEKRETARIS' && !empty($unitKerjaName)) {
            $unitUpper = strtoupper($unitKerjaName);
            if (strpos($unitUpper, 'DINAS') !== false)       $jab = 'SEKRETARIS DINAS';
            elseif (strpos($unitUpper, 'BADAN') !== false)   $jab = 'SEKRETARIS BADAN';
            elseif (strpos($unitUpper, 'KECAMATAN') !== false) $jab = 'SEKRETARIS KECAMATAN';
            elseif (strpos($unitUpper, 'KELURAHAN') !== false) $jab = 'SEKRETARIS KELURAHAN';
        }

        return $jab;
    }
}
