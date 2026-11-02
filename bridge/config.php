<?php
/**
 * Konfigurasi API Bridge TTE BSrE - Pemkab Sinjai
 * Lokasi path: /tte/
 */

return [
    // Kunci API untuk pengamanan tambahan (opsional jika menggunakan Basic Auth)
    'api_key' => 'SinjaiSmartKampung2026!Key',

    // Pengaturan apakah mewajibkan API Key
    'require_api_key' => false,

    // Konfigurasi Server TTE BSrE Lokal
    'tte' => [
        'base_url' => getenv('BSRE_BASE_URL') ? getenv('BSRE_BASE_URL') : 'http://10.91.162.6',
        'timeout'  => 120, // detik
    ],

    // Pengaturan Logging
    'log' => [
        'enabled' => true,
        'file'    => __DIR__ . '/tte.log',
    ]
];
