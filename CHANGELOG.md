# Session History - 23 Juni 2026

## Fitur Baru
- **Antrean Ekspor Laporan PDF (Background Job)**:
    - Memindahkan proses *rendering* PDF berukuran besar (khususnya Detail Unit Kerja) ke antrean latar belakang (QueueWorker) untuk mengatasi kendala *504 Gateway Timeout* pada server saat mengekspor ribuan data.
    - Menambahkan tabel `export_histories` untuk mencatat riwayat dan status *export*.
    - Menambahkan antarmuka (UI) khusus "Riwayat Laporan" yang dapat diakses melalui Sidebar untuk memantau status *rendering* (PENDING, PROCESSING, COMPLETED) dan tombol *download* jika PDF sudah siap.
    - Menambahkan perintah Spark baru `queue:clean-exports` yang bertugas membersihkan entri riwayat beserta file fisik PDF yang umurnya lebih dari 3 hari.
- **CLI Sync Pegawai per Unit Kerja (`sync:pegawai-unit`)**:
    - Menambahkan perintah Spark baru `sync:pegawai-unit [unit_id] [--asn=...]` untuk melakukan sinkronisasi massal data kepegawaian (Jabatan, Pangkat, Golongan) langsung dari API Simpeg berdasarkan Unit Kerja.
    - Menggunakan fungsi batch yang sudah ada di `PegawaiSyncService`.
    - Mendukung filter status ASN secara spesifik (misal: PNS, PPPK).
- **Tukar Data Akun (Swap)**:
    - Tambah form swap data antar dua akun menggunakan dropdown dengan fitur pencarian (`Choices.js`).
    - Data yang ditukar: NIK, NIP, nama, gelar, tempat/tanggal lahir, jabatan, golongan, pangkat, unit kerja, eselon, status ASN, pimpinan, dan pensiun_at. Email tidak ikut ditukar.
    - Mencegah `Duplicate Entry` NIK/NIP saat swap dengan menggunakan nilai sementara (`temp_`) sebelum update final.
    - Menggunakan *Query Builder* langsung (bukan `Model::update()`) untuk memastikan eksekusi tidak di-*block* oleh CodeIgniter *callbacks*.
    - Audit log `SWAP_DATA` dicatat setiap eksekusi.

## Refaktor
- **Pembersihan File Redundan**:
    - Menghapus file skrip percobaan sisa pengembangan fitur Swap (`test_swap.php`).
    - Menghapus sisa *temporary file* dari proses backup database yang pernah gagal/terputus di dalam direktori `writable/backups`.
- **Generator Email Batch Create**:
    - Urutan kandidat email disederhanakan: base → tahun lahir (NIP[3-4]) → tanggal lahir (NIP[7-8]) → random 2 digit.
    - Menghapus fungsi `getNikPart()` yang redundan (sama dengan tahun lahir NIP).
    - Menghapus `getNipMonth()` dan `getNipSeq()` dari fallback, diganti random 2 digit yang lebih sederhana.
    - Password suffix sekarang mengikuti urutan kandidat yang sama secara independen (tahun lahir dulu, tanggal lahir, random).
- **Retry Password Weak di Batch Create**:
    - Backend otomatis mencoba kandidat password berikutnya jika cPanel menolak karena *weak password*.
    - Urutan retry: tahun lahir → tanggal lahir (NIP[7-8]) → random 2 digit. Suffix **diganti**, bukan ditambahkan.
    - Password yang akhirnya berhasil disimpan ke database lokal.

## Bug Fixes
- **Fix `Undefined array key "hp"`** di `swapAccountData`: Menghapus field `hp`, `masa_kerja`, `is_pensiun`, `is_operator` yang tidak ada di skema database.
- **Fix `Call to undefined function log_audit()`**: Menambahkan `require_once audit_helper.php` di `BaseController` agar fungsi tersedia secara global di semua controller.
- **Fix Swap Data Tidak Berubah**: Beralih dari `Model::update()` ke *Query Builder* langsung untuk mencegah update diam-diam yang diblokir oleh CodeIgniter model *callbacks*.
- **Fix Filter `--asn=PNS` Tidak Bekerja di CLI**: Menambahkan parsing fallback untuk format `--asn=PNS` (dengan tanda `=`) yang tidak dikenali `CLI::getOption()` di CodeIgniter 4.
- **Fix Status "Available" Palsu di Batch Preview**:
    - Menambahkan `->withDeleted()` pada semua query di `batch_check_availability` agar akun yang sudah di-*soft-delete* tetap ditandai sebagai tidak tersedia.
    - Memaksa `isAvailable = false` jika NIK atau NIP sudah ada di database, walaupun email-nya belum dipakai.
- **Fix Badge Status Preview Batch**:
    - Menambahkan badge **Existing** (oranye) untuk NIK/NIP sudah ada di DB.
    - Menambahkan badge **Duplikat** (kuning) untuk NIK/NIP duplikat dalam satu batch.
    - Tombol **Eksekusi** dinonaktifkan selama ada baris berstatus Existing, Duplikat, atau Unavailable.

---

# Session History - 22 Juni 2026


## Fitur Baru
- **Notifikasi Telegram saat Tambah 1 Akun Email Baru**:
    - Menambahkan notifikasi Telegram otomatis di `EmailService::createSingleEmail()` setelah akun berhasil dibuat.
    - Format notifikasi menggunakan `TelegramMessageBuilder` yang sudah terstandarisasi (menampilkan nama, jabatan, unit kerja, email, dan waktu pembuatan).
    - Baris yang kosong (nama/jabatan/unit kerja) otomatis disembunyikan, bukan digantikan teks *fallback*.
    - Notifikasi dibungkus `try-catch` terpisah agar kegagalan Telegram tidak mengganggu proses pembuatan akun.

## Refaktor
- **Diet Controller Website (`WebDesaKelurahan.php` & `WebOpd.php`)**:
    - Memindahkan logika *query* agregasi statistik (total, aktif, nonaktif) dan logika *sorting* distribusi platform ke dalam `WebsiteService.php` (fungsi baru: `getDesaKelurahanStats()`, `getDesaKelurahanPlatformStats()`, `getOpdStats()`).
    - Kedua *controller* kini hanya memanggil metode *service* tanpa logika *query* langsung.
- **Format Notifikasi Telegram (Baris Kosong)**:
    - Mengubah perilaku `TelegramMessageBuilder::addUserProfile()` agar baris yang nilainya kosong (nama, jabatan, unit kerja) **tidak dicetak sama sekali** (tidak lagi menampilkan teks *fallback* seperti "Tanpa Nama" atau "Jabatan Belum Diisi").
    - Berlaku untuk semua notifikasi: kuota penuh dan TTE Pimpinan *expired*.
- **Pembersihan Cache Otomatis Pasca Sinkronisasi**:
    - Menambahkan pembersihan `cache` dashboard (`dashboard_summary_data_v3` & `email_dashboard_summary`) secara otomatis di akhir eksekusi `SyncAllCommand` (setelah semua fase sinkronisasi selesai).
    - Menambahkan pembersihan `cache` di `QueueWorker` saat antrean habis (`stopWhenEmpty`), memastikan data *dashboard* selalu *real-time* setelah proses *background job* tuntas.

## Bug Fixes
- **Fix Tanggal Terakhir Sinkronisasi cPanel Tidak Berubah di Dashboard**:
    - Akar masalah #1: `SyncAllCommand::syncCpanel()` kelupaan memanggil `$this->saveLastSyncTime('last_sync_cpanel')` setelah antrean berhasil dibuat.
    - Akar masalah #2: `DashboardService` tidak mengambil kunci `last_sync_cpanel` dari database (hanya mengambil `last_sync_time` yang sudah usang).
    - Akar masalah #3: `app/Views/home/index.php` masih menggunakan variabel `$last_sync_time` (usang) untuk menampilkan tanggal cPanel, bukan `$last_sync_cpanel`.
    - Ketiga akar masalah telah diperbaiki secara bersamaan.
- **Fix `Duplicate Entry` pada Sinkronisasi cPanel (`sync_cpanel`)**:
    - Akun email yang sudah di-*soft-delete* tidak terdeteksi saat proses pengecekan `upsertBatch`, sehingga sistem mencoba memasukkan data baru dan terkena error duplikasi.
    - Diperbaiki dengan menambahkan `->withDeleted()` pada query pengecekan eksistensi, sehingga sistem akan melakukan *update* (bukan *insert*) meskipun akunnya berada di tong sampah.
- **Fix `null` Error pada Notifikasi Kuota (`sync_quota_report`)**:
    - `TelegramMessageBuilder::addUserProfile()` menerima `null` pada argumen `$name` untuk akun yang belum diisi namanya, menyebabkan *fatal error*.
    - Diperbaiki dengan sanitasi nilai `null` sebelum dikirim ke *builder*.

