<?php

namespace App\Shared\Services;

use App\Shared\Libraries\CpanelApi;
use App\Shared\Libraries\BsreApi;
use App\Shared\Libraries\PegawaiApi;

class SystemHealthService
{
    public function checkAll()
    {
        return [
            'cpanel'  => $this->checkCpanel(),
            'bsre'    => $this->checkBsre(),
            'pegawai' => $this->checkPegawai(),
        ];
    }

    private function checkCpanel()
    {
        $cpanel = new CpanelApi();
        $result = $cpanel->test_connection();
        return [
            'status' => $result['success'] ? 'UP' : 'DOWN',
            'message' => $result['message'],
        ];
    }

    private function checkBsre()
    {
        $bsre = new BsreApi();
        // Use a lightweight check, like checking status for a known non-existent email
        $result = $bsre->checkStatus('health-check@sinjaikab.go.id', 'email');
        
        // Even if email not found, if success is true, it means API is UP
        return [
            'status' => $result['success'] ? 'UP' : 'DOWN',
            'message' => $result['message'] ?? ($result['success'] ? 'Connected to BSrE API' : 'Failed to connect to BSrE API'),
        ];
    }

    private function checkPegawai()
    {
        $pegawai = new PegawaiApi();
        // Basic connectivity check to apps.sinjaikab.go.id
        $result = $pegawai->getPegawaiData('000000000000000000'); // Dummy NIP
        
        return [
            'status' => $result['success'] ? 'UP' : 'DOWN',
            'message' => $result['message'] ?? ($result['success'] ? 'Connected to Pegawai API' : 'Failed to connect to Pegawai API'),
        ];
    }
}
