<?php

namespace App\Domains\Email\Controllers;

use App\Shared\BaseController;
use App\Domains\Email\Models\PkModel;
use App\Domains\Email\Models\EmailModel;
use App\Domains\UnitKerja\Models\UnitKerjaModel;
use App\Shared\Libraries\BsreApi;

class TteBupatiController extends BaseController
{
    protected PkModel $pkModel;
    protected EmailModel $emailModel;
    protected UnitKerjaModel $unitKerjaModel;

    public function __construct()
    {
        $this->pkModel = new PkModel();
        $this->emailModel = new EmailModel();
        $this->unitKerjaModel = new UnitKerjaModel();
    }

    /**
     * Halaman antrean TTE PK untuk PPPK (status_asn_id = 2)
     */
    public function pppk()
    {
        return $this->renderTtePk(2, 'PK PPPK', 'tte-pk/pppk');
    }

    /**
     * Halaman antrean TTE PK untuk PPPK Paruh Waktu (status_asn_id = 3)
     */
    public function pppkPw()
    {
        return $this->renderTtePk(3, 'PK PPPK PW', 'tte-pk/pppk-pw');
    }

    /**
     * Halaman umum antrean TTE Bupati (fallback / semua)
     */
    public function index()
    {
        return $this->renderTtePk(null, 'TTE PK', 'tte-pk');
    }

