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
        $host = trim($this->config->cpanel_host ?? '');
        $port = trim((string)($this->config->cpanel_port ?? ''));
        $user = trim($this->config->cpanel_username ?? '');

        // Detailed logging for debugging
        log_message('debug', "CpanelApi Config - Host: [$host], Port: [$port], User: [$user]");

        // Aggressively clean: Remove quotes if they exist
        $host = str_replace(['"', "'"], '', $host);
        $port = str_replace(['"', "'"], '', $port);

        if (empty($host) || empty($port)) {
            throw new Exception("Konfigurasi cPanel tidak lengkap di .env (Host: '$host', Port: '$port'). Pastikan kunci cpanel.host dan cpanel.port sudah benar.");
        }

        // Deep clean host: remove protocol and trailing slashes
        $host = str_replace(['https://', 'http://'], '', $host);
        $host = rtrim($host, '/');

        $url = "https://{$host}:{$port}/execute/{$module}/{$function}";

        log_message('debug', "CpanelApi Final URL: $url");

        // Initialize client WITHOUT baseURI to avoid confusion
        $client = Services::curlrequest([
            'timeout' => 300,
            'http_errors' => false,
        ]);

        $headers = [
            'Authorization' => 'cpanel ' . $this->config->cpanel_username . ':' . $this->config->api_token,
            'User-Agent' => 'CodeIgniter-cPanel-API/2.0',
            'Accept' => 'application/json',
        ];

        $options = [
            'headers' => $headers,
        ];

        if ($method === 'POST') {
            $options['form_params'] = $parameters;
            $response = $client->post($url, $options);
        } else {
            if (!empty($parameters)) {
                $url .= '?' . http_build_query($parameters);
            }
            $response = $client->get($url, $options);
        }


        if ($response->getStatusCode() !== 200) {
            throw new Exception('HTTP Error: ' . $response->getStatusCode() . ' - ' . $response->getReasonPhrase());
        }

        $data = json_decode($response->getBody(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON parse error: ' . json_last_error_msg());
        }

        return $data;
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
}
