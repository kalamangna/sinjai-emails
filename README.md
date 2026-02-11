# Sinjai Emails - Portal Manajemen Identitas Digital

Platform manajemen terpusat yang dirancang untuk mengefisiensikan penyediaan identitas digital, pelacakan aset institusional, dan aktivitas dukungan teknis bagi Pemerintah Kabupaten Sinjai.

## Fitur Utama

### 🆔 Manajemen Identitas (Email)
- **Penyediaan Akun Digital:** Pengelolaan siklus hidup lengkap identitas elektronik resmi (@sinjaikab.go.id) yang terintegrasi dengan cPanel API.
- **Pemrosesan Massal (Layanan Batch):** Pembuatan akun dalam jumlah besar, pembaruan profil, dan otomatisasi pembuatan dokumen PK (Perjanjian Kerja).
- **Integrasi Layanan:** Sinkronisasi waktu nyata dengan mail server untuk memantau penggunaan, kuota disk, dan kesehatan akun.

### 🏢 Direktori Institusi
- **Hierarki Unit Kerja:** Mengelola struktur instansi dengan hubungan bertingkat (OPD, UPTD, dan Kecamatan).
- **Klasifikasi & Peran:** Melacak status kepegawaian (ASN/PPPK), pangkat resmi (Eselon), dan posisi pimpinan.
- **Hub Data Pimpinan:** Pelacakan khusus untuk pimpinan OPD, Kepala Desa, dan Lurah.

### 🔐 Kepatuhan & Tanda Tangan Elektronik (TTE)
- **Integrasi BSrE:** Pemantauan otomatis status sertifikasi tanda tangan elektronik melalui BSrE API.
- **Pemantauan Status Terstandar:** Pelacakan status dengan kode warna yang konsisten (ISSUE, EXPIRED, NO_CERTIFICATE, RENEW, dll) di seluruh platform.
- **Sinkronisasi SDM:** Terhubung dengan sistem kepegawaian lokal (Simpegnas) untuk menjaga akurasi informasi pegawai.

### 📊 Pemantauan Website
- **Pelacakan Terpusat:** Memantau status dan ketersediaan situs web resmi pemerintah (OPD dan Desa).
- **Metadata Otomatis:** Pemeriksaan cerdas untuk masa berlaku domain dan status pengelolaan (Dikelola Kominfo/Mandiri).
- **Analitik Visual:** Dasbor interaktif yang memberikan wawasan tentang distribusi aset dan status menggunakan ApexCharts.

### 🤝 Log Pendampingan (Dukungan Teknis)
- **Pencatatan Aktivitas:** Mendokumentasikan bantuan teknis dan pendampingan yang diberikan ke berbagai unit kerja.
- **Kategorisasi Layanan:** Pelacakan khusus untuk area dukungan yang berbeda seperti aplikasi sistem, dukungan email, atau pengelolaan website.
- **Pelacakan Metode:** Mencatat interaksi berdasarkan penyampaian dukungan (Remot/Online atau On-site).

### 📄 Pelaporan Profesional
- **Ekspor Terstandar:** Laporan PDF dan CSV profesional dengan branding terpadu dan format lokal.
- **Generasi Dokumen Massal:** Pembuatan dokumen PK otomatis untuk PPPK Paruh Waktu dengan kemampuan ekspor ZIP.
- **Wawasan Terfilter:** Pembuatan laporan bertarget berdasarkan Unit Kerja, Eselon, atau status sertifikasi tertentu.

## Tumpukan Teknologi

- **Backend:** PHP 8.1+ dengan Framework CodeIgniter 4
- **Database:** Relational Database (MySQL / MariaDB)
- **Frontend:** Tailwind CSS & JavaScript Modern (Async/Fetch)
- **Visualisasi:** ApexCharts.js
- **Pelaporan:** Dompdf untuk pembuatan PDF tingkat lanjut
- **Integrasi API:** cPanel UAPI, BSrE API V2, Simpegnas API

## Persyaratan Sistem

- PHP 8.1 atau lebih tinggi
- Composer Dependency Manager
- MySQL 5.7+ atau MariaDB 10.3+
- Node.js (untuk proses build Tailwind CSS)