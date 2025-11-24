<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\BsreApi;

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

        // Panggil fungsi checkStatus via Email
        $result = $bsreApi->checkStatus($email, 'email');

        if ($result['success']) {
            // Ambil status string dari response BSrE (misal: "ISSUE", "EXPIRED")
            // Struktur response biasanya langsung string atau object, sesuaikan dengan hasil dump real-nya
            // Berdasarkan dokumen, ini mengembalikan JSON status.

            $statusUser = $result['data'];

            // Logika bisnis aplikasi Anda berdasarkan status 
            $pesan = '';
            switch ($statusUser) { // Asumsi response langsung string status, atau sesuaikan key-nya (misal $result['data']['status'])
                case 'ISSUE':
                    $pesan = 'User Aktif. Siap Tanda Tangan.';
                    break;
                case 'EXPIRED':
                    $pesan = 'Sertifikat kadaluarsa. Silakan perbarui.';
                    break;
                case 'NOT_REGISTERED':
                    $pesan = 'User belum terdaftar di BSrE.';
                    break;
                case 'REVOKE':
                    $pesan = 'Sertifikat telah dicabut.';
                    break;
                default:
                    $pesan = 'Status: ' . json_encode($statusUser);
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
}
