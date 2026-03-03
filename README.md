# Sinjai Emails

Portal terintegrasi untuk manajemen identitas digital, sertifikat elektronik, dan monitoring infrastruktur web Pemerintah Kabupaten Sinjai. Dirancang untuk mengefisiensikan tata kelola administrasi TI di lingkungan Diskominfo-SP.

## Fitur Utama

### 1. Manajemen Email Institusi

- **Integrasi cPanel API**: Pembuatan, penghapusan, dan sinkronisasi akun email secara otomatis dengan server hosting.
- **Advanced Search**: Pencarian akun berdasarkan Nama, NIP, atau NIK secara konsisten di seluruh tabel.
- **Manajemen Profil**: Pendataan NIK, NIP, Jabatan, Golongan (untuk PNS/PPPK), dan Unit Kerja untuk setiap pemegang akun.
- **Manajemen Pegawai**: Pengelompokan dan pemantauan khusus untuk daftar PNS, PPPK (Penuh Waktu), dan PPPK PW (Paruh Waktu) dengan fitur **Pagination** untuk efisiensi data besar.

### 2. Monitoring Sertifikat Elektronik (TTE)

- **Integrasi BSrE API**: Pemantauan status sertifikat digital secara _real-time_ (Aktif, Expired, Revoked, dsb).
- **Pengelompokan Pejabat**: Akses cepat untuk monitoring TTE Pimpinan OPD dan Kepala Desa.
- **Reporting**: Dashboard statistik status TTE di seluruh perangkat daerah.

### 3. Monitoring Website & Domain

- **Website OPD**: Pemantauan status pemanfaatan subdomain OPD.
- **Website Desa & Kelurahan**: Pelacakan masa berlaku domain `.desa.id` melalui protokol **RDAP PANDI**.
- **Visualisasi Data**: Grafik distribusi platform dan status operasional yang modern dan informatif.

### 4. Batch Operations (Super Admin)

- **Refactored Module**: Modul batch yang terorganisir untuk efisiensi pemrosesan data massal.
- **Advanced Spreadsheet Import**: Migrasi dari CSV ke **XLSX (Excel)** untuk impor data yang lebih andal dan mudah diedit.
- **Mass Account Creation**: Pembuatan akun massal dengan input ala Excel dan validasi otomatis.
- **Bulk Updates**: Pembaruan data profil dan dokumen Perjanjian Kerja (PK) secara massal, termasuk pembaruan Unit Kerja per baris.
- **Unit Kerja Batch**: Pembuatan daftar Unit Kerja secara massal melalui file Excel.
- **PK Export System**: Generasi otomatis dokumen Perjanjian Kerja (PPPK/Paruh Waktu) dengan format standar (A4, font Bookman Old Style).
- **Subfolder Archive**: ZIP hasil batch yang terorganisir secara otomatis berdasarkan status kepegawaian.

### 5. Log Pendampingan Teknis

- **Documentation**: Pencatatan riwayat bantuan teknis (email, website, TTE, Srikandi) kepada instansi.
- **Advanced Filtering**: Filter berdasarkan kategori, bulan, dan tahun untuk pelaporan periodik.

### 6. Administrasi & Keamanan

- **Role-Based Access Control (RBAC)**: Pembatasan akses antara _Super Admin_ dan _Admin_.
- **Eksport Data**: Generasi laporan dalam format PDF, Excel (XLSX), dan CSV yang telah dioptimasi (layout landscape/portrait, clickable domain, dan ringkasan statistik).
- **Restructured UI**: Navigasi yang dikelompokkan secara logis (Dashboard, Email, Pegawai, Pejabat, Organisasi) untuk kemudahan penggunaan.

## Tech Stack

### Backend

- **Core Framework**: PHP 8.1+ (CodeIgniter 4.6)
- **Database**: MySQL / MariaDB
- **Spreadsheet Engine**: PhpSpreadsheet (XLSX Support)
- **PDF Engine**: Dompdf
- **HTTP Client**: CodeIgniter CURLRequest

### Frontend

- **CSS Framework**: Tailwind CSS (JIT Compiler)
- **Interactivity**: Alpine.js & Plugins (Collapse)
- **Charts**: ApexCharts (Data Visualization)
- **UI Components**: Font Awesome 6, Choices.js (Searchable Selects)

### Integrasi Eksternal

- **cPanel UAPI**: Manajemen akun email server.
- **BSrE API**: Validasi status sertifikat elektronik.
- **PANDI RDAP**: Pengecekan masa aktif domain desa.

## Instalasi

1. **Clone & Dependencies**

   ```bash
   git clone https://github.com/kalamangna/sinjai-emails.git
   composer install
   npm install
   ```

2. **Environment**
   Salin `env` ke `.env` dan konfigurasi Database serta API Credentials:

   ```ini
   database.default.hostname = localhost
   database.default.database = sinjai_emails

   # cPanel Config
   cpanel.host = your-server.com
   cpanel.user = username
   cpanel.token = your-api-token
   ```

3. **Migration & Seeding**

   ```bash
   php spark migrate
   php spark db:seed UserSeeder
   ```

4. **Build Assets**
   ```bash
   npm run build
   ```

---

© 2026 Diskominfo-SP Sinjai.
