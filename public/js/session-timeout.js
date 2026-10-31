/**
 * session-timeout.js — Auto-logout Inactivity Tracker
 *
 * Memantau aktivitas pengguna dan secara otomatis melakukan logout langsung
 * jika tidak ada aktivitas (idle) tanpa perlu konfirmasi dialog/modal.
 * Mendukung sinkronisasi multi-tab melalui localStorage dan keep-alive berkala di latar belakang.
 */

(function () {
    'use strict';

    if (!window.SESSION_CONFIG) {
        return;
    }

    const config = window.SESSION_CONFIG;
    const lifetime = parseInt(config.lifetime, 10) || 7200; // detik (default 2 jam / konfigurasi sesi)
    const keepAliveUrl = config.keepAliveUrl || '/auth/keep-alive';
    const logoutUrl = config.logoutUrl || '/logout?expired=1';
    const STORAGE_KEY = 'sinjai_last_active';
    const PING_INTERVAL = 10 * 60 * 1000; // 10 menit

    let isLoggingOut = false;
    let lastRecordedActivity = Date.now();
    let lastKeepAlivePing = Date.now();

    // Inisialisasi waktu aktivitas: selalu validasi timestamp di localStorage
    const now = Date.now();
    const storedActiveStr = localStorage.getItem(STORAGE_KEY);
    const storedActive = storedActiveStr ? parseInt(storedActiveStr, 10) : 0;

    // Jika belum ada, tidak valid, bernilai masa depan, atau telah kedaluwarsa dari sesi sebelumnya,
    // reset ke waktu saat ini karena halaman ini baru berhasil diverifikasi & dimuat oleh server
    if (!storedActive || isNaN(storedActive) || storedActive > now || (now - storedActive) / 1000 >= lifetime) {
        localStorage.setItem(STORAGE_KEY, now.toString());
        lastRecordedActivity = now;
    } else {
        lastRecordedActivity = storedActive;
    }

    /**
     * Kirim sinyal keep-alive ke server di latar belakang saat pengguna aktif
     */
    function pingKeepAlive() {
        fetch(keepAliveUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        }).catch(function () {
            // Abaikan kegagalan jaringan sementara
        });
    }

    /**
     * Catat aktivitas pengguna (throttled setiap 5 detik)
     */
    function recordActivity() {
        if (isLoggingOut) {
            return;
        }

        const now = Date.now();
        if (now - lastRecordedActivity > 5000) {
            lastRecordedActivity = now;
            localStorage.setItem(STORAGE_KEY, now.toString());

            // Segarkan sesi server secara berkala selama pengguna masih aktif
            if (now - lastKeepAlivePing > PING_INTERVAL) {
                lastKeepAlivePing = now;
                pingKeepAlive();
            }
        }
    }

    // Pantau event interaksi pengguna
    const events = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart'];
    events.forEach(function (eventName) {
        window.addEventListener(eventName, recordActivity, { passive: true });
    });

    /**
     * Hitung sisa waktu inaktivitas dalam detik
     */
    function getRemainingSeconds() {
        const lastActiveStr = localStorage.getItem(STORAGE_KEY);
        const lastActive = lastActiveStr ? parseInt(lastActiveStr, 10) : Date.now();
        if (isNaN(lastActive) || lastActive > Date.now()) {
            return lifetime;
        }
        const elapsed = Math.floor((Date.now() - lastActive) / 1000);
        return lifetime - elapsed;
    }

    /**
     * Pemeriksaan inaktivitas berkala setiap detik
     */
    function checkInactivity() {
        if (isLoggingOut) return;

        const remaining = getRemainingSeconds();

        if (remaining <= 0) {
            // Waktu inaktivitas habis -> langsung redirect auto-logout tanpa konfirmasi
            isLoggingOut = true;
            localStorage.removeItem(STORAGE_KEY);
            window.location.href = logoutUrl;
        }
    }

    // Jalankan timer pemeriksaan inaktivitas per detik
    setInterval(checkInactivity, 1000);

    // Sinkronisasi status logout antar-tab browser
    window.addEventListener('storage', function (e) {
        if (e.key === STORAGE_KEY && !e.newValue && !isLoggingOut) {
            // Tab lain telah melakukan logout / menghapus sesi
            isLoggingOut = true;
            window.location.href = logoutUrl;
        }
    });
})();