    /**
     * Render data halaman antrean TTE PK
     */
    protected function renderTtePk(?int $statusAsnId = null, string $title = 'TTE PK', string $baseRoute = 'tte-pk/pppk')
    {
        $search = trim($this->request->getGet('search') ?? '');
        $unitKerjaId = $this->request->getGet('unit_kerja_id');
        $tab = $this->request->getGet('tab') ?? 'pending'; // 'pending', 'unsigned', atau 'completed'

        $builder = $this->pkModel->withPegawaiDetails();

        if ($tab === 'completed') {
            $builder->where('pk.tte_status', 'completed')
                    ->where('pk.tte_bupati_file IS NOT NULL')
                    ->where('pk.tte_bupati_file !=', '');
            $builder->orderBy('pk.tte_bupati_at', 'DESC');
        } elseif ($tab === 'unsigned') {
            $builder->groupStart()
                        ->where('pk.tte_status', 'unsigned')
                        ->orWhere('pk.tte_status', 'draft')
                        ->orWhere('pk.tte_status IS NULL')
                        ->orWhere('pk.tte_status', '')
                    ->groupEnd();
            $builder->orderBy('CAST(pk.nomor AS UNSIGNED)', 'ASC');
        } else {
            // Default pending: signed_pppk (Siap TTE Bupati)
            $tab = 'pending';
            $builder->where('pk.tte_status', 'signed_pppk')
                    ->where('pk.tte_pegawai_file IS NOT NULL')
                    ->where('pk.tte_pegawai_file !=', '');
            $builder->orderBy('pk.tte_pegawai_at', 'ASC');
        }

        if ($statusAsnId !== null) {
            $builder->where('emails.status_asn_id', $statusAsnId);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('emails.name', $search)
                ->orLike('emails.nip', $search)
                ->orLike('emails.email', $search)
                ->orLike('pk.nomor', $search)
            ->groupEnd();
        }

        $targetUnitIds = null;
        if (!empty($unitKerjaId)) {
            // Ambil ID unit induk dan semua unit anak di bawahnya
            $childIds = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->findColumn('id') ?: [];
            $targetUnitIds = array_merge([(int) $unitKerjaId], array_map('intval', $childIds));
            $builder->whereIn('emails.unit_kerja_id', $targetUnitIds);
        }

        $perPage = 20;
        $items = $builder->paginate($perPage, 'default');
        $pager = $this->pkModel->pager;

        // Hitung statistik keseluruhan PK (spesifik per status ASN dan Unit Kerja jika dipilih)
        $totalPkQuery = $this->pkModel->withPegawaiDetails();
        if ($statusAsnId !== null) {
            $totalPkQuery->where('emails.status_asn_id', $statusAsnId);
        }
        if (!empty($targetUnitIds)) {
            $totalPkQuery->whereIn('emails.unit_kerja_id', $targetUnitIds);
        }
        $totalPkCount = $totalPkQuery->countAllResults();

        $belumTteQuery = $this->pkModel->withPegawaiDetails()
            ->groupStart()
                ->where('pk.tte_status', 'unsigned')
                ->orWhere('pk.tte_status', 'draft')
                ->orWhere('pk.tte_status IS NULL')
                ->orWhere('pk.tte_status', '')
            ->groupEnd();
        if ($statusAsnId !== null) {
            $belumTteQuery->where('emails.status_asn_id', $statusAsnId);
        }
        if (!empty($targetUnitIds)) {
            $belumTteQuery->whereIn('emails.unit_kerja_id', $targetUnitIds);
        }
        $belumTteCount = $belumTteQuery->countAllResults();

        $pendingQuery = $this->pkModel->withPegawaiDetails()
            ->where('pk.tte_status', 'signed_pppk')
            ->where('pk.tte_pegawai_file IS NOT NULL')
            ->where('pk.tte_pegawai_file !=', '');
        if ($statusAsnId !== null) {
            $pendingQuery->where('emails.status_asn_id', $statusAsnId);
        }
        if (!empty($targetUnitIds)) {
            $pendingQuery->whereIn('emails.unit_kerja_id', $targetUnitIds);
        }
        $pendingCount = $pendingQuery->countAllResults();

        $completedTotalQuery = $this->pkModel->withPegawaiDetails()
            ->where('pk.tte_status', 'completed')
            ->where('pk.tte_bupati_file IS NOT NULL')
            ->where('pk.tte_bupati_file !=', '');
        if ($statusAsnId !== null) {
            $completedTotalQuery->where('emails.status_asn_id', $statusAsnId);
        }
        if (!empty($targetUnitIds)) {
            $completedTotalQuery->whereIn('emails.unit_kerja_id', $targetUnitIds);
        }
        $completedTotalCount = $completedTotalQuery->countAllResults();

        // Hanya tampilkan Unit Kerja Induk (Parent) pada dropdown filter
        $unitKerjaList = $this->unitKerjaModel
            ->groupStart()
                ->where('parent_id IS NULL')
                ->orWhere('parent_id', '')
                ->orWhere('parent_id', 0)
            ->groupEnd()
            ->orderBy('nama_unit_kerja', 'ASC')
            ->findAll();

        $totalCount = $pager->getTotal('default');

        $data = [
            'title'                 => $title,
            'items'                 => $items,
            'pager'                 => $pager,
            'total_count'           => $totalCount,
            'total_pk_count'        => $totalPkCount,
            'belum_tte_count'       => $belumTteCount,
            'search'                => $search,
            'unit_kerja_id'         => $unitKerjaId,
            'status_asn_id'         => $statusAsnId,
            'tab'                   => $tab,
            'base_route'            => $baseRoute,
            'unit_kerja_list'       => $unitKerjaList,
            'pending_count'         => $pendingCount,
            'completed_total_count' => $completedTotalCount,
        ];

        return view('email/tte_bupati', $data);
    }

