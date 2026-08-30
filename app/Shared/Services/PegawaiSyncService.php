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
        $nipList = array_values($nipList);
        $total = count($nipList);

        foreach ($nipList as $index => $nip) {
            $success = false;
            $statusMessage = 'Gagal / Tidak ditemukan';

            try {
                $result = $emailService->syncPegawaiFromApi($nip);
                if (!empty($result['success'])) {
                    $success = true;
                    $statusMessage = $result['message'] ?? 'Sukses';
                } else {
                    $statusMessage = $result['message'] ?? 'Gagal';
                }
            } catch (\Throwable $e) {
                $statusMessage = 'Error: ' . $e->getMessage();
            }

            if (is_callable($onProgress)) {
                $onProgress($index + 1, $total, $nip, $success, $statusMessage);
            }

            // Jeda mikro 100ms (~10 request/detik) untuk menjaga beban server SIMPEG
            usleep(100000);
        }
    }
}