## Optimasi Database (Performance)
- **Migrasi Indeks Baru (`2026-06-22-140700_AddMissingIndexes`)**:
    - Menambahkan `INDEX deleted_at_idx` pada tabel `emails` untuk mempercepat semua *query* yang menggunakan fitur *soft delete* (digunakan hampir di seluruh sistem).
    - Menambahkan *composite index* `bsre_status_deleted_at_idx` pada tabel `emails` untuk mempercepat *query* dashboard TTE.
    - Menambahkan `INDEX status_idx` pada tabel `web_opd` dan `web_desa_kelurahan`.
    - Menambahkan `INDEX kecamatan_idx` pada tabel `web_desa_kelurahan` untuk mempercepat fitur filter.

## Refaktor Lanjutan (Babak 2)
- **Standarisasi Semua Notifikasi Telegram ke `TelegramMessageBuilder`**:
    - Notifikasi *Pensiun* (`mark_pensiun`): Diubah dari *raw string* ke `TelegramMessageBuilder`. NIP/NIK kembali ditampilkan (jika ada).
    - Notifikasi *Hapus Permanen dari Trash* (`forceDelete`): Diubah dari *raw string* ke `TelegramMessageBuilder`. NIP/NIK kembali ditampilkan.
    - Notifikasi *Hapus Permanen Direct* (`delete`): Diubah dari *raw string* ke `TelegramMessageBuilder`. NIP/NIK kembali ditampilkan.
    - Notifikasi *Batch Create & Update* (`sendBatchNotification`): Diubah dari *raw string* ke `TelegramMessageBuilder`. Kini menampilkan nama admin yang mengeksekusi dan *timestamp*.
    - **Aturan NIP/NIK berlaku seragam** di semua notifikasi: ditampilkan jika ada (NIP prioritas, NIK cadangan, jika kosong baris otomatis hilang).
- **Audit Log Konsisten di Semua Titik Aksi Kritis**:
    - Tambah `log_audit('CREATE')` saat buat 1 akun (`EmailApi::create_single_email`).
    - Tambah `log_audit('UPDATE')` saat edit profil (`Email::update_details`).
    - Tambah `log_audit('PENSIUN')` saat akun ditandai pensiun (`Email::mark_pensiun`).
    - Tambah `log_audit('BATCH_CREATE')` saat pembuatan akun massal (`BatchController::save_batch_create`).
    - Tambah `log_audit('BATCH_UPDATE')` saat pembaruan akun massal (`BatchController::save_batch_update`).
    - *Restore* dan *Force Delete* dari Trash sudah tercatat sejak sebelumnya.
- **Implementasi Keamanan (Rate Limiting)**:
    - Membuat filter `Throttle.php` untuk membatasi *request* berlebih (*rate limit*).
    - Dikonfigurasi dengan batas **120 *request* per menit** per alamat IP.
    - Diterapkan pada *endpoint* pencarian `email/search` dan seluruh layanan API di bawah *group* `api/v1/*` untuk mencegah eksploitasi dan *scraping* data menggunakan *bot/script*.
- **Diet Controller `Email.php` & `EmailList.php`**:
    - Memindahkan seluruh logika bisnis `update_details()` (rename cPanel, transaksi DB, sinkronisasi NIP ke akun lain) ke `EmailService::updateProfileDetails()`.
    - *Controller* `update_details()` dipangkas dari ~90 baris menjadi ~45 baris.
    - Menghapus pemanggilan `AppSettingModel` yang tidak terpakai dari `index()`.
    - Membersihkan `EmailList.php` dari *dependency injection* model yang tidak terpakai (EselonModel, StatusAsnModel).
- **Diet Controller `EmailApi.php`**:
    - Memindahkan logika pencarian email (`search`) ke `EmailService::searchEmails()`.
    - Memindahkan logika query email per unit kerja (`api_unit_emails`) ke `EmailService::getUnitEmails()`.
    - Memindahkan logika sinkronisasi data pegawai dari API eksternal (`sync_pegawai`) ke `EmailService::syncPegawaiFromApi()`.
    - `EmailApi.php` berkurang dari **437 baris** menjadi **~260 baris** — murni hanya sebagai *dispatcher* tipis.

# Session History - 16 Juni 2026

## Fitur & Refaktor
- **Integrasi Backup Database Otomatis**:
    - Menambahkan `BackupCommand.php` untuk mencadangkan database otomatis (termasuk kompresi GZIP).
    - Menambahkan fitur auto-cleanup untuk menghapus file backup yang berumur lebih dari 7 hari.
    - Menambahkan notifikasi Telegram apabila eksekusi *mysqldump* gagal atau *error*.
    - Menambahkan parameter `--no-tablespaces` pada *mysqldump* untuk mencegah error hak akses PROCESS di environment cPanel / Shared Hosting.
- **Pembaruan Alur Eksekusi Antrean (*Queue Worker*)**:
    - Memindahkan pemanggilan eksekusi `queue:work --stop-when-empty` langsung ke dalam skrip `sync.sh`. Hal ini mengefisiensikan tugas *cronjob* di sisi server agar tidak perlu dipanggil setiap menit, dan menjamin laporan hanya diproses tepat setelah *job* utama selesai.

## Debug & Fixes
- **Fix Pengiriman Laporan TTE & Kuota**:
    - Memisahkan pembuatan laporan `checkQuotaAlerts` dan `checkTteExpiredAlerts` menjadi dua pekerjaan (Job) independen (`sync_quota_report` & `sync_tte_report`) yang dijadwalkan secara pasti dalam `SyncAllCommand`.
    - Sebelumnya, laporan ini bergantung pada logika `stopWhenEmpty` yang tidak pernah terpicu jika *worker* dijalankan sebagai daemon.
    - Memperbaiki salah ketik (*typo*) variabel `$e['nama']` menjadi `$e['name']` pada *builder* pesan laporan TTE, yang sebelumnya dapat menyebabkan *worker* *crash* karena *Undefined array key*.
- **Fix Internal Server Error (HTTP 500) & Parse Error**:
    - Memperbaiki *Parse Error* pada PHP 8.1+ akibat dari sintaks *Closure* atau ekspresi *Regex* variabel `$1` yang disalahgunakan sebagai variabel di dalam namespace (*use*) berbagai *Controller*.

# Session History - 11 Juni 2026
- **Pemusnahan Enkripsi Hash (NIP/NIK)**:
    - Membatalkan dan menghapus sepenuhnya penggunaan algoritma enkripsi (AES-256) dan indeks rahasia (*blind index*) `nip_hash` & `nik_hash` dari seluruh tabel, model, layanan, dan API.
    - Semua data NIP dan NIK kini kembali menggunakan *plain text* asli sesuai permintaan, dan sistem pencarian global langsung disesuaikan menggunakan metode klausa `LIKE` biasa.
    - Mengeksekusi *Migration* untuk mendrop kolom `nip_hash` dan `nik_hash` secara permanen.
- **Refaktor Logika Pemicu Notifikasi Telegram di QueueWorker**:
    - Mengubah logika pengecekan tugas (*job*) terakhir di `QueueWorker`. Sebelumnya, pekerja menghitung sisa baris di tabel `jobs`, yang menyebabkan bug gagal lapor jika ada *phantom job* (tugas yang gagal dan ditunda ke masa depan). 
    - Logika baru merekam jenis tugas yang diproses di dalam memori PHP (Sesi Pekerja) dan memicu peringatan Telegram tepat saat pekerja akan mati (*queue is empty*), memastikan akurasi notifikasi 100%.

## Pembersihan & UI
- **Penyelarasan Judul Helpdesk**: Menyesuaikan judul halaman `HelpdeskAdminController` dari "Manajemen Tiket Helpdesk TTE" menjadi "Helpdesk Layanan" agar identik (sinkron) dengan judul menu di *sidebar*.
- **Pembersihan File Temp/Debug**: Melenyapkan 22 file *script* PHP usang yang sebelumnya digunakan untuk *debugging* pemulihan data (misal: `CheckNik.php`, `RecoverNips.php`) untuk merampingkan folder `app/Commands`.
- **Pembersihan Environment Production**: Menghapus folder `tests/`, file pengaturan `phpunit`, dan `.DS_Store` yang berserakan, untuk meringankan server *production*.

## Debug & Fixes
- **Fix Dashboard TTE Count Bug**:
    - Memperbaiki *bug* tersembunyi pada `Home.php` di mana kolom `unit_kerja_id` tidak terpanggil dalam query `SELECT`, yang menyebabkan ribuan pegawai tanpa NIP disalahartikan sebagai "NON_TTE" (Bukan Sasaran TTE), sehingga menyembunyikan status TTE Aktif mereka dari dasbor.
- **Cleanup Temporary Routes**:
    - Menghapus rute, *controller*, dan *view* sementara (`duplicate_nips` dan `ambiguous`) yang sebelumnya digunakan untuk *debugging* pemulihan data NIP.

# Session History - 10 Juni 2026

## Debug & Fixes
- **Fix 403 Forbidden pada Batch Update**:
    - Mengganti payload dari `application/json` ke `multipart/form-data` (menggunakan `FormData`) pada fungsi `create_single_email`, `batch_check_availability`, dan `batch-update-data`. Hal ini mencegah ModSecurity (WAF) dari memblokir payload JSON yang dikira berisi injeksi berbahaya saat menyimpan banyak data array.
