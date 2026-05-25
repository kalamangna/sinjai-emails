<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cpanel extends BaseConfig
{
    public string $cpanel_host;
    public int $cpanel_port;
    public string $cpanel_username;
    public string $api_token;
    public string $domain;

    public function __construct()
    {
        parent::__construct();
        $this->cpanel_host = env('CPANEL_HOST');
        $this->cpanel_port = env('CPANEL_PORT');
        $this->cpanel_username = env('CPANEL_USERNAME');
        $this->api_token = env('CPANEL_API_TOKEN');
        $this->domain = env('CPANEL_DOMAIN');
    }
}
