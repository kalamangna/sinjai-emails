<?php

namespace App\Shared\Services;

use App\Domains\Email\Services\EmailService;

class PegawaiSyncService
{
    /**
     * Memproses batch sinkronisasi data pegawai (Pangkat/Golongan/Jabatan) ke API SIMPEG
     * Menggunakan EmailService secara langsung untuk konsistensi 100% dengan tombol UI
     * 
     * @param array $nipList Array berisi daftar NIP
     */
    public function processBatch(array $nipList, ?callable $onProgress = null)
    {
        $emailService = new EmailService();
        $nipList = array_values(array_unique(array_filter($nipList)));
        $total = count($nipList);
        $retryQueue = [];

        foreach ($nipList as $index => $nip) {
            $success = false;
            $statusMessage = 'Gagal / Tidak ditemukan';
            $isRateLimited = false;

            try {
                $result = $emailService->syncPegawaiFromApi($nip);
                if (!empty($result['success'])) {
                    $success = true;
                    $statusMessage = $result['message'] ?? 'Sukses';
                } else {
                    $statusMessage = $result['message'] ?? 'Gagal';
                    if (!empty($result['is_rate_limit']) || strpos($statusMessage, '429') !== false || stripos($statusMessage, 'Rate Limit') !== false || stripos($statusMessage, 'Too Many Requests') !== false) {
                        $isRateLimited = true;
                    }
                }
            } catch (\Throwable $e) {
                $statusMessage = 'Error: ' . $e->getMessage();
                if (strpos($statusMessage, '429') !== false || stripos($statusMessage, 'Rate Limit') !== false || stripos($statusMessage, 'Too Many Requests') !== false) {
                    $isRateLimited = true;
                }
            }

            if ($isRateLimited) {
                $retryQueue[] = $nip;
                $statusMessage = 'Rate limited (429) -> Dimasukkan ke Antrean Ulang';
            }

            if (is_callable($onProgress)) {
                $onProgress($index + 1, $total, $nip, $success, $statusMessage);
            }

            // Jika terkena 429, berikan cooldown 15 detik agar kuota rate limiter pulih
            if ($isRateLimited) {
                sleep(15);
            } else {
                // Pacing aman 1.100ms (~54 request/menit) selalu berada di bawah kuota 60 req/menit SIMPEG
                usleep(1100000);
            }
        }

        // Jalankan Retry Pass jika ada baris yang terkena rate limit
        $maxRetryPasses = 3;
        $pass = 0;
        while (!empty($retryQueue) && $pass < $maxRetryPasses) {
            $pass++;
            $currentRetries = $retryQueue;
            $retryQueue = [];
            $retryTotal = count($currentRetries);

            // Jeda pendinginan 15 detik sebelum retry pass dimulai
            sleep(15);

            foreach ($currentRetries as $rIndex => $nip) {
                $success = false;
                $statusMessage = 'Gagal';
                $isRateLimited = false;

                try {
                    $result = $emailService->syncPegawaiFromApi($nip);
                    if (!empty($result['success'])) {
                        $success = true;
                        $statusMessage = $result['message'] ?? 'Sukses';
                    } else {
                        $statusMessage = $result['message'] ?? 'Gagal';
                        if (!empty($result['is_rate_limit']) || strpos($statusMessage, '429') !== false || stripos($statusMessage, 'Rate Limit') !== false || stripos($statusMessage, 'Too Many Requests') !== false) {
                            $isRateLimited = true;
                        }
                    }
                } catch (\Throwable $e) {
                    $statusMessage = 'Error: ' . $e->getMessage();
                    if (strpos($statusMessage, '429') !== false || stripos($statusMessage, 'Rate Limit') !== false || stripos($statusMessage, 'Too Many Requests') !== false) {
                        $isRateLimited = true;
                    }
                }

                if ($isRateLimited) {
                    $retryQueue[] = $nip;
                    $statusMessage = "Rate limited (429) -> Antrean Ulang Putaran " . ($pass + 1);
                }

                if (is_callable($onProgress)) {
                    $onProgress($rIndex + 1, $retryTotal, $nip, $success, "[RETRY #$pass] $statusMessage");
                }

                if ($isRateLimited) {
                    sleep(15);
                } else {
                    usleep(1200000); // 1.2 detik pacing saat retry pass
                }
            }
        }
    }
}