- **Fix Queue Worker Crash (Laporan Telegram TTE)**:
    - Mengoreksi klausul pencarian dengan query builder `$jobModel->like('payload', $type)` dari sebelumnya yang menggunakan tanda kutip ganda ganda `'"type":"' . $type . '"'`. Tanda kutip ganda menyebabkan error `Unknown column 'type'` di database yang menggunakan konfigurasi `ANSI_QUOTES`.
    - Perbaikan ini memastikan bahwa fungsi `checkTteExpiredAlerts` (pengiriman notifikasi TTE pimpinan yang expired ke Telegram) dapat dieksekusi dengan lancar setelah tugas batch sinkronisasi selesai tanpa terhenti karena _crash_ SQL.
    - **Perbaikan Sinkronisasi `status_asn_id`**: Menutup celah bug pada `processBatchUpdate` di mana tabel `pk` (Perjanjian Kerja) gagal mendapatkan nilai `status_asn_id` yang baru jika pembaruan dari UI/Excel hanya mengubah status ASN tanpa mengubah data PK lainnya.
    - **Transaksi pada Batch Create**: Memperbaiki `processBatchCreate` yang sebelumnya tidak memiliki pelindung transaksi (*Database Transaction*). Sekarang, sistem akan memasukkan data ke database lokal terlebih dahulu; jika berhasil, barulah membuat akun di cPanel. Jika pembuatan cPanel gagal, maka rekaman di database lokal otomatis dibatalkan (*Rollback*) sehingga tidak ada *orphan account*.
- **Refaktor Queue Worker & Sinkronisasi Notifikasi Asinkron**:
    - Memindahkan fungsi notifikasi Telegram (`checkTteExpiredAlerts` dan `checkQuotaAlerts`) ke sebuah *service* terpusat (`AlertService`).
    - Menghapus pemanggilan laporan dari *Cron Job* (`SyncAllCommand`) yang prematur, dan memindahkannya ke dalam `QueueWorker` agar laporan dikirimkan **hanya setelah** tugas latar belakang (API calls) tersebut benar-benar tuntas.
    - Menambahkan notifikasi darurat (CRITICAL ERROR) otomatis ke grup Telegram apabila terdapat antrean tugas sinkronisasi yang gagal permanen setelah 3 kali percobaan.
    - Menambahkan logika pembaruan nama `jabatan` pada proses sinkronisasi massal data pegawai di `QueueWorker`.
- **Fix Laporan Notifikasi Telegram**: Memperbaiki fungsi *Cron Job* `SyncAllCommand` di mana fungsi pengecekan `checkTteExpiredAlerts()` (untuk mendeteksi TTE pimpinan yang *expired*) dan `checkQuotaAlerts()` (untuk peringatan limit *cPanel*) sebelumnya terdefinisi namun terlewat untuk dieksekusi. Sekarang kedua fungsi tersebut akan dipanggil secara otomatis pada sinkronisasi harian dan mingguan.
- **Refaktor Batch Update (EmailBatchService)**:
    - **Performa (*Pre-fetching*)**: Menghilangkan masalah *N+1 Query* dengan mengambil seluruh data `email` dan `pk` secara *bulk* di awal proses (memecah ke dalam *chunk* 500 baris) sebelum perbandingan data.
    - **Konsistensi Data**: Menerapkan *Database Transaction* (`transBegin`, `transCommit`, `transRollback`) sehingga jika salah satu pembaruan gagal, data tidak akan bentrok (setengah jalan).
    - **Sinkronisasi cPanel**: Mengaktifkan sinkronisasi pembaruan *password* massal langsung ke server cPanel secara *real-time*.
    - **Fix Logika Unit Kerja & Gaji**: Memperbaiki prioritas *override* unit kerja (data Excel kini tidak tertimpa pilihan dropdown antarmuka) dan memperbaiki logika filter pemisah ribuan pada nominal gaji yang memiliki akhiran desimal `.00` atau `,00`.
- **Halaman Debug NIP Ganda**: 
    - Menambahkan rute `/email/duplicate_nips` dan method `duplicate_nips` pada `EmailController` untuk melacak dan menampilkan secara khusus akun-akun pegawai yang memiliki NIP yang sama di dalam database.
    - Menambahkan antarmuka (view) bergaya "Slate Clean Government" khusus untuk menampilkan tabel akun-akun dengan NIP ganda tersebut beserta tautan cepat untuk mengeditnya.
- **Fix Local Environment Cookie**:
    - Mengubah pengaturan cookie `public bool $secure = true;` menjadi `false` pada `app/Config/Cookie.php` untuk mengatasi `SecurityException` saat login di *local environment* (pengujian HTTP biasa tanpa HTTPS).

# Session History - 9 Juni 2026

- **Fix Batch Route 404 & GET Redirect**:
    - Mengganti nama rute (`execute-update` -> `process-update`) untuk menghindari blokir WAF.
    - Menambahkan header CSRF (`X-CSRF-TOKEN`) pada pemanggilan `fetch` di sisi klien.
    - Memperbarui `baseURL` di `App.php` ke HTTPS untuk mencegah pengalihan (redirect) dari POST ke GET akibat ketidakcocokan protokol.

## Reviu Proses Batch & Unit Testing
- **Refaktor EmailBatchService**:
    - Mengimplementasikan **Dependency Injection** pada constructor `EmailBatchService` untuk mempermudah unit testing dengan memungkinkan penggunaan *mock objects* untuk `CpanelApi`, `EmailModel`, `UnitKerjaModel`, dan `PkModel`.
- **Implementasi Unit Test**:
    - Membuat suite pengujian komprehensif di `tests/app/Domains/Batch/EmailBatchServiceTest.php`.
    - Mencakup pengujian logika `processBatchUpdate` (mode Email & NIK, deteksi *no-change*) dan `processBatchCreate` (keberhasilan pembuatan & validasi duplikasi).
    - Memastikan integritas logika bisnis dengan 6 *test cases* dan 24 *assertions* yang lulus uji.
- **Optimasi & Perbaikan Logika**:
    - **Peningkatan Performa**: Memindahkan pencarian Unit Kerja ke luar *looping* pada proses batch update untuk mengurangi beban database.
    - **Robust Error Reporting**: Memperbarui penanganan error agar memberikan pesan yang lebih spesifik (seperti detail kegagalan database atau enkripsi) per baris data, menggantikan pesan umum "Gagal memproses data".
    - **Logging Detail**: Menambahkan pencatatan *stack trace* lengkap pada `BatchController` untuk memudahkan diagnosa kegagalan sistemik di lingkungan server.
    - **Normalisasi Data**: Memperbaiki perbandingan data agar lebih toleran terhadap perbedaan tipe data antara database (`null`) dan input spreadsheet (string kosong).

## Perluasan Layanan Helpdesk
- **Transformasi Portal Helpdesk**:
    - Mengalihkan fokus Helpdesk yang sebelumnya hanya untuk TTE menjadi portal bantuan terpadu untuk seluruh layanan (Website OPD, Email Resmi, TTE, Aplikasi Srikandi, dan Website Desa).
    - Memperbarui `HelpdeskPublicController` untuk mendukung pemetaan kategori dan layanan secara dinamis dari domain `Assistance`.
- **Formulir Publik Dinamis**:
    - Implementasi **Cascading Dropdowns**: Pengguna kini memilih Kategori terlebih dahulu, diikuti Layanan Spesifik, dan terakhir Jenis Kendala yang relevan (menggunakan logic yang sama dengan Log Pendampingan).
    - Perbaikan UI/UX pada `public_form.php` dengan penambahan kolom deskripsi detail kendala dan penyesuaian teks agar lebih inklusif untuk seluruh layanan TIK.
- **Otomasi Integrasi Admin**:
    - Memperbarui `HelpdeskAdminController` agar tiket yang diselesaikan secara otomatis dicatat ke Log Pendampingan (`Assistance`) dengan kategori dan layanan yang sesuai dengan pilihan pengguna, bukan lagi hardcoded sebagai TTE.
- **Peningkatan Aksesibilitas**:
    - Memperbarui tautan bantuan di halaman Login dari "Butuh Bantuan TTE?" menjadi "Butuh Bantuan Layanan?" untuk mencerminkan perluasan cakupan layanan.
- **Penyederhanaan Formulir**:
    - Menghapus field `deskripsi_kendala` dari database dan formulir helpdesk untuk mempercepat proses pelaporan bagi pengguna.
- **Fitur Manajemen Admin Helpdesk**:
    - Menambahkan fitur hapus permohonan pada panel admin helpdesk (tersedia di halaman index dan detail).
    - Implementasi proteksi konfirmasi sebelum penghapusan dan pencatatan aksi hapus ke dalam Audit Trail.

