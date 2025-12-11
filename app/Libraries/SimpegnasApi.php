<?php

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

class SimpegnasApi
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        // Load configuration from .env
        $this->baseUrl = getenv('SIMPEGNAS_BASE_URL');
        $this->apiKey  = getenv('SIMPEGNAS_API_KEY');

        // Initialize CURLRequest service
        // Pass false as the 4th argument to ensure we get a fresh instance with our options
        // specifically the base_uri, instead of a potentially already instantiated shared instance.
        $this->client = Services::curlrequest([
            'base_uri' => $this->baseUrl,
            'timeout'  => 30,
            'verify'   => false, // Set to true in production if SSL is valid
            'headers'  => [
                'User-Agent' => 'SinjaiKab-Emails/1.0',
            ]
        ], null, null, false);
    }

    /**
     * Get Data Utama ASN
     * Endpoint: /absensi/api/get/asn/data-utama
     * 
     * @param string $nip NIP Pegawai
     * @return array
     */
    public function getDataUtamaAsn($nip)
    {
        try {
            // Debugging: Log the loaded Base URL
            log_message('info', 'SimpegnasApi Base URL: ' . $this->baseUrl);

            // Construct full URL manually to avoid base_uri merging issues
            $url = rtrim($this->baseUrl, '/') . '/absensi/api/get/asn/data-utama';

            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'presensi-key' => $this->apiKey, 
                    'Accept'    => 'application/json',
                ],
                'query' => ['nip' => $nip],
            ]);


            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);

            if ($statusCode >= 200 && $statusCode < 300) {
                return [
                    'success' => true,
                    'data'    => $body,
                    'code'    => $statusCode
                ];
            }

            return [
                'success' => false,
                'message' => 'Remote API returned HTTP ' . $statusCode,
                'data'    => $body,
                'code'    => $statusCode
            ];
        } catch (\Exception $e) {
            log_message('error', '[SimpegnasApi] Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code'    => 500
            ];
        }
    }
}