    /**
     * Tanda tangani satu dokumen PK oleh Bupati
     */
    public function signSingle($id)
    {
        $nik = trim($this->request->getPost('nik') ?? '');
        $passphrase = $this->request->getPost('passphrase') ?? '';

        if (empty($nik)) {
            $nik = env('BSRE_BUPATI_NIK', '');
        }

        if (empty($nik) || empty($passphrase)) {
            return redirect()->back()->with('error', 'NIK dan Passphrase BSrE Bupati wajib diisi.');
        }

        $pk = $this->pkModel->withPegawaiDetails()->where('pk.id', $id)->first();
        if (!$pk) {
            return redirect()->back()->with('error', 'Data Perjanjian Kerja tidak ditemukan.');
        }

        if ($pk['tte_status'] === 'completed' && !empty($pk['tte_bupati_file'])) {
            return redirect()->back()->with('error', 'Dokumen ini telah selesai ditandatangani oleh Bupati.');
        }

        if (empty($pk['tte_pegawai_file'])) {
            return redirect()->back()->with('error', 'Dokumen ini belum ditandatangani oleh PPPK.');
        }

        $sourcePdfPath = WRITEPATH . 'uploads/signed_pk/' . $pk['tte_pegawai_file'];
        if (!file_exists($sourcePdfPath)) {
            return redirect()->back()->with('error', 'Berkas PDF bertandatangan PPPK tidak ditemukan di server.');
        }

        $bsreApi = new BsreApi();
        $verifyUrl = site_url('verifikasi/' . $pk['user']);

        $signResult = $bsreApi->signPdf($sourcePdfPath, $nik, $passphrase, [
            'tag_koordinat' => '${ttd_pengirim2}',
            'linkQR'        => $verifyUrl,
            'width'         => 110,
            'height'        => 110,
        ]);

        if (!$signResult['success']) {
            $errorMsg = $signResult['message'] ?? 'Tanda tangan elektronik gagal diproses.';
            helper('audit');
            log_audit('TTE_BUPATI_FAILED', 'Pk', $pk['id'], 'TTE Bupati Gagal: ' . $errorMsg . ' (' . ($pk['nip'] ?? $pk['user']) . ')');
            return redirect()->back()->with('error', 'Gagal TTE Bupati: ' . $errorMsg);
        }

        $signedDir = WRITEPATH . 'uploads/signed_pk/';
        if (!is_dir($signedDir)) {
            mkdir($signedDir, 0775, true);
        }

        $finalFilename = 'signed_final_' . ($pk['nip'] ?: $pk['user']) . '_' . date('Ymd_His') . '.pdf';
        file_put_contents($signedDir . $finalFilename, $signResult['pdf_content']);

        $this->pkModel->update($pk['id'], [
            'tte_status'      => 'completed',
            'tte_bupati_at'   => date('Y-m-d H:i:s'),
            'tte_bupati_file' => $finalFilename,
            'tte_bupati_ip'   => $this->request->getIPAddress(),
        ]);

        helper('audit');
        log_audit('TTE_BUPATI_SUCCESS', 'Pk', $pk['id'], 'TTE Bupati Sukses: ' . $pk['name'] . ' (' . ($pk['nip'] ?? $pk['user']) . ')');

        return redirect()->back()->with('success', 'Dokumen PK atas nama ' . esc($pk['name']) . ' berhasil ditandatangani oleh Bupati (Status Lengkap)!');
    }

