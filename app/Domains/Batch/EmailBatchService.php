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

    public function __construct()
    {
        $this->cpanelApi = new CpanelApi();
        $this->emailModel = new EmailModel();
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->pkModel = new PkModel();
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

        $results = [];
        foreach ($identifiers as $index => $identifier) {
            $emailRecord = null;
            if ($mode === 'email') {
                $emailRecord = $this->emailModel->where('email', $identifier)->first();
            } else {
                $emailRecord = $this->emailModel->where('nik', $identifier)->first();
            }

            if (!$emailRecord) {
                $results[] = ['identifier' => $identifier, 'success' => false, 'message' => 'Record not found in local database.'];
                continue;
            }

            $emailUpdateData = [];
            if (isset($newNames[$index]) && !empty($newNames[$index])) {
                $emailUpdateData['name'] = $newNames[$index];
            }
            if (isset($newGelarDepans[$index]) && !empty($newGelarDepans[$index])) {
                $emailUpdateData['gelar_depan'] = $newGelarDepans[$index];
            }
            if (isset($newGelarBelakangs[$index]) && !empty($newGelarBelakangs[$index])) {
                $emailUpdateData['gelar_belakang'] = $newGelarBelakangs[$index];
            }
            if (isset($newPasswords[$index]) && !empty($newPasswords[$index])) {
                $emailUpdateData['password'] = $newPasswords[$index];
            }
            if (isset($newNiks[$index]) && !empty($newNiks[$index])) {
                $emailUpdateData['nik'] = $newNiks[$index];
            }
            if (isset($newNips[$index]) && !empty($newNips[$index])) {
                $emailUpdateData['nip'] = $newNips[$index];
            }
            if (isset($newTempatLahirs[$index]) && !empty($newTempatLahirs[$index])) {
                $emailUpdateData['tempat_lahir'] = $newTempatLahirs[$index];
            }
            if (isset($newTanggalLahirs[$index]) && !empty($newTanggalLahirs[$index])) {
                $emailUpdateData['tanggal_lahir'] = $newTanggalLahirs[$index];
            }
            if (isset($newPendidikans[$index]) && !empty($newPendidikans[$index])) {
                $emailUpdateData['pendidikan'] = $newPendidikans[$index];
            }
            if (isset($newJabatans[$index]) && !empty($newJabatans[$index])) {
                $emailUpdateData['jabatan'] = $newJabatans[$index];
            }
            if (isset($newGolongans[$index]) && !empty($newGolongans[$index])) {
                $emailUpdateData['golongan'] = $newGolongans[$index];
            }
            if (isset($newUnitKerjaIds[$index]) && !empty($newUnitKerjaIds[$index])) {
                $emailUpdateData['unit_kerja_id'] = $newUnitKerjaIds[$index];
            }
            if (!empty($newStatusAsn)) {
                $emailUpdateData['status_asn_id'] = $newStatusAsn;
            }
            if (!empty($newEselonId)) {
                $emailUpdateData['eselon_id'] = $newEselonId;
            }
            if (!empty($newBsreStatus)) {
                $emailUpdateData['bsre_status'] = $newBsreStatus;
            }
            if (isset($newPimpinan) && $newPimpinan !== '') {
                $emailUpdateData['pimpinan'] = $newPimpinan;
            }
            if (isset($newPimpinanDesa) && $newPimpinanDesa !== '') {
                $emailUpdateData['pimpinan_desa'] = $newPimpinanDesa;
            }
            if (!empty($newUnitKerja)) {
                $unit = $this->unitKerjaModel->where('nama_unit_kerja', $newUnitKerja)->first();
                if ($unit) {
                    $emailUpdateData['unit_kerja_id'] = $unit['id'];
                }
            }


            $pkUpdateData = [];
            if (isset($newNomors[$index]) && !empty($newNomors[$index])) {
                $pkUpdateData['nomor'] = $newNomors[$index];
            }
            if (isset($newGajiNominals[$index]) && !empty($newGajiNominals[$index])) {
                $pkUpdateData['gaji_nominal'] = $newGajiNominals[$index];
            }
            if (isset($newGajiTerbilangs[$index]) && !empty($newGajiTerbilangs[$index])) {
                $pkUpdateData['gaji_terbilang'] = $newGajiTerbilangs[$index];
            }
            if (isset($newTanggalKontrakAwals[$index]) && !empty($newTanggalKontrakAwals[$index])) {
                $pkUpdateData['tanggal_kontrak_awal'] = $newTanggalKontrakAwals[$index];
            }
            if (isset($newTanggalKontrakAkhirs[$index]) && !empty($newTanggalKontrakAkhirs[$index])) {
                $pkUpdateData['tanggal_kontrak_akhir'] = $newTanggalKontrakAkhirs[$index];
            }

            if (!empty($pkUpdateData)) {
                $pkUpdateData['status_asn_id'] = $emailRecord['status_asn_id'];
            }

            if (empty($emailUpdateData) && empty($pkUpdateData)) {
                $results[] = ['identifier' => $identifier, 'success' => true, 'message' => 'Skipped (no data to update).'];
                continue;
            }

            $updatedEmail = false;
            $updatedPk = false;

            if (!empty($emailUpdateData)) {
                try {
                    $updatedEmail = $this->emailModel->update($emailRecord['id'], $emailUpdateData);
                } catch (Exception $e) {
                    log_message('error', 'Error updating EmailModel for ' . $identifier . ': ' . $e->getMessage());
                }
            }

            if (!empty($pkUpdateData)) {
                try {
                    $pkRecord = $this->pkModel->where('email', $emailRecord['email'])->first();
                    if ($pkRecord) {
                        $updatedPk = $this->pkModel->update($pkRecord['id'], $pkUpdateData);
                    } else {
                        $pkUpdateData['email'] = $emailRecord['email'];
                        $updatedPk = $this->pkModel->insert($pkUpdateData);
                    }
                } catch (Exception $e) {
                    log_message('error', 'Error updating PkModel for ' . $identifier . ': ' . $e->getMessage());
                }
            }

            if ($updatedEmail || $updatedPk) {
                $results[] = ['identifier' => $identifier, 'success' => true, 'message' => 'Successfully updated.'];
            } else {
                $results[] = ['identifier' => $identifier, 'success' => false, 'message' => 'Failed to update (no changes or database error).'];
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
            return $item->nik ?? null;
        }, $data));

        $existing_emails = $this->emailModel->whereIn('email', $emails)->findColumn('email') ?? [];
        $existing_niks = !empty($niks) ? ($this->emailModel->whereIn('nik', $niks)->findColumn('nik') ?? []) : [];

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
        foreach ($data as $item) {
            try {
                $this->cpanelApi->create_email_account($item->email, $item->password, $item->quota);

                $unitKerjaId = null;
                if (!empty($item->unitKerja)) {
                    $unit = $this->unitKerjaModel->where('nama_unit_kerja', $item->unitKerja)->first();
                    if ($unit) {
                        $unitKerjaId = $unit['id'];
                    }
                }

                $this->emailModel->insert([
                    'email'      => $item->email,
                    'user'       => explode('@', $item->email)[0],
                    'domain'     => explode('@', $item->email)[1],
                    'unit_kerja_id' => $unitKerjaId,
                    'password'   => $item->password ?? null,
                    'nik'        => $item->nik ?? null,
                    'nip'        => $item->nip ?? null,
                    'name'       => $item->name ?? null,
                    'jabatan'    => $item->jabatan ?? null,
                    'status_asn_id' => $item->statusAsn ?? null,
                ]);

                $results[] = ['email' => $item->email, 'success' => true];
            } catch (Exception $e) {
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