## Perbaikan Bug & Optimasi
- **Fix Password Edit**: Memperbaiki error `Undefined array key "success"` saat mengubah password dengan menyesuaikan pengecekan status respon dari cPanel API.
- **Fix Batch Operations JS**: Memperbaiki error `Cannot read properties of undefined (reading 'length')` pada proses Batch Update dan Batch PK dengan menambahkan validasi respon server dan penanganan error yang lebih kokoh pada sisi klien.
- **Fix Batch Route 404**: Memperbaiki kesalahan `404 Not Found` pada rute batch dengan mengubah pemanggilan URL menjadi relatif dan mengganti nama rute (`execute-update`) guna menghindari konflik penamaan atau blokir WAF pada server.
- **Fix Batch Payload Sync**: Memperbaiki sinkronisasi data pada proses Batch Update di mana data bergeser (index mismatch) akibat penghapusan baris kosong pada identifier. Sistem kini mewajibkan identifier di setiap baris dan memastikan pemetaan kolom tetap konsisten.

# Session History - 8 Juni 2026

## Perbaikan Fitur Pencarian & Keamanan Data
- **Optimasi Pencarian NIK/NIP pada Data Terenkripsi**:
    - Memperbaiki kegagalan pencarian NIK dan NIP di seluruh sistem (Pencarian Global, Dashboard, Unit Kerja, dan Export).
    - Implementasi **Normalisasi Query**: Sistem kini secara otomatis membersihkan karakter non-numerik (spasi, titik, tanda hubung) dari input pencarian sebelum diproses.
    - Implementasi **Blind Index Search**: Mengalihkan pencarian NIK/NIP dari `LIKE` query (yang tidak kompatibel dengan enkripsi) ke pencocokan tepat menggunakan `nik_hash` dan `nip_hash`.
    - **Pencarian Multi-Kriteria**: Memastikan pencarian berdasarkan Nama dan Email tetap berjalan secara paralel meskipun input berupa angka NIK/NIP.
- **Konsistensi Layanan**:
    - Pembaruan logika pencarian pada `EmailApi`, `EmailService`, `EmailList`, `PimpinanController`, dan `EmailExportService`.
- **Perbaikan Dekripsi Tampilan Data**:
    - Memperbaiki bug di mana NIK dan NIP ditampilkan dalam bentuk *hash* pada halaman Detail Akun dan file Ekspor (PDF/Excel).
    - Mengoreksi fungsi *callback* `decryptData` di `EmailModel` agar dapat mendeteksi dan mendekripsi data *single result* maupun *multiple results* secara akurat (menggunakan identifikasi `singleton`).
    - Menghapus penggunaan `allowCallbacks(false)` yang tidak semestinya pada `EmailExportService` agar proses dekripsi data tetap berjalan saat mengunduh dokumen.

# Session History - 5 Juni 2026

## Otomatisasi & Perintah CLI Baru
- **Perintah Sinkronisasi TTE Per Unit**:
    - Penambahan custom command `php spark sync:tte-unit {unit_id}` untuk sinkronisasi status TTE secara manual dan spesifik per unit kerja.
    - **Eksekusi Langsung**: Berbeda dengan `sync:all`, perintah ini memproses data secara sekuensial dan sinkron (tanpa antrean) untuk memberikan umpan balik *real-time* di terminal.
    - **Fitur Validasi**: Menyediakan pengecekan keberadaan ID Unit Kerja dan pelaporan statistik (Berhasil/Gagal) setelah proses selesai.

# Session History - 4 Juni 2026

## Keamanan Data, Antrean Tugas, & Pemantauan Sistem
- **Enkripsi Data Sensitif & Blind Index**:
    - Implementasi enkripsi dua arah (AES-256) untuk kolom `nik`, `nip`, dan `password` di `EmailModel.php`.
    - Menambahkan fitur *Blind Index* (Hashing SHA-256) pada kolom `nik_hash` dan `nip_hash` untuk memungkinkan pencarian data sensitif secara cepat tanpa perlu mendekripsi seluruh database.
    - Migrasi kueri pencarian pada `EmailService` dan `EmailApi` untuk menggunakan hash guna meningkatkan performa dan keamanan.
- **Sistem Antrean (Job Queue)**:
    - Migrasi sinkronisasi berat (cPanel, TTE Status, dan Data Pegawai) dari proses sinkron menjadi berbasis antrean (*Queued Jobs*).
    - Penambahan `JobModel` dan tabel `jobs` untuk manajemen antrean tugas latar belakang.
    - Pembaruan `SyncAllCommand.php` untuk mendispatch tugas ke antrean dalam bentuk *chunk* kecil guna mencegah beban server berlebih.
- **Pemantauan Kesehatan Sistem (Health Check)**:
    - Penambahan `SystemHealthService` untuk memantau status konektivitas layanan eksternal (cPanel UAPI, BSrE API, dan Pegawai API).
    - Integrasi widget *real-time health check* pada Dashboard utama.
- **Peningkatan Audit Trail**:
    - Penambahan ringkasan statistik aksi dan entitas pada halaman Audit Log untuk memudahkan pengawasan aktivitas sistem.
- **Standarisasi UI, SEO & Perbaikan Metadata**:
    - Implementasi SEO Best Practices: Penambahan meta tag dinamis (title, description, robots, canonical URL) dan dukungan Open Graph/Twitter Card di seluruh halaman utama.
    - Penyesuaian meta title pada halaman Detail Unit Kerja agar konsisten dengan struktur navigasi global.
    - Fix Meta Title Conflict: Menonaktifkan `saveData` pada komponen modal untuk mencegah judul modal menimpa judul halaman utama.
    - Redesain Dashboard: Merapikan section "Terakhir Sinkronisasi" dan "Layanan Eksternal" dengan tata letak grid, ikon kategori, dan status badge (pill) yang lebih modern.
    - Pembersihan kode korup dan perbaikan logika penghapusan otomatis data pensiun yang telah melewati batas 30 hari.
- **Perbaikan Bug Kritis & Refactoring**:
    - **Optimasi Verifikasi Publik**: Migrasi kueri verifikasi identitas ke *Blind Index* (`nik_hash`) untuk performa maksimal (O(1)) dan memperbaiki rute `/verifikasi` yang sebelumnya tidak berfungsi.
    - **Fix Encrypted Queries**: Memperbaiki semua kueri pencarian identitas di `EmailApi`, `User`, dan `QueueWorker` agar menggunakan hash untuk mendukung data yang terenkripsi.
    - **Integrasi Transaksi Database**: Implementasi `transStart()` pada pembaruan profil untuk menjamin integritas data antara cPanel dan database lokal.
    - **Keamanan AJAX**: Penambahan proteksi CSRF pada semua operasi sinkronisasi massal berbasis AJAX.
    - **Soft Delete Fix**: Memperbaiki kegagalan pemulihan (*restore*) akun dari sampah dengan akses langsung ke query builder.

# Session History - 3 Juni 2026

## Perbaikan Bug & Optimasi Otomatisasi
- **Optimasi Sinkronisasi cPanel (Mingguan)**:
    - Memperbaiki kegagalan *timeout* pada sinkronisasi mingguan dengan menaikkan batas waktu HTTP request di `CpanelApi.php` dari 300 detik menjadi 1800 detik, mengantisipasi besarnya waktu kalkulasi disk untuk 7.500+ akun email di server cPanel.
    - Mengimplementasikan proses *Chunking* pada metode `upsertBatch` di `EmailModel.php`. Data ribuan email kini dipecah menjadi kelompok (chunk) berisi maksimal 500 baris sebelum diproses ke database untuk mencegah error `Query too large` atau terputusnya koneksi MySQL karena beban kueri `WHERE IN` yang masif.

# Session History - 26 Mei 2026

## Integrasi & Optimasi Unit Kerja
- **Sinkronisasi ID Unit Eksternal**:
    - Menambahkan kolom `api_unit_id` pada tabel `unit_kerja` untuk menyimpan referensi ID dari API `apps.sinjaikab.go.id`.
    - Implementasi perintah Spark `sync:unit-api` untuk pemetaan otomatis dengan tingkat akurasi 100% (43 unit utama).
- **Dukungan Hierarki API**:
    - Endpoint `/api/v1/unit/{id}` kini mendukung pengambilan data secara rekursif (termasuk sub-unit seperti sekolah dan puskesmas).
    - Pegawai di sub-unit secara otomatis mewarisi `api_unit_id` dari unit induk dalam respon JSON.
- **Filter Query Dinamis**:
    - Menambahkan dukungan parameter filter via URL pada API Gateway (`name`, `email`, `nip`, `nik`, `jabatan`, `bsre_status`, dan `api_unit_id`).
- **Standarisasi UI & Modal**:
    - Implementasi komponen reusable `modal.php` dengan standarisasi layering Z-Index.
    - Pembaruan dokumentasi interaktif di `/api-docs` yang mencakup daftar ID Unit Kerja dan panduan parameter filter.
- **Pemeliharaan Data**:
    - Implementasi perintah `maintenance:unit-duplicates` untuk penggabungan data unit ganda dengan tetap mempertahankan integritas nama lokal.
    - Implementasi perintah `maintenance:unit-uppercase` untuk standarisasi format penulisan.

