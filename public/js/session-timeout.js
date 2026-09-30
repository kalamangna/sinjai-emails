/**
 * session-timeout.js — Auto-logout & Idle Session Tracker
 *
 * Memantau aktivitas pengguna dan secara otomatis menampilkan dialog peringatan
 * sebelum sesi kedaluwarsa, serta mengarahkan ke logout jika waktu habis.
 * Mendukung sinkronisasi multi-tab melalui localStorage.
 */

(function () {
    'use strict';

    if (!window.SESSION_CONFIG) {
        return;
    }

    const config = window.SESSION_CONFIG;
    const lifetime = parseInt(config.lifetime, 10) || 7200; // detik
    const warningTime = parseInt(config.warningTime, 10) || 60; // detik
    const keepAliveUrl = config.keepAliveUrl || '/auth/keep-alive';
    const logoutUrl = config.logoutUrl || '/logout?expired=1';
    const STORAGE_KEY = 'sinjai_last_active';

    let isWarningShown = false;
    let isLoggingOut = false;
    let lastRecordedActivity = Date.now();

    // Inisialisasi aktivitas pertama kali jika belum ada di localStorage
    if (!localStorage.getItem(STORAGE_KEY)) {
        localStorage.setItem(STORAGE_KEY, Date.now().toString());
    }

    /**
     * Catat aktivitas pengguna (throttled setiap 5 detik)
     */
    function recordActivity() {
        // Jangan reset otomatis jika modal peringatan sedang aktif (harus klik Tetap Masuk)
        if (isWarningShown || isLoggingOut) {
            return;
        }

        const now = Date.now();
        if (now - lastRecordedActivity > 5000) {
            lastRecordedActivity = now;
            localStorage.setItem(STORAGE_KEY, now.toString());
        }
    }

    // Pantau event interaksi pengguna
    const events = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart'];
    events.forEach(function (eventName) {
        window.addEventListener(eventName, recordActivity, { passive: true });
    });

    /**
     * Hitung sisa waktu sesi dalam detik
     */
    function getRemainingSeconds() {
        const lastActiveStr = localStorage.getItem(STORAGE_KEY);
        const lastActive = lastActiveStr ? parseInt(lastActiveStr, 10) : Date.now();
        const elapsed = Math.floor((Date.now() - lastActive) / 1000);
        return lifetime - elapsed;
    }

    /**
     * Tampilkan modal peringatan
     */
    function showWarningModal(remaining) {
        if (isWarningShown) return;
        isWarningShown = true;

        const countdownEl = document.getElementById('session-timeout-countdown');
        if (countdownEl) {
            countdownEl.textContent = remaining;
        }

        if (typeof window.openModal === 'function') {
            window.openModal('session-timeout-modal');
        } else {
            const modalEl = document.getElementById('session-timeout-modal');
            if (modalEl) {
                modalEl.classList.remove('hidden');
                modalEl.classList.add('flex');
            }
        }
    }

    /**
     * Sembunyikan modal peringatan
     */
    function hideWarningModal() {
        if (!isWarningShown) return;
        isWarningShown = false;

        if (typeof window.closeModal === 'function') {
            window.closeModal('session-timeout-modal');
        } else {
            const modalEl = document.getElementById('session-timeout-modal');
            if (modalEl) {
                modalEl.classList.add('hidden');
                modalEl.classList.remove('flex');
            }
        }
    }

    /**
     * Perpanjang sesi melalui keep-alive endpoint
     */
    function extendSession() {
        const btn = document.getElementById('btn-extend-session');
        const originalText = btn ? btn.innerHTML : 'Tetap Masuk';

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memperbarui...';
        }

        fetch(keepAliveUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
            .then(function (response) {
                if (response.status === 401) {
                    // Sesi telah habis di server
                    isLoggingOut = true;
                    window.location.href = logoutUrl;
                    return null;
                }
                return response.json();
            })
            .then(function (data) {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }

                if (data && data.success) {
                    const now = Date.now();
                    lastRecordedActivity = now;
                    localStorage.setItem(STORAGE_KEY, now.toString());
                    hideWarningModal();
                }
            })
            .catch(function (error) {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
                console.error('[SessionTimeout] Gagal memperpanjang sesi:', error);
            });
    }

    /**
     * Pemeriksaan berkala setiap detik
     */
    function tick() {
        if (isLoggingOut) return;

        const remaining = getRemainingSeconds();

        if (remaining <= 0) {
            // Waktu sesi habis -> redirect auto-logout
            isLoggingOut = true;
            localStorage.removeItem(STORAGE_KEY);
            window.location.href = logoutUrl;
            return;
        }

        if (remaining <= warningTime) {
            showWarningModal(remaining);
            const countdownEl = document.getElementById('session-timeout-countdown');
            if (countdownEl) {
                countdownEl.textContent = remaining;
            }
        } else {
            // Jika ada tab lain yang memperbarui aktivitas
            if (isWarningShown) {
                hideWarningModal();
            }
        }
    }

    // Jalankan timer per detik
    setInterval(tick, 1000);

    // Sinkronisasi antar tab browser
    window.addEventListener('storage', function (e) {
        if (e.key === STORAGE_KEY) {
            const lastActive = parseInt(e.newValue || Date.now(), 10);
            const elapsed = Math.floor((Date.now() - lastActive) / 1000);
            const remaining = lifetime - elapsed;

            if (remaining > warningTime && isWarningShown) {
                hideWarningModal();
            }
        }
    });

    // Pasang event listener tombol Tetap Masuk
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('btn-extend-session');
        if (btn) {
            btn.addEventListener('click', extendSession);
        }
    });
})();
