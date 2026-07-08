# Riwayat Perubahan — Sistem Identitas Digital Sinjai

Semua perubahan penting pada proyek ini dicatat di berkas ini.
Format mengacu pada [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

# [8 Juli 2026]

## Penambahan Data
- **Tambah Platform Baru — KOMINFO**:
    - Menambahkan platform `KOMINFO` ke tabel `platforms` melalui migration `2026-07-08-153748_AddKominfoPlatform.php`.
    - Platform ini kini tersedia sebagai pilihan di dropdown form edit Website Desa & Kelurahan serta filter halaman monitoring.

---

# [7 Juli 2026]

## Perbaikan & Peningkatan
- **Responsive Layout Navbar (Topbar)**:
    - Merestruktur layout `header` di `app/Views/layouts/main.php` agar tidak meluap di layar mobile.
    - Search bar global **selalu tampil** di header pada semua ukuran layar (mobile & desktop).
    - Tombol **Verifikasi PDF** dan **Riwayat Laporan** disembunyikan di layar mobile (`hidden sm:flex`) dan dipindahkan ke dalam menu **User Dropdown** agar tetap dapat diakses. Di dropdown, keduanya hanya tampil pada layar mobile (`sm:hidden`) menggunakan divider tersendiri.
    - Info nama/role user di header disesuaikan ke `hidden md:flex` (tampil mulai tablet) dengan batas lebar `max-w-[120px] truncate` agar tidak meluap.
    - Info user di dalam dropdown menu kini **selalu tampil**, memastikan konsistensi di semua ukuran layar.
    - Menambahkan atribut `aria-label`, `aria-haspopup`, dan `aria-expanded` pada tombol-tombol header untuk aksesibilitas.
- **Perbaikan Bug JS Sidebar**:
    - Memperbaiki typo variabel `activeMenuValueValue` (tidak terdefinisi) menjadi `activeMenuValue` yang benar di dalam fungsi `toggleSubmenu()` pada `app/Views/layouts/main.php`. Bug ini menyebabkan error diam (*silent error*) saat state sidebar dicoba disimpan ke `localStorage`.


---

# [29 Juni 2026]


## Fitur Baru
- **Indikator & Filter Penggunaan Disk di Daftar Email**:
    - Menambahkan kolom **Penggunaan Disk** pada tabel daftar email utama (`app/Views/email/index.php`) berupa progress bar mini dan persentase yang menunjukkan sisa kuota penyimpanan email.
    - Progress bar dirancang dinamis dengan pewarnaan visual (Merah jika ≥ 85%, Jingga jika ≥ 70%, dan Slate gelap jika normal) konsisten dengan detail akun.
    - Menambahkan filter pencarian **Penggunaan Disk** di form atas untuk menyaring akun berstatus *Kritis* (≥ 85%) atau *Penuh* (≥ 95%).
    - Memperbarui `EmailService.php` dan `Email.php` controller untuk memproses parameter `disk_usage` dan melakukan filter query builder di database.
- **Statistik Top 10 OPD Teraktif Sertifikat TTE**:
    - Menambahkan tabel data statistik **Top 10 OPD Teraktif Sertifikat TTE** di bagian bawah halaman Dashboard utama (`app/Views/home/index.php`) dengan layout lebar penuh (*full-width card*).
    - Tabel menampilkan peringkat OPD berdasarkan persentase TTE aktif tertinggi dari seluruh akun berstatus wajib TTE (yang memiliki NIP, status pimpinan, atau terkait unit kerja).
    - Menambahkan klausul filter `HAVING COUNT(e.id) >= 5` untuk **mengecualikan OPD yang memiliki total akun wajib TTE di bawah 5**, menjaga relevansi peringkat.
    - Mengintegrasikan fungsi penggabungan (*roll-up*) data email sub-unit (seperti sekolah atau puskesmas) secara otomatis ke OPD induknya (seperti Dinas Pendidikan atau Dinas Kesehatan) agar persentase valid.
    - Menambahkan tautan (*hyperlink*) langsung pada nama OPD di tabel menuju halaman **Detail Unit Kerja** terkait untuk pemantauan instan.
    - Dilengkapi progress bar visual berwarna dinamis (hijau $\ge 85\%$, jingga $\ge 50\%$, abu-abu jika di bawahnya) serta informasi total wajib TTE dan jumlah aktif.
    - Memperbarui `DashboardService.php` untuk memproses perhitungan SQL ini dan membersihkan cache sistem secara otomatis.
- **Grafik Top 10 Persentase Penggunaan Disk Terbesar**:
    - Menambahkan widget grafik **Top 10 Persentase Penggunaan Disk Terbesar** menggunakan diagram batang horizontal (Horizontal Bar Chart) ApexCharts di halaman Dashboard utama (`app/Views/home/index.php`) secara bertumpuk (*stacked*) langsung di bawah tabel TTE teraktif.
    - Menampilkan 10 akun email dengan persentase penggunaan penyimpanan terbesar terhadap kapasitas kuotanya, diurutkan menurun (`diskusedpercent_float DESC`).
    - Mengintegrasikan filter pencarian untuk **mengecualikan seluruh email dengan kuota tak terbatas (unlimited)** agar grafik fokus pada akun berkapasitas berbatas.
    - Kategori bar menggunakan nama email lengkap secara utuh sebagai label visual pada sumbu diagram.
    - Menambahkan interaksi klik (**event listener**) pada batang diagram (bars) dan label teks alamat email untuk mengarahkan pengguna langsung ke halaman **Detail Email** terkait.
    - Tooltip interaktif kustom yang menampilkan informasi nama lengkap, alamat email lengkap, ukuran penyimpanan terpakai, dan persentase penggunaan.
    - Memproses data di `DashboardService.php` dengan pembagian bytes ke Megabytes.
    - Menambahkan dekorasi CSS kustom untuk memberikan penunjuk cursor pointer dan underline visual pada label yang dapat diklik.

## Perbaikan Bug
- **Sinkronisasi Jumlah Email Database vs cPanel**:
    - Menemukan akar masalah ketidaksinkronan: metode `syncFromCpanel()` di `SyncService.php` sebelumnya hanya melakukan *upsert* (tambah/perbarui) tanpa pernah menghapus akun yang sudah tidak ada di cPanel.
    - Menambahkan logika **Soft-Delete Sinkron**: setelah proses *upsert* selesai, sistem kini membandingkan seluruh daftar email aktif di database dengan daftar dari cPanel. Akun yang ada di database namun tidak ditemukan di cPanel akan otomatis di-*soft delete*.
    - Proses penghapusan dilakukan per batch 500 record untuk efisiensi query dan dicatat ke log sistem untuk keperluan audit.
    - Nilai kembalian `syncFromCpanel()` diperbarui untuk menyertakan informasi jumlah akun yang disinkronkan (`synced`) dan dihapus (`deleted`).

## Antarmuka & Konsistensi Desain
- **Pembersihan Menu Sidebar**:
    - Menghapus entri menu **Verifikasi PDF** dari sidebar agar navigasi samping lebih ringkas.
    - Tombol **Verifikasi PDF** tetap tersedia di bagian kanan atas navbar untuk akses cepat.
- **Penyelarasan Halaman Error Verifikasi Akun**:
    - Mendesain ulang `app/Views/email/error.php` menjadi halaman mandiri (*standalone centered card*) berlatar belakang `bg-slate-50` dengan header gelap `bg-slate-800`, selaras dengan desain `verify.php`, `verify_pdf.php`, dan `login.php`.
- **Penambahan Panel "Detail Kendala & Layanan" di Halaman Admin Helpdesk**:
    - Menambahkan kartu "Detail Kendala & Layanan" pada `app/Views/helpdesk/admin_detail.php` yang menampilkan **Kategori**, **Layanan Spesifik**, dan **Jenis Masalah** — informasi ini sebelumnya tidak ditampilkan di halaman detail.

## Panduan Pengembangan
- **Pembaruan `GEMINI.md`**:
    - Menambahkan aturan bahwa kompilasi CSS, pencatatan CHANGELOG, dan push hanya dijalankan saat ada instruksi eksplisit "push" dari pengguna.
    - Menambahkan catatan bahwa pengguna menjalankan `npm run dev` (*watch mode*) secara aktif sehingga build manual tidak diperlukan.

---

# [28 Juni 2026]

## Antarmuka & Konsistensi Desain
- **Halaman Verifikasi PDF**:
    - Menyederhanakan tampilan hasil verifikasi: menghapus status "Dokumen Telah Dimodifikasi", "Keutuhan Berkas", dan "Kepercayaan Sertifikat".
    - Mengubah label panel "Hasil Analisis" menjadi **"Hasil Verifikasi"**.
    - Menambahkan penyesuaian warna dinamis: hijau (*emerald*) jika terdeteksi TTE, jingga (*amber*) jika tidak ada TTE. Header utama tetap abu-abu gelap.
    - Mengubah tata letak Detail Penandatangan menjadi 1 kolom vertikal yang rapi.
    - Menambahkan fungsi `formatIndonesianDate` untuk menampilkan waktu TTE dalam zona waktu **WITA** (contoh: "27 Juni 2026, 18:36:42 WITA").
- **Halaman Helpdesk Publik**:
    - Mendesain ulang halaman formulir (`public_form.php`) dan sukses (`public_success.php`) menjadi halaman mandiri (*standalone centered card*) selaras dengan gaya halaman Verifikasi Akun.
    - Menyelaraskan gaya input form (`bg-white`, `rounded-lg`, warna label `text-slate-700`).
    - Memperlebar kartu formulir dari `max-w-2xl` menjadi `max-w-3xl`.
    - Mengubah status input **NIP / NIK** dari opsional menjadi wajib (*required*), beserta validasi server-side di `HelpdeskPublicController.php`.
- **Tombol Verifikasi PDF & Helpdesk**:
    - Menambahkan tombol Verifikasi PDF di navbar dan halaman login dengan struktur grid responsif.
    - Mengonfigurasi seluruh tautan publik agar terbuka di tab baru (`target="_blank"`).
- **Penyelarasan Meta & Judul**:
    - Menyatukan deskripsi OpenGraph, Twitter Card, dan deskripsi default di seluruh halaman publik menjadi satu format seragam.
    - Menyederhanakan judul halaman: Login → `Login`, Helpdesk → `Helpdesk`, Error Verifikasi → `Verifikasi Akun`.
- **Pembersihan Aset**:
    - Menghapus berkas spreadsheet sementara di folder `public/` (`batch_restore.csv`, `batch_restore.xlsx`, `restore_data.xlsx`).

## Fitur Baru
- **Batch Registrasi BSrE di Detail Unit Kerja**:
    - Menambahkan tombol "Register BSrE" pada halaman Detail Unit Kerja untuk mendaftarkan massal seluruh akun berstatus `NOT_REGISTERED` ke BSrE secara sekuensial via AJAX.
- **Registrasi BSrE Langsung di Detail Akun**:
    - Tombol "Daftarkan BSrE" muncul otomatis hanya jika status BSrE pegawai bernilai `NOT_REGISTERED`.
    - Setelah registrasi berhasil, status TTE otomatis di-sync tanpa memuat ulang halaman.
- **Validasi Pembuatan Akun Tunggal**:
    - Menambahkan pembersihan format NIK dan validasi keunikan NIK di database lokal pada `createSingleEmail`.
    - Tombol eksekusi otomatis terkunci jika NIK sudah terdaftar.
- **Kapitalisasi Nama Otomatis**:
    - Nama pegawai kini secara otomatis dikonversi ke huruf kapital (`mb_strtoupper`) di sisi server pada semua proses (tunggal, massal, pembaruan massal).
- **Logo & Favicon**:
    - Menambahkan logo daerah (`logo.png`) di sidebar dan halaman login.
    - Menetapkan `meta.png` sebagai gambar Open Graph utama.
- **Halaman Verifikasi PDF Publik** (`GET /verifikasi-pdf`):
    - Menyediakan halaman verifikasi publik mandiri yang dapat diakses tanpa otentikasi.
- **Endpoint BSrE**:
    - `POST /bsre/register` — pendaftaran user baru ke BSrE API v2.
    - `POST /bsre/verify` — verifikasi keaslian PDF ter-TTE, mendukung upload file langsung atau string Base64.
- **Penanganan Kesalahan BSrE API**:
    - Mengonfigurasi cURL agar tidak melempar eksepsi pada HTTP non-2xx sehingga pesan error rinci dari server BSrE dapat diteruskan ke frontend.

## Panduan Pengembangan
- **Pembaruan `GEMINI.md`**: Menyesuaikan nama proyek dari "PANRITA" menjadi "Sistem Identitas Digital".

---

# [25 Juni 2026]

## Panduan & Infrastruktur
- **Pembaruan `README.md`**: Memperbarui perintah Cron Job dengan *absolute path* PHP cPanel (`/opt/cpanel/ea-php83/root/usr/bin/php`) agar kompatibel dengan lingkungan cron production.

---

# [23 Juni 2026]

## Fitur Baru
- **Antrean Ekspor PDF Latar Belakang**:
    - Memindahkan proses *rendering* PDF besar ke antrean latar belakang (QueueWorker) untuk mengatasi *504 Gateway Timeout*.
    - Menambahkan tabel `export_histories` untuk mencatat status ekspor.
    - Menambahkan antarmuka "Riwayat Laporan" di sidebar untuk memantau status (PENDING, PROCESSING, COMPLETED) dan mengunduh PDF yang sudah siap.
    - Menambahkan perintah Spark `queue:clean-exports` untuk membersihkan riwayat dan file PDF yang berumur lebih dari 3 hari.
- **Sinkronisasi Pegawai per Unit Kerja via CLI** (`sync:pegawai-unit`):
    - Mendukung filter status ASN (contoh: PNS, PPPK).
    - Menggunakan fungsi batch yang sudah ada di `PegawaiSyncService`.
- **Tukar Data Akun (Swap)**:
    - Formulir swap data antar dua akun menggunakan dropdown dengan fitur pencarian (Choices.js).
    - Data yang ditukar: NIK, NIP, nama, gelar, tempat/tanggal lahir, jabatan, golongan, pangkat, unit kerja, eselon, status ASN, pimpinan, dan pensiun_at. Email tidak ikut ditukar.
    - Mencegah `Duplicate Entry` NIK/NIP saat swap dengan nilai sementara (`temp_`) sebelum update final.
    - Audit log `SWAP_DATA` dicatat setiap eksekusi.

## Perbaikan Bug
- **Halaman 404 pada Riwayat Laporan**: Memperbaiki rute `/reports/history` dan `/reports/download/(:num)` yang tidak bisa diakses karena terdaftar di dalam *group* rute `email`.
- **Fix `Undefined array key "hp"`** di `swapAccountData`: Menghapus field yang tidak ada di skema database.
- **Fix `Call to undefined function log_audit()`**: Menambahkan `require_once audit_helper.php` di `BaseController`.
- **Fix Swap Data Tidak Berubah**: Beralih dari `Model::update()` ke *Query Builder* langsung untuk mencegah update yang diblokir oleh CodeIgniter model *callbacks*.
- **Fix Filter `--asn=PNS` di CLI**: Menambahkan parsing fallback untuk format `--asn=PNS` (dengan tanda `=`).
- **Fix Badge Status Preview Batch**:
    - Menambahkan badge **Existing** (jingga) untuk NIK/NIP yang sudah ada di DB.
    - Menambahkan badge **Duplikat** (kuning) untuk NIK/NIP duplikat dalam satu batch.
    - Tombol Eksekusi dinonaktifkan selama ada baris berstatus Existing, Duplikat, atau Unavailable.

## Antarmuka & Desain
- **Standarisasi Tombol Kembali**: Menyeragamkan ukuran tombol kembali menjadi kotak simetris (`!w-10 !h-10 !p-0`) di semua halaman form.
- **Optimasi Formulir Tukar Data**: Mengganti dropdown statis ribuan email menjadi pencarian dinamis AJAX via `api/search` untuk mempercepat pemuatan halaman.

## Optimasi Antrean (Queue Worker)
- **Session Locking Fix**: Mengakhiri sesi PHP lebih awal (`session_write_close()`) agar request AJAX tidak memblokir antarmuka.
- **Bypass Command Helper**: Memanggil objek `QueueWorker` secara langsung untuk menghindari bug parsing opsi CLI di CodeIgniter.
- **Memory Limit Bypass**: Menaikkan batas memori menjadi 512MB pada `api_trigger_queue` khusus untuk kebutuhan mPDF.
- **Auto-Trigger Cerdas**: Menambahkan fitur auto-trigger pada halaman Riwayat Laporan jika masih terdapat pekerjaan berstatus PENDING atau PROCESSING.

## Refaktor
- **Generator Email Batch Create**: Urutan kandidat disederhanakan — base → tahun lahir → tanggal lahir → random 2 digit. Password suffix mengikuti urutan yang sama.
- **Retry Password Weak di Batch Create**: Backend otomatis mencoba kandidat password berikutnya jika cPanel menolak karena password lemah.

---

# [22 Juni 2026]

## Fitur Baru
- **Notifikasi Telegram saat Pembuatan Akun Email Baru**:
    - Notifikasi otomatis dikirim setelah akun berhasil dibuat via `EmailService::createSingleEmail()`.
    - Baris kosong (nama/jabatan/unit kerja) otomatis disembunyikan.
    - Notifikasi dibungkus `try-catch` terpisah agar kegagalan Telegram tidak mengganggu pembuatan akun.

## Refaktor
- **Diet Controller Website**: Memindahkan logika query agregasi statistik ke `WebsiteService.php`.
- **Format Notifikasi Telegram**: Baris yang nilainya kosong tidak dicetak sama sekali (tidak lagi menampilkan teks *fallback*). Berlaku untuk semua notifikasi.
- **Pembersihan Cache Otomatis**: Cache dashboard dibersihkan otomatis di akhir eksekusi `SyncAllCommand` dan saat antrean `QueueWorker` habis.

## Perbaikan Bug
- **Fix Tanggal Terakhir Sinkronisasi cPanel Tidak Berubah di Dashboard**:
    - `SyncAllCommand::syncCpanel()` lupa memanggil `saveLastSyncTime('last_sync_cpanel')`.
    - `DashboardService` tidak mengambil kunci `last_sync_cpanel` dari database.
    - View `index.php` masih menggunakan variabel `$last_sync_time` yang sudah usang.
- **Fix `Duplicate Entry` pada Sinkronisasi cPanel**: Menambahkan `->withDeleted()` pada pengecekan eksistensi agar akun yang di-*soft-delete* tidak di-*insert* ulang.
- **Fix `null` Error pada Notifikasi Kuota**: Sanitasi nilai `null` pada argumen `$name` di `TelegramMessageBuilder::addUserProfile()`.

## Optimasi Database
- **Migrasi Indeks Baru** (`2026-06-22-140700_AddMissingIndexes`):
    - `INDEX deleted_at_idx` pada tabel `emails` untuk mempercepat query *soft delete*.
    - *Composite index* `bsre_status_deleted_at_idx` pada tabel `emails` untuk dashboard TTE.
    - `INDEX status_idx` pada tabel `web_opd` dan `web_desa_kelurahan`.
    - `INDEX kecamatan_idx` pada tabel `web_desa_kelurahan` untuk filter.

## Standarisasi Lanjutan
- **Semua Notifikasi Telegram ke `TelegramMessageBuilder`**: Notifikasi Pensiun, Hapus Permanen, Batch Create/Update kini menggunakan builder terstandarisasi.
- **Audit Log Konsisten**: `log_audit()` ditambahkan di semua titik aksi kritis (CREATE, UPDATE, PENSIUN, BATCH_CREATE, BATCH_UPDATE).
- **Rate Limiting**: Filter `Throttle.php` dengan batas 120 request/menit per IP diterapkan pada endpoint pencarian dan seluruh `api/v1/*`.
- **Diet Controller `Email.php` & `EmailList.php`**: Logika bisnis `update_details()` dipindahkan ke `EmailService::updateProfileDetails()`.
- **Diet Controller `EmailApi.php`**: Berkurang dari 437 baris menjadi ~260 baris — murni sebagai *dispatcher* tipis.

---

# [16 Juni 2026]

## Fitur & Refaktor
- **Backup Database Otomatis** (`BackupCommand.php`):
    - Pencadangan database dengan kompresi GZIP.
    - Auto-cleanup file backup yang berumur lebih dari 7 hari.
    - Notifikasi Telegram jika eksekusi *mysqldump* gagal.
    - Parameter `--no-tablespaces` untuk mencegah error hak akses di cPanel.
- **Pembaruan Alur Eksekusi Antrean**: Pemanggilan `queue:work --stop-when-empty` dipindahkan ke dalam `sync.sh` untuk efisiensi.

## Perbaikan Bug
- **Fix Pengiriman Laporan TTE & Kuota**: Memisahkan `checkQuotaAlerts` dan `checkTteExpiredAlerts` menjadi dua Job independen yang dijadwalkan secara pasti.
- **Fix Typo Variabel**: `$e['nama']` diubah menjadi `$e['name']` pada builder pesan TTE untuk mencegah *crash* worker.
- **Fix HTTP 500 & Parse Error**: Memperbaiki sintaks *Closure* yang tidak kompatibel dengan PHP 8.1+.

---

# [11 Juni 2026]

## Perubahan Besar
- **Pemusnahan Enkripsi Hash (NIP/NIK)**:
    - Menghapus sepenuhnya enkripsi AES-256 dan *blind index* (`nip_hash`, `nik_hash`) dari seluruh tabel, model, layanan, dan API.
    - NIP dan NIK kini kembali menggunakan *plain text* dan pencarian menggunakan klausa `LIKE` biasa.
    - Migrasi untuk menghapus kolom `nip_hash` dan `nik_hash` secara permanen.

## Refaktor
- **Logika Notifikasi QueueWorker**: Merekam jenis tugas yang diproses di memori PHP dan memicu notifikasi Telegram tepat saat pekerja akan berhenti (*queue is empty*).

## Antarmuka & Pembersihan
- **Penyelarasan Judul Helpdesk**: Mengubah judul `HelpdeskAdminController` menjadi "Helpdesk Layanan".
- **Pembersihan File Debug**: Menghapus 22 skrip PHP sisa pengembangan dari `app/Commands`.
- **Pembersihan Environment Production**: Menghapus folder `tests/`, file `phpunit`, dan `.DS_Store`.

## Perbaikan Bug
- **Fix Dashboard TTE Count**: Kolom `unit_kerja_id` tidak terpanggil dalam query SELECT sehingga ribuan pegawai tanpa NIP disalahartikan sebagai "NON_TTE".
- **Cleanup Rute Sementara**: Menghapus rute, controller, dan view sementara `duplicate_nips` dan `ambiguous`.

---

# [10 Juni 2026]

## Perbaikan Bug
- **Fix 403 Forbidden pada Batch Update**: Mengganti payload dari `application/json` ke `multipart/form-data` (`FormData`) untuk mencegah pemblokiran ModSecurity (WAF).
- **Fix Queue Worker Crash (Laporan Telegram TTE)**: Mengoreksi klausul `LIKE` yang menggunakan tanda kutip ganda pada nama kolom sehingga menyebabkan error SQL `ANSI_QUOTES`.
- **Fix Sinkronisasi `status_asn_id`**: Menutup celah bug pada `processBatchUpdate` di mana tabel `pk` gagal mendapatkan `status_asn_id` baru.
- **Transaksi pada Batch Create**: Menambahkan proteksi *Database Transaction* — jika pembuatan akun di cPanel gagal, data di database lokal otomatis dibatalkan (*Rollback*).

## Refaktor
- **AlertService Terpusat**: Memindahkan fungsi notifikasi Telegram ke `AlertService`. Laporan dikirimkan hanya setelah tugas latar belakang benar-benar tuntas.
- **Notifikasi Darurat (CRITICAL ERROR)**: Notifikasi otomatis ke Telegram jika antrean tugas gagal permanen setelah 3 kali percobaan.
- **Refaktor Batch Update (`EmailBatchService`)**: Menghilangkan masalah *N+1 Query*, menerapkan *Database Transaction*, mengaktifkan sinkronisasi password ke cPanel secara *real-time*.

---

# [9 Juni 2026]

## Perbaikan Bug
- **Fix Batch Route 404 & GET Redirect**: Mengganti nama rute dan memperbarui `baseURL` ke HTTPS untuk mencegah pengalihan POST ke GET.

## Perluasan Layanan Helpdesk
- **Portal Bantuan Terpadu**: Mengalihkan fokus Helpdesk dari TTE saja menjadi portal bantuan untuk seluruh layanan (Website OPD, Email Resmi, TTE, Srikandi, Website Desa).
- **Formulir Publik Dinamis**: Implementasi *Cascading Dropdowns* — pengguna memilih Kategori → Layanan Spesifik → Jenis Kendala secara berurutan.
- **Otomasi Integrasi Admin**: Tiket yang diselesaikan otomatis dicatat ke Log Pendampingan dengan kategori dan layanan yang sesuai.
- **Fitur Manajemen Admin Helpdesk**: Menambahkan fitur hapus permohonan dengan konfirmasi dan audit trail.

## Reviu & Pengujian Batch
- **Dependency Injection `EmailBatchService`**: Memudahkan unit testing dengan *mock objects*.
- **Unit Test**: 6 *test cases* dan 24 *assertions* untuk logika `processBatchUpdate` dan `processBatchCreate`.

## Optimasi
- **Peningkatan Performa**: Pencarian Unit Kerja dipindahkan ke luar *looping* pada proses batch update.
- **Robust Error Reporting**: Pesan error lebih spesifik per baris data.
- **Normalisasi Data**: Perbandingan data lebih toleran terhadap perbedaan `null` vs string kosong.

---

# [8 Juni 2026]

## Perbaikan Pencarian & Keamanan Data
- **Normalisasi Query Pencarian**: Sistem secara otomatis membersihkan karakter non-numerik dari input NIK/NIP sebelum diproses.
- **Blind Index Search**: Pencarian NIK/NIP dialihkan ke pencocokan `nik_hash` dan `nip_hash`.
- **Pencarian Multi-Kriteria**: Pencarian Nama dan Email tetap berjalan paralel meskipun input berupa angka.
- **Perbaikan Dekripsi Tampilan**: NIK dan NIP tidak lagi tampil sebagai *hash* di halaman Detail Akun dan file Ekspor.

---

# [5 Juni 2026]

## Fitur Baru
- **Perintah CLI Sinkronisasi TTE Per Unit** (`php spark sync:tte-unit {unit_id}`):
    - Memproses data secara sekuensial dan sinkron tanpa antrean untuk umpan balik *real-time* di terminal.
    - Menyediakan validasi keberadaan ID Unit Kerja dan statistik (Berhasil/Gagal) setelah selesai.

---

# [4 Juni 2026]

## Keamanan & Infrastruktur
- **Enkripsi Data Sensitif & Blind Index**:
    - Enkripsi dua arah AES-256 untuk kolom `nik`, `nip`, dan `password` di `EmailModel.php`.
    - *Blind Index* SHA-256 pada `nik_hash` dan `nip_hash` untuk pencarian data terenkripsi tanpa dekripsi massal.
- **Sistem Antrean (Job Queue)**:
    - Migrasi sinkronisasi berat (cPanel, TTE, Data Pegawai) ke antrean latar belakang.
    - Penambahan `JobModel` dan tabel `jobs`.
    - `SyncAllCommand.php` mendispatch tugas dalam *chunk* kecil.
- **Pemantauan Kesehatan Sistem**:
    - `SystemHealthService` memantau konektivitas cPanel UAPI, BSrE API, dan Pegawai API.
    - Widget *real-time health check* di Dashboard utama.
- **Audit Trail**: Penambahan ringkasan statistik aksi dan entitas pada halaman Audit Log.
- **SEO & Metadata**: Implementasi meta tag dinamis (title, description, robots, canonical, Open Graph, Twitter Card) di seluruh halaman.

## Perbaikan Bug
- **Optimasi Verifikasi Publik**: Migrasi query ke *Blind Index* (`nik_hash`) dan perbaikan rute `/verifikasi`.
- **Integrasi Transaksi Database**: `transStart()` pada pembaruan profil untuk menjamin integritas data.
- **Keamanan AJAX**: Proteksi CSRF pada semua sinkronisasi massal berbasis AJAX.
- **Soft Delete Fix**: Memperbaiki kegagalan *restore* akun dari sampah.

---

# [26 Mei 2026]

## Integrasi & Optimasi Unit Kerja
- **Sinkronisasi ID Unit Eksternal**: Menambahkan kolom `api_unit_id` dan perintah Spark `sync:unit-api` untuk pemetaan otomatis.
- **Dukungan Hierarki API**: Endpoint `/api/v1/unit/{id}` mendukung pengambilan data rekursif termasuk sub-unit.
- **Filter Query Dinamis**: Dukungan parameter filter via URL pada API Gateway (`name`, `email`, `nip`, `nik`, `jabatan`, `bsre_status`, `api_unit_id`).
- **Pemeliharaan Data**: Perintah `maintenance:unit-duplicates` dan `maintenance:unit-uppercase` untuk standarisasi data unit kerja.

---

# [24 Mei 2026]

## Perbaikan Bug
- **Sinkronisasi API cPanel**: Memperbaiki error "Bad URL" dengan refaktor konstruksi URL yang lebih *robust*.
- **Koreksi Filter TTE**: Memastikan status `ISSUE`, `EXPIRED`, dll. hanya menampilkan akun wajib TTE. Menghilangkan "kebocoran data" akun NON_TTE ke kategori filter lain.

## Fitur Baru
- **API Gateway Login**: Otentikasi ganda — validasi ke API eksternal `apps.sinjaikab.go.id` dengan *fallback* ke password lokal.
- **Otomatisasi Akun Pensiun**: Penangguhan akses login cPanel instan + pembersihan data otomatis + penghapusan permanen setelah 30 hari.
- **Standarisasi Notifikasi Telegram**: Semua alert (Kuota, TTE, Pensiun) menyertakan Nama, NIP, Jabatan, dan Unit Kerja.

---

# [31 Maret 2026]

## Fitur Baru
- **Sinkronisasi Data Pegawai**: Sinkronisasi Jabatan, Pangkat, dan Golongan Ruang dari API Pegawai eksternal dalam satu operasi menggunakan NIP.
- **Perluasan Database**: Penambahan kolom `pangkat_nama` dan `pangkat_golruang` via migrasi baru.
- **Standarisasi Jabatan Pimpinan**: Migrasi data untuk menyesuaikan jabatan pimpinan secara otomatis. Sinkronisasi diatur untuk melewati update `jabatan` bagi akun `pimpinan`.

## Antarmuka
- **Halaman Detail Akun**: Menampilkan Pangkat dan Golongan secara terstruktur dengan visibilitas kondisional berdasarkan Status ASN.
- **Form Edit Profil**: Toggle visibilitas field secara *real-time* berdasarkan pilihan Status ASN.

---

# [26 Maret 2026]

## Arsitektur
- **Sistem Paginasi Terpusat**: Komponen reusable `app/Views/components/pagination.php` diterapkan di semua halaman daftar (Email, PNS, PPPK, Unit Kerja, Web Monitoring, Assistance). Menghapus lebih dari 300 baris HTML redundan.

---

# [5 Maret 2026]

## Dashboard & Analitik
- **Peningkatan Metrik**: Persentase pada kartu metrik Website. Semua kartu metrik dapat diklik langsung ke tampilan terfilter.
- **Peningkatan Legenda Grafik**: Persentase pada semua legenda grafik donut (TTE Status, ASN Status, Website Status).
- **Identitas Digital & QR Code**: QR Code di halaman Detail Akun muncul saat status TTE "ISSUE". Halaman verifikasi publik (`/verifikasi/{hash}`) menggunakan hash MD5 untuk obfuskasi URL.

## Antarmuka & Navigasi
- **Migrasi Vanilla JS**: Seluruh sistem navigasi sidebar dan submenu berhasil dimigrasikan dari Alpine.js ke Vanilla JavaScript murni. Menghilangkan *layout flickering* saat pemuatan halaman.
- **Pencarian Global**: Pencarian *real-time* di header untuk Email, Nama, NIP, dan NIK. Responsif di mobile.
- **Menu User di Topbar**: Memindahkan menu "Ganti Password" dan "Logout" dari sidebar ke dropdown di topbar.

## SEO & Privasi
- **Meta Robots**: Menambahkan `noindex, nofollow` ke layout utama, halaman login, semua halaman error, dan template ekspor PDF.
- **`robots.txt`**: Menonaktifkan pengindeksan semua bagian aplikasi.

## RBAC
- **Perluasan Hak Admin**: Admin kini dapat memicu sinkronisasi cPanel & TTE, mengakses Batch Operations, membuat/mengedit akun, dan memodifikasi informasi website.
- **Pembatasan Super Admin**: Operasi hapus, Master Data Unit Kerja, dan Log Layanan dibatasi hanya untuk Super Admin.

---

# [3 Maret 2026]

## Antarmuka
- **Halaman Error**: Mendesain ulang semua halaman error (`400`, `404`, `exception`, `error.php`) agar sesuai dengan estetika "Slate Clean Government".
- **PPPK Summary**: Menambahkan ringkasan status TTE yang dikelompokkan per unit kerja induk di halaman `pppk_list.php`.
- **Sidebar**: Menambahkan tombol toggle sidebar untuk desktop dengan penyimpanan status di `localStorage`.

## Perbaikan Bug
- **SQL Errors di `pppk_list.php`**: Memperbaiki error `Not unique table/alias`, `only_full_group_by`, dan `Duplicate column name 'id'`.
- **Query Builder State**: Mengisolasi query ringkasan ke instansi model terpisah agar tidak mereset state query utama.

---

# [2 Maret 2026]

## Fitur Baru
- **Menu Pegawai di Sidebar**: Submenu PNS, PPPK Penuh Waktu, dan PPPK Paruh Waktu.
- **Paginasi**: 100 record per halaman untuk daftar PNS dan PPPK.
- **Impor Excel (Batch)**: Dukungan XLSX via `PhpSpreadsheet` untuk Batch Create, Batch Update, dan Batch PK.
- **Batch Unit Kerja**: Impor Excel untuk membuat banyak Unit Kerja sekaligus.
- **Refaktor Controller Email**: Dipecah menjadi 4 controller spesifik — `Email.php`, `EmailList.php`, `EmailExport.php`, dan `EmailApi.php`.

## Ekspor PDF
- **Standarisasi Visual**: Semua template ekspor menggunakan estetika "Slate Clean Government" berbasis `Dompdf`.
- **Format Beragam**: Mendukung ekspor PDF, CSV, dan ZIP.

---

# [21 Mei 2026]

## Fitur Baru
- **Endpoint API PPPK**: Menambahkan `api_pppk_list` dan `api_pppk_pw_list` di `EmailApi.php`.
- **Optimasi Payload API**: Menambahkan `nik` ke respons API, menghapus `user`, `bsre_status`, dan join tabel `pk`.
- **Pemindahan Riwayat Sesi**: Riwayat sesi teknis dipindahkan dari `GEMINI.md` ke `CHANGELOG.md`.