# Session History - 24 Mei 2026

## Perbaikan Bug & Refaktor
- **Sinkronisasi API cPanel**:
    - Memperbaiki kesalahan "3 : Bad URL" dengan refaktor konstruksi URL yang lebih robust (pembersihan protokol/port otomatis).
    - Menjamin isolasi request menggunakan instansi `CURLRequest` baru untuk setiap panggilan API.
- **Koreksi Filter TTE (Mutually Exclusive)**:
    - Melakukan refaktor mendalam pada logika filter status TTE di `EmailService`.
    - Memastikan status `ISSUE`, `EXPIRED`, `NOT_SYNCED`, dll. hanya menampilkan akun wajib TTE.
    - Menghilangkan masalah "kebocoran data" dimana akun NON_TTE muncul di kategori filter lain.

## Fitur Baru & Optimasi
- **API Gateway Login**:
    - Implementasi gateway otentikasi eksternal terintegrasi dengan `apps.sinjaikab.go.id`.
    - **Otentikasi Ganda**: Sistem memprioritaskan validasi kredensial (NIP & Password) ke API eksternal, dengan mekanisme *fallback* ke password lokal untuk menjamin reliabilitas akses.
    - **Pendaftaran User Cerdas**: Admin kini cukup memasukkan NIP saat mendaftarkan administrator baru; sistem secara otomatis memvalidasi data dan menarik nama lengkap pegawai via API.
    - **Login Tanpa Password Lokal**: Kolom password pada tabel user kini bersifat opsional (nullable). Untuk pendaftaran user berbasis NIP, sistem tidak lagi men-generate password acak, melainkan membiarkannya kosong karena otentikasi sepenuhnya dialihkan ke API Gateway eksternal.
- **Otomatisasi Akun Pensiun**:
    - Fitur **"Tandai Pensiun"** manual: Penangguhan akses login cPanel instan + Pembersihan data identitas/kedinasan otomatis dari database.
    - **Pembersihan Permanen**: Fase 5 otomatis yang menghapus akun setelah masa tunggu aman 30 hari.
- **Standarisasi Notifikasi Telegram**:
    - **Laporan Batch**: Notifikasi audit trail instan setelah operasi Batch Create/Update selesai.
    - **Detail Komprehensif**: Semua alert (Kuota, TTE, Pensiun) kini menyertakan Nama, NIP, Jabatan, dan Unit Kerja yang sinkron.
    - **Pemisahan Statistik**: Laporan TTE kini membedakan angka statistik global dengan detail prioritas pimpinan.
- **Transparansi Dashboard**:
    - Menambahkan panel status sinkronisasi multi-modul di Dashboard utama (cPanel, TTE, Pegawai, Website).
    - Mempermudah Admin memantau "kesegaran" data di seluruh sistem secara terpusat.
- **Optimasi TTE Pimpinan**:
    - Membatasi sinkronisasi harian hanya untuk akun **Pimpinan** dan **Pimpinan Desa** guna efisiensi maksimal kuota API BSrE.

## Pembersihan Sistem
- **Standardisasi NON_TTE**: Mengubah seluruh label dan kunci "NON-TTE" menjadi **"NON_TTE"** untuk konsistensi penamaan sistem.
- **Penghapusan Fitur Tren**: Menghapus grafik tren pertumbuhan dan tabel `email_stats_history` untuk fokus pada penyajian data real-time yang akurat.

# Changelog

Semua perubahan penting pada proyek ini akan dicatat dalam berkas ini.

Format didasarkan pada [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), dan proyek ini mengikuti pola versi rilis internal.

## [Unreleased] - (Status Saat Ini berdasarkan README.md)

### Added
- **Optimasi Batch Create**: 
  - Implementasi endpoint batch check (`/user/batch_check_availability`) untuk verifikasi NIK, NIP, dan ketersediaan email dalam satu operasi database.
  - Refaktor logika preview 'Batch Create' untuk menggunakan batch check, mengurangi request jaringan dari ratusan panggilan sekuensial menjadi satu request yang dioptimalkan.
  - **Multi-Candidate Batch Check**: Menyiapkan 3 kandidat email sekaligus per user dan memvalidasi semuanya dalam satu request batch, menghilangkan request sekuensial saat terjadi konflik email.
  - Penambahan blok `try...catch...finally` pada JavaScript untuk mencegah antarmuka hang dan memastikan tombol selalu aktif kembali.
- **Peningkatan UI/UX Preview**:
  - **Hapus Baris**: Penambahan tombol hapus pada setiap baris tabel preview untuk kontrol data yang lebih fleksibel sebelum eksekusi.
  - **Pembersihan Nama Otomatis**: Fitur pembersihan nama dari tanda baca dan perbaikan format nama yang terpisah spasi (contoh: "A H M A D" -> "AHMAD").
  - **Sinkronisasi Live**: Perubahan nama pada tabel preview otomatis memicu pembuatan ulang email dan validasi ulang (termasuk kandidat alternatif).
  - **Password Nama Pendek**: Perbaikan logika password untuk nama di bawah 5 huruf dengan sistem pengulangan karakter (contoh: "ALI" -> "Alial") untuk memenuhi syarat keamanan.
- **Standar Alur Kerja Git**: Pembaruan `GEMINI.md` dengan urutan 4 langkah wajib: Build CSS -> Update Changelog -> Update README -> Push.
- **Otomatisasi Sinkronisasi (CLI)**: 
  - Penambahan custom command `php spark sync:all` untuk otomatisasi sinkronisasi massal.
  - Skrip mencakup sinkronisasi Akun cPanel, Status TTE BSrE, dan Data Pegawai dalam satu alur kerja.
  - Dirancang untuk dijalankan melalui Cron Job guna pembaruan data berkala tanpa intervensi manual.
- **Notifikasi Telegram**: 
  - Implementasi `TelegramLibrary` untuk pengiriman laporan otomatis ke Channel atau Grup Telegram.
  - Integrasi notifikasi pada skrip sinkronisasi: mengirim pesan saat proses dimulai dan ringkasan statistik detail (jumlah berhasil/gagal/tetap) saat proses selesai.
  - Mendukung konfigurasi bot token dan chat ID secara aman melalui file `.env`.
- **Indikator Waktu Sinkronisasi Spesifik**:
  - `SyncAllCommand` kini mencatat waktu penyelesaian secara individual untuk setiap fase (`last_sync_tte`, `last_sync_pegawai`, `last_sync_website`).
  - Penambahan tampilan "Terakhir Sync" di Dashboard utama.
  - Penambahan informasi waktu sinkronisasi khusus (TTE & Pegawai) pada halaman Detail Akun Email.
  - Penambahan informasi waktu sinkronisasi masa aktif domain pada halaman Website Desa & Kelurahan.
- **Peningkatan Analitik & Monitoring**:
  - **Grafik Tren Pertumbuhan**: Fitur ini telah dihapus karena keterbatasan API eksternal dalam menyediakan data historis yang akurat (tanggal pembuatan akun).
  - **Alert Otomatis Telegram**: 
      - Integrasi pengecekan kuota pada sinkronisasi cPanel; mengirimkan peringatan instan ke Telegram jika penggunaan disk akun mencapai >= 90%. Laporan kini menyertakan data lengkap (Nama, NIP, dan Unit Kerja) untuk mempermudah identifikasi pengguna.
      - Penambahan notifikasi otomatis untuk akun dengan status TTE 'EXPIRED' setelah proses sinkronisasi TTE selesai. Notifikasi kini membedakan antara **statistik total seluruh akun** yang expired dengan **detail identitas lengkap khusus Pimpinan**, guna memberikan gambaran beban kerja sekaligus menjaga fokus pada posisi strategis.
      - Implementasi peringatan otomatis untuk domain website Desa/Kelurahan yang akan kadaluwarsa (sisa aktif <= 30 hari) guna memastikan kontinuitas layanan publik di tingkat desa.
- **Standardisasi Konfigurasi (.env)**:
    - Melakukan audit dan penyelarasan seluruh variabel lingkungan menggunakan format `UPPER_SNAKE_CASE` yang konsisten.
    - Memindahkan endpoint Pegawai API dari kode program (*hardcoded*) ke file `.env` untuk kemudahan pemeliharaan dan keamanan.
- **Pembersihan Sistem (Cleanup)**:
    - Menghapus fitur Gemini AI secara menyeluruh karena ketergantungan pada API Key pihak ketiga yang bersifat opsional.
    - Menghapus kolom `se_status` dari tabel `users` karena sudah tidak relevan dengan alur otentikasi baru.
    - Menghapus tabel database `email_stats_history` dan model terkait.
    - Menghapus seluruh logika pencatatan data statistik harian dan visualisasi grafik tren di Dashboard.
