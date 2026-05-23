#!/bin/bash

# ==============================================================================
# Script Otomatisasi Sinkronisasi Sistem Identitas Digital
# Deskripsi: Menjalankan sinkronisasi cPanel, BSrE, Pegawai, dan Website.
# ==============================================================================

# Ambil direktori tempat skrip ini berada
PROJECT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Berpindah ke direktori proyek
cd "$PROJECT_DIR"

# Jalankan perintah sinkronisasi spark
# Output akan diarahkan ke file log dengan timestamp
LOG_FILE="writable/logs/cron_sync.log"
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memulai sinkronisasi otomatis..." >> "$LOG_FILE"

php spark sync:all >> "$LOG_FILE" 2>&1

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Sinkronisasi selesai." >> "$LOG_FILE"
echo "----------------------------------------------------" >> "$LOG_FILE"
