<?php

namespace App\Domains\Website;

use App\Domains\Website\WebDesaKelurahanModel;
use Config\Services;

class WebsiteService
{
    protected $webDesaModel;

    public function __construct()
    {
        $this->webDesaModel = new WebDesaKelurahanModel();
        require_once APPPATH . 'Shared/Helpers/TanggalHelper.php';
    }

    public function determineExpirationDate($desaKelurahan, $domain, $manualDate)
    {
        // Rule for Kelurahan: Expire in 2/1/2027
        if (stripos($desaKelurahan, 'KELURAHAN') !== false) {
            return '2027-02-01';
        }

        // Rule for Desa: Check PANDI RDAP
        if (!empty($domain)) {
            $cleanDomain = preg_replace('#^https?://#', '', $domain);
            $cleanDomain = rtrim($cleanDomain, '/');

            $fetchedDate = $this->fetchPandiExpiration($cleanDomain);
            if ($fetchedDate) {
                return $fetchedDate;
            }
        }

        return $manualDate ?: null;
    }

    public function fetchPandiExpiration($domain)
    {
        try {
            $client = Services::curlrequest();
            $response = $client->request('GET', "https://rdap.pandi.id/rdap/domain/{$domain}", [
                'timeout' => 5,
                'http_errors' => false
            ]);

            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody(), true);
                if (isset($body['events']) && is_array($body['events'])) {
                    foreach ($body['events'] as $event) {
                        if (isset($event['eventAction']) && $event['eventAction'] === 'expiration') {
                            if (isset($event['eventDate'])) {
                                return formatIsiInput($event['eventDate']);
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'PANDI RDAP Error: ' . $e->getMessage());
        }

        return null;
    }

    public function calculateDaysRemaining($date)
    {
        if (!$date) return null;
        
        $end = new \DateTime($date);
        $now = new \DateTime();
        $diff = $now->diff($end);
        return (int)$diff->format('%r%a');
    }
}
