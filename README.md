# Sistem Identitas Digital

Platform manajemen identitas digital berbasis **CodeIgniter 4** dan **Tailwind CSS**, dirancang untuk mengelola akun email institusi dan sertifikat elektronik (TTE) bagi Pemerintah Kabupaten Sinjai.

Aplikasi ini menerapkan standar estetika **"Slate Clean Government"**—antarmuka profesional dengan kontras tinggi yang dioptimalkan untuk efisiensi administratif dan kejelasan data.

## 🚀 Fitur Utama

### 📧 Manajemen Email & Akun
- **Integrasi cPanel:** Sinkronisasi real-time dengan UAPI cPanel untuk pembuatan akun, pembaruan kata sandi, dan pengelolaan kuota penyimpanan.
- **Sinkronisasi API Pegawai:** Pembaruan otomatis data **Jabatan**, **Pangkat**, dan **Golongan Ruang** melalui API Pegawai resmi.
- **Kategorisasi Data:** Tampilan khusus untuk **PNS**, **PPPK (Penuh Waktu)**, dan **PPPK (Paruh Waktu)** dengan filter canggih.
- **Endpoint API Internal:** Penyediaan data terstruktur (JSON) untuk integrasi data PPPK lintas sistem secara aman.
- **Identitas Digital:** Pembuatan QR Code otomatis untuk verifikasi identitas publik yang aman melalui rute terenkripsi (hash).
- **Debug & Validasi Data:** Fitur deteksi otomatis untuk akun yang mengalami NIP ganda atau ambiguitas data profil.

### ✍️ Integrasi TTE BSrE
- **Monitoring Status:** Pelacakan status Sertifikat Elektronik secara real-time (ISSUE, EXPIRED, NO_CERTIFICATE, dll).
- **Sinkronisasi Massal:** Sinkronisasi status TTE berurutan dengan performa tinggi untuk seluruh kategori pegawai dengan indikator progres langsung.

### 📊 Pemantauan & Analitik
- **Monitoring Website:** Pelacakan domain **OPD** dan **Desa/Kelurahan**, termasuk sinkronisasi otomatis masa berlaku SSL dan Domain.
- **Helpdesk Layanan:** Portal bantuan publik terpadu untuk seluruh layanan TIK (Email, Website, TTE, Srikandi) dengan sistem *ticketing* dan integrasi log otomatis.
- **Dashboard Dinamis:** Analitik dengan kartu metrik yang menampilkan persentase performa data secara real-time.
- **Log Pendampingan:** Pencatatan terpusat untuk bantuan teknis dan log layanan (khusus Super Admin).

### 📥 Operasi Batch (XLSX)
- **Mesin Spreadsheet:** Menggunakan `PhpSpreadsheet` untuk pemrosesan file Excel yang tangguh.
- **Preview Performa Tinggi:** Sistem *Multi-Candidate Batch Check* untuk validasi massal (NIP, NIK, Email) dalam satu permintaan, mengeliminasi antrian request sekuensial.
- **Pembersihan Nama Cerdas:** Otomatisasi pembersihan tanda baca, perbaikan format nama yang terpisah spasi (spaced-out), dan standarisasi casing.
- **Handler Terpadu:** Template khusus untuk **Batch Create**, **Batch Update**, dan **Batch PK** dengan deteksi perubahan data untuk optimasi database.

