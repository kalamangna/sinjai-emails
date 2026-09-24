<?php

namespace App\Domains\Email\Controllers;

use App\Shared\BaseController;
use App\Domains\Email\Models\EmailModel;
use App\Domains\Email\Models\PkModel;
use App\Domains\Email\Services\EmailExportService;
use App\Shared\Libraries\BsreApi;
use Config\Services;
use Exception;

class PortalPkController extends BaseController
{
    protected $emailModel;
    protected $pkModel;
    protected $exportService;
    protected $bsreApi;

    public function __construct()
    {
        $this->emailModel    = new EmailModel();
        $this->pkModel       = new PkModel();
        $this->exportService = new EmailExportService();
        $this->bsreApi       = new BsreApi();
    }

    /**
     * Halaman Verifikasi Masuk (Portal Login PPPK)
     */
    public function index()
    {
        if (session()->get('portal_pppk_logged_in')) {
            return redirect()->to('portal-pk/dashboard');
        }

        $data = [
            'title' => 'Portal TTE PPPK'
        ];

        return view('auth/portal_pk_login', $data);
    }

    /**
     * Proses Verifikasi Identitas PPPK (NIP, NIK, Tanggal Lahir)
     */
    public function auth()
    {
        $throttler = Services::throttler();
        $clientIp  = $this->request->getIPAddress();
        $throttleKey = 'portal_pk_throttle_' . md5($clientIp);

        // Batasi 5 kali percobaan gagal per 15 menit
        if ($throttler->check($throttleKey, 5, 900) === false) {
            return redirect()->back()->withInput()->with('error', 'Terlalu banyak percobaan masuk yang gagal. Silakan tunggu 15 menit lagi.');
        }

        $rules = [
            'nip'           => 'required|numeric|exact_length[18]',
            'nik'           => 'required|numeric|exact_length[16]',
            'tanggal_lahir' => 'required|valid_date[Y-m-d]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validasi gagal: ' . implode(', ', $this->validator->getErrors()));
        }

        $nip          = trim($this->request->getPost('nip') ?? '');
        $nik          = trim($this->request->getPost('nik') ?? '');
        $tanggalLahir = trim($this->request->getPost('tanggal_lahir') ?? '');

        // Query data pegawai
        $email = $this->emailModel->withDetails()
            ->where('emails.nip', $nip)
            ->where('emails.nik', $nik)
            ->where('emails.tanggal_lahir', $tanggalLahir)
            ->whereIn('emails.status_asn_id', [2, 3]) // PPPK (2) atau PPPK Paruh Waktu (3)
            ->first();

        if (!$email) {
            return redirect()->back()->withInput()->with('error', 'Identitas kepegawaian tidak ditemukan atau Anda bukan berstatus PPPK aktif di Kabupaten Sinjai.');
        }

        // Pastikan akun memiliki data Perjanjian Kerja (PK)
        $pk = $this->pkModel->where('email', $email['email'])->first();
        if (!$pk) {
            return redirect()->back()->withInput()->with('error', 'Data Perjanjian Kerja (PK) untuk akun Anda belum tercatat di sistem. Silakan hubungi BKPSDMA.');
        }

        // Set session khusus portal PPPK
        session()->set([
            'portal_pppk_logged_in' => true,
            'pppk_email_id'         => $email['id'],
            'pppk_email'            => $email['email'],
            'pppk_user'             => $email['user'],
            'pppk_nip'              => $email['nip'],
            'pppk_nik'              => $email['nik'],
            'pppk_name'             => $email['name'],
            'pppk_status_asn_id'    => $email['status_asn_id'],
        ]);

        log_audit('PORTAL_LOGIN', 'Email', $email['id'], 'Login Portal TTE PK oleh PPPK: ' . $email['name'] . ' (' . $email['nip'] . ')');

