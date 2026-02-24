# Sinjai Emails - Portal Manajemen Identitas Digital

Portal terpusat untuk pengelolaan akun email institusi (@sinjaikab.go.id), pemantauan website, dan log pendampingan teknis Pemerintah Kabupaten Sinjai.

## Fitur Utama
- **Manajemen Email:** Provisioning akun via cPanel API, sinkronisasi kuota, dan pemrosesan massal (batch).
- **Integrasi BSrE:** Pemantauan otomatis status sertifikat digital (TTE).
- **Direktori Institusi:** Database terstruktur unit kerja, pimpinan, dan status kepegawaian.
- **Pemantauan Website:** Pelacakan ketersediaan dan masa berlaku domain website OPD & Desa.
- **Log Pendampingan:** Dokumentasi bantuan teknis IT antar instansi.
- **Pelaporan:** Ekspor data ke format PDF dan CSV standar institusi.

## Tumpukan Teknologi
- **Backend:** PHP 8.1+ (CodeIgniter 4)
- **Database:** MySQL / MariaDB
- **Frontend:** Tailwind CSS, ApexCharts.js
- **Integrasi:** cPanel UAPI, BSrE API V2

## Instalasi Cepat

1. **Setup Awal:**
   ```bash
   git clone https://github.com/kalamangna/sinjai-emails.git
   composer install
   npm install
   ```

2. **Konfigurasi:**
   Salin `env` ke `.env` dan sesuaikan database serta kredensial API.

3. **Migrasi & Build:**
   ```bash
   php spark migrate
   npm run build
   ```

4. **Jalankan:**
   ```bash
   php spark serve
   ```

---
© 2026 Pemerintah Kabupaten Sinjai. Dikembangkan oleh Diskominfo-SP Sinjai.
