<?php

namespace App\Domains\Batch;

use App\Shared\Libraries\CpanelApi;
use App\Domains\Email\EmailModel;
use App\Domains\UnitKerja\UnitKerjaModel;
use App\Domains\Email\PkModel;
use Exception;

class EmailBatchService
{
    protected $cpanelApi;
    protected $emailModel;
    protected $unitKerjaModel;
    protected $pkModel;

    public function __construct(
        CpanelApi $cpanelApi = null,
        EmailModel $emailModel = null,
        UnitKerjaModel $unitKerjaModel = null,
        PkModel $pkModel = null
    ) {
        $this->cpanelApi = $cpanelApi ?? new CpanelApi();
        $this->emailModel = $emailModel ?? new EmailModel();
        $this->unitKerjaModel = $unitKerjaModel ?? new UnitKerjaModel();
        $this->pkModel = $pkModel ?? new PkModel();
    }

    public function processBatchUpdate(array $data)
    {
        $mode = $data['mode'] ?? 'nik';
        $identifiers = $data['identifiers'];
        $newNames = $data['names'] ?? [];
        $newPasswords = $data['passwords'] ?? [];
        $newNiks = $data['niks'] ?? [];
        $newNips = $data['nips'] ?? [];
        $newGelarDepans = $data['gelar_depans'] ?? [];
        $newGelarBelakangs = $data['gelar_belakangs'] ?? [];
        $newNomors = $data['nomors'] ?? [];
        $newGajiNominals = $data['gaji_nominals'] ?? [];
        $newGajiTerbilangs = $data['gaji_terbilangs'] ?? [];
        $newTanggalKontrakAwals = $data['tanggal_kontrak_awals'] ?? [];
        $newTanggalKontrakAkhirs = $data['tanggal_kontrak_akhirs'] ?? [];
        $newTempatLahirs = $data['tempat_lahirs'] ?? [];
        $newTanggalLahirs = $data['tanggal_lahirs'] ?? [];
        $newPendidikans = $data['pendidikans'] ?? [];
        $newJabatans = $data['jabatans'] ?? [];
        $newGolongans = $data['golongans'] ?? [];
        $newUnitKerjaIds = $data['unit_kerja_ids'] ?? [];
        $newStatusAsn = $data['status_asn'] ?? null;
        $newEselonId = $data['eselon_id'] ?? null;
        $newBsreStatus = $data['bsre_status'] ?? null;
        $newPimpinan = $data['pimpinan'] ?? null;
        $newPimpinanDesa = $data['pimpinan_desa'] ?? null;
        $newUnitKerja = $data['unit_kerja'] ?? null;
        
        $newUnitKerjaIdFromNama = null;
        if (!empty($newUnitKerja)) {
            $unit = $this->unitKerjaModel->where('nama_unit_kerja', $newUnitKerja)->first();
            if ($unit) {
                $newUnitKerjaIdFromNama = $unit['id'];
            }
        }

        // PRE-FETCHING (Menghindari N+1 Query)
        $cleanIdentifiers = [];
        foreach ($identifiers as $identifier) {
            if (empty($identifier)) continue;
            if ($mode === 'email') {
                $cleanIdentifiers[] = $identifier;
            } else {
                $cleanIdentifiers[] = hash('sha256', str_replace([' ', '.', '-', '\''], '', $identifier));
            }
        }

        $existingEmails = [];
        $existingPks = [];
        if (!empty($cleanIdentifiers)) {
            // Pecah menjadi chunk 500 untuk mencegah kueri terlalu besar
            $chunks = array_chunk($cleanIdentifiers, 500);
            foreach ($chunks as $chunk) {
                if ($mode === 'email') {
                    $records = $this->emailModel->whereIn('email', $chunk)->findAll();
                } else {
                    $records = $this->emailModel->whereIn('nik_hash', $chunk)->findAll();
                }
                foreach ($records as $r) {
                    $key = ($mode === 'email') ? $r['email'] : $r['nik_hash'];
                    $existingEmails[$key] = $r;
                }
            }
            
            $emailsList = array_column($existingEmails, 'email');
            if (!empty($emailsList)) {
                $pkChunks = array_chunk($emailsList, 500);
                foreach ($pkChunks as $pChunk) {
                    $pks = $this->pkModel->whereIn('email', $pChunk)->findAll();
                    foreach ($pks as $p) {
                        $existingPks[$p['email']] = $p;
                    }
                }
            }
        }

        $results = [];
        $db = \Config\Database::connect();

        foreach ($identifiers as $index => $identifier) {
            if (empty($identifier)) {
                $results[] = ['identifier' => 'Baris ' . ($index + 1), 'success' => false, 'message' => 'Identifier wajib diisi.'];
                continue;
            }

            $searchKey = ($mode === 'email') ? $identifier : hash('sha256', str_replace([' ', '.', '-', '\''], '', $identifier));
            
            if (!isset($existingEmails[$searchKey])) {
                $results[] = ['identifier' => $identifier, 'success' => false, 'message' => 'Record not found in local database.'];
                continue;
            }

            $emailRecord = $existingEmails[$searchKey];
            $pkRecord = $existingPks[$emailRecord['email']] ?? null;

            $emailUpdateData = [];
            
            $compareAndUpdate = function($field, $newValue) use (&$emailUpdateData, $emailRecord) {
                if ($newValue !== null && $newValue !== '' && (string)($emailRecord[$field] ?? '') !== (string)$newValue) {
                    $emailUpdateData[$field] = $newValue;
                }
            };

            if (isset($newNames[$index])) $compareAndUpdate('name', $newNames[$index]);
            if (isset($newGelarDepans[$index])) $compareAndUpdate('gelar_depan', $newGelarDepans[$index]);
            if (isset($newGelarBelakangs[$index])) $compareAndUpdate('gelar_belakang', $newGelarBelakangs[$index]);
            if (isset($newPasswords[$index])) $compareAndUpdate('password', $newPasswords[$index]);
            if (isset($newNiks[$index])) $compareAndUpdate('nik', $newNiks[$index]);
            if (isset($newNips[$index])) $compareAndUpdate('nip', $newNips[$index]);
            if (isset($newTempatLahirs[$index])) $compareAndUpdate('tempat_lahir', $newTempatLahirs[$index]);
            if (isset($newTanggalLahirs[$index])) $compareAndUpdate('tanggal_lahir', $newTanggalLahirs[$index]);
            if (isset($newPendidikans[$index])) $compareAndUpdate('pendidikan', $newPendidikans[$index]);
            if (isset($newJabatans[$index])) $compareAndUpdate('jabatan', mb_strtoupper($newJabatans[$index], 'UTF-8'));
            if (isset($newGolongans[$index])) $compareAndUpdate('golongan', $newGolongans[$index]);
            
            // LOGIKA UNIT KERJA
            $rowUnitKerjaId = $newUnitKerjaIds[$index] ?? null;
            $finalUnitKerjaId = null;
            if (!empty($rowUnitKerjaId)) {
                $finalUnitKerjaId = $rowUnitKerjaId; // Prioritas ke ID dari baris Excel
            } elseif ($newUnitKerjaIdFromNama) {
                $finalUnitKerjaId = $newUnitKerjaIdFromNama; // Fallback ke dropdown UI
            }

            if ($finalUnitKerjaId && (string)$emailRecord['unit_kerja_id'] !== (string)$finalUnitKerjaId) {
                $emailUpdateData['unit_kerja_id'] = $finalUnitKerjaId;
            }

            if (!empty($newStatusAsn) && (string)$emailRecord['status_asn_id'] !== (string)$newStatusAsn) {
                $emailUpdateData['status_asn_id'] = $newStatusAsn;
            }
            if (!empty($newEselonId) && (string)$emailRecord['eselon_id'] !== (string)$newEselonId) {
                $emailUpdateData['eselon_id'] = $newEselonId;
            }
            if (!empty($newBsreStatus) && (string)$emailRecord['bsre_status'] !== (string)$newBsreStatus) {
                $emailUpdateData['bsre_status'] = $newBsreStatus;
            }
            if (isset($newPimpinan) && $newPimpinan !== '' && (int)$emailRecord['pimpinan'] !== (int)$newPimpinan) {
                $emailUpdateData['pimpinan'] = $newPimpinan;
            }
            if (isset($newPimpinanDesa) && $newPimpinanDesa !== '' && (int)$emailRecord['pimpinan_desa'] !== (int)$newPimpinanDesa) {
                $emailUpdateData['pimpinan_desa'] = $newPimpinanDesa;
            }

            $pkUpdateData = [];
            
            $compareAndUpdatePk = function($field, $newValue) use (&$pkUpdateData, $pkRecord) {
                $oldValue = $pkRecord ? $pkRecord[$field] : null;
                
                if ($field === 'gaji_nominal') {
                    // Bersihkan dari sen koma/titik 00 dan pemisah ribuan
                    $cleanedNew = str_replace(['.00', ',00'], '', $newValue);
                    $cleanedNew = str_replace(['.', ','], '', $cleanedNew);
                    
                    if ($oldValue !== null && $newValue !== '' && round((float)$oldValue) === round((float)$cleanedNew)) {
                        return;
                    }
                    $newValue = $cleanedNew;
                }
                
                if ($newValue !== null && $newValue !== '' && (string)$oldValue !== (string)$newValue) {
                    $pkUpdateData[$field] = $newValue;
                }
            };

            if (isset($newNomors[$index])) $compareAndUpdatePk('nomor', $newNomors[$index]);
            if (isset($newGajiNominals[$index])) $compareAndUpdatePk('gaji_nominal', $newGajiNominals[$index]);
            if (isset($newGajiTerbilangs[$index])) $compareAndUpdatePk('gaji_terbilang', $newGajiTerbilangs[$index]);
            if (isset($newTanggalKontrakAwals[$index])) $compareAndUpdatePk('tanggal_kontrak_awal', $newTanggalKontrakAwals[$index]);
            if (isset($newTanggalKontrakAkhirs[$index])) $compareAndUpdatePk('tanggal_kontrak_akhir', $newTanggalKontrakAkhirs[$index]);

            $targetStatusAsnId = $emailUpdateData['status_asn_id'] ?? $emailRecord['status_asn_id'];
            if ($pkRecord && (string)$pkRecord['status_asn_id'] !== (string)$targetStatusAsnId) {
                $pkUpdateData['status_asn_id'] = $targetStatusAsnId;
            } elseif (!$pkRecord && !empty($pkUpdateData)) {
                $pkUpdateData['status_asn_id'] = $targetStatusAsnId;
            }

            if (empty($emailUpdateData) && empty($pkUpdateData)) {
                $results[] = ['identifier' => $identifier, 'success' => true, 'message' => 'Skipped (no changes detected).'];
                continue;
            }

            // TRANSAKSI DATABASE DAN SINKRONISASI CPANEL
            $db->transBegin();
            try {
                if (!empty($emailUpdateData)) {
                    // Sinkronisasi Password cPanel
                    if (isset($emailUpdateData['password']) && !empty($emailUpdateData['password'])) {
                        $cpanelRes = $this->cpanelApi->change_password($emailRecord['email'], $emailUpdateData['password']);
                        if (!isset($cpanelRes['status']) || $cpanelRes['status'] != 1) {
                            throw new \Exception('Gagal sinkron password ke cPanel: ' . ($cpanelRes['errors'][0] ?? 'Unknown error'));
                        }
                    }

                    if (!$this->emailModel->update($emailRecord['id'], $emailUpdateData)) {
                        throw new \Exception('Gagal menyimpan data Email ke database.');
                    }
                }

                if (!empty($pkUpdateData)) {
                    if ($pkRecord) {
                        $updatedPk = $this->pkModel->update($pkRecord['id'], $pkUpdateData);
                    } else {
                        $pkUpdateData['email'] = $emailRecord['email'];
                        $updatedPk = $this->pkModel->insert($pkUpdateData);
                    }
                    if (!$updatedPk) {
                        throw new \Exception('Gagal menyimpan data Perjanjian Kerja ke database.');
                    }
                }

                if ($db->transStatus() === false) {
                    $db->transRollback();
                    $results[] = ['identifier' => $identifier, 'success' => false, 'message' => 'Transaksi database gagal di-commit.'];
                } else {
                    $db->transCommit();
                    $results[] = ['identifier' => $identifier, 'success' => true, 'message' => 'Successfully updated.'];
                }

            } catch (\Throwable $e) {
                $db->transRollback();
                log_message('error', 'Batch Update Error for ' . $identifier . ': ' . $e->getMessage());
                $results[] = ['identifier' => $identifier, 'success' => false, 'message' => $e->getMessage()];
            }
        }

        return $results;
    }

