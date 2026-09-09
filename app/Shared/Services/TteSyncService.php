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
            $hasCustomEmailBsre = !empty($email['email_bsre']);
            $targetEmail = $hasCustomEmailBsre ? $email['email_bsre'] : $email['email'];

            $result = $bsreApi->checkStatus($targetEmail, 'email');
            if ($result['success']) {
                $responseBody = $result['data'];
                // BSrE API terkadang membungkus datanya dalam 'data', kita amankan dengan fallback
                $statusFromBsre = $responseBody['status'] ?? ($responseBody['data']['status'] ?? 'UNKNOWN');
                
                // Jika akun valid via NIK dan status email di BSrE bukan ISSUE/EXPIRED, pertahankan status dari NIK
                if (!$hasCustomEmailBsre && !in_array($statusFromBsre, ['ISSUE', 'EXPIRED']) && ($email['tte_source'] ?? '') === 'nik' && in_array($email['bsre_status'] ?? '', ['ISSUE', 'EXPIRED'])) {
                    // Biarkan status tetap (ISSUE / EXPIRED via NIK)
                } else {
                    $newTteSource = in_array($statusFromBsre, ['ISSUE', 'EXPIRED'])
                        ? ($hasCustomEmailBsre ? 'email_bsre' : 'email')
                        : ($email['tte_source'] ?? 'email');

                    $emailModel->update($email['id'], [
                        'bsre_status' => $statusFromBsre,
                        'tte_source'  => $newTteSource,
                    ]);
                }
            } elseif (isset($result['code']) && ($result['code'] === 429 || $result['code'] === 503)) {
                // Backoff adaptif jika BSrE mengalami beban tinggi
                sleep(2);
            }

            // Jeda mikro 80ms (~3 request/detik) untuk mencegah rate-limiting & menjaga kestabilan server BSrE
            usleep(80000);
        }
    }
}