### 🤖 Antrean Tugas & Otomatisasi
- **Background Queue:** Migrasi proses sinkronisasi berat (cPanel, TTE, Pegawai) ke sistem antrean latar belakang menggunakan `JobModel` untuk performa server yang lebih stabil.
- **Worker Command:** Penambahan perintah Spark `queue:work` untuk memproses antrean secara efisien.
- **Laporan Telegram:** Pengiriman ringkasan statistik sinkronisasi secara otomatis ke Channel Telegram.
- **Alert Kuota Email:** Notifikasi instan via Telegram jika terdapat akun dengan penggunaan penyimpanan di atas 90%, lengkap dengan detail data pengguna dan unit kerja.
- **Alert TTE Expired:** Peringatan otomatis melalui Telegram untuk akun dengan status sertifikat elektronik yang kadaluwarsa, disertai detail identitas lengkap.
- **Notifikasi Operasi Batch:** Laporan audit trail instan ke Telegram setiap kali Admin melakukan proses impor/update data massal via Excel.
- **Pembersihan Akun Pensiun:** Mekanisme penangguhan login instan dan penghapusan permanen otomatis setelah masa tunggu 30 hari untuk akun yang ditandai pensiun.
- **Penjadwalan Tugas:** Skrip shell terpadu (`sync.sh`) untuk otomatisasi tugas harian (TTE), mingguan (cPanel), dan bulanan (Pegawai/Website).
- **Optimasi Sinkronisasi TTE:** Sistem secara cerdas membatasi sinkronisasi harian hanya untuk akun Pimpinan guna efisiensi maksimal API.
- **Transparansi Data:** Pencatatan dan tampilan waktu sinkronisasi terakhir dari berbagai modul (cPanel, TTE, Pegawai, Website) secara terpusat di Dashboard utama.

### 🌐 API Gateway (v1)
Penyediaan data internal yang aman untuk integrasi lintas sektoral di Pemkab Sinjai:
- **Health Check API:** Titik akhir internal untuk memantau konektivitas layanan eksternal (cPanel, BSrE, Pegawai API) secara real-time.
- **Endpoints:** `/api/v1/emails`, `/api/v1/pns`, `/api/v1/pppk`, `/api/v1/pppk-pw`, `/api/v1/unit/{id}`.
- **Data Terintegrasi:** Sertifikasi `api_unit_id` (External ID) pada setiap record untuk sinkronisasi lintas platform yang presisi.
- **Keamanan:** Mendukung *Bearer Token* dan *Session-based Access* (untuk pengguna terdaftar).
- **Dokumentasi:** Halaman panduan interaktif di `/api-docs` lengkap dengan daftar ID Unit Kerja eksternal.

### 📄 Sistem Ekspor
- **Mesin PDF:** Pelaporan profesional menggunakan `Dompdf`, dioptimalkan untuk standar visual "Slate Clean".
- **Berbagai Format:** Mendukung ekspor PDF, CSV, dan ZIP untuk monitoring akun, ringkasan organisasi, dan log tanda tangan digital.

## 🛠 Teknologi

- **Backend:** PHP 8.3+, CodeIgniter 4.x
- **Frontend:** Tailwind CSS, Vanilla JS (Performa Tinggi), Alpine.js (Utilitas), Choices.js
- **Database:** MySQL/MariaDB
- **Integrasi:** cPanel UAPI, BSrE API, API Pegawai, Telegram Bot API
- **Library:** PhpSpreadsheet, Dompdf, SimpleQR

## 🏗 Arsitektur

Proyek ini menggunakan pendekatan **Domain-Driven Design (DDD)** di dalam direktori `app/Domains`:

- **Assistance:** Log bantuan teknis.
- **Auth:** RBAC (Super Admin/Admin) dan manajemen sesi aman.
- **Batch:** Logika pemrosesan data massal.
- **Dashboard:** Portal analitik utama.
- **Email:** Mutasi akun inti dan logika identitas.
- **UnitKerja:** Manajemen struktur organisasi hierarkis.
- **Website:** Pemantauan domain dan SSL.

## 🔒 Keamanan & RBAC

- **Enkripsi Data Sensitif:** Penggunaan **AES-256** untuk mengenkripsi data identitas (NIK, NIP) dan password di database.
- **Blind Index:** Implementasi indexing berbasis hash untuk pencarian data terenkripsi secara efisien tanpa menurunkan level keamanan.
- **Super Admin:** Akses sistem penuh, Master Data (Unit Kerja), Log Layanan, dan operasi destruktif.
- **Admin:** Manajemen operasional, Mutasi Akun, Operasi Batch, dan Monitoring Website.
- **Privasi Data:** Penegakan meta tag `noindex, nofollow` global dan hash verifikasi publik yang aman.
- **Resiliensi Error:** Penanganan `\Throwable` global memastikan stabilitas dan tampilan error yang profesional di seluruh domain.

