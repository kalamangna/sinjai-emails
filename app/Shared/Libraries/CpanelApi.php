<?php

namespace App\Shared\Libraries;

use Config\Services;
use Exception;

class CpanelApi
{
    private $config;

    public function __construct()
    {
        $this->config = config('Cpanel');
    }

    private function make_request(string $module, string $function, string $method = 'GET', array $parameters = [])
    {
        // 1. Get raw values
        $rawHost = $this->config->cpanel_host ?? '';
        $rawPort = (string)($this->config->cpanel_port ?? '');

        // 2. Initial cleaning
        $host = trim(str_replace(['"', "'"], '', $rawHost));
        $port = trim(str_replace(['"', "'"], '', $rawPort));

        // 3. Handle protocol and path in host
        if (preg_match('/^https?:\/\//', $host)) {
            $parsedUrl = parse_url($host);
            $host = $parsedUrl['host'] ?? '';
            // If port was in the host URL, use it if our config port is empty
            if (empty($port) && !empty($parsedUrl['port'])) {
                $port = (string)$parsedUrl['port'];
            }
        }
        
        // Remove any path part if present
        $host = explode('/', $host)[0];
        
        // Handle port already in host (e.g. "server.com:2083")
        if (strpos($host, ':') !== false) {
            list($h, $p) = explode(':', $host, 2);
            $host = $h;
            if (empty($port)) {
                $port = $p;
            }
        }

        // 4. Final validation of essential components
        if (empty($host)) {
            throw new Exception("Konfigurasi cPanel tidak lengkap: Host kosong.");
        }
        
        // Default cPanel port if still empty
        if (empty($port)) {
            $port = '2083';
        }

        $url = "https://{$host}:{$port}/execute/{$module}/{$function}";

        // 5. Initialize client - Use fresh instance to avoid shared config issues
        $client = Services::curlrequest([
            'timeout' => 1800,
            'http_errors' => false,
            'verify' => false, // cPanel often uses self-signed or internal CA
        ], null, null, false);

        $headers = [
            'Authorization' => 'cpanel ' . $this->config->cpanel_username . ':' . $this->config->api_token,
            'User-Agent' => 'CodeIgniter-cPanel-API/2.0',
            'Accept' => 'application/json',
        ];

        $options = [
            'headers' => $headers,
        ];

        try {
            if (strtoupper($method) === 'POST') {
                $options['form_params'] = $parameters;
                $response = $client->post($url, $options);
            } else {
                if (!empty($parameters)) {
                    $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($parameters);
                }
                $response = $client->get($url, $options);
            }

            if ($response->getStatusCode() !== 200) {
                throw new Exception('HTTP Error: ' . $response->getStatusCode() . ' - ' . $response->getReasonPhrase() . ' - ' . $response->getBody());
            }

            $data = json_decode($response->getBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON parse error: ' . json_last_error_msg() . ' | Body: ' . substr($response->getBody(), 0, 500));
            }

            return $data;

        } catch (\Throwable $e) {
            log_message('error', "CpanelApi Request Failed. URL: [$url], Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function get_email_accounts_detailed()
    {
        log_message('debug', 'CpanelApi: Starting get_email_accounts_detailed');
        try {
            $response = $this->make_request('Email', 'list_pops_with_disk');
            log_message('debug', 'CpanelApi: Finished get_email_accounts_detailed successfully');
            return $response['data'] ?? [];
        } catch (\Throwable $e) {
            log_message('error', 'Failed to get email accounts: ' . $e->getMessage());
            throw new Exception('Failed to retrieve email list: ' . $e->getMessage());
        }
    }

    public function get_email_account_detail($email)
    {
        try {
            $all_accounts = $this->get_email_accounts_detailed();

            foreach ($all_accounts as $account) {
                if (isset($account['email']) && $account['email'] === $email) {
                    return $account;
                }
            }

            throw new Exception('Email account tidak ditemukan: ' . $email);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to get email detail: ' . $e->getMessage());
            throw new Exception('Failed to retrieve email details: ' . $e->getMessage());
        }
    }

    public function test_connection()
    {
        require_once APPPATH . 'Shared/Helpers/TanggalHelper.php';
        try {
            $emails = $this->get_email_accounts_detailed();

            return [
                'success' => true,
                'message' => 'cPanel API Email Module connection successful!',
                'data' => [
                    'total_emails' => count($emails),
                    'api_module' => 'Email',
                    'timestamp' => untukDatabase('now')
                ]
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'cPanel API connection failed: ' . $e->getMessage(),
                'timestamp' => untukDatabase('now')
            ];
        }
    }

    public function create_email_account($email, $password, $quota = 250)
    {
        try {
            list($user, $domain) = explode('@', $email);

            $parameters = [
                'email' => $user,
                'domain' => $domain,
                'password' => $password,
                'quota' => $quota,
            ];

            $response = $this->make_request('Email', 'add_pop', 'POST', $parameters);

            if (isset($response['status']) && $response['status'] == 1) {
                return $response;
            } else {
                $error_message = $response['errors'][0] ?? 'Unknown error during email account creation.';
                throw new Exception($error_message);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create email account: ' . $e->getMessage());
            throw new Exception('Failed to create email account: ' . $e->getMessage());
        }
    }

    public function change_password($email, $new_password)
    {
        try {
            list($user, $domain) = explode('@', $email);

            $parameters = [
                'email' => $user,
                'password' => $new_password,
                'domain' => $domain,
            ];

            $response = $this->make_request('Email', 'passwd_pop', 'POST', $parameters);

            if (isset($response['status']) && $response['status'] == 1) {
                return $response;
            } else {
                $error_message = $response['errors'][0] ?? 'Unknown error during password change.';
                throw new Exception($error_message);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to change password: ' . $e->getMessage());
            throw new Exception('Failed to change password: ' . $e->getMessage());
        }
    }

    public function delete_email_account($email)
    {
        try {
            list($user, $domain) = explode('@', $email);

            $parameters = [
                'email' => $user,
                'domain' => $domain,
            ];

            $response = $this->make_request('Email', 'delete_pop', 'POST', $parameters);

            if (isset($response['status']) && $response['status'] == 0) {

                $error_message = $response['errors'][0] ?? 'Unknown error during email deletion.';

                throw new Exception($error_message);
            }

            return $response;
        } catch (\Throwable $e) {
            log_message('error', 'Failed to delete email account: ' . $e->getMessage());
            throw new Exception('Failed to delete email account: ' . $e->getMessage());
        }
    }

    public function suspend_email_login($email)
    {
        try {
            $parameters = ['email' => $email];
            $response = $this->make_request('Email', 'suspend_login', 'POST', $parameters);

            if (isset($response['status']) && $response['status'] == 1) {
                return $response;
            } else {
                $error_message = $response['errors'][0] ?? 'Unknown error during email login suspension.';
                throw new Exception($error_message);
            }
        } catch (\Throwable $e) {
            log_message('error', "Failed to suspend email login for $email: " . $e->getMessage());
            throw new Exception("Failed to suspend email login: " . $e->getMessage());
        }
    }

    public function unsuspend_email_login($email)
    {
        try {
            $parameters = ['email' => $email];
            $response = $this->make_request('Email', 'unsuspend_login', 'POST', $parameters);

            if (isset($response['status']) && $response['status'] == 1) {
                return $response;
            } else {
                $error_message = $response['errors'][0] ?? 'Unknown error during email login unsuspension.';
                throw new Exception($error_message);
            }
        } catch (\Throwable $e) {
            log_message('error', "Failed to unsuspend email login for $email: " . $e->getMessage());
            throw new Exception("Failed to unsuspend email login: " . $e->getMessage());
        }
    }
}
