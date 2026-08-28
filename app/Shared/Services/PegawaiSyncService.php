<?php

namespace App\Shared\Services;

use App\Domains\Email\Models\EmailModel;
use App\Shared\Libraries\PegawaiApi;

class PegawaiSyncService
{
    /**
     * Memproses batch sinkronisasi data pegawai (Pangkat/Golongan/Jabatan) ke API SIMPEG
     * 
     * @param array $nipList Array berisi daftar NIP
     */
    public function processBatch(array $nipList, ?callable $onProgress = null)
    {
        $emailModel = new EmailModel();
        $pegawaiApi = new PegawaiApi();
        
        $total = count($nipList);
        foreach ($nipList as $index => $nip) {
            $success = false;
            $statusMessage = 'Gagal / Tidak ditemukan';
            
            $result = $pegawaiApi->getPegawaiData($nip);
            if ($result['success']) {
                $data = $result['data'];
                // API Simpeg kadang mengembalikan array index 0, kadang langsung object
                $source = (is_array($data) && isset($data[0])) ? $data[0] : $data;
                
                if (isset($source['pangkat_nama']) || isset($source['pangkat_golruang'])) {
                    $updateData = [
                        'pangkat_nama' => $source['pangkat_nama'] ?? null,
                        'pangkat_golruang' => $source['pangkat_golruang'] ?? null
                    ];
                    
                    // Normalisasi nama jabatan agar kapital semua (sesuai standar database)
                    if (isset($source['jabatan'])) {
                        $updateData['jabatan'] = mb_strtoupper($source['jabatan'], 'UTF-8');
                    }
                    
                    // Update ke database
                    $emailModel->where('nip', $nip)->set($updateData)->update();
                    $success = true;
                    $statusMessage = 'Sukses';
                }
            } elseif (isset($result['code']) && ($result['code'] === 429 || $result['code'] === 503)) {
                // Backoff adaptif jika terkena rate limit
                sleep(2);
            }

            if (is_callable($onProgress)) {
                $onProgress($index + 1, $total, $nip, $success, $statusMessage);
            }

            // Jeda mikro 80ms (~3 request/detik) untuk melindungi server SIMPEG
            usleep(80000);
        }
    }
}
