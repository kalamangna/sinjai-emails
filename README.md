# Sinjai Emails

Portal terintegrasi untuk manajemen identitas digital, sertifikat elektronik, dan monitoring infrastruktur web Pemerintah Kabupaten Sinjai. Dirancang untuk mengefisiensikan tata kelola administrasi TI di lingkungan Diskominfo-SP.

## Fitur Utama

### 1. Manajemen Email Institusi

- **Integrasi cPanel API**: Pembuatan, penghapusan, dan sinkronisasi akun email secara otomatis dengan server hosting.
- **Advanced Search**: Pencarian akun berdasarkan Nama, NIP, atau NIK secara konsisten di seluruh tabel.
- **Integrasi Pegawai API**: Sinkronisasi data kepegawaian (Jabatan, Pangkat, dan Golongan Ruang) secara otomatis dari `apps.sinjaikab.go.id` berdasarkan NIP.
- **Manajemen Profil**: Pendataan lengkap NIK, NIP, Jabatan, Pangkat, Golongan Ruang, dan Unit Kerja untuk setiap pemegang akun.
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

- **Unified Component Architecture**: Implementasi komponen UI yang dapat digunakan kembali (reusable components) seperti sistem **Pagination Terpusat**, yang menstandarisasi pengalaman pengguna dan mempermudah pemeliharaan kode di seluruh aplikasi.
- **Robust Error Handling**: Sistem penanganan kesalahan tingkat lanjut menggunakan `\Throwable` untuk memastikan semua jenis _error_ (termasuk kesalahan database atau tipe data) ditangkap dan dirender dengan antarmuka aplikasi yang seragam.
- **Global Omni-Search**: Bar pencarian cerdas di bagian header untuk akses cepat ke data pegawai dan akun dari halaman mana saja.
- **Role-Based Access Control (RBAC)**: Pembatasan akses antara _Super Admin_ dan _Admin_. Admin memiliki kemampuan untuk mengelola akun, memodifikasi informasi website, dan melakukan sinkronisasi, sementara Super Admin memegang kontrol penuh atas penghapusan data, manajemen Unit Kerja, dan Log Layanan.
- **Eksport Data**: Antarmuka ekspor yang telah disederhanakan melalui menu _dropdown_, menghasilkan laporan PDF, Excel (XLSX), dan CSV yang dioptimasi (layout landscape/portrait, clickable domain, dan ringkasan statistik).
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
- **UI Logic**: Vanilla JavaScript (ES6+)
- **Interactivity**: Lightweight, high-performance Vanilla JS for all core UI interactions, including navigation, dropdowns, toggles, and real-time batch synchronization.
- **Charts**: ApexCharts (Data Visualization)
- **UI Components**: Font Awesome 6, Choices.js (Searchable Selects)

### Integrasi Eksternal

- **Pegawai API**: `apps.sinjaikab.go.id` (Sinkronisasi data PNS/PPPK).
- **cPanel UAPI**: Manajemen akun email server.
- **BSrE API**: Validasi status sertifikat elektronik.
- **PANDI RDAP**: Pengecekan masa aktif domain desa.

---

© 2026 Diskominfo-SP Sinjai.