- **API Gateway (v1)**:
  - Implementasi *API Gateway* terpusat dengan dukungan otentikasi ganda: *Bearer Token* (untuk integrasi sistem) dan *Session-based* (untuk akses browser pengguna terdaftar).
  - **Daftar Endpoint Terstandarisasi**:
    - `GET /api/v1/emails`: Daftar seluruh email aktif.
    - `GET /api/v1/pns`: Daftar akun pegawai PNS.
    - `GET /api/v1/pppk`: Daftar akun pegawai PPPK Penuh Waktu.
    - `GET /api/v1/pppk-pw`: Daftar akun pegawai PPPK Paruh Waktu.
    - `GET /api/v1/unit/{id}`: Daftar akun berdasarkan ID Unit Kerja.
  - **Optimasi Payload API**: Menambahkan field `nik` dan menghapus field `humandiskquota` serta `humandiskused` untuk efisiensi data.
  - **Halaman Dokumentasi Interaktif**: Penambahan halaman `/api-docs` yang dapat diakses oleh seluruh pengguna untuk melihat panduan integrasi dan contoh respons JSON.
  - **Unit Kerja ID Modal**: Penambahan modal interaktif dengan fitur pencarian cepat untuk mempermudah pencarian ID Unit Kerja pada halaman dokumentasi API.
- **Manajemen Email & Akun**: 
  - Integrasi UAPI cPanel untuk sinkronisasi, pembuatan akun, dan reset kata sandi secara real-time.
  - Kategorisasi spesifik untuk entitas PNS, PPPK (Penuh Waktu), dan PPPK (Paruh Waktu).
  - Sinkronisasi otomatis data Jabatan, Pangkat, dan Golongan Ruang dengan API Pegawai eksternal.
  - Verifikasi Identitas Publik menggunakan teknologi QR Code terenkripsi (Hash).
- **Integrasi TTE BSrE**: 
  - Monitoring status Sertifikat Elektronik secara real-time.
  - Sinkronisasi batch/massal untuk status TTE dengan indikator progres visual.
- **Pemantauan & Analitik**: 
  - Dashboard dinamis dengan metrik persentase dan visualisasi grafik donut.
  - Modul Pemantauan Website khusus untuk domain OPD dan Desa/Kelurahan (termasuk masa aktif SSL dan Domain).
  - Modul pencatatan Log Bantuan/Pendampingan untuk bantuan teknis.
- **Operasi Batch (XLSX)**: 
  - Pemrosesan file Excel terintegrasi menggunakan `PhpSpreadsheet`.
  - Dukungan alur kerja untuk *Batch Create*, *Batch Update*, dan *Batch PK* (Perjanjian Kerja).
  - Mekanisme deteksi cerdas untuk melompati pembaruan database jika tidak ada perubahan data ("No-Change").
- **Sistem Ekspor PDF & Data**: 
  - Tata letak PDF yang menggunakan estetika "Slate Clean Government" dan berbasis `Dompdf`.
  - Ekspor komprehensif mendukung format PDF, CSV, dan kompilasi ZIP.
- **Arsitektur & Keamanan**: 
  - Mengimplementasikan pola arsitektur **Domain-Driven Design (DDD)** (Assistance, Auth, Batch, Dashboard, Email, UnitKerja, Website).
  - Sistem *Role-Based Access Control (RBAC)* yang solid memisahkan hak istimewa Super Admin (Data Induk, Log Layanan, Hapus Data) dan Admin.
  - Privasi data ditegakkan melalui meta tag global `noindex, nofollow`.
  - Penanganan Error Global menggunakan `\Throwable` untuk menghasilkan halaman error yang profesional.
- **Antarmuka Pengguna (UI/UX)**: 
  - Standar estetika *Slate Clean Government* menggunakan Tailwind CSS.
  - Performa navigasi dan sidebar dioptimalkan dengan memigrasikan logika dari Alpine.js ke Vanilla JavaScript untuk performa kilat.

  # Session History - May 21, 2026

  ## Features Added
  - **PPPK API Endpoints**: Created `api_pppk_list` and `api_pppk_pw_list` endpoints in `EmailApi.php` to serve structured JSON data for PPPK and PPPK Paruh Waktu employees.
  - **API Payload Optimization**: Added `nik` to the API response and removed `user`, `bsre_status`, and the `pk` table join (`nomor_pk`) to optimize the database query.

  ## UI/UX Improvements
  - **Capitalize Button Refinement**: Relocated the "Huruf Kapital" utility button to sit directly next to the "Nama Lengkap" input labels across all form views (`email/create.php`, `batch/create.php`, `batch/update.php`) for better context and accessibility.
  - **Website Monitoring Auto-Scroll**: Removed the static, large progress bar from the Website Desa/Kelurahan sync interface (`web_desa_kelurahan/index.php`). Replaced it with a smooth auto-scroll mechanism (`scrollIntoView`) that dynamically highlights and tracks the specific row currently being synchronized.

  ## Documentation Improvements
  - **Three Pillars Standard**: Extracted technical session histories from `GEMINI.md` into `CHANGELOG.md` to comply with the project's new "Tiga Pilar Dokumentasi" architecture.

  # Session History - March 2, 2026

## Features Added
- **Pegawai Management**: Added a new "Pegawai" menu in the sidebar with submenus for PNS, PPPK (Penuh Waktu), and PPPK PW (Paruh Waktu).
- **Pagination**: Implemented pagination (100 records per page) for PNS and PPPK lists.
- **Excel Import (Batch)**:
    - Replaced CSV import with XLSX support using `PhpSpreadsheet`.
    - Added XLSX template downloads for Batch Create, Batch Update, and Batch PK.
    - Unified spreadsheet parsing through a generic backend handler.
    - Added support for individual `unit_kerja_id` updates in Batch Update.
- **Unit Kerja Batch**: Added Excel import functionality for creating multiple Unit Kerja records at once.
- **Unit Kerja Detail Refinements**:
    - Added a "TOTAL DATA" badge showing the total number of filtered records.
    - Improved conditional display of the "Unit Kerja" column when viewing sub-units.
    - Switched Unit Kerja display to show Child unit on top and Parent unit below.
    - Refined sorting logic: Eselon > Status ASN > (Unit Kerja if multi-unit) > Name.

## UI/UX Improvements
- **PDF Export Enhancements**:
    - Switched paper orientation to Portrait for all monitoring exports.
    - Adjusted column widths and improved layout for Desa/Kelurahan and OPD exports.
    - Added status count summaries (Total, Aktif, Nonaktif) above the tables.
    - Made domain names clickable links in the PDFs.
    - Standardized table header background colors.
    - Optimized "Pimpinan", "Akun", and "Status" PDF layouts (switching child/parent unit kerja display, adding NIP to Akun PDF).
- **Batch Forms**: Restructured textareas in Batch Update into logical pairings for better usability and increased the width of dropdown selections.

## Database & Data Integrity
- **PK Table Schema**: Added `status_asn_id` to the `pk` (Perjanjian Kerja) table.
- **Data Sync**: Synchronized `status_asn_id` from existing email records into the PK table.
- **Cleanup**: Uppercased all school names under "Dinas Pendidikan" for data consistency.
- **Duplicates Check**: Verified duplicate PK numbers in the database and provided a list of affected accounts.

## Technical Details
- Refactored `Email` controller into four specialized controllers:
    - `Email.php`: Dashboard, Index, Detail, and core mutation actions (Create, Sync, Edit Profile, Delete).
    - `EmailList.php`: Categorized lists (Unit Kerja, Eselon, PNS, PPPK).
    - `EmailExport.php`: PDF, CSV, and ZIP export actions.
    - `EmailApi.php`: API endpoints and AJAX helpers.
- Added `import_generic_spreadsheet` method to `BatchController` for flexible XLSX parsing.
- Added `status_asn_id` to `PkModel` allowed fields.
- Created `batch-update.js`, `batch-pk.js`, and `unit-kerja-batch.js` to handle specialized import logic.
- Defined a `precise_find` utility to map school names to database IDs.

# Session History - March 3, 2026

## UI/UX Improvements
- **Sync TTE**:
    - Replaced `fa-sync-alt` with `fa-fingerprint` icon for all "Sync TTE" buttons.
    - Added `scrollIntoView` behavior to all batch sync operations for better user feedback.
    - Removed individual per-row sync buttons from `pppk_list.php` and `pppk_pw_list.php` to streamline the interface.
- **Error Pages**:
    - Re-styled all standard error pages (`400`, `404`, `exception`, and a custom `error.php`) to match the global application's "slate clean government" aesthetic, ensuring a consistent user experience even on error states.
- **PPPK Summary**:
    - Implemented a summary section on the `pppk_list.php` page, showing TTE status counts grouped by parent `unit_kerja`.
    - Iteratively refined the summary's styling, content, and grouping logic based on feedback.
- **Sidebar & Layout**:
    - Enabled the sidebar toggle button for desktop screens with a full-hide behavior.
    - Implemented state persistence using `localStorage`, ensuring the sidebar remains in the user's preferred state across page reloads.
    - Optimized layout rendering by applying the sidebar state before the body renders to prevent flicker during navigation.
- **Individual TTE Sync Removal**: Removed per-row "Sync TTE" buttons from `pns_list.php` for consistency with other employee lists.

