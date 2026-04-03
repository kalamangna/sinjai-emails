# Sistem Manajemen Email & Identitas Digital Kabupaten Sinjai

Platform manajemen identitas digital berbasis **CodeIgniter 4** dan **Tailwind CSS**, dirancang untuk mengelola akun email institusi dan sertifikat elektronik (TTE) bagi Pemerintah Kabupaten Sinjai.

Aplikasi ini menerapkan standar estetika **"Slate Clean Government"**—antarmuka profesional dengan kontras tinggi yang dioptimalkan untuk efisiensi administratif dan kejelasan data.

## 🚀 Fitur Utama

### 📧 Manajemen Email & Akun
- **Integrasi cPanel:** Sinkronisasi real-time dengan UAPI cPanel untuk pembuatan akun, pembaruan kata sandi, dan pengelolaan kuota penyimpanan.
- **Sinkronisasi API Pegawai:** Pembaruan otomatis data **Jabatan**, **Pangkat**, dan **Golongan Ruang** melalui API Pegawai resmi.
- **Kategorisasi Data:** Tampilan khusus untuk **PNS**, **PPPK (Penuh Waktu)**, dan **PPPK (Paruh Waktu)** dengan filter canggih.
- **Identitas Digital:** Pembuatan QR Code otomatis untuk verifikasi identitas publik yang aman melalui rute terenkripsi (hash).

### ✍️ Integrasi TTE BSrE
- **Monitoring Status:** Pelacakan status Sertifikat Elektronik secara real-time (ISSUE, EXPIRED, NO_CERTIFICATE, dll).
- **Sinkronisasi Massal:** Sinkronisasi status TTE berurutan dengan performa tinggi untuk seluruh kategori pegawai dengan indikator progres langsung.

### 📊 Pemantauan & Analitik
- **Monitoring Website:** Pelacakan domain **OPD** dan **Desa/Kelurahan**, termasuk sinkronisasi otomatis masa berlaku SSL dan Domain.
- **Dashboard Dinamis:** Analitik dengan grafik donut dan kartu metrik yang menampilkan persentase performa data.
- **Log Pendampingan:** Pencatatan terpusat untuk bantuan teknis dan log layanan (khusus Super Admin).

### 📥 Operasi Batch (XLSX)
- **Mesin Spreadsheet:** Menggunakan `PhpSpreadsheet` untuk pemrosesan file Excel yang tangguh.
- **Handler Terpadu:** Template khusus untuk **Batch Create**, **Batch Update**, dan **Batch PK (Perjanjian Kerja)** dengan deteksi "No-Change" untuk optimasi database.

### 📄 Sistem Ekspor
- **Mesin PDF:** Pelaporan profesional menggunakan `Dompdf`, dioptimalkan untuk standar visual "Slate Clean".
- **Berbagai Format:** Mendukung ekspor PDF, CSV, dan ZIP untuk monitoring akun, ringkasan organisasi, dan log tanda tangan digital.

## 🛠 Teknologi

- **Backend:** PHP 8.1+, CodeIgniter 4.x
- **Frontend:** Tailwind CSS, Vanilla JS (Performa Tinggi), Alpine.js (Utilitas), Choices.js
- **Database:** MySQL/MariaDB
- **Integrasi:** cPanel UAPI, BSrE API, API Pegawai
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

- **Super Admin:** Akses sistem penuh, Master Data (Unit Kerja), Log Layanan, dan operasi destruktif.
- **Admin:** Manajemen operasional, Mutasi Akun, Operasi Batch, dan Monitoring Website.
- **Privasi Data:** Penegakan meta tag `noindex, nofollow` global dan hash verifikasi publik yang aman.
- **Resiliensi Error:** Penanganan `\Throwable` global memastikan stabilitas dan tampilan error yang profesional di seluruh domain.

## ⚙️ Persyaratan & Instalasi

1. **PHP 8.1+** dengan ekstensi `intl`, `mbstring`, `gd`, dan `curl`.
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

---
Dikembangkan oleh **Diskominfo-SP Sinjai** | &copy; 2026
