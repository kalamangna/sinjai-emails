<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Domains\Email\Models\EmailModel;
use App\Shared\Models\StatusAsnModel;

class CheckPensiun extends BaseCommand
{
    protected $group = 'App';
    protected $name = 'pns:check-pensiun';
    protected $description = 'Analyze retirement status (BUP) for all PNS accounts based on 18-digit NIP.';
    protected $usage = 'pns:check-pensiun [options]';
    protected $options = [
        '--age'    => 'Filter specific minimum age (default: 58)',
        '--export' => 'Export list of retired / near-retirement PNS to CSV file',
    ];

    public function run(array $params)
    {
        $minAge = (int)(CLI::getOption('age') ?? 58);
        $isExport = CLI::getOption('export') !== null || in_array('--export', $params);

        $emailModel = new EmailModel();
        $statusAsnModel = new StatusAsnModel();

        $pnsStatus = $statusAsnModel->where('nama_status_asn', 'PNS')->first();
        $pnsId = $pnsStatus['id'] ?? 1;

        $accounts = $emailModel->select('emails.id, emails.email, emails.name, emails.nip, emails.jabatan, emails.pangkat_golruang, emails.pangkat_nama, unit_kerja.nama_unit_kerja')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
            ->where('emails.deleted_at IS NULL')
            ->where('emails.status_asn_id', $pnsId)
            ->findAll();

        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');

        $totalAccounts = count($accounts);
        $withNip = 0;
        $withoutNip = 0;

        $retired60List = [];
        $retired58List = [];
        $nearRetireList = []; // Usia 57
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

                    if ($age >= 60) {
                        $retired60List[] = $item;
                    } elseif ($age >= 58) {
                        $retired58List[] = $item;
                    } elseif ($age >= 57) {
                        $nearRetireList[] = $item;
                    } else {
                        $activeList[] = $item;
                    }
                }
            } else {
                $withoutNip++;
            }
        }

        CLI::write("==========================================================", 'yellow');
        CLI::write("       ANALISIS STATUS USIA PENSIUN PNS KAB. SINJAI       ", 'yellow');
        CLI::write("==========================================================", 'yellow');
        CLI::write("Tahun Evaluasi                : " . $currentYear, 'cyan');
        CLI::write("Total Akun PNS di Database    : " . $totalAccounts, 'yellow');
        CLI::write("• Memiliki NIP Valid          : " . $withNip, 'green');
        CLI::write("  - Usia Aktif (< 57 Tahun)   : " . count($activeList) . " Akun", 'green');
        CLI::write("  - Menjelang Pensiun (57 Thn): " . count($nearRetireList) . " Akun", 'cyan');
        CLI::write("  - Lewat BUP 58 (58-59 Thn)  : " . count($retired58List) . " Akun (Pensiun Jabatan Pelaksana/Pengawas)", 'yellow');
        CLI::write("  - Lewat BUP 60 (>= 60 Thn)  : " . count($retired60List) . " Akun (Purna Tugas / Lewat BUP)", 'red');
        CLI::write("• Belum Memiliki NIP          : " . $withoutNip . " Akun", 'light_gray');
        CLI::write("==========================================================\n");

        // Tampilkan Sampel Usia >= 60 Tahun
        if (!empty($retired60List)) {
            CLI::write("--- [SAMPEL 10 PEGAWAI USIA >= 60 TAHUN (PURNA TUGAS / BUP 60)] ---", 'red');
            foreach (array_slice($retired60List, 0, 10) as $r) {
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
            if (count($retired60List) > 10) {
                CLI::write("... dan " . (count($retired60List) - 10) . " pegawai lainnya usia >= 60 tahun.\n");
            } else {
                CLI::write("");
            }
        }

        // Tampilkan Sampel Usia 58 - 59 Tahun
        if (!empty($retired58List)) {
            CLI::write("--- [SAMPEL 10 PEGAWAI USIA 58 - 59 TAHUN (BUP PELAKSANA / PENGAWAS)] ---", 'yellow');
            foreach (array_slice($retired58List, 0, 10) as $r) {
                $acc = $r['account'];
                CLI::write(sprintf(
                    "• [%s] %s\n  NIP: %s | Tgl Lahir: %s | Usia: %d Thn | Unit: %s",
                    $acc['email'],
                    $acc['name'] ?: '-',
                    $r['nip'],
                    $r['birth_date'],
                    $r['age'],
                    $acc['nama_unit_kerja'] ?: 'Tanpa Unit'
                ), 'yellow');
            }
            if (count($retired58List) > 10) {
                CLI::write("... dan " . (count($retired58List) - 10) . " pegawai lainnya usia 58-59 tahun.\n");
            } else {
                CLI::write("");
            }
        }

        // Ekspor ke CSV jika flag --export
        if ($isExport) {
            $exportDir = WRITEPATH . 'exports';
            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0777, true);
            }
            $filename = 'rekap_pensiun_pns_' . date('YmdHis') . '.csv';
            $filepath = $exportDir . '/' . $filename;
            $fp = fopen($filepath, 'w');

            // BOM UTF-8
            fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($fp, ['No', 'Nama Pegawai', 'NIP', 'Email', 'Tgl Lahir', 'Usia (Tahun)', 'Kategori Pensiun', 'Jabatan', 'Golongan', 'Unit Kerja']);

            $no = 1;
            $combined = array_merge($retired60List, $retired58List, $nearRetireList);
            foreach ($combined as $item) {
                $acc = $item['account'];
                $kat = $item['age'] >= 60 ? 'Usia >= 60 Thn (Lewat BUP)' : ($item['age'] >= 58 ? 'Usia 58-59 Thn (BUP Pelaksana/Pengawas)' : 'Usia 57 Thn (Menjelang Pensiun)');
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

            CLI::write("✓ Berhasil mengekspor data pensiun ke file:", 'green');
            CLI::write("  $filepath\n", 'cyan');
        } else {
            CLI::write("💡 Tips: Untuk mengekspor daftar pegawai pensiun ke file CSV, jalankan:", 'yellow');
            CLI::write("php spark pns:check-pensiun --export\n", 'cyan');
        }
    }
}
