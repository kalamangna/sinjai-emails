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
    protected $description = 'Strictly analyze retirement status (BUP 60 Years) for all PNS accounts based on 18-digit NIP.';
    protected $usage = 'pns:check-pensiun [options]';
    protected $options = [
        '--age'          => 'Filter specific minimum age threshold (default: 60)',
        '--include-near' => 'Include employees approaching retirement (age 59) in the export/review',
        '--export'       => 'Export list of retired PNS (age >= 60) to CSV file',
    ];

    public function run(array $params)
    {
        $minAge = (int)(CLI::getOption('age') ?? 60);
        $includeNear = CLI::getOption('include-near') !== null || in_array('--include-near', $params);
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

                    if ($age >= 60) {
                        $retired60List[] = $item;
                    } elseif ($age >= 59) {
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
        CLI::write("Tahun Evaluasi                   : " . $currentYear, 'cyan');
        CLI::write("Ambang Batas Pensiun             : >= 60 Tahun (Lahir <= 1966)", 'green');
        CLI::write("Total Akun PNS di Database       : " . $totalAccounts, 'yellow');
        CLI::write("• Memiliki NIP Valid             : " . $withNip, 'green');
        CLI::write("  - Usia Aktif (<= 58 Tahun)     : " . count($activeList) . " Akun", 'green');
        CLI::write("  - Menjelang Pensiun (59 Tahun) : " . count($nearRetireList) . " Akun (MPP BUP 60)", 'cyan');
        CLI::write("  - PENSIUN / PURNA TUGAS (>= 60): " . count($retired60List) . " Akun", 'red');
        CLI::write("• Belum Memiliki NIP             : " . $withoutNip . " Akun", 'light_gray');
        CLI::write("==========================================================\n");

        // Tampilkan Sampel Pensiun Usia >= 60 Tahun
        if (!empty($retired60List)) {
            CLI::write("--- [DAFTAR PEGAWAI USIA >= 60 TAHUN (PURNA TUGAS / PENSIUN)] ---", 'red');
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
                CLI::write("... dan " . (count($retired60List) - 15) . " pegawai pensiun lainnya (usia >= 60 tahun).\n");
            } else {
                CLI::write("");
            }
        }

        // Tampilkan Sampel Menjelang Pensiun (59 Tahun)
        if (!empty($nearRetireList) && $includeNear) {
            CLI::write("--- [SAMPEL PEGAWAI USIA 59 TAHUN (MENJELANG BUP 60)] ---", 'cyan');
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
                CLI::write("... dan " . (count($nearRetireList) - 10) . " pegawai lainnya usia 59 tahun.\n");
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
                $kat = $item['age'] >= 60 ? 'PENSIUN (Usia >= 60 Thn)' : 'Menjelang Pensiun (Usia 59 Thn)';
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

            CLI::write("✓ Berhasil mengekspor data PNS pensiun (>= 60 tahun) ke file CSV:", 'green');
            CLI::write("  $filepath\n", 'cyan');
        } else {
            CLI::write("💡 Tips: Untuk mengekspor daftar seluruh PNS usia >= 60 tahun ke file CSV, jalankan:", 'yellow');
            CLI::write("php spark pns:check-pensiun --export\n", 'cyan');
        }
    }
}
