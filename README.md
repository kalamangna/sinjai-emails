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
- **Reporting**: Dashboard statistik status TTE di seluruh perangkat daerah dilengkapi dengan persentase capaian.

### 3. Monitoring Website & Domain

- **Website OPD**: Pemantauan status pemanfaatan subdomain OPD.
- **Website Desa & Kelurahan**: Pelacakan masa berlaku domain `.desa.id` melalui protokol **RDAP PANDI**.
- **Visualisasi Data**: Grafik distribusi platform dan status operasional yang modern dan informatif dengan legend persentase.

### 4. Digital Identity Verification

- **Dynamic QR Code**: Generasi QR Code otomatis pada detail akun yang memiliki sertifikat elektronik aktif.
- **Branded QR**: QR Code dilengkapi dengan logo instansi di bagian tengah untuk tampilan profesional.
- **Public Verification Page**: Halaman verifikasi publik yang dioptimasi untuk perangkat _mobile_, memungkinkan validasi identitas digital secara instan melalui pemindaian QR Code.

### 5. Batch Operations (Admin & Super Admin)

- **Refactored Module**: Modul batch yang terorganisir untuk efisiensi pemrosesan data massal.
- **XLSX (Excel) Import**: Dukungan penuh impor file XLSX untuk pembuatan dan pembaruan data secara massal yang lebih andal.
- **Mass Account Creation**: Pembuatan akun massal dengan input ala Excel dan validasi otomatis.
- **Bulk Updates**: Pembaruan data profil dan dokumen Perjanjian Kerja (PK) secara massal, termasuk pembaruan Unit Kerja per baris.
- **Smart Update Logic**: Sistem secara otomatis mendeteksi dan melompati pembaruan jika data yang diimpor identik dengan data yang sudah ada di database, meminimalkan operasi database yang tidak perlu.
- **PK Export System**: Generasi otomatis dokumen Perjanjian Kerja (PPPK/Paruh Waktu) dengan format standar (A4, font Bookman Old Style).
- **Subfolder Archive**: ZIP hasil batch yang terorganisir secara otomatis berdasarkan status kepegawaian.

### 5. Log Pendampingan Teknis (Super Admin)

- **Documentation**: Pencatatan riwayat bantuan teknis (email, website, TTE, Srikandi) kepada instansi.
- **Advanced Filtering**: Filter berdasarkan kategori, bulan, dan tahun untuk pelaporan periodik.

### 6. Administrasi & Keamanan

- **Global Omni-Search**: Bar pencarian cerdas di bagian header untuk akses cepat ke data pegawai dan akun dari halaman mana saja.
- **Role-Based Access Control (RBAC)**: Pembatasan akses antara _Super Admin_ dan _Admin_. Admin kini memiliki kemampuan untuk mengelola akun, memodifikasi informasi website, dan melakukan sinkronisasi, sementara Super Admin memegang kontrol penuh atas penghapusan data, manajemen Unit Kerja, dan Log Layanan.
- **Eksport Data**: Generasi laporan dalam format PDF, Excel (XLSX), dan CSV yang telah dioptimasi (layout landscape/portrait, clickable domain, dan ringkasan statistik).
- **Restructured UI**: Navigasi yang dikelompokkan secara logis (Dashboard, Email, Pegawai, Pejabat, Organisasi) untuk kemudahan penggunaan.
- **SEO & Privacy**: Perlindungan privasi data melalui penonaktifan pengindeksan mesin pencari (noindex, nofollow) dan pembatasan akses bot melalui `robots.txt`.

## Tech Stack

### Backend

- **Core Framework**: PHP 8.1+ (CodeIgniter 4.6)
- **Database**: MySQL / MariaDB
- **Spreadsheet Engine**: PhpSpreadsheet (XLSX Support)
- **PDF Engine**: Dompdf
- **HTTP Client**: CodeIgniter CURLRequest

### Frontend

- **CSS Framework**: Tailwind CSS (JIT Compiler)
- **UI Logic**: Pure Vanilla JavaScript (ES6+) for maximum performance and zero layout flicker.
- **Interactivity**: Custom state management system replacing Alpine.js for core navigation and sidebar interactions.
- **Charts**: ApexCharts (Data Visualization)
- **UI Components**: Font Awesome 6, Choices.js (Searchable Selects)

### Integrasi Eksternal

- **cPanel UAPI**: Manajemen akun email server.
- **BSrE API**: Validasi status sertifikat elektronik.
- **PANDI RDAP**: Pengecekan masa aktif domain desa.

---

© 2026 Diskominfo-SP Sinjai.
