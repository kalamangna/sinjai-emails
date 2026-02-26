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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
