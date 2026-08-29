#!/bin/bash

# ==============================================================================
# CLI Wrapper: Sistem Identitas Digital (./sync)
# ==============================================================================

# Direktori proyek
PROJECT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$PROJECT_DIR"

# Path ke PHP binary
if [ -f "/usr/local/bin/ea-php83" ]; then
    PHP_BIN="/usr/local/bin/ea-php83"
elif command -v php8.3 >/dev/null 2>&1; then
    PHP_BIN="php8.3"
elif command -v php83 >/dev/null 2>&1; then
    PHP_BIN="php83"
else
    PHP_BIN="php"
fi

LOG_FILE="writable/logs/cron_sync.log"
mkdir -p writable/logs

MODE=$1
ARG2=$2

case "$MODE" in
    pegawai)
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memulai sinkronisasi DATA PEGAWAI (SIMPEG)..." | tee -a "$LOG_FILE"
        $PHP_BIN spark sync:all --pegawai 2>&1 | tee -a "$LOG_FILE"
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memproses antrean data pegawai..." | tee -a "$LOG_FILE"
        $PHP_BIN spark queue:work --stop-when-empty 2>&1 | tee -a "$LOG_FILE"
        ;;
    tte)
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memulai sinkronisasi STATUS TTE (BSrE)..." | tee -a "$LOG_FILE"
        $PHP_BIN spark sync:all --tte 2>&1 | tee -a "$LOG_FILE"
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memproses antrean status TTE..." | tee -a "$LOG_FILE"
        $PHP_BIN spark queue:work --stop-when-empty 2>&1 | tee -a "$LOG_FILE"
        ;;
    daily)
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memulai sinkronisasi HARIAN (TTE Pimpinan)..." | tee -a "$LOG_FILE"
        $PHP_BIN spark sync:all --daily 2>&1 | tee -a "$LOG_FILE"
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memproses antrean..." | tee -a "$LOG_FILE"
        $PHP_BIN spark queue:work --stop-when-empty 2>&1 | tee -a "$LOG_FILE"
        ;;
    weekly)
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memulai sinkronisasi MINGGUAN (cPanel & Website)..." | tee -a "$LOG_FILE"
        $PHP_BIN spark sync:all --weekly 2>&1 | tee -a "$LOG_FILE"
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memproses antrean..." | tee -a "$LOG_FILE"
        $PHP_BIN spark queue:work --stop-when-empty 2>&1 | tee -a "$LOG_FILE"
        ;;
    monthly)
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memulai sinkronisasi BULANAN (Pegawai & TTE)..." | tee -a "$LOG_FILE"
        $PHP_BIN spark sync:all --monthly 2>&1 | tee -a "$LOG_FILE"
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memproses antrean..." | tee -a "$LOG_FILE"
        $PHP_BIN spark queue:work --stop-when-empty 2>&1 | tee -a "$LOG_FILE"
        ;;
    unit)
        if [ -z "$ARG2" ]; then
            echo "Error: Nama atau ID Unit Kerja wajib diisi. Contoh: ./sync unit \"Dinas Komunikasi\""
            exit 1
        fi
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memulai sinkronisasi Pegawai untuk Unit: $ARG2..." | tee -a "$LOG_FILE"
        $PHP_BIN spark sync:pegawai-unit "$ARG2" 2>&1 | tee -a "$LOG_FILE"
        ;;
    tte-unit)
        if [ -z "$ARG2" ]; then
            echo "Error: Nama atau ID Unit Kerja wajib diisi. Contoh: ./sync tte-unit \"Dinas Komunikasi\""
            exit 1
        fi
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memulai sinkronisasi TTE untuk Unit: $ARG2..." | tee -a "$LOG_FILE"
        $PHP_BIN spark sync:tte-unit "$ARG2" 2>&1 | tee -a "$LOG_FILE"
        ;;
    flush|clear)
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Membersihkan seluruh antrean background job..." | tee -a "$LOG_FILE"
        $PHP_BIN spark queue:flush 2>&1 | tee -a "$LOG_FILE"
        exit 0
        ;;
    help|--help|-h)
        echo "================================================================="
        echo " 🏛️ SISTEM IDENTITAS DIGITAL - SCRIPT SINKRONISASI (./sync)"
        echo "================================================================="
        echo "Penggunaan:"
        echo "  ./sync              : Menjalankan sinkronisasi PENUH (Semua Objek)"
        echo "  ./sync pegawai      : Menjalankan sinkronisasi DATA PEGAWAI (SIMPEG)"
        echo "  ./sync tte          : Menjalankan sinkronisasi STATUS TTE (BSrE)"
        echo "  ./sync unit <nama>  : Menjalankan sinkronisasi Pegawai per Unit Kerja"
        echo "  ./sync tte-unit <n> : Menjalankan sinkronisasi TTE per Unit Kerja"
        echo "  ./sync flush        : Membersihkan / mengosongkan seluruh antrean (Queue)"
        echo "  ./sync daily        : Menjalankan sinkronisasi HARIAN (TTE Pimpinan)"
        echo "  ./sync weekly       : Menjalankan sinkronisasi MINGGUAN (Email & Website)"
        echo "  ./sync monthly      : Menjalankan sinkronisasi BULANAN (Pegawai & TTE)"
        echo "================================================================="
        exit 0
        ;;
    *)
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memulai sinkronisasi PENUH..." | tee -a "$LOG_FILE"
        $PHP_BIN spark sync:all 2>&1 | tee -a "$LOG_FILE"
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Memproses antrean sinkronisasi latar belakang..." | tee -a "$LOG_FILE"
        $PHP_BIN spark queue:work --stop-when-empty 2>&1 | tee -a "$LOG_FILE"
        ;;
esac

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Selesai." | tee -a "$LOG_FILE"
echo "----------------------------------------------------" >> "$LOG_FILE"
