<?php

namespace App\Domains\Email\Controllers;

use App\Shared\BaseController;
use App\Shared\Libraries\BsreApi;
use App\Domains\Auth\Models\UserModel;

class BsreController extends BaseController
{
    /**
     * Halaman Publik Verifikasi PDF
     * GET /verifikasi-pdf
     */
    public function publicVerify()
    {
        return view('email/verify_pdf', [
            'title' => 'Verifikasi PDF',
            'isPublic' => true,
        ]);
    }

    public function checkStatus()
    {
        // Validasi input sederhana
        $email = $this->request->getVar('email');

        if (!$email) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Email wajib diisi'
            ]);
        }

        $bsreApi = new BsreApi();

        // Panggil fungsi checkStatus (yang sudah digabung)
        $result = $bsreApi->checkStatus($email, 'email');

        if ($result['success']) {
            // Ambil status string dari response BSrE (misal: "ISSUE", "EXPIRED")
            // Struktur response biasanya langsung string atau object, sesuaikan dengan hasil dump real-nya
            // Mengambil status dari key yang tepat di dalam $result['data']
            // Seringkali API BSrE mengembalikan status di dalam key 'status' atau 'message'
            $responseBody = $result['data'];
            $statusUser = $responseBody['status'] ?? ($responseBody['data']['status'] ?? 'UNKNOWN');

            // Logika bisnis aplikasi Anda berdasarkan status 
            $pesan = '';
            switch ($statusUser) {
                case 'ISSUE':
                    $pesan = 'Sertifikat Aktif / Siap TTE';
                    break;
                case 'EXPIRED':
                    $pesan = 'Masa Berlaku Habis';
                    break;
                case 'RENEW':
                    $pesan = 'Proses Pembaruan';
                    break;
                case 'WAITING_FOR_VERIFICATION':
                    $pesan = 'Menunggu Verifikasi';
                    break;
                case 'NEW':
                    $pesan = 'Belum Aktivasi';
                    break;
                case 'NO_CERTIFICATE':
                    $pesan = 'Belum Ada Sertifikat';
                    break;
                case 'NOT_REGISTERED':
                    $pesan = 'Pengguna Tidak Terdaftar';
                    break;
                case 'SUSPEND':
                    $pesan = 'Akun Ditangguhkan';
                    break;
                case 'REVOKE':
                    $pesan = 'Sertifikat Dicabut';
                    break;
                default:
                    $pesan = 'Status Tidak Dikenali: ' . (is_string($statusUser) ? $statusUser : json_encode($statusUser));
            }

            return $this->response->setJSON([
                'status' => 'success',
                'bsre_status' => $statusUser,
                'keterangan' => $pesan
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghubungi server BSrE: ' . $result['message']
            ]);
        }
    }

    public function syncStatus()
    {
        $emailAddress = $this->request->getVar('email');

        if (!$emailAddress) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Email address required'
            ]);
        }

        $bsreApi = new BsreApi();
        $emailModel = new \App\Domains\Email\Models\EmailModel(); // Use EmailModel

        // Call the BSrE API
        $result = $bsreApi->checkStatus($emailAddress, 'email');

        if ($result['success']) {
            $responseBody = $result['data'];
            $statusFromBsre = $responseBody['status'] ?? ($responseBody['data']['status'] ?? 'UNKNOWN');

            // Find the email in the database
            $emailRecord = $emailModel->where('email', $emailAddress)->first();

            if ($emailRecord) {
                // Update the bsre_status in the emails table
                $emailModel->update($emailRecord['id'], ['bsre_status' => $statusFromBsre]);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Status synced successfully',
                    'bsre_status' => $statusFromBsre
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Email not found in local database, cannot sync status.'
                ]);
            }
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to fetch status from BSrE: ' . $result['message']
            ]);
        }
    }

    public function syncAllStatus()
    {
        // Check if user is super_admin or admin
        $role = session()->get('role');
        if ($role !== 'super_admin' && $role !== 'admin') {
            return redirect()->to('email')->with('error', 'Unauthorized access');
        }

        set_time_limit(0);
        $emailModel = new \App\Domains\Email\Models\EmailModel();
        $bsreApi = new BsreApi();

        $search = $this->request->getGet('search');
        $bsre_status = $this->request->getGet('bsre_status');

        $builder = $emailModel->select('id, email');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('email', $search)
                ->orLike('name', $search)
                ->orLike('nik', $search)
                ->orLike('nip', $search)
                ->groupEnd();
        }

        if (!empty($bsre_status)) {
            if ($bsre_status === 'not_synced') {
                $builder->groupStart()->where('bsre_status', null)->orWhere('bsre_status', '')->groupEnd();
            } else {
                $builder->where('bsre_status', $bsre_status);
            }
        }

        $emails = $builder->findAll();

        $successCount = 0;
        $failCount = 0;

        foreach ($emails as $email) {
            $result = $bsreApi->checkStatus($email['email'], 'email');
            if ($result['success']) {
                $responseBody = $result['data'];
                $statusFromBsre = $responseBody['status'] ?? ($responseBody['data']['status'] ?? 'UNKNOWN');
                $emailModel->update($email['id'], ['bsre_status' => $statusFromBsre]);
                $successCount++;
            } else {
                $failCount++;
            }
        }

        return redirect()->to('email')->with('success', "Berhasil menyinkronkan status TTE untuk $successCount akun. Gagal: $failCount.");
    }

    /**
     * Mendaftarkan User Baru di BSrE (API v2)
     * POST /bsre/register
     */
    public function registerUser()
    {
        $nik = $this->request->getVar('nik');
        $nama = $this->request->getVar('nama');
        $email = $this->request->getVar('email');

        if (empty($nik) || empty($nama) || empty($email)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'NIK, Nama, dan Email wajib diisi'
            ])->setStatusCode(400);
        }

        $bsreApi = new BsreApi();
        $result = $bsreApi->registerUser($nama, $email, $nik);

        if ($result['success']) {
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $result['data']
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $result['message']
            ])->setStatusCode($result['code'] ?: 500);
        }
    }

    /**
     * Verifikasi TTE File PDF (API v2)
     * POST /bsre/verify
     */
    public function verifyPdf()
    {
        $fileBase64 = '';
        $password = $this->request->getVar('password');

        // Dukung upload file biner (multipart) atau string base64 langsung
        $uploadedFile = $this->request->getFile('file');
        if ($uploadedFile && $uploadedFile->isValid() && !$uploadedFile->hasMoved()) {
            if ($uploadedFile->getMimeType() !== 'application/pdf') {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'File harus berupa dokumen PDF'
                ])->setStatusCode(400);
            }
            $fileData = file_get_contents($uploadedFile->getTempName());
            $fileBase64 = base64_encode($fileData);
        } else {
            $fileBase64 = $this->request->getVar('file');
        }

        if (empty($fileBase64)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File PDF (dalam format file upload atau string Base64) wajib disertakan'
            ])->setStatusCode(400);
        }

        $bsreApi = new BsreApi();
        $result = $bsreApi->verifyPdf($fileBase64, $password);

        if ($result['success']) {
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $result['data']
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $result['message']
            ])->setStatusCode($result['code'] ?: 500);
        }
    }
}
