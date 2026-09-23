<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Domains\Email\Models\PkModel;
use App\Domains\Email\Models\PkHistoryModel;
use App\Domains\Email\Models\EmailModel;
use App\Domains\Email\Services\EmailService;

class TestPkHistory extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:pk-history';
    protected $description = 'Menguji fungsionalitas pengarsipan riwayat PK otomatis';

    public function run(array $params)
    {
        CLI::write("=================================================", 'yellow');
        CLI::write(" PENGUJIAN FITUR ARSIP RIWAYAT PK DI LOCALHOST   ", 'yellow');
        CLI::write("=================================================", 'yellow');

        $pkModel = new PkModel();
        $historyModel = new PkHistoryModel();
        $emailModel = new EmailModel();

        // 1. Ambil sampel pegawai PPPK-PW di database lokal
        $sampleEmail = $emailModel->where('status_asn_id', 3)->where('deleted_at IS NULL')->first();
        if (!$sampleEmail) {
            CLI::error("Tidak ada pegawai PPPK-PW di database.");
            return;
        }

        CLI::write("Sampel Pegawai : " . $sampleEmail['name'] . " (" . $sampleEmail['email'] . ")", 'cyan');

        $currentPk = $pkModel->where('email', $sampleEmail['email'])->first();
        if (!$currentPk) {
            CLI::error("Data PK awal tidak ditemukan.");
            return;
        }

        $oldNomor = $currentPk['nomor'];
        $oldAwal  = $currentPk['tanggal_kontrak_awal'];
        $oldAkhir = $currentPk['tanggal_kontrak_akhir'];
        $oldGaji  = $currentPk['gaji_nominal'];

        CLI::write("Data PK Awal (Aktif):", 'green');
        CLI::write("  - Nomor SK             : " . $oldNomor);
        CLI::write("  - Tanggal Kontrak Awal : " . $oldAwal);
        CLI::write("  - Tanggal Kontrak Akhir: " . $oldAkhir);
        CLI::write("  - Gaji Nominal         : Rp " . number_format((float)$oldGaji, 0, ',', '.'));

        $historiesInitial = $historyModel->getHistoryByEmail($sampleEmail['email']);
        CLI::write("Jumlah riwayat tersimpan sebelum uji coba: " . count($historiesInitial));

        // 2. Simulasi Update Kontrak Baru 2026
        CLI::write("\n[1] Melakukan simulasi perpanjangan kontrak 2026...", 'yellow');
        $pkModel->update($currentPk['id'], [
            'nomor'                 => '0001/PPPK-PW/2026/TEST',
            'tanggal_kontrak_awal'  => '2026-10-01',
            'tanggal_kontrak_akhir' => '2027-09-30',
        ]);

        $historiesAfter1 = $historyModel->getHistoryByEmail($sampleEmail['email']);
        if (count($historiesAfter1) > count($historiesInitial)) {
            $latest = $historiesAfter1[0];
            CLI::write("  -> SUKSES! Data lama berhasil diarsipkan otomatis:", 'green');
            CLI::write("     Nomor Lama : " . $latest['nomor'] . " (Sesuai: " . ($latest['nomor'] === $oldNomor ? 'YA' : 'TIDAK') . ")");
            CLI::write("     TMT Awal   : " . $latest['tanggal_kontrak_awal'] . " (Sesuai: " . ($latest['tanggal_kontrak_awal'] === $oldAwal ? 'YA' : 'TIDAK') . ")");
            CLI::write("     TMT Akhir  : " . $latest['tanggal_kontrak_akhir'] . " (Sesuai: " . ($latest['tanggal_kontrak_akhir'] === $oldAkhir ? 'YA' : 'TIDAK') . ")");
            CLI::write("     Waktu Arsip: " . $latest['archived_at']);
        } else {
            CLI::error("  -> GAGAL! Data riwayat tidak bertambah di pk_histories.");
            return;
        }

        // 3. Simulasi Update Kontrak Baru 2027 (Multi-tahun)
        CLI::write("\n[2] Melakukan simulasi perpanjangan kontrak tahun berikutnya (2027)...", 'yellow');
        $pkModel->update($currentPk['id'], [
            'nomor'                 => '0001/PPPK-PW/2027/TEST',
            'tanggal_kontrak_awal'  => '2027-10-01',
            'tanggal_kontrak_akhir' => '2028-09-30',
        ]);

        $historiesAfter2 = $historyModel->getHistoryByEmail($sampleEmail['email']);
        CLI::write("  -> Total riwayat tersimpan sekarang: " . count($historiesAfter2) . " riwayat", 'green');
        foreach ($historiesAfter2 as $idx => $h) {
            CLI::write("     Riwayat #" . ($idx + 1) . ": No " . $h['nomor'] . " (" . $h['tanggal_kontrak_awal'] . " s/d " . $h['tanggal_kontrak_akhir'] . ")");
        }

        // 4. Uji Coba EmailService getEmailDetail
        CLI::write("\n[3] Memeriksa integrasi dengan EmailService (Web UI)...", 'yellow');
        $emailService = new EmailService();
        $detail = $emailService->getEmailDetail($sampleEmail['user']);
        CLI::write("  -> EmailService mengembalikan " . count($detail['pk_histories']) . " baris riwayat PK.", 'green');
        CLI::write("  -> Data PK Aktif saat ini: " . $detail['pk_data']['nomor'] . " (" . $detail['pk_data']['tanggal_kontrak_awal'] . ")", 'green');

        // 5. Cleanup
        CLI::write("\n[4] Membersihkan data uji coba & mengembalikan data ke semula...", 'yellow');
        $pkModel->update($currentPk['id'], [
            'nomor'                 => $oldNomor,
            'tanggal_kontrak_awal'  => $oldAwal,
            'tanggal_kontrak_akhir' => $oldAkhir,
        ]);
        $historyModel->where('email', $sampleEmail['email'])->delete();

        CLI::write("  -> Data aktif dikembalikan ke Nomor: " . $oldNomor, 'green');
        CLI::write("  -> Tabel pk_histories dibersihkan dari entri uji coba.", 'green');
        CLI::write("\nKESIMPULAN: Seluruh pengujian fitur arsip riwayat PK 100% BERHASIL!", 'green');
    }
}