## ⚙️ Persyaratan & Instalasi

1. **PHP 8.3+** dengan ekstensi `intl`, `mbstring`, `gd`, dan `curl`.
2. **Composer** untuk manajemen dependensi.
3. **Node.js** untuk kompilasi Tailwind CSS.
4. **Token API cPanel** untuk integrasi server email.

```bash
# Instal dependensi
composer install
npm install

# Kompilasi CSS
npm run build

# Jalankan Migrasi
php spark migrate
```

## 🤖 Otomatisasi (Cron Job)

Untuk menjaga data tetap mutakhir, Anda dapat menggunakan skrip shell yang telah disediakan:

```bash
# Memberikan izin eksekusi (sekali saja)
chmod +x sync.sh

# Jalankan sinkronisasi secara manual
./sync.sh daily    # Hanya Status TTE (Harian)
./sync.sh weekly   # Hanya cPanel (Mingguan)
./sync.sh monthly  # Pegawai & Website (Bulanan)
./sync.sh          # Sinkronisasi Penuh

# Contoh penjadwalan Cron Job
# Skrip sync.sh sudah otomatis menjalankan 'queue:work' di akhir prosesnya.

# 1. Setiap Hari jam 02:00 AM (Status TTE)
0 2 * * * /home/tte/sinjai-emails/sync.sh daily

# 2. Setiap Hari Minggu jam 03:00 AM (cPanel)
0 3 * * 0 /home/tte/sinjai-emails/sync.sh weekly

# 3. Setiap Tanggal 25 jam 04:00 AM (Pegawai & Website)
0 4 25 * * /home/tte/sinjai-emails/sync.sh monthly

# 4. Backup Database Harian (Jam 00:00)
0 0 * * * /usr/local/bin/ea-php83 /home/tte/sinjai-emails/spark app:backup >> /dev/null 2>&1
```

Skrip ini secara cerdas membagi tugas:
- **Daily**: Memperbarui status **TTE BSrE** (Paling sering).
- **Weekly**: Sinkronisasi akun **cPanel** (Kuota & Status).
- **Monthly**: Memperbarui data **API Pegawai** dan masa aktif domain **PANDI**.

Sistem secara transparan mencatat dan menampilkan waktu terakhir masing-masing sinkronisasi tersebut di **Dashboard**, **Detail Akun**, dan **Monitoring Website** untuk memastikan kesegaran data.

### 🛠 Sinkronisasi Manual (Per Unit Kerja)

Selain penjadwalan otomatis, Anda dapat melakukan sinkronisasi status TTE secara manual untuk unit kerja tertentu melalui terminal. Perintah ini mendukung fitur **Child Unit** (anak unit akan ikut tersinkronisasi otomatis) dan **Filter Status ASN**.

ID Unit dapat dilihat langsung di halaman **Detail Unit Kerja** pada Dashboard.

```bash
# Sinkronisasi TTE untuk unit ID 5 (Dinas Pendidikan)
php spark sync:tte-unit 5

# Sinkronisasi hanya untuk PNS di unit tersebut
php spark sync:tte-unit 5 --asn=PNS

# Sinkronisasi hanya untuk PPPK di unit tersebut
php spark sync:tte-unit 5 --asn=PPPK

# Sinkronisasi hanya untuk PPPK Paruh Waktu
php spark sync:tte-unit 5 --asn="PPPK PARUH WAKTU"
```

### 📢 Notifikasi Telegram

Sistem ini mendukung laporan otomatis ke Telegram. Untuk mengaktifkannya, tambahkan kredensial berikut ke file `.env`:

```env
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here
API_GATEWAY_TOKEN=your_secure_api_token_here
```

Seluruh aktivitas akan dicatat dalam file log di `writable/logs/cron_sync.log` dan dikirimkan sebagai ringkasan ke Telegram.

---
Dikembangkan oleh **Diskominfo-SP Sinjai** | &copy; 2026
