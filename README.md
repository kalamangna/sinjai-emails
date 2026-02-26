# Sinjai Emails

Portal terintegrasi untuk manajemen identitas digital, sertifikat elektronik, dan monitoring infrastruktur web Pemerintah Kabupaten Sinjai. Dirancang untuk mengefisiensikan tata kelola administrasi TI di lingkungan Diskominfo-SP.

## Fitur Utama

### 1. Manajemen Email Institusi
- **Integrasi cPanel API**: Pembuatan, penghapusan, dan sinkronisasi akun email secara otomatis dengan server hosting.
- **Batch Processing**: Pembuatan akun massal dan pembaruan data profil dalam satu kali proses (Excel-like input).
- **Manajemen Profil**: Pendataan NIK, NIP, Jabatan, dan Unit Kerja untuk setiap pemegang akun.

### 2. Monitoring Sertifikat Elektronik (TTE)
- **Integrasi BSrE API**: Pemantauan status sertifikat digital secara *real-time* (Aktif, Expired, Revoked, dsb).
- **Reporting**: Dashboard statistik status TTE pimpinan dan ASN di seluruh perangkat daerah.

### 3. Monitoring Website & Domain
- **Website OPD**: Pemantauan status pemanfaatan subdomain OPD.
- **Website Desa & Kelurahan**: Pelacakan masa berlaku domain `.desa.id` melalui protokol **RDAP PANDI**.
- **Visualisasi Data**: Grafik distribusi platform dan status operasional menggunakan ApexCharts.

### 4. Log Pendampingan Teknis
- **Documentation**: Pencatatan riwayat bantuan teknis (email, website, TTE, Srikandi) kepada instansi.
- **Advanced Filtering**: Filter berdasarkan kategori, bulan, dan tahun untuk pelaporan bulanan.

### 5. Administrasi & Keamanan
- **Role-Based Access Control (RBAC)**: Perbedaan akses fungsional antara *Super Admin* dan *Admin*.
- **Eksport Data**: Generasi laporan dalam format PDF dan CSV menggunakan Dompdf.
- **Otomatisasi Dokumen**: Pembuatan dokumen Perjanjian Kerja (PK) massal dalam format PDF/ZIP.

## Tech Stack

### Backend
- **Core Framework**: PHP 8.1+ (CodeIgniter 4.6)
- **Database**: MySQL / MariaDB
- **PDF Engine**: Dompdf
- **HTTP Client**: CodeIgniter CURLRequest

### Frontend
- **CSS Framework**: Tailwind CSS (JIT Compiler)
- **Interactivity**: Alpine.js (Lightweight JavaScript)
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
© 2026 Diskominfo-SP Kabupaten Sinjai.