    public function processBatchCreate(array $data)
    {
        $emails = array_map(function ($item) {
            return $item->email;
        }, $data);

        $niks = array_filter(array_map(function ($item) {
            $val = $item->nik ?? null;
            return $val ? str_replace([' ', '.', '-', '\''], '', $val) : null;
        }, $data));

        $existing_emails = $this->emailModel->whereIn('email', $emails)->findColumn('email') ?? [];
        
        $existing_niks = [];
        if (!empty($niks)) {
            $nikHashes = array_map(fn($n) => hash('sha256', $n), $niks);
            $existing_nik_hashes = $this->emailModel->whereIn('nik_hash', $nikHashes)->findColumn('nik_hash') ?? [];
            if (!empty($existing_nik_hashes)) {
                // For error message reporting, we'd ideally show the plain NIKs
                // We'll just collect which input NIKs are already hashed in the DB
                $hashesMap = array_flip($existing_nik_hashes);
                foreach ($niks as $nik) {
                    if (isset($hashesMap[hash('sha256', $nik)])) {
                        $existing_niks[] = $nik;
                    }
                }
            }
        }

        if (!empty($existing_emails) || !empty($existing_niks)) {
            $errors = [];
            if (!empty($existing_emails)) {
                $errors[] = 'Email(s) already exist: ' . implode(', ', $existing_emails);
            }
            if (!empty($existing_niks)) {
                $errors[] = 'NIK(s) already exist: ' . implode(', ', $existing_niks);
            }
            throw new Exception(implode(' | ', $errors) . '. Please remove them from the list and try again.');
        }

        $results = [];
        $db = \Config\Database::connect();
        
        foreach ($data as $item) {
            $db->transBegin();
            try {
                $unitKerjaId = null;
                if (!empty($item->unitKerja)) {
                    $unit = $this->unitKerjaModel->where('nama_unit_kerja', $item->unitKerja)->first();
                    if ($unit) {
                        $unitKerjaId = $unit['id'];
                    }
                }

                // 1. Simpan ke database lokal terlebih dahulu
                $this->emailModel->insert([
                    'email'      => $item->email,
                    'user'       => explode('@', $item->email)[0],
                    'domain'     => explode('@', $item->email)[1],
                    'unit_kerja_id' => $unitKerjaId,
                    'password'   => $item->password ?? null,
                    'nik'        => $item->nik ?? null,
                    'nip'        => $item->nip ?? null,
                    'name'       => $item->name ?? null,
                    'jabatan'    => !empty($item->jabatan) ? mb_strtoupper($item->jabatan, 'UTF-8') : null,
                    'status_asn_id' => $item->statusAsn ?? null,
                ]);

                // 2. Buat akun di cPanel
                $this->cpanelApi->create_email_account($item->email, $item->password, $item->quota);

                if ($db->transStatus() === false) {
                    throw new Exception('Gagal menyimpan data ke database lokal.');
                }
                
                $db->transCommit();
                $results[] = ['email' => $item->email, 'success' => true];
            } catch (\Throwable $e) {
                $db->transRollback();
                
                $errorMessage = $e->getMessage();
                if (strpos($errorMessage, 'already exists') !== false) {
                    $results[] = ['email' => $item->email, 'success' => false, 'message' => 'Email already exists on cPanel.'];
                } else {
                    $results[] = ['email' => $item->email, 'success' => false, 'message' => $errorMessage];
                }
            }
        }

        return $results;
    }
}
