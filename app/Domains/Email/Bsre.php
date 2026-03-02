<?php

namespace App\Domains\Email;

use App\Shared\BaseController;
use App\Shared\Libraries\BsreApi;
use App\Domains\Auth\UserModel;

class Bsre extends BaseController
{
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
        $emailModel = new \App\Domains\Email\EmailModel(); // Use EmailModel

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
        // Check if user is super_admin
        if (session()->get('role') !== 'super_admin') {
            return redirect()->to('email')->with('error', 'Unauthorized access');
        }

        set_time_limit(0);
        $emailModel = new \App\Domains\Email\EmailModel();
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
}
