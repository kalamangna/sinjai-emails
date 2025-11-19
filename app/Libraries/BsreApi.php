<?php

namespace App\Libraries;

use Config\Services;
use Exception;

class BsreApi
{
    private string $base_url;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->base_url = getenv('BSRE_BASE_URL');
        $this->username = getenv('BSRE_USERNAME');
        $this->password = getenv('BSRE_PASSWORD');
    }

    public function checkStatusByEmail(string $email): array
    {
        try {
            $client = Services::curlrequest([
                'baseURI' => $this->base_url,
                'timeout' => 30,
                'http_errors' => false,
            ]);

            $response = $client->post('/api/v2/user/check/status', [
                'auth' => [$this->username, $this->password],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'email' => $email,
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return [
                    'success' => false,
                    'message' => 'HTTP Error: ' . $response->getStatusCode() . ' - ' . $response->getReasonPhrase(),
                    'data' => null,
                ];
            }

            $data = json_decode($response->getBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'message' => 'JSON parse error: ' . json_last_error_msg(),
                    'data' => null,
                ];
            }

            return [
                'success' => true,
                'data' => $data,
                'message' => 'Successfully checked user status.',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }
}
