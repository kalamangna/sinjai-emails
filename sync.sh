#!/bin/bash

# ==============================================================================
# Script Otomatisasi Sinkronisasi Sistem Identitas Digital
# Deskripsi: Menjalankan sinkronisasi cPanel, BSrE, Pegawai, dan Website.
# ==============================================================================

# Ambil direktori tempat skrip ini berada
PROJECT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Berpindah ke direktori proyek
cd "$PROJECT_DIR"

# Jalankan perintah sinkronisasi spark berdasarkan argumen
# Output akan diarahkan ke file log dengan timestamp
LOG_FILE="writable/logs/cron_sync.log"
MODE=$1

if [ "$MODE" == "daily" ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memulai sinkronisasi HARIAN (cPanel & TTE)..." >> "$LOG_FILE"
    php spark sync:all --daily >> "$LOG_FILE" 2>&1
elif [ "$MODE" == "monthly" ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memulai sinkronisasi BULANAN (Pegawai & Website)..." >> "$LOG_FILE"
    php spark sync:all --monthly >> "$LOG_FILE" 2>&1
else
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memulai sinkronisasi PENUH..." >> "$LOG_FILE"
    php spark sync:all >> "$LOG_FILE" 2>&1
fi

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Sinkronisasi selesai." >> "$LOG_FILE"
echo "----------------------------------------------------" >> "$LOG_FILE"
