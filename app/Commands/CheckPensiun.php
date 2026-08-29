<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Domains\Email\Models\EmailModel;
use App\Domains\UnitKerja\Models\UnitKerjaModel;
use App\Shared\Models\StatusAsnModel;
use App\Shared\Libraries\CpanelApi;
use App\Shared\Libraries\TelegramMessageBuilder;

class CheckPensiun extends BaseCommand
{
    protected $group = 'App';
    protected $name = 'pns:check-pensiun';
    protected $description = 'Analyze and process retirement workflow for PNS (BUP >= 60 Years or Unregistered/Purna PNS accounts).';
    protected $usage = 'pns:check-pensiun [options]';
    protected $options = [
        '--apply'        => 'Execute retirement workflow: Suspend cPanel login, clear pegawai data, set pensiun_at, and soft-delete to Trash.',
        '--age'          => 'Filter specific minimum age threshold for NIP evaluation (default: 60)',
        '--unmatched'    => 'Target PNS accounts without NIP that are not registered in the active SIMPEG database (Purna Tugas/Mantan Pegawai)',
        '--include-near' => 'Include employees approaching retirement (age 59) in the export/review',
        '--export'       => 'Export list of retired PNS to CSV file',
    ];

    public function run(array $params)
    {
        $isApply = CLI::getOption('apply') !== null || in_array('--apply', $params) || isset($params['apply']);
        $minAge = (int)(CLI::getOption('age') ?? 60);
        $isUnmatched = CLI::getOption('unmatched') !== null || in_array('--unmatched', $params);
        $includeNear = CLI::getOption('include-near') !== null || in_array('--include-near', $params);
        $isExport = CLI::getOption('export') !== null || in_array('--export', $params);

        $emailModel = new EmailModel();
        $statusAsnModel = new StatusAsnModel();
        $unitModel = new UnitKerjaModel();

        // 1. Ambil ID PNS Aktif
        $pnsStatus = $statusAsnModel->where('nama_status_asn', 'PNS')->first();
        $pnsId = $pnsStatus['id'] ?? 1;

        if ($isUnmatched) {
            $this->processUnmatchedPns($emailModel, $unitModel, $pnsId, $isApply, $isExport);
            return;
        }

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
        CLI::write("• Belum Memiliki NIP             : " . $withoutNip, 'light_gray');
        CLI::write("");

        // Tampilkan Sampel Akun Pensiun
        if (!empty($retired60List)) {
            CLI::write("--- [DAFTAR PEGAWAI PNS PENSIUN / USIA >= {$minAge} TAHUN (" . count($retired60List) . " Pegawai)] ---", 'red');
            foreach ($retired60List as $idx => $r) {
                $acc = $r['account'];
                CLI::write(sprintf(
                    "%2d. [%s] %s (Usia: %d Thn, Tgl Lahir: %s)\n    NIP: %s | %s | %s",
                    $idx + 1,
                    $acc['email'],
                    $acc['name'] ?: '-',
                    $r['age'],
                    $r['birth_date'],
                    $r['nip'],
                    $acc['jabatan'] ?: '-',
                    $acc['nama_unit_kerja'] ?: '-'
                ), 'light_red');
            }
            CLI::write("");
        }

        // Tampilkan Sampel Menjelang Pensiun
        if ($includeNear && !empty($nearRetireList)) {
            CLI::write("--- [DAFTAR MENJELANG PENSIUN / USIA " . ($minAge - 1) . " TAHUN (" . count($nearRetireList) . " Pegawai)] ---", 'cyan');
            foreach ($nearRetireList as $idx => $r) {
                $acc = $r['account'];
                CLI::write(sprintf(
                    "%2d. [%s] %s (Usia: %d Thn, Tgl Lahir: %s)\n    NIP: %s | %s | %s",
                    $idx + 1,
                    $acc['email'],
                    $acc['name'] ?: '-',
                    $r['age'],
                    $r['birth_date'],
                    $r['nip'],
                    $acc['jabatan'] ?: '-',
                    $acc['nama_unit_kerja'] ?: '-'
                ), 'light_cyan');
            }
            CLI::write("");
        }

        // Eksekusi Pensiun jika mode --apply
        if ($isApply && !empty($retired60List)) {
            $this->executeRetirementWorkflow($emailModel, $retired60List, "Akun usia >= $minAge tahun");
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

    private function processUnmatchedPns(EmailModel $emailModel, UnitKerjaModel $unitModel, int $pnsId, bool $isApply, bool $isExport): void
    {
        CLI::write("==========================================================", 'yellow');
        CLI::write("   ANALISIS AKUN PNS TIDAK TERDAFTAR DI SIMPEG (PURNA)    ", 'yellow');
        CLI::write("==========================================================", 'yellow');

        // Muat SIMPEG master list dari cache
        $cacheFile = WRITEPATH . 'cache/simpeg_units_pegawai.json';
        $diskCache = [];
        if (file_exists($cacheFile)) {
            $diskCache = json_decode(@file_get_contents($cacheFile), true) ?: [];
        }

        $allPegawai = [];
        foreach ($diskCache as $uData) {
            if (!empty($uData['data']) && is_array($uData['data'])) {
                foreach ($uData['data'] as $p) {
                    $allPegawai[] = $p;
                }
            }
        }

        // Ambil semua akun PNS tanpa NIP
        $accounts = $emailModel->select('emails.id, emails.user, emails.email, emails.name, emails.nip, emails.jabatan, unit_kerja.nama_unit_kerja')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
            ->where('emails.deleted_at IS NULL')
            ->where('emails.pensiun_at IS NULL')
            ->where('emails.status_asn_id', $pnsId)
            ->groupStart()
                ->where('emails.nip IS NULL')
                ->orWhere('emails.nip', '')
            ->groupEnd()
            ->findAll();

        $unmatchedPurnaList = [];

        foreach ($accounts as $acc) {
            $accName = trim($acc['name'] ?? '');
            if (empty($accName)) {
                $accName = explode('@', $acc['email'])[0];
                $accName = str_replace(['.', '_', '-'], ' ', $accName);
            }

            $normName = $this->normalizeName($accName);
            $normNameNoAndi = trim(preg_replace('/^ANDI\s+/i', '', $normName));

            // Cari di SIMPEG
            $found = false;
            foreach ($allPegawai as $p) {
                $pNorm = $this->normalizeName($p['nama'] ?? '');
                $pNormNoAndi = trim(preg_replace('/^ANDI\s+/i', '', $pNorm));

                if ($normName === $pNorm || (!empty($normNameNoAndi) && $normNameNoAndi === $pNormNoAndi)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $unmatchedPurnaList[] = [
                    'account' => $acc,
                ];
            }
        }

        CLI::write("Total Akun PNS Tanpa NIP Dievaluasi : " . count($accounts), 'yellow');
        CLI::write("Akun Tidak Ditemukan di SIMPEG Aktif: " . count($unmatchedPurnaList) . " Akun (Purna Tugas/Mantan Pegawai)", 'red');
        CLI::write("");

        if (!empty($unmatchedPurnaList)) {
            CLI::write("--- [DAFTAR AKUN PNS TIDAK TERDAFTAR DI SIMPEG (" . count($unmatchedPurnaList) . " Akun)] ---", 'red');
            foreach ($unmatchedPurnaList as $idx => $item) {
                $acc = $item['account'];
                CLI::write(sprintf(
                    "%2d. [%s] %s | Unit: %s (%s)",
                    $idx + 1,
                    $acc['email'],
                    $acc['name'] ?: '-',
                    $acc['nama_unit_kerja'] ?: 'Tanpa Unit',
                    $acc['jabatan'] ?: 'Tidak ada jabatan'
                ), 'light_red');
            }
            CLI::write("");
        }

        if (!$isApply) {
            CLI::write("MODE: [SIMULASI / DRY-RUN] (Tidak ada perubahan database)", 'cyan');
            CLI::write("💡 Tips: Untuk mengeksekusi pensiun pada akun-akun ini (suspend login + lepas data + pindah ke Trash), jalankan:", 'yellow');
            CLI::write("php spark pns:check-pensiun --unmatched --apply\n", 'cyan');
        } else {
            CLI::write("MODE: [LIVE UPDATE / APPLY] (Alur Lengkap Pensiun & Penangguhan)", 'red');
            $confirm = CLI::prompt("Apakah Anda yakin ingin memproses " . count($unmatchedPurnaList) . " akun PNS tidak terdaftar ini sebagai PENSIUN (suspend cPanel login, hapus data kepegawaian & pindah ke Kotak Sampah)?", ['y', 'n']);
            if (strtolower($confirm) !== 'y') {
                CLI::write("Dibatalkan oleh pengguna.", 'yellow');
                return;
            }

            $this->executeRetirementWorkflow($emailModel, $unmatchedPurnaList, "Akun tidak terdaftar di SIMPEG (Purna Tugas)");
        }

        if ($isExport) {
            $exportDir = WRITEPATH . 'exports';
            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0777, true);
            }
            $filename = 'rekap_pns_tidak_terdaftar_simpeg_' . date('YmdHis') . '.csv';
            $filepath = $exportDir . '/' . $filename;
            $fp = fopen($filepath, 'w');

            fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($fp, ['No', 'Email', 'Nama Akun', 'Jabatan', 'Unit Kerja', 'Status']);

            $no = 1;
            foreach ($unmatchedPurnaList as $item) {
                $acc = $item['account'];
                fputcsv($fp, [
                    $no++,
                    $acc['email'] ?? '',
                    $acc['name'] ?? '',
                    $acc['jabatan'] ?? '',
                    $acc['nama_unit_kerja'] ?? 'Tanpa Unit',
                    'Tidak Terdaftar di SIMPEG (Purna Tugas)',
                ]);
            }
            fclose($fp);

            CLI::write("✓ Berhasil mengekspor data ke file CSV:", 'green');
            CLI::write("  $filepath\n", 'cyan');
        }
    }

    private function executeRetirementWorkflow(EmailModel $emailModel, array $list, string $auditReason): void
    {
        $cpanelApi = new CpanelApi();
        $cpanelSuccess = 0;
        $cpanelFailed = 0;
        $appliedCount = 0;

        CLI::write("\nMemulai pemrosesan pensiun & penangguhan login...", 'yellow');

        foreach ($list as $item) {
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
            log_audit('PENSIUN', 'Email', $acc['id'], "$auditReason diproses pensiun via CLI: " . $acc['email']);

            $appliedCount++;
        }

        // 5. Kirim Ringkasan Notifikasi ke Telegram
        try {
            $tgBuilder = new TelegramMessageBuilder();
            $tgBuilder->setTitle('REKAP PENANGGUHAN AKUN PENSIUN / PURNA TUGAS', '🚫')
                ->addText("📋 <b>Total Akun Diproses:</b> $appliedCount Pegawai")
                ->addText("🔒 <b>cPanel Suspend:</b> $cpanelSuccess berhasil" . ($cpanelFailed > 0 ? " ($cpanelFailed gagal)" : ""))
                ->addText("🗑️ <b>Status Database:</b> Data pegawai dilepas & dipindahkan ke Kotak Sampah (Retensi 30 Hari)");
            $tgBuilder->send();
        } catch (\Throwable $e) {
            // Abaikan jika telegram offline
        }

        CLI::write("✓ Selesai: $appliedCount akun berhasil diproses pensiun!", 'green');
        CLI::write("  • Akses login cPanel ditangguhkan ($cpanelSuccess berhasil)", 'light_gray');
        CLI::write("  • Data pegawai dilepaskan & akun dipindahkan ke Kotak Sampah", 'light_gray');
        CLI::write("  • Log audit & notifikasi Telegram telah dicatat.\n", 'light_gray');
    }

    private function normalizeName(string $name): string
    {
        $name = mb_strtoupper($name, 'UTF-8');

        if (strpos($name, ',') !== false) {
            $name = substr($name, 0, strpos($name, ','));
        }

        $name = str_replace([',.', '.,', ';', '`', '\'', '"', '(', ')', '[', ']'], ' ', $name);
        $name = preg_replace('/\b(PROF|DRS|DRA|DR|IR|HJ|H|DRH)\.?\s+/i', ' ', $name);
        $name = preg_replace('/(?<=[A-Z0-9])\.(?=[A-Z0-9])/i', '', $name);
        $name = preg_replace('/[\.,]/', ' ', $name);
        $name = preg_replace('/\s+/', ' ', trim($name));

        $titles = [
            'PROF', 'DR', 'DRA', 'DRS', 'IR', 'H', 'HJ', 'HAJI', 'HAJJAH', 'DRH',
            'SKOM', 'SPD', 'SSOS', 'SSTP', 'SE', 'SH', 'SSI', 'SKM', 'STRKEB', 'STRGZ', 'STRTRA', 'STR', 'STRIP',
            'SAP', 'SIP', 'ST', 'SP', 'SAG', 'SKEP', 'STP', 'SS', 'SKED', 'SIKOM', 'SFARM', 'SPT', 'SPI', 'SM', 'SPKP', 'STPAR', 'SEI',
            'MSI', 'MPD', 'MM', 'MKOM', 'MAP', 'MTRAP', 'MKES', 'MH', 'MAG', 'MAK', 'MT', 'MIKOM', 'MP', 'MKM', 'MSC', 'MANIMSC', 'MLING',
            'AMD', 'AMDKEB', 'AMDKEP', 'AMDKL', 'AMDPK', 'AMKG', 'AMTEK', 'AMDPI', 'AMDRAD', 'AMKL',
            'NS', 'APT', 'GR', 'AP', 'SEK', 'IP', 'CGCAE', 'CGRE'
        ];

        $words = explode(' ', $name);
        $cleanWords = [];

        foreach ($words as $w) {
            $cleanW = trim($w);
            if (empty($cleanW)) continue;

            if (in_array($cleanW, $titles)) {
                continue;
            }

            if ($cleanW === 'MUH' || $cleanW === 'MUHAMMAD') {
                $cleanWords[] = 'MUHAMMAD';
            } elseif ($cleanW === 'ABD' || $cleanW === 'ABDUL') {
                $cleanWords[] = 'ABDUL';
            } elseif ($cleanW === 'ACH' || $cleanW === 'ACHMAD' || $cleanW === 'AHMAD') {
                $cleanWords[] = 'AHMAD';
            } else {
                $cleanWords[] = $cleanW;
            }
        }

        if (!empty($cleanWords) && ($cleanWords[0] === 'A' || $cleanWords[0] === 'ANDI')) {
            $cleanWords[0] = 'ANDI';
        }

        return implode(' ', $cleanWords);
    }
}
