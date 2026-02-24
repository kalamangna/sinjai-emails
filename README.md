# Sinjai Emails

Portal terintegrasi manajemen email institusi, sertifikat digital, dan monitoring website Pemerintah Kabupaten Sinjai.

## Modul Utama
- **Email:** Manajemen akun via cPanel API & Monitoring TTE (BSrE).
- **Web:** Monitoring status & masa berlaku domain OPD/Desa.
- **Logs:** Dokumentasi bantuan teknis dan pendampingan IT.
- **Auth:** Akses berbasis peran (Super Admin & Admin).

## Tech Stack
- PHP 8.1 (CodeIgniter 4)
- Tailwind CSS
- MySQL
- cPanel & BSrE API

## Setup Cepat
1. `composer install`
2. `npm install`
3. Configure `.env`
4. `php spark migrate && php spark db:seed UserSeeder`
5. `npm run build`
6. `php spark serve`

## Akun Default
- **Super Admin:** `kalamangna` / `Syazani`
- **Admin (View):** `aptika` / `Kominfo101`

---
© 2026 Diskominfo-SP Sinjai.