## Feature Refinements
- **Assistance Logs**: Updated the creation form to set "Online" as the default assistance method.

## Bug Fixes
- **SQL Errors**:
    - Resolved a cascade of complex SQL errors on the `pppk_list.php` page related to database queries with multiple joins and `GROUP BY` clauses.
    - Fixed "Not unique table/alias" error by refactoring summary query.
    - Fixed "only_full_group_by" incompatibility by adding `groupBy()` and using aggregate functions (`MIN`) in the `orderBy()` clause.
    - Fixed "Duplicate column name 'id'" error by refactoring the main query to be explicit and not use `select(*)`.
- **Query Builder State**: Fixed a bug where a shared query builder instance was being reset, causing the main page query to fail after the summary query was executed. Isolated the summary query to its own model instance.

## Global Design Standards
The project adheres to a **"Slate Clean Government"** aesthetic:
- **Primary Palette**: Tailwind **Slate** (bg-slate-50 for body, bg-slate-800 for sidebar, border-slate-200).
- **Typography**: Uses **Inter** font with high-contrast weights and uppercase tracking for UI labels.
- **Semantic Feedback**: Uses Emerald (Success), Red (Danger), Amber (Warning), and Blue (Info).
- **Standardized Components**: Centralized buttons in `input.css`, unified badges in `badge.php`, and standardized status color mapping in `main.php`.
- **Interactions**: Uses Alpine.js for lightweight UI logic and smooth transitions.

## Architectural Improvements
- **Controller Refactoring**: Decomposed the "fat" `Email` controller into four specialized, maintainable units: `Email.php`, `EmailList.php`, `EmailExport.php`, and `EmailApi.php`, strictly adhering to the Single Responsibility Principle.
- **Service Optimization**: Refined `AssistanceExportService` to utilize fresh query builders for each request, preventing filter bleeding and ensuring data integrity in reports.

## PDF Export System Refinement
- **Standardized Styling**: Unified all export templates (`Email`, `Pimpinan`, `Website`, `Assistance`) under the "Clean Slate Government" visual standard.
- **Layout Stability**: Migrated from float-based positioning to robust, table-based layouts, resolving blank page and alignment issues in `Dompdf`.
- **Data Richness**:
    - Added NIP and NIK columns to account and unit kerja exports.
    - Implemented a dynamic "Ringkasan Data" (Summary) section in Website and Unit Kerja exports.
    - Switched `Account Detail` export to Landscape orientation for better data density.
- **UX Improvements**:
    - Repositioned activation instructions and TTE legends for better prominence above tables.
    - Enforced fixed widths for "No." and "Status TTE" columns while allowing other data to flow flexibly.
    - Ensured footers appear consistently on every page of the generated reports.
    - Optimized data cleanliness by replacing "N/A" or "-" placeholders with empty strings for a more professional look.

## Housekeeping
- **CSS Build**: Compiled production Tailwind CSS assets.
- **Filter Fixes**: Corrected the assistance export link to properly propagate active filters (Category, Month, Year) via query strings.
- **Parse Errors**: Resolved a syntax error in `WebMonitoringExportService`.

# Session History - March 5, 2026

## Dashboard & Analytics
- **Metric Enhancements**:
    - Refactored dashboard metrics to focus on "Aktif" counts for Emails, TTE, and Websites.
    - Added percentage indicators to Website metrics (OPD and Desa/Kelurahan) for better performance tracking.
    - Improved metric card typography and layout (font sizes, rounded values, semantic colors).
    - Implemented "Click-to-Page" functionality for all dashboard metric cards, linking directly to filtered views.
- **Chart Legend Improvements**:
    - Added percentage breakdowns to all donut chart legends (TTE Status, ASN Status, Website Status, and Platform Distribution).
    - Standardized legend layouts to prevent text overflow and improve readability on all monitoring pages.

## Unit Kerja Monitoring
- **Data Richness**:
    - Added a dedicated "TTE Expired" metric card to the Unit Kerja detail page.
    - Integrated percentage displays for both "Aktif" and "Expired" TTE statuses relative to the unit's total email count.
    - Refined the visual hierarchy by using emerald/red border accents for status-critical metric cards.
- **Visual Consistency**:
    - Adjusted metric container widths for a more balanced layout.
    - Synchronized chart legend styling with the main dashboard.

## PDF Export System
- **Metric Percentages**: Integrated "Aktif" and "Nonaktif" percentages into Website Monitoring PDF exports (OPD and Desa/Kelurahan).
- **Inline Layouts**: Switched to an inline display for percentages in Unit Kerja PDF reports to match the website monitoring style and improve space efficiency.

## SEO & Privacy
- **Search Engine Indexing**:
    - Added `<meta name="robots" content="noindex, nofollow">` to the main layout (`main.php`), login page (`login.php`), and all error pages (`400`, `404`, `exception`, `production`).
    - Applied the same meta tag to all PDF export HTML templates to prevent indexing of generated reports.
    - Updated `public/robots.txt` to explicitly disallow all user agents from indexing any part of the application.

## Role-Based Access Control (RBAC) Refinements
- **Admin Role Expansion**:
    - Expanded permissions for the "Admin" role to bridge the gap between simple viewing and full management.
    - **cPanel Sync**: Admins can now trigger cPanel email synchronization.
    - **TTE Sync**: Admins can now trigger TTE status synchronization (individual and batch).
    - **Batch Operations**: Admins now have full access to Batch Create, Batch Update, and Batch PK operations.
    - **Account Mutations**: Admins can now create new accounts and edit existing profiles, passwords, and PK data.
    - **Website Monitoring**: Admins can now modify website information (Edit/Update) and sync domain expirations.
- **Super Admin Restrictions**:
    - Reserved "Delete" operations for Super Admins only to prevent accidental data loss.
    - Restricted "Master Data" (Unit Kerja management) to Super Admins.
    - Restricted "Log Layanan" (Assistance/Pendampingan) to Super Admins only, removing visibility and access for the Admin role.
- **UI/UX Consistency**:
    - Updated sidebar visibility to show/hide "Batch", "Master Data", and "Log Layanan" based on roles.
    - Adjusted buttons and action links across `index`, `detail`, `unit_kerja_detail`, and `web_monitoring` views to reflect updated permissions.

## Navigation & UX Logic Migration
- **Vanilla JS Transition**:
    - Successfully migrated the entire sidebar navigation and submenu interaction system from Alpine.js to highly-optimized **Vanilla JavaScript**.
    - Eliminated layout flickering during page loads by implementing early state detection in the `<head>` using CSS data-attribute mapping.
- **Global Omni-Search**:
    - Implemented a high-performance global search bar in the top header.
    - Real-time results: providing instant access to account details across the entire system.
    - Context-aware matching: supports searching by Email, Name, NIP, or NIK with strict URL matching for active states.
    - Mobile-responsive: adapts to screen size with specialized mobile layouts.
- **Advanced Interaction Behavior**:
    - Implemented a hybrid accordion behavior: menu headers toggle independently, but clicking any child link automatically collapses unrelated menus to maintain a clean interface.
    - Added strict URL path matching (including full support for query parameters) to ensure active states are accurately identified and reflected in the UI.
    - Implemented a robust mobile offcanvas system with a dynamic overlay and automatic body-scroll locking.
- **Accessibility & Performance**:
    - Added `aria-current="page"` and `aria-expanded` attributes for better screen reader compatibility.
    - Guaranteed zero external library dependencies for core navigation, resulting in near-instant interaction response.

## Digital Identity & Verification
- **Dynamic Identity QR Code**:
    - Added a QR code identity card to the Account Detail page that appears when TTE status is "ISSUE".
    - Enhanced the QR code with a centered logo overlay for a professional, integrated look.
    - Made the QR code clickable, linking to a new public verification route (`/verifikasi/{hash}`).
- **Secure Public Verification**:
    - Implemented a dedicated, mobile-optimized public verification view (`verifikasi.php`).
    - Obfuscated public verification URLs using secure MD5 hashes to prevent account enumeration.
    - Displays formal confirmation of digital signature ownership without exposing sensitive data (NIP/NIK).
    - Features a full-height, large card layout designed specifically for smartphone scans.

## Technical Refinements & Housekeeping
- **Batch Processing Optimizations**:
    - Optimized Batch Update and Batch PK processes to skip database writes if the incoming data is identical to the existing record.
    - Implemented robust numeric comparison for financial data (`gaji_nominal`) to handle formatting and decimal differences.
    - Improved feedback for skipped records, clearly marking them as "no changes detected" in the results log.
- **Global Error Handling**:
    - Implemented a unified error modal system in `main.php`.
    - Updated TTE synchronization logic to display detailed API failure reasons in a modal instead of basic tooltips.
    - Improved sequential processing feedback with live status counters.
- **Visual Branding**:
    - Standardized application favicon across all layouts and error pages using `logo.png`.
    - Generated a professional sidebar-themed Open Graph image for high-quality social media previews.