    /**
     * Batch TTE Bupati melalui AJAX untuk dokumen terpilih
     * Request JSON: { nik: '...', passphrase: '...', pk_ids: [1, 2, 3] }
     */
    public function signBatch()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Permintaan tidak valid.'
            ]);
        }

        $json = $this->request->getJSON(true) ?? [];
        $nik = trim($json['nik'] ?? '');
        $passphrase = $json['passphrase'] ?? '';
        $pkIds = $json['pk_ids'] ?? [];

        if (empty($nik)) {
            $nik = env('BSRE_BUPATI_NIK', '');
        }

        if (empty($nik) || empty($passphrase)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'NIK dan Passphrase BSrE Bupati wajib diisi.'
            ]);
        }

        if (empty($pkIds) || !is_array($pkIds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak ada dokumen yang dipilih untuk ditandatangani.'
            ]);
        }

        $bsreApi = new BsreApi();
        $signedDir = WRITEPATH . 'uploads/signed_pk/';
        if (!is_dir($signedDir)) {
            mkdir($signedDir, 0775, true);
        }

        $successList = [];
        $failList = [];
        $authErrorOccurred = false;
        helper('audit');

        foreach ($pkIds as $id) {
            $pk = $this->pkModel->withPegawaiDetails()->where('pk.id', $id)->first();
            if (!$pk) {
                $failList[] = ['id' => $id, 'name' => 'ID #' . $id, 'reason' => 'Data tidak ditemukan'];
                continue;
            }

            if ($pk['tte_status'] === 'completed' && !empty($pk['tte_bupati_file'])) {
                $failList[] = ['id' => $id, 'name' => $pk['name'], 'reason' => 'Sudah ditandatangani sebelumnya'];
                continue;
            }

            if (empty($pk['tte_pegawai_file'])) {
                $failList[] = ['id' => $id, 'name' => $pk['name'], 'reason' => 'Belum ditandatangani PPPK'];
                continue;
            }

            $sourcePdfPath = $signedDir . $pk['tte_pegawai_file'];
            if (!file_exists($sourcePdfPath)) {
                $failList[] = ['id' => $id, 'name' => $pk['name'], 'reason' => 'Berkas PDF PPPK tidak ditemukan'];
                continue;
            }

            $verifyUrl = site_url('verifikasi/' . $pk['user']);
            $signResult = $bsreApi->signPdf($sourcePdfPath, $nik, $passphrase, [
                'tag_koordinat' => '${ttd_pengirim2}',
                'linkQR'        => $verifyUrl,
                'width'         => 110,
                'height'        => 110,
            ]);

            if (!$signResult['success']) {
                $errMsg = $signResult['message'] ?? 'Tanda tangan gagal.';
                $failList[] = ['id' => $id, 'name' => $pk['name'], 'reason' => $errMsg];

                log_audit('TTE_BUPATI_BATCH_FAILED', 'Pk', $pk['id'], 'Gagal Batch TTE Bupati: ' . $errMsg . ' (' . ($pk['nip'] ?? $pk['user']) . ')');

                // Jika error adalah autentikasi (passphrase salah / 401 / unauthorized / user locked)
                // Hentikan loop segera agar akun BSrE tidak terblokir!
                $lowerMsg = strtolower($errMsg);
                if (
                    ($signResult['code'] ?? 0) === 401 ||
                    ($signResult['code'] ?? 0) === 403 ||
                    str_contains($lowerMsg, 'passphrase') ||
                    str_contains($lowerMsg, 'password') ||
                    str_contains($lowerMsg, 'unauthorized') ||
                    str_contains($lowerMsg, 'terblokir') ||
                    str_contains($lowerMsg, 'pin')
                ) {
                    $authErrorOccurred = true;
                    break;
                }
                continue;
            }

            // Simpan signed final PDF
            $finalFilename = 'signed_final_' . ($pk['nip'] ?: $pk['user']) . '_' . date('Ymd_His') . '.pdf';
            file_put_contents($signedDir . $finalFilename, $signResult['pdf_content']);

            $this->pkModel->update($pk['id'], [
                'tte_status'      => 'completed',
                'tte_bupati_at'   => date('Y-m-d H:i:s'),
                'tte_bupati_file' => $finalFilename,
                'tte_bupati_ip'   => $this->request->getIPAddress(),
            ]);

            log_audit('TTE_BUPATI_BATCH_SUCCESS', 'Pk', $pk['id'], 'Sukses Batch TTE Bupati: ' . $pk['name'] . ' (' . ($pk['nip'] ?? $pk['user']) . ')');
            $successList[] = ['id' => $id, 'name' => $pk['name']];
        }

        return $this->response->setJSON([
            'success'            => count($successList) > 0,
            'auth_aborted'       => $authErrorOccurred,
            'success_count'      => count($successList),
            'fail_count'         => count($failList),
            'success_list'       => $successList,
            'fail_list'          => $failList,
            'message'            => count($successList) . ' dokumen berhasil ditandatangani.' .
                                    (count($failList) > 0 ? ' (' . count($failList) . ' gagal/dilewati' . ($authErrorOccurred ? ', proses dihentikan otomatis untuk mencegah pemblokiran akun BSrE' : '') . ')' : '')
        ]);
    }
}
