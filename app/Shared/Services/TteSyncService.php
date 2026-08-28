<?php

namespace App\Shared\Services;

use App\Domains\Email\Models\EmailModel;
use App\Shared\Libraries\BsreApi;

class TteSyncService
{
    /**
     * Memproses batch sinkronisasi status TTE ke API BSrE
     * 
     * @param array $emailList Array asosiatif yang berisi ['id' => ..., 'email' => ...]
     */
    public function processBatch(array $emailList)
    {
        $emailModel = new EmailModel();
        $bsreApi = new BsreApi();
        
        foreach ($emailList as $email) {
            $result = $bsreApi->checkStatus($email['email'], 'email');
            if ($result['success']) {
                $responseBody = $result['data'];
                // BSrE API terkadang membungkus datanya dalam 'data', kita amankan dengan fallback
                $statusFromBsre = $responseBody['status'] ?? ($responseBody['data']['status'] ?? 'UNKNOWN');
                
                // Update ke database
                $emailModel->update($email['id'], ['bsre_status' => $statusFromBsre]);
            } elseif (isset($result['code']) && ($result['code'] === 429 || $result['code'] === 503)) {
                // Backoff adaptif jika BSrE mengalami beban tinggi
                sleep(2);
            }

            // Jeda mikro 80ms (~3 request/detik) untuk mencegah rate-limiting & menjaga kestabilan server BSrE
            usleep(80000);
        }
    }
}
