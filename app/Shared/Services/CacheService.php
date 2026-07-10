<?php

namespace App\Shared\Services;

/**
 * CacheService
 *
 * Titik terpusat untuk manajemen cache key dan invalidasi.
 * Semua nama key cache HARUS didefinisikan di sini sebagai konstanta
 * agar tidak ada string literal yang tersebar di seluruh codebase.
 */
class CacheService
{
    // ─── Cache Key Constants ───────────────────────────────────────────────
    const KEY_DASHBOARD_SUMMARY  = 'dashboard_summary_data_v3';
    const KEY_EMAIL_SUMMARY      = 'email_dashboard_summary';
    const KEY_SYSTEM_HEALTH      = 'system_health_status_v3';

    // ─── Cache TTL Constants (in seconds) ─────────────────────────────────
    const TTL_DASHBOARD  = 600;  // 10 minutes
    const TTL_EMAIL      = 600;  // 10 minutes
    const TTL_HEALTH     = 300;  // 5 minutes

    /**
     * Hapus semua cache yang berkaitan dengan data dashboard & ringkasan email.
     * Panggil method ini setiap kali ada perubahan data email (create, update, delete).
     */
    public static function invalidateDashboard(): void
    {
        $cache = \Config\Services::cache();
        $cache->delete(self::KEY_DASHBOARD_SUMMARY);
        $cache->delete(self::KEY_EMAIL_SUMMARY);
    }

    /**
     * Hapus cache health check layanan eksternal.
     */
    public static function invalidateHealth(): void
    {
        $cache = \Config\Services::cache();
        $cache->delete(self::KEY_SYSTEM_HEALTH);
    }
}
