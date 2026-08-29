<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Domains\Email\Models\EmailModel;
use App\Shared\Models\StatusAsnModel;
use App\Shared\Libraries\CpanelApi;
use App\Shared\Libraries\TelegramMessageBuilder;

class CheckPensiun extends BaseCommand
{
    protected $group = 'App';
    protected $name = 'pns:check-pensiun';
    protected $description = 'Strictly analyze and process retirement workflow (BUP >= 60 Years) for PNS accounts.';
    protected $usage = 'pns:check-pensiun [options]';
    protected $options = [
        '--apply'        => 'Execute retirement workflow: Suspend cPanel login, clear pegawai data, set pensiun_at, and soft-delete to Trash.',
        '--age'          => 'Filter specific minimum age threshold (default: 60)',
        '--include-near' => 'Include employees approaching retirement (age 59) in the export/review',
        '--export'       => 'Export list of retired PNS (age >= 60) to CSV file',
    ];

    public function run(array $params)
    {
        $isApply = CLI::getOption('apply') !== null || in_array('--apply', $params) || isset($params['apply']);
        $minAge = (int)(CLI::getOption('age') ?? 60);
        $includeNear = CLI::getOption('include-near') !== null || in_array('--include-near', $params);
        $isExport = CLI::getOption('export') !== null || in_array('--export', $params);

        $emailModel = new EmailModel();
        $statusAsnModel = new StatusAsnModel();

        // 1. Ambil ID PNS Aktif
        $pnsStatus = $statusAsnModel->where('nama_status_asn', 'PNS')->first();
        $pnsId = $pnsStatus['id'] ?? 1;

        $accounts = $emailModel->select('emails.id, emails.user, emails.email, emails.name, emails.nip, emails.jabatan, emails.pangkat_golruang, emails.pangkat_nama, unit_kerja.nama_unit_kerja')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
            ->where('emails.deleted_at IS NULL')
            ->where('emails.pensiun_at IS NULL')
            ->where('emails.status_asn_id', $pnsId)
            ->findAll();

        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');

        $totalAccounts = count($accounts);
        $withNip = 0;
        $withoutNip = 0;

        $retired60List = [];
        $nearRetireList = []; // Usia 59 (Menjelang BUP 60)
        $activeList = [];

        foreach ($accounts as $acc) {
            $nip = preg_replace('/[^0-9]/', '', $acc['nip'] ?? '');

            if (strlen($nip) >= 8) {
                $withNip++;
                $birthYear = (int)substr($nip, 0, 4);
                $birthMonth = (int)substr($nip, 4, 2);
                $birthDay = (int)substr($nip, 6, 2);

                if ($birthYear >= 1940 && $birthYear <= ($currentYear - 17)) {
                    $age = $currentYear - $birthYear;
                    if ($currentMonth < $birthMonth) {
                        $age--;
                    }

                    $item = [
                        'account'    => $acc,
                        'nip'        => $nip,
                        'birth_date' => sprintf('%02d-%02d-%04d', $birthDay, $birthMonth, $birthYear),
                        'age'        => $age,
                    ];

                    if ($age >= $minAge) {
                        $retired60List[] = $item;
                    } elseif ($age >= ($minAge - 1)) {
                        $nearRetireList[] = $item;
                    } else {
                        $activeList[] = $item;
                    }
                }
            } else {
                $withoutNip++;
            }
        }

        // Urutkan dari usia paling tua
        usort($retired60List, function($a, $b) {
            return $b['age'] <=> $a['age'];
        });

        CLI::write("==========================================================", 'yellow');
        CLI::write("    ANALISIS STATUS PENSIUN PNS (KRITERIA KETAT: >= 60 THN) ", 'yellow');
        CLI::write("==========================================================", 'yellow');

        if (!$isApply) {
            CLI::write("MODE: [SIMULASI / DRY-RUN] (Tidak ada perubahan database)", 'cyan');
            CLI::write("Gunakan flag --apply untuk memproses pensiun (suspend login, lepas data & pindah ke Kotak Sampah).", 'light_gray');
        } else {
            CLI::write("MODE: [LIVE UPDATE / APPLY] (Alur Lengkap Pensiun & Penangguhan)", 'red');
            $confirm = CLI::prompt("Apakah Anda yakin ingin memproses " . count($retired60List) . " akun PNS (usia >= 60 thn) sebagai PENSIUN (suspend login & pindah ke Kotak Sampah)?", ['y', 'n']);
            if (strtolower($confirm) !== 'y') {
                CLI::write("Dibatalkan oleh pengguna.", 'yellow');
                return;
            }
        }
        CLI::write("");

        CLI::write("Tahun Evaluasi                   : " . $currentYear, 'cyan');
        CLI::write("Ambang Batas Pensiun             : >= {$minAge} Tahun (Lahir <= " . ($currentYear - $minAge) . ")", 'green');
        CLI::write("Total Akun PNS Aktif Dievaluasi  : " . $totalAccounts, 'yellow');
        CLI::write("• Memiliki NIP Valid             : " . $withNip, 'green');
        CLI::write("  - Usia Aktif (< " . ($minAge - 1) . " Tahun)     : " . count($activeList) . " Akun", 'green');
        CLI::write("  - Menjelang Pensiun (" . ($minAge - 1) . " Tahun) : " . count($nearRetireList) . " Akun (MPP BUP 60)", 'cyan');
        CLI::write("  - PENSIUN / PURNA TUGAS (>= {$minAge}): " . count($retired60List) . " Akun", 'red');
        CLI::write("• Belum Memiliki NIP             : " . $withoutNip . " Akun", 'light_gray');
        CLI::write("==========================================================\n");

        // Tampilkan Sampel Pensiun Usia >= 60 Tahun
        if (!empty($retired60List)) {
            CLI::write("--- [DAFTAR PEGAWAI USIA >= {$minAge} TAHUN (PURNA TUGAS / PENSIUN)] ---", 'red');
            foreach (array_slice($retired60List, 0, 15) as $r) {
                $acc = $r['account'];
                CLI::write(sprintf(
                    "• [%s] %s\n  NIP: %s | Tgl Lahir: %s | Usia: %d Thn | Unit: %s",
                    $acc['email'],
                    $acc['name'] ?: '-',
                    $r['nip'],
                    $r['birth_date'],
                    $r['age'],
                    $acc['nama_unit_kerja'] ?: 'Tanpa Unit'
                ), 'light_red');
            }
            if (count($retired60List) > 15) {
                CLI::write("... dan " . (count($retired60List) - 15) . " pegawai pensiun lainnya (usia >= {$minAge} tahun).\n");
            } else {
                CLI::write("");
            }
        }

        // Tampilkan Sampel Menjelang Pensiun (59 Tahun)
        if (!empty($nearRetireList) && $includeNear) {
            CLI::write("--- [SAMPEL PEGAWAI USIA " . ($minAge - 1) . " TAHUN (MENJELANG BUP 60)] ---", 'cyan');
            foreach (array_slice($nearRetireList, 0, 10) as $r) {
                $acc = $r['account'];
                CLI::write(sprintf(
                    "• [%s] %s\n  NIP: %s | Tgl Lahir: %s | Usia: %d Thn | Unit: %s",
                    $acc['email'],
                    $acc['name'] ?: '-',
                    $r['nip'],
                    $r['birth_date'],
                    $r['age'],
                    $acc['nama_unit_kerja'] ?: 'Tanpa Unit'
                ), 'cyan');
            }
            if (count($nearRetireList) > 10) {
                CLI::write("... dan " . (count($nearRetireList) - 10) . " pegawai lainnya usia " . ($minAge - 1) . " tahun.\n");
            } else {
                CLI::write("");
            }
        }

        // Eksekusi Pensiun Sesuai Prosedur Sistem jika --apply
        if ($isApply && !empty($retired60List)) {
            CLI::write("\n================ MENJALANKAN PROSEDUR PENSIUN ================", 'yellow');
            CLI::write("Memproses " . count($retired60List) . " akun PNS (usia >= {$minAge} thn) ke status pensiun...", 'cyan');

            helper('audit');
            $cpanelApi = new CpanelApi();
            $appliedCount = 0;
            $cpanelSuccess = 0;
            $cpanelFailed = 0;

            foreach ($retired60List as $item) {
                $acc = $item['account'];

                // 1. Suspend cPanel Email Login
                try {
                    $cpanelApi->suspend_email_login($acc['email']);
                    $cpanelSuccess++;
                } catch (\Throwable $e) {
                    $cpanelFailed++;
                }

                // 2. Update Database & Lepas Data Kepegawaian
                $emailModel->update($acc['id'], [
                    'suspended_login'  => 1,
                    'pensiun_at'       => date('Y-m-d H:i:s'),
                    'unit_kerja_id'    => null,
                    'nik'              => null,
                    'nip'              => null,
                    'jabatan'          => null,
                    'golongan'         => null,
                    'pangkat_golruang' => null,
                    'pangkat_nama'     => null,
                    'status_asn_id'    => null,
                    'eselon_id'        => null,
                    'bsre_status'      => null,
                    'pimpinan'         => 0,
                    'pimpinan_desa'    => 0,
                    'gelar_depan'      => null,
                    'gelar_belakang'   => null,
                    'tempat_lahir'     => null,
                    'tanggal_lahir'    => null,
                    'pendidikan'       => null,
                ]);

                // 3. Move to Kotak Sampah (Soft Delete)
                $emailModel->delete($acc['id']);

                // 4. Audit Log
                log_audit('PENSIUN', 'Email', $acc['id'], 'Akun usia >= ' . $minAge . ' tahun diproses pensiun via CLI: ' . $acc['email']);

                $appliedCount++;
            }

            // 5. Kirim Ringkasan Notifikasi ke Telegram
            try {
                $tgBuilder = new TelegramMessageBuilder();
                $tgBuilder->setTitle('REKAP PENANGGUHAN AKUN PENSIUN (BUP >= 60 THN)', '🚫')
                    ->addText("📋 <b>Total Akun Diproses:</b> $appliedCount Pegawai")
                    ->addText("🔒 <b>cPanel Suspend:</b> $cpanelSuccess berhasil" . ($cpanelFailed > 0 ? " ($cpanelFailed gagal)" : ""))
                    ->addText("🗑️ <b>Status Database:</b> Data pegawai dilepas & dipindahkan ke Kotak Sampah (Retensi 30 Hari)");
                $tgBuilder->send();
            } catch (\Throwable $e) {
                // Abaikan jika telegram offline
            }

            CLI::write("✓ Selesai: $appliedCount akun PNS usia >= {$minAge} tahun berhasil diproses pensiun!", 'green');
            CLI::write("  • Akses login cPanel ditangguhkan ($cpanelSuccess berhasil)", 'light_gray');
            CLI::write("  • Data pegawai dilepaskan & akun dipindahkan ke Kotak Sampah", 'light_gray');
            CLI::write("  • Log audit & notifikasi Telegram telah dicatat.\n", 'light_gray');
        }

        // Ekspor ke CSV jika flag --export
        if ($isExport) {
            $exportDir = WRITEPATH . 'exports';
            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0777, true);
            }
            $filename = 'rekap_pensiun_pns_60thn_' . date('YmdHis') . '.csv';
            $filepath = $exportDir . '/' . $filename;
            $fp = fopen($filepath, 'w');

