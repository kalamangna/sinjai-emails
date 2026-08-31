<?php

use CodeIgniter\I18n\Time;

/**
 * TanggalHelper - Centralized Indonesian Date and Time Helper
 */

if (!function_exists('formatTanggal')) {
    /**
     * Format date to Indonesian format: d F Y (e.g., 27 Februari 2026)
     */
    function formatTanggal($date)
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '-';
        }

        try {
            if (is_numeric($date)) {
                $time = Time::createFromTimestamp($date, 'Asia/Makassar');
            } else {
                $time = $date instanceof Time ? $date : Time::parse($date, 'Asia/Makassar');
            }
            
            $months = [
                1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            
            return $time->getDay() . ' ' . $months[$time->getMonth()] . ' ' . $time->getYear();
        } catch (\Throwable $e) {
            return $date;
        }
    }
}

if (!function_exists('formatTanggalWaktu')) {
    /**
     * Format datetime to Indonesian format: d F Y, H:i
     */
    function formatTanggalWaktu($date)
    {
        if (empty($date) || $date === '0000-00-00 00:00:00') {
            return '-';
        }

        try {
            if (is_numeric($date)) {
                $time = Time::createFromTimestamp($date, 'Asia/Makassar');
            } else {
                $time = $date instanceof Time ? $date : Time::parse($date, 'Asia/Makassar');
            }
            $tanggal = formatTanggal($time);
            return $tanggal . ', ' . $time->format('H:i');
        } catch (\Throwable $e) {
            return $date;
        }
    }
}

if (!function_exists('formatSingkat')) {
    /**
     * Format date to short format: d/m/Y
     */
    function formatSingkat($date)
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '-';
        }

        try {
            if (is_numeric($date)) {
                $time = Time::createFromTimestamp($date, 'Asia/Makassar');
            } else {
                $time = $date instanceof Time ? $date : Time::parse($date, 'Asia/Makassar');
            }
            return $time->format('d/m/Y');
        } catch (\Throwable $e) {
            return $date;
        }
    }
}

if (!function_exists('formatStrip')) {
    /**
     * Format date to dash format: d-m-Y
     */
    function formatStrip($date)
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '-';
        }

        try {
            if (is_numeric($date)) {
                $time = Time::createFromTimestamp($date, 'Asia/Makassar');
            } else {
                $time = $date instanceof Time ? $date : Time::parse($date, 'Asia/Makassar');
            }
            return $time->format('d-m-Y');
        } catch (\Throwable $e) {
            return $date;
        }
    }
}

if (!function_exists('waktuRelatif')) {
    /**
     * Format date to relative time in Indonesian
     */
    function waktuRelatif($date)
    {
        if (empty($date) || $date === '0000-00-00 00:00:00') {
            return '-';
        }

        try {
            if (is_numeric($date)) {
                $time = Time::createFromTimestamp($date, 'Asia/Makassar');
            } else {
                $time = $date instanceof Time ? $date : Time::parse($date, 'Asia/Makassar');
            }
            
            return $time->humanize();
        } catch (\Throwable $e) {
            return $date;
        }
    }
}

if (!function_exists('namaHari')) {
    /**
     * Get Indonesian day name from date
     */
    function namaHari($date)
    {
        if (empty($date)) {
            return '-';
        }

        try {
            if (is_numeric($date)) {
                $time = Time::createFromTimestamp($date, 'Asia/Makassar');
            } else {
                $time = $date instanceof Time ? $date : Time::parse($date, 'Asia/Makassar');
            }
            $days = [
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            ];
            
            return $days[$time->format('l')];
        } catch (\Throwable $e) {
            return $date;
        }
    }
}

if (!function_exists('untukDatabase')) {
    /**
     * Format date for database: Y-m-d H:i:s
     */
    function untukDatabase($date = 'now')
    {
        try {
            if (is_numeric($date)) {
                $time = Time::createFromTimestamp($date, 'Asia/Makassar');
            } else {
                $time = $date instanceof Time ? $date : Time::parse($date, 'Asia/Makassar');
            }
            return $time->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return date('Y-m-d H:i:s');
        }
    }
}

if (!function_exists('formatBulanTahun')) {
    /**
     * Format date to Indonesian Month Year format: F Y
     */
    function formatBulanTahun($date)
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '-';
        }

        try {
            if (is_numeric($date)) {
                $time = Time::createFromTimestamp($date, 'Asia/Makassar');
            } else {
                $time = $date instanceof Time ? $date : Time::parse($date, 'Asia/Makassar');
            }
            
            $months = [
                1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            
            return $months[$time->getMonth()] . ' ' . $time->getYear();
        } catch (\Throwable $e) {
            return $date;
        }
    }
}

if (!function_exists('formatIsiInput')) {
    /**
     * Format date for HTML date input: Y-m-d
     */
    function formatIsiInput($date = 'now')
    {
        try {
            if (is_numeric($date)) {
                $time = Time::createFromTimestamp($date, 'Asia/Makassar');
            } else {
                $time = $date instanceof Time ? $date : Time::parse($date, 'Asia/Makassar');
            }
            return $time->format('Y-m-d');
        } catch (\Throwable $e) {
            return date('Y-m-d');
        }
    }
}

