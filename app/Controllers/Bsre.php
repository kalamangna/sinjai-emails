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
                    $pesan = 'Status: ' . (is_string($statusUser) ? $statusUser : json_encode($statusUser));
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