            // BOM UTF-8
            fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($fp, ['No', 'Nama Pegawai', 'NIP', 'Email', 'Tgl Lahir', 'Usia (Tahun)', 'Status', 'Jabatan', 'Golongan', 'Unit Kerja']);

            $no = 1;
            $exportData = $retired60List;
            if ($includeNear) {
                $exportData = array_merge($retired60List, $nearRetireList);
            }

            foreach ($exportData as $item) {
                $acc = $item['account'];
                $kat = $item['age'] >= $minAge ? "PENSIUN (Usia >= {$minAge} Thn)" : "Menjelang Pensiun (Usia " . ($minAge - 1) . " Thn)";
                fputcsv($fp, [
                    $no++,
                    $acc['name'] ?? '',
                    "\t" . $item['nip'],
                    $acc['email'] ?? '',
                    $item['birth_date'],
                    $item['age'],
                    $kat,
                    $acc['jabatan'] ?? '',
                    $acc['pangkat_golruang'] ?? '',
                    $acc['nama_unit_kerja'] ?? '',
                ]);
            }
            fclose($fp);

            CLI::write("✓ Berhasil mengekspor data PNS pensiun (>= {$minAge} tahun) ke file CSV:", 'green');
            CLI::write("  $filepath\n", 'cyan');
        }

        if (!$isApply) {
            CLI::write("💡 Tips: Untuk memproses seluruh akun pensiun ke database (suspend login + pindah ke Trash), jalankan:", 'yellow');
            CLI::write("php spark pns:check-pensiun --apply\n", 'cyan');
        }
    }
}
