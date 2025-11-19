<?php

namespace App\Libraries;

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
        $url = "https://{$this->config->cpanel_host}:{$this->config->cpanel_port}/execute/{$module}/{$function}";

        $client = Services::curlrequest([
            'baseURI' => $url,
            'timeout' => 30,
            'http_errors' => false, // Allow handling of non-200 responses
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
            $response = $client->post('', $options);
        } else {
            if (!empty($parameters)) {
                $url .= '?' . http_build_query($parameters);
            }
            $response = $client->get('', $options);
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            log_message('error', 'Failed to get email detail: ' . $e->getMessage());
            throw new Exception('Failed to retrieve email details: ' . $e->getMessage());
        }
    }

    public function test_connection()
    {
        try {
            $emails = $this->get_email_accounts_detailed();

            return [
                'success' => true,
                'message' => 'cPanel API Email Module connection successful!',
                'data' => [
                    'total_emails' => count($emails),
                    'api_module' => 'Email',
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'cPanel API connection failed: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
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
            return $response;
        } catch (Exception $e) {
            log_message('error', 'Failed to create email account: ' . $e->getMessage());
            throw new Exception('Failed to create email account: ' . $e->getMessage());
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

            if (isset($response['status']) && $response['status'] == 1) {
                return $response;
            } else {
                $error_message = $response['errors'][0] ?? 'Unknown error during email deletion.';
                throw new Exception($error_message);
            }
        } catch (Exception $e) {
            log_message('error', 'Failed to delete email account: ' . $e->getMessage());
            throw new Exception('Failed to delete email account: ' . $e->getMessage());
        }
    }
}
