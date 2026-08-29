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
        
        $unitModel = new \App\Domains\UnitKerja\Models\UnitKerjaModel();
        $allUnits = $unitModel->where('api_unit_id IS NOT NULL')->where('api_unit_id !=', '')->findAll();
        $unitMapByApiId = [];
        foreach ($allUnits as $u) {
            $unitMapByApiId[(string)$u['api_unit_id']] = $u['id'];
        }

        $total = count($nipList);
        foreach ($nipList as $index => $nip) {
            $success = false;
            $statusMessage = 'Gagal / Tidak ditemukan';
            
            $result = $pegawaiApi->getPegawaiData($nip);
            if ($result['success']) {
                $data = $result['data'];
                // API Simpeg kadang mengembalikan array index 0, kadang langsung object
                $source = (is_array($data) && isset($data[0])) ? $data[0] : $data;
                
                if (isset($source['pangkat_nama']) || isset($source['pangkat_golruang']) || isset($source['unit_id'])) {
                    $updateData = [];

                    if (isset($source['pangkat_nama'])) {
                        $updateData['pangkat_nama'] = trim($source['pangkat_nama']);
                    }
                    if (isset($source['pangkat_golruang'])) {
                        $updateData['pangkat_golruang'] = trim($source['pangkat_golruang']);
                    }
                    
                    // Sinkronisasi Unit Kerja jika terjadi mutasi / pindah tugas di SIMPEG
                    if (!empty($source['unit_id'])) {
                        $apiUnitIdStr = (string)$source['unit_id'];
                        if (isset($unitMapByApiId[$apiUnitIdStr])) {
                            $updateData['unit_kerja_id'] = $unitMapByApiId[$apiUnitIdStr];
                        }
                    }

                    // Normalisasi nama jabatan agar bersih dan rapi (huruf kapital, tanpa trailing dot/koma/spasi ganda)
                    $rawJabatan = $source['jabatan_nama'] ?? $source['jabatan'] ?? null;
                    if (!empty($rawJabatan)) {
                        $rawJ = mb_strtoupper(trim($rawJabatan), 'UTF-8');
                        $rawJ = preg_replace('/[,\.]+\s*$/', '', $rawJ);

                        // Format ringkas pimpinan
                        if (stripos($rawJ, 'KEPALA DINAS') === 0 || stripos($rawJ, 'KEPALA SATUAN POLISI') === 0) {
                            $rawJ = 'KEPALA DINAS';
                        } elseif (stripos($rawJ, 'KEPALA BADAN') === 0) {
                            $rawJ = 'KEPALA BADAN';
                        } elseif (stripos($rawJ, 'KEPALA BAGIAN') === 0) {
                            $rawJ = 'KEPALA BAGIAN';
                        } elseif (stripos($rawJ, 'DIREKTUR') === 0) {
                            $rawJ = 'DIREKTUR';
                        } elseif (strpos($rawJ, '/') !== false && !preg_match('/\b[IVX]+\/[A-D]\b/i', $rawJ)) {
                            $parts = explode('/', $rawJ);
                            if (count($parts) > 1 && strlen(trim($parts[0])) > 2 && strlen(trim($parts[1])) > 2) {
                                $managerialKeywords = ['KEPALA', 'DIREKTUR', 'KOORDINATOR', 'KETUA', 'WAKIL', 'SEKRETARIS', 'KASUBAG', 'KASI', 'KABID', 'PIMPINAN'];
                                $chosen = null;
                                foreach ($parts as $p) {
                                    $pUpper = strtoupper(trim($p));
                                    foreach ($managerialKeywords as $kw) {
                                        if (stripos($pUpper, $kw) !== false) {
                                            $chosen = $p;
                                            break 2;
                                        }
                                    }
                                }
                                if (!$chosen) {
                                    $chosen = $parts[0];
                                }
                                $rawJ = $chosen;
                            }
                        }

                        // Koreksi SUB. BAGIAN / SUB. BIDANG menjadi SUB BAGIAN / SUB BIDANG
                        $rawJ = preg_replace('/\bSUB\.\s*/i', 'SUB ', $rawJ);

                        $rawJ = preg_replace('/\s+([,\.])/', '$1', $rawJ);
                        $rawJ = preg_replace('/([,\.])\s+/', '$1 ', $rawJ);
                        $rawJ = preg_replace('/\s*\/\s*/', '/', $rawJ);
                        $rawJ = preg_replace('/\s*-\s*/', ' - ', $rawJ);
                        $rawJ = preg_replace('/\s+/', ' ', trim($rawJ));
                        $rawJ = str_replace(
                            ['TEHNOLOGI', 'KOMSUMSI', 'HOLTIKULTURA'],
                            ['TEKNOLOGI', 'KONSUMSI', 'HORTIKULTURA'],
                            $rawJ
                        );
                        $updateData['jabatan'] = $rawJ;
                    }
                    
                    // Update ke database
                    if (!empty($updateData)) {
                        $emailModel->where('nip', $nip)->set($updateData)->update();
                        $success = true;
                        $statusMessage = 'Sukses';
                    }
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
