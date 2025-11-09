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
        $this->cpanel_host = env('cpanel.host');
        $this->cpanel_port = env('cpanel.port');
        $this->cpanel_username = env('cpanel.username');
        $this->api_token = env('cpanel.api_token');
        $this->domain = env('cpanel.domain');
    }
}