        return redirect()->to('portal-pk/dashboard')->with('success', 'Selamat datang, ' . $email['name']);
    }

    /**
     * Dashboard Portal PPPK (Informasi Kontrak & Preview PK)
     */
    public function dashboard()
    {
        $this->ensurePortalAuth();

        $emailId = session()->get('pppk_email_id');
        $email   = $this->emailModel->withDetails()->find($emailId);

        if (!$email) {
            $this->logout();
            return redirect()->to('portal-pk')->with('error', 'Sesi Anda telah kedaluwarsa. Silakan masuk kembali.');
        }

        $pk = $this->pkModel->where('email', $email['email'])->first();

        $data = [
            'title' => 'Portal TTE PPPK',
            'email' => $email,
            'pk'    => $pk,
        ];

        return view('email/portal_pk_dashboard', $data);
    }

    /**
     * Preview Berkas PDF PK (Streaming ke Browser)
     */
    public function previewPdf()
    {
        $this->ensurePortalAuth();

        $emailId = session()->get('pppk_email_id');
        $email   = $this->emailModel->withDetails()->find($emailId);
        $pk      = $this->pkModel->where('email', $email['email'])->first();

        // 1. Jika sudah bertandatangan dan filenya ada, tampilkan berkas signed
        if ($pk && $pk['tte_status'] === 'signed_pppk' && !empty($pk['tte_pegawai_file'])) {
            $signedPath = WRITEPATH . 'uploads/signed_pk/' . $pk['tte_pegawai_file'];
            if (file_exists($signedPath)) {
                return $this->response
                    ->setHeader('Content-Type', 'application/pdf')
                    ->setHeader('Content-Disposition', 'inline; filename="' . $pk['tte_pegawai_file'] . '"')
                    ->setBody(file_get_contents($signedPath));
            }
        }

        // 2. Jika belum bertandatangan, buat draf PDF secara instan
        try {
            $pdfResult = $this->exportService->generatePerjanjianKerjaPdf($email['user']);
            $pdfOutput = $pdfResult['dompdf']->output();

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="' . $pdfResult['filename'] . '"')
                ->setBody($pdfOutput);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setBody('Gagal memuat pratinjau dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Proses Eksekusi TTE via API BSrE
     */
    public function sign()
    {
        $this->ensurePortalAuth();

        $passphrase = $this->request->getPost('passphrase');
        if (empty($passphrase)) {
            return redirect()->back()->with('error', 'Passphrase BSrE wajib diisi.');
        }

        $emailId = session()->get('pppk_email_id');
        $email   = $this->emailModel->withDetails()->find($emailId);
        $pk      = $this->pkModel->where('email', $email['email'])->first();

        if (!$pk) {
            return redirect()->back()->with('error', 'Data PK tidak ditemukan.');
        }

        // Cek jika sudah pernah ditandatangani
        if ($pk['tte_status'] === 'signed_pppk' && !empty($pk['tte_pegawai_file'])) {
            return redirect()->back()->with('error', 'Dokumen Perjanjian Kerja ini sudah ditandatangani sebelumnya.');
        }

        // 1. Generate draf PDF ke temporary file
        $tempDir = WRITEPATH . 'uploads/temp/';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $pdfResult = $this->exportService->generatePerjanjianKerjaPdf($email['user']);
        $tempPdfPath = $tempDir . 'draft_' . $email['user'] . '_' . time() . '.pdf';
        file_put_contents($tempPdfPath, $pdfResult['dompdf']->output());

        // 2. Hubungi API BSrE untuk TTE
        $verifyUrl = site_url('verifikasi/' . $email['user']);
        $signResult = $this->bsreApi->signPdf($tempPdfPath, $email['nik'], $passphrase, [
            'tag_koordinat' => '${ttd_pengirim1}',
            'linkQR'        => $verifyUrl,
            'width'         => 110,
            'height'        => 110,
        ]);

        // Hapus draf temporary
        if (file_exists($tempPdfPath)) {
            unlink($tempPdfPath);
        }

        if (!$signResult['success']) {
            $errorMsg = $signResult['message'] ?? 'Tanda tangan elektronik gagal diproses.';
            log_audit('TTE_PK_FAILED', 'Pk', $pk['id'], 'TTE PK Gagal: ' . $errorMsg . ' (' . $email['nip'] . ')');
            return redirect()->back()->with('error', 'Gagal TTE: ' . $errorMsg);
        }

        // 3. Simpan signed PDF di storage aman
        $signedDir = WRITEPATH . 'uploads/signed_pk/';
        if (!is_dir($signedDir)) {
            mkdir($signedDir, 0775, true);
        }

        $signedFilename = 'signed_pppk_' . $email['nip'] . '_' . date('Ymd_His') . '.pdf';
        file_put_contents($signedDir . $signedFilename, $signResult['pdf_content']);

        // 4. Update data tabel pk
        $this->pkModel->update($pk['id'], [
            'tte_status'       => 'signed_pppk',
            'tte_pegawai_at'   => date('Y-m-d H:i:s'),
            'tte_pegawai_file' => $signedFilename,
            'tte_pegawai_ip'   => $this->request->getIPAddress(),
        ]);

        log_audit('TTE_PK_SUCCESS', 'Pk', $pk['id'], 'TTE PK Sukses oleh PPPK: ' . $email['name'] . ' (' . $email['nip'] . ')');

        return redirect()->to('portal-pk/dashboard')->with('success', 'Dokumen Perjanjian Kerja berhasil ditandatangani secara elektronik (TTE)!');
    }

    /**
     * Unduh Berkas PK (Signed / Draft)
     */
    public function download()
    {
        $this->ensurePortalAuth();

        $emailId = session()->get('pppk_email_id');
        $email   = $this->emailModel->withDetails()->find($emailId);
        $pk      = $this->pkModel->where('email', $email['email'])->first();

        if ($pk && $pk['tte_status'] === 'signed_pppk' && !empty($pk['tte_pegawai_file'])) {
            $signedPath = WRITEPATH . 'uploads/signed_pk/' . $pk['tte_pegawai_file'];
            if (file_exists($signedPath)) {
                $downloadName = 'Perjanjian_Kerja_' . url_title($email['name'], '_', true) . '_' . $email['nip'] . '_SIGNED.pdf';
                return $this->response->download($signedPath, null)->setFileName($downloadName);
            }
        }

        // Fallback jika belum bertandatangan, unduh draft
        $pdfResult = $this->exportService->generatePerjanjianKerjaPdf($email['user']);
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $pdfResult['filename'] . '"')
            ->setBody($pdfResult['dompdf']->output());
    }

    /**
     * Keluar dari Portal PPPK
     */
    public function logout()
    {
        session()->remove([
            'portal_pppk_logged_in',
            'pppk_email_id',
            'pppk_email',
            'pppk_user',
            'pppk_nip',
            'pppk_nik',
            'pppk_name',
            'pppk_status_asn_id',
        ]);

        return redirect()->to('portal-pk')->with('success', 'Anda telah keluar dari Portal PPPK.');
    }

    /**
     * Helper proteksi akses portal PPPK
     */
    protected function ensurePortalAuth()
    {
        if (!session()->get('portal_pppk_logged_in')) {
            header('Location: ' . site_url('portal-pk'));
            exit;
        }
    }
}