- **UI Cleanup**:
    - Refined `unit_kerja_detail.php`: removed redundant table headers and moved filtered data summaries to the footer for better data density.
- **Route Optimization**:
    - Refactored `Routes.php` to use cleaner group-based filters for role restrictions and added support for the new `/verifikasi` public route.
- **Code Optimization**:
    - Migrated legacy spreadsheet logic to unified XLSX handler.
    - Cleaned up redundant Alpine.js state management in favor of native ES6 logic.

# Session History - March 6, 2026

## UI/UX Improvements
- **Topbar User Menu**: Migrated the "User Management" section (Change Password, Logout) from the sidebar to a new dropdown menu in the topbar, improving accessibility and aligning with modern UI patterns.
- **Alpine.js Removal**: Replaced all Alpine.js functionality with lightweight, high-performance Vanilla JavaScript, including the new topbar dropdown and the manual input toggle on the `unit_kerja/batch_create` page.
- **Expanded Mobile Search**: Removed the mobile app logo and search toggle, integrating the global search bar directly into the main header for a more streamlined experience on smaller devices.
- **Responsive Refinements**: Adjusted the responsive layout of the Account Detail page (`email/detail.php`) to ensure proper alignment, spacing, and readability on tablet and mobile devices without altering the core design or text content.
- **Icon/Photo Cleanup**: Removed user icon placeholders ("photos") from both the global search dropdown and the Account Detail page for a cleaner, more data-focused presentation.

## Housekeeping
- **CSS Build**: Compiled production Tailwind CSS assets.

# Session History - March 26, 2026

## Architectural Improvements
- **Unified Pagination System**:
    - Created a new, reusable pagination component at `app/Views/components/pagination.php`.
    - Refactored all major listing pages (`Email`, `PNS`, `PPPK`, `Unit Kerja`, `Web Monitoring`, and `Assistance`) to utilize the centralized component.
    - Eliminated over 300 lines of redundant HTML and inline CSS from view files, ensuring a single source of truth for pagination UI/UX.
- **Standardized Data Flow**:
    - Unified the variable naming convention across Controllers and Services, strictly using `$pager` to represent the pagination state.
    - Updated `Email.php`, `EmailList.php`, `EmailService.php`, and `PimpinanController.php` to ensure consistent data delivery to the new component.

## UI/UX Refinements
- **Centralized Styling**: Moved all pagination-related CSS into the component or global build, resulting in a cleaner and more maintainable frontend codebase.
- **Responsive Pagination**: Ensured the new component maintains the high-contrast, "Slate Clean Government" aesthetic while being fully responsive across all device types.

## Housekeeping
- **Untracked Files**: Added `app/Views/components/pagination.php` to the repository.
- **CSS Cleanup**: Re-compiled `output.css` after removing redundant inline styles from multiple view files.

# Session History - March 31, 2026

## Features Added
- **Sync Data Pegawai**:
    - Implemented a comprehensive synchronization feature using the external Pegawai API (`http://apps.sinjaikab.go.id/api/pegawai/data_pegawai/`).
    - Synchronizes **Jabatan**, **Pangkat**, and **Golongan Ruang** in a single operation using the employee's NIP.
    - Added support for both individual and batch synchronization.
- **Database Expansion**:
    - Added `pangkat_nama` and `pangkat_golruang` columns to the `emails` table via a new migration.
    - Integrated these fields into the `EmailModel` and the profile update logic.
- **Pimpinan Title Standardization**:
    - Created a data migration to automatically adjust the `jabatan` field for all organizational leaders based on their unit name (e.g., KEPALA DINAS, CAMAT, LURAH).
    - Specifically updated the Sekretaris Daerah title.
    - Updated the synchronization logic to **skip** updating the `jabatan` field for accounts marked as `pimpinan`, ensuring these official titles are not overwritten by generic API data.

## UI/UX Improvements
- **Refined Detail View**:
    - Redesigned the "Kepegawaian" section on the Account Detail page to explicitly show Rank (Pangkat) and Grade (Golongan Ruang) in a structured grid.
    - Restored missing badges for **Eselon** and **Golongan (PPPK)** for a more complete profile.
    - Implemented **Conditional Visibility**: Rank and grade fields are now dynamically shown or hidden based on the Status ASN (PNS, PPPK, or Paruh Waktu) to ensure data relevance.
    - Repositioned the "Sync Pegawai" action to the main Profil card header for better accessibility and grouping with the "Edit Profil" action.
- **Dynamic Edit Form**:
    - Updated the "Edit Profil" form with real-time JavaScript to toggle field visibility based on Status ASN selection, improving data entry accuracy.
- **Simplified Unit Kerja Actions**:
    - Consolidated multiple export and sync buttons on the Unit Kerja detail page into logical dropdown menus (Export, Batch PK, and Sync).
    - This declutters the header and prevents layout wrapping on smaller screens.
- **Standardized Loading Feedback**:
    - Unified row-level loading indicators with animated "SYNCING" badges during batch operations.
    - Implemented live progress counters (e.g., `PEG: 5/20`) on the main dropdown buttons during active synchronization.

## Robust Error Handling
- **Global Throwable Refactor**:
    - Refactored over 30 `catch` blocks across Controllers, Services, and Libraries to use `\Throwable` instead of `\Exception`.
    - This ensures that all types of PHP errors (including missing database columns, TypeErrors, and logic errors) are correctly caught and rendered within the application's themed error page instead of falling back to default server error screens.
- **Defensive Rendering**:
    - Added null coalescing (`??`) to all displays of the newly added rank fields to prevent application crashes during the migration transition period.

## Refactoring & Naming
- **Standardization**: Renamed all instances of "Sync Jabatan" to "Sync Data Pegawai" across the entire codebase (routes, methods, and JS functions) to accurately reflect the expanded scope of the feature.
- **Casing Policy**: Standardized the `jabatan` (position) field to use **Uppercase** formatting across all views, PDFs, and database storage for institutional consistency.

# Session History - April 3, 2026

## Features Added
- **Batch Sync Data Pegawai**:
    - Implemented batch synchronization functionality for "Data Pegawai" across all employee lists (PNS, PPPK, PPPK PW).
    - Added a "Sync Pegawai" button to list headers that iterates through all records with a NIP on the current page.
- **Advanced Filtering**:
    - Introduced a "Filter NIP" dropdown on employee listing pages, allowing administrators to filter records by "With NIP", "Without NIP", or "All".
- **API Logic Refinement**:
    - Optimized the `sync_pegawai` API handler to skip updating the `jabatan` field if the API response contains "PLT" (Acting) to prevent overwriting primary roles.
    - Added an explicit check to return a failure status (`success: false`) if the Pegawai API returns an empty data set, ensuring these cases are correctly logged as "FAILED" or "NO DATA" in batch operations instead of silently succeeding.
    - Standardized "Sekretaris" position titles: any position containing "SEKRETARIS" is now automatically simplified to "SEKRETARIS DINAS", "SEKRETARIS BADAN", "SEKRETARIS KECAMATAN", or "SEKRETARIS KELURAHAN" based dynamically on the employee's assigned `unit_kerja` rather than guessing from the API string.
    - **Automated Eselon Assignment**: Replaced the hardcoded Eselon mapping logic with dynamic assignment. The system now extracts the `jabatan_jenis_eselon` field directly from the Pegawai API response, formats it, and automatically matches it with the internal `eselon` database table (e.g., syncing "III.a" as `IIIa`).
    - **Paruh Waktu Optimization**: The system now completely skips the external Pegawai API call for employees with the status "PPPK PARUH WAKTU", returning early with a graceful success state to conserve API resources and protect data integrity. Additionally, the "Sync Pegawai" button has been removed from the Paruh Waktu list page and the individual account detail page for these users.
- **Database Standardization**:
    - Created a migration to update `nama_eselon` in the `eselon` table from numeric-alpha format (e.g., "3a", "4b") to standard Roman numerals ("IIIa", "IVb") for professional consistency and better alignment with API response formatting.

## UI/UX Improvements
- **Standardized Sync Interface**: Simplified the "Sync" interface on the Pimpinan Desa page to match the Pimpinan page, replacing the dropdown with a single "Sync TTE" button for better consistency.
- **Improved Batch Feedback**: Updated the batch sync UI across list pages to display a specific amber "NO DATA" badge when the API returns no profile data, providing clearer distinction from system failures.

## Documentation Improvements
- **README Overhaul**: Rewrote and simplified the project's `README.md` title to **"Sistem Identitas Digital"**, providing content in both English and Bahasa Indonesia that accurately reflects the current domain-driven architecture, tech stack, and comprehensive feature set.
- **Session History Persistence**: Updated `GEMINI.md` with the latest session history to maintain a clear audit trail of project evolution.

## Technical Auditing
- **Architecture Review**: Conducted a comprehensive review of the project's domain-driven structure, service layer patterns, and frontend optimizations (Vanilla JS transition, CSS data-attribute mapping).
- **Security & Integrity Validation**: Verified RBAC enforcement, secure public verification routes, and defensive data synchronization logic.