if (!function_exists('tahunSekarang')) {
    /**
     * Get current year
     */
    function tahunSekarang()
    {
        return date('Y');
    }
}

if (!function_exists('bulanSekarang')) {
    /**
     * Get current month number (1-12)
     */
    function bulanSekarang()
    {
        return date('n');
    }
}

if (!function_exists('hitungBupInfo')) {
    /**
     * Hitung Informasi BUP & TMT Pensiun untuk semua ASN
     *
     * @param array $account Data akun (nip, tanggal_lahir, jabatan, eselon_id, status_asn_id)
     * @return array|null
     */
    function hitungBupInfo(array $account): ?array
    {
        $nip = trim((string)($account['nip'] ?? ''));
        $birthDateStr = trim((string)($account['tanggal_lahir'] ?? ''));
        $jabatan = strtoupper(trim((string)($account['jabatan'] ?? '')));
        $eselonId = (int)($account['eselon_id'] ?? 0);

        // 1. Ekstraksi Tanggal Lahir (8 digit awal NIP/NI PPPK atau kolom tanggal_lahir)
        $birthDate = null;
        if (preg_match('/^(\d{4})(\d{2})(\d{2})/', $nip, $m)) {
            $year = (int)$m[1];
            $month = (int)$m[2];
            $day = (int)$m[3];
            if (checkdate($month, $day, $year) && $year >= 1940 && $year <= 2020) {
                $birthDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }
        if (!$birthDate && !empty($birthDateStr)) {
            $ts = strtotime($birthDateStr);
            if ($ts !== false) {
                $birthDate = date('Y-m-d', $ts);
            }
        }

        if (!$birthDate) {
            return null;
        }

        // 2. Tentukan Usia BUP berdasarkan Klasifikasi Jabatan
        $bupAge = 58; // Default: Pelaksana, Pengawas (Eselon IV), Administrator (Eselon III), JF Terampil/Mahir/Penyelia, JF Pertama & Muda
        if (
            stripos($jabatan, 'AHLI UTAMA') !== false ||
            (stripos($jabatan, 'UTAMA') !== false && stripos($jabatan, 'AHLI') !== false)
        ) {
            $bupAge = 65;
        } elseif (
            stripos($jabatan, 'AHLI MADYA') !== false ||
            stripos($jabatan, 'GURU') !== false ||
            stripos($jabatan, 'KEPALA SEKOLAH') !== false ||
            stripos($jabatan, 'PENGAWAS SEKOLAH') !== false ||
            stripos($jabatan, 'PENILIK') !== false ||
            stripos($jabatan, 'DOKTER') !== false ||
            stripos($jabatan, 'KEPALA DINAS') === 0 ||
            stripos($jabatan, 'KEPALA BADAN') === 0 ||
            stripos($jabatan, 'INSPEKTUR') === 0 ||
            stripos($jabatan, 'SEKRETARIS DAERAH') === 0 ||
            stripos($jabatan, 'SEKRETARIS DPRD') === 0 ||
            stripos($jabatan, 'STAF AHLI') === 0 ||
            stripos($jabatan, 'ASISTEN') === 0 ||
            $eselonId === 2
        ) {
            $bupAge = 60;
        }

        // 3. Hitung TMT Pensiun (Tanggal 1 bulan berikutnya setelah bulan lahir ke-BUP)
        $birthDateTime = new \DateTime($birthDate);
        $bupYear = (int)$birthDateTime->format('Y') + $bupAge;
        $birthMonth = (int)$birthDateTime->format('m');

        $tmtYear = $bupYear;
        $tmtMonth = $birthMonth + 1;
        if ($tmtMonth > 12) {
            $tmtMonth = 1;
            $tmtYear++;
        }
        $tmtPensiun = sprintf('%04d-%02d-01', $tmtYear, $tmtMonth);
        $today = date('Y-m-d');
        $diffDays = (int)((strtotime($tmtPensiun) - strtotime($today)) / 86400);

        $isPensiun = ($diffDays <= 0);
        $isApproaching = ($diffDays <= 365); // Kurang dari 1 tahun atau sudah lewat

        // Label sisa waktu
        if ($isPensiun) {
            $sisaWaktuLabel = 'Telah mencapai BUP';
        } else {
            $diffMonths = (int)round($diffDays / 30.44);
            if ($diffMonths > 0) {
                $sisaWaktuLabel = "Sisa {$diffMonths} Bulan";
            } else {
                $sisaWaktuLabel = "Sisa {$diffDays} Hari";
            }
        }

        return [
            'bup_age'            => $bupAge,
            'birth_date'         => $birthDate,
            'tmt_pensiun'        => $tmtPensiun,
            'is_pensiun'         => $isPensiun,
            'days_until_pensiun' => $diffDays,
            'is_approaching'     => $isApproaching,
            'sisa_waktu_label'   => $sisaWaktuLabel,
        ];
    }
}

