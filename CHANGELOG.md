# Riwayat Perubahan — Sistem Identitas Digital Sinjai

Semua perubahan penting pada proyek ini dicatat di berkas ini.
Format mengacu pada [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

# [31 Agustus 2026] — Integrasi Jabatan Plt (Pelaksana Tugas) & Normalisasi Nama Jabatan

- **Manajemen & Penanganan Jabatan Plt**:
  - Menambahkan kolom `jabatan_plt` dan `unit_kerja_plt_id` pada tabel `emails` melalui migrasi [`2026-08-31-194100_AddJabatanPltToEmails.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Database/Migrations/2026-08-31-194100_AddJabatanPltToEmails.php).
  - Mengintegrasikan resolusi tugas Plt pada [`PegawaiApi.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Shared/Libraries/PegawaiApi.php) dan [`EmailService.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Domains/Email/Services/EmailService.php) dengan mekanisme auto-detect lintas unit (`getAllPltAssignments`) agar pegawai yang profil perorangannya tercatat definitif tetap otomatis terdeteksi jika memiliki tugas Plt di OPD lain.
  - Menstandarisasi penulisan nama jabatan Plt pimpinan secara ringkas tanpa pengulangan nama instansi/OPD (contoh: `PLT. KEPALA DINAS`, `PLT. KEPALA BADAN`, `PLT. CAMAT`, `PLT. LURAH`, `PLT. DIREKTUR`).
- **Integrasi Antarmuka & Dokumen Export**:
  - Menampilkan informasi ganda jabatan definitif dan penugasan Plt beserta kartu unit kerja masing-masing pada halaman Detail Akun ([`detail.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Views/email/detail.php)).
  - Menampilkan pegawai Plt pada tabel Web Detail Unit Kerja ([`unit_kerja_detail.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Views/email/unit_kerja_detail.php)) dan Dokumen Export PDF ([`unit_kerja_pdf.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Views/email/exports/unit_kerja_pdf.php) & [`account_detail_pdf.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Views/email/exports/account_detail_pdf.php)) di unit kerja Plt tujuan.
- **Aset & Antarmuka**:
  - Membangun dan mengompilasi ulang berkas CSS Tailwind (`npm run build`).

---

# [31 Agustus 2026] — Integrasi Indikator BUP pada Halaman Detail Akun

- **Tampilan Indikator BUP pada Detail Akun (`detail.php`)**:
  - Menempatkan badge ringkas BUP di samping nomor NIP pada kartu Profil bagian Kepegawaian (`BUP [Usia] • TMT [Tanggal Singkat]`).
  - Indikator otomatis muncul khusus untuk ASN yang $\le 1$ tahun menuju TMT pensiun atau telah mencapai BUP.

---

# [31 Agustus 2026] — Standarisasi Logika & Frasa Ringkas Filter BUP Pegawai ASN

- **Penyederhanaan Frasa & Indikator BUP**:
  - Menyederhanakan opsi filter dropdown BUP menjadi 2 pilihan spesifik: **`BUP < 1 Tahun`** dan **`Mencapai BUP`** pada seluruh halaman ASN ([`pns_list.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Views/email/pns_list.php), [`pppk_list.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Views/email/pppk_list.php), [`pppk_pw_list.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Views/email/pppk_pw_list.php)).
  - Menyertakan tanggal TMT langsung di dalam badge BUP (`BUP [Usia] • TMT [Tanggal]`) dan menyederhanakan teks tooltip saat hover menjadi frasa ringkas (*to the point*): `Sisa X Bulan` atau `Mencapai BUP`.
  - Mengoptimalkan logika query SQL pada [`EmailService.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Domains/Email/Services/EmailService.php) untuk penghitungan dan penyaringan BUP berbasis database.

---

# [31 Agustus 2026] — Integrasi Indikator & Filter BUP pada Halaman Pegawai ASN

- **Indikator & Filter BUP pada Daftar Pegawai (PNS, PPPK, PPPK Paruh Waktu)**:
  - Memindahkan indikator BUP ke 3 halaman pegawai ([`pns_list.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Views/email/pns_list.php), [`pppk_list.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Views/email/pppk_list.php), [`pppk_pw_list.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Views/email/pppk_pw_list.php)) dan meletakkannya di samping nomor NIP/identitas.
  - Menambahkan toolbar filter lengkap (Pencarian, Filter BUP, Status TTE, Tombol Filter & Reset) pada ketiga halaman pegawai dengan susunan 1 baris grid yang simetris (4-3-3-2 kolom).
  - Memperbarui method `getAsnList` pada [`EmailService.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Domains/Email/Services/EmailService.php) dan controller [`EmailListController.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Domains/Email/Controllers/EmailListController.php) untuk memproses pencarian teks dan filter status BUP di tingkat database.
  - Mengembalikan tampilan kolom tabel Detail Unit Kerja ([`unit_kerja_detail.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Views/email/unit_kerja_detail.php)) ke format standar.
- **Aset & Antarmuka**:
  - Membangun dan mengompilasi ulang berkas CSS Tailwind (`npm run build`).

---

# [31 Agustus 2026] — Tampilan Indikator BUP & Perapian Filter Detail Unit Kerja

- **Indikator Batas Usia Pensiun (BUP) di Tabel Unit Kerja**:
  - Menambahkan fungsi helper `hitungBupInfo()` pada [`TanggalHelper.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Shared/Helpers/TanggalHelper.php) untuk menghitung usia BUP (58/60/65 tahun), tanggal lahir, TMT pensiun, dan sisa waktu pensiun bagi semua ASN.
  - Menampilkan badge adaptif BUP di kolom *Jabatan / Status* pada [`unit_kerja_detail.php`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Views/email/unit_kerja_detail.php) khusus bagi ASN yang $\le 1$ tahun menuju TMT pensiun (Badge Amber) atau telah mencapai masa pensiun (Badge Rose).
- **Penyederhanaan & Perapian Grid Filter Toolbar**:
  - Menghapus filter dropdown Sub Unit dan menyelaraskan seluruh kontrol (Pencarian, Status ASN, Status TTE, Password, Tombol Filter & Reset) ke dalam 1 baris grid (12 kolom) yang rapi.
- **Aset & Antarmuka**:
  - Membangun dan mengompilasi ulang berkas CSS Tailwind (`npm run build`).

---

# [31 Agustus 2026] — Fitur Kebijakan Retensi Log 90 Hari (Database & Filesystem)

- **Kebijakan & Perintah Pembersihan Retensi Log**:
  - Menambahkan Spark CLI Command baru: `php spark audit:clean [--days=90] [--dry-run]` untuk membersihkan rekaman riwayat `audit_logs` pada basis data serta berkas log `writable/logs/log-*.log` pada filesystem yang telah melewati batas umur simpan (default: 90 hari / 3 bulan).
  - Menambahkan method `countOldLogs` dan `purgeOldLogs` pada [`AuditLogModel`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Shared/Models/AuditLogModel.php).
  - Mengintegrasikan fungsi pembersihan retensi log secara otomatis ke dalam siklus cron job harian pada [`SyncAllCommand`](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Commands/SyncAllCommand.php).

---

# [31 Agustus 2026] — Standarisasi Teks Log Audit & Penyelarasan Notifikasi Telegram

- **Standarisasi Log Audit (*Audit Trail*)**:
  - Menyeragamkan seluruh string teks `log_audit()` ke format Bahasa Indonesia yang ringkas, padat, dan *to the point* pada aksi Pensiun, Auto Pensiun, Buat Akun, Ubah Password, Pulihkan Akun, Hapus Permanen, Login/Logout, serta Helpdesk.
- **Penyelarasan Notifikasi Telegram Admin**:
  - Memperbaiki helper `TelegramMessageBuilder::addUserProfile()` agar nomor identitas NIP selalu dicetak konsisten di bawah nama pemilik akun.
  - Menyeragamkan judul dan format notifikasi monitoring: `KUOTA EMAIL (>90%)`, `TTE PIMPINAN EXPIRED`, dan `DOMAIN EXPIRED (<30 HARI)`.
  - Menghapus subjudul duplikatif pada pesan alert sistem agar tampilan pesan di Telegram lebih bersih, padat, dan langsung menampilkan rincian data.
  - Menyelaraskan seluruh judul aksi siklus akun: `AKUN BARU DIBUAT`, `AUTO PENSIUN`, `AKUN PENSIUN`, `AKUN DIPULIHKAN`, `AKUN DIHAPUS PERMANEN`, `PEMBERSIHAN KOTAK SAMPAH`, `BACKUP DATABASE GAGAL`, dan `ANTREAN EKSPOR GAGAL`.

---

# [31 Agustus 2026] — Penyederhanaan Tampilan Kotak Sampah & Kompilasi CSS

- **Penyelarasan & Penyederhanaan Antarmuka Kotak Sampah (`trash.php`)**:
  - Menyelaraskan tata letak tabel dengan menambahkan kolom **No.** font mono, kolom **Akun** (Email, Nama, NIP), kolom **Unit Kerja** (Instansi Induk & Sub-Unit), kolom **Tgl Dihapus** (Tanggal & Badge Hitung Mundur Sisa Hari Ringkas), serta kolom **Aksi**.
  - Menyederhanakan copy teks (*microcopy*) agar lebih ringkas dan konsisten dengan halaman tabel lainnya di sistem.
  - Membangun dan mengompilasi ulang aset CSS Tailwind (`npm run build`).

---

# [31 Agustus 2026] — Retensi Data NIP & Unit Kerja pada Akun Pensiun di Kotak Sampah

- **Retensi Data NIP & Unit Kerja saat Pensiun/Soft Delete**:
  - Mempertahankan data identitas NIP, unit kerja, dan profil ASN pada akun yang ditandai pensiun / auto-pensiun (`markPensiun` dan `processAutoPensiun`) agar integritas riwayat historis tetap terjaga.
  - Memastikan pencabutan hak akses dan status keamanan tetap berjalan: menangguhkan login email (`suspended_login = 1`), mengisi `pensiun_at`, mencabut peran pimpinan, dan memindahkan akun ke Kotak Sampah (*Soft Delete*).
- **Pembaruan Tampilan Kotak Sampah (`trash.php`)**:
  - Menambahkan kolom **Unit Kerja** (termasuk unit kerja induk jika ada) pada tabel Kotak Sampah.
  - Menampilkan nomor **NIP** di bawah nama pemilik akun untuk memudahkan identifikasi dan verifikasi sebelum pemulihan (*restore*) atau pembersihan permanen.

---

# [31 Agustus 2026] — Fitur Auto Pensiun Berbasis BUP & Pembaruan Antarmuka Kotak Sampah

- **Fitur Auto Pensiun Berbasis Batas Usia Pensiun (BUP)**:
  - Menambahkan kalkulator otomatis BUP ASN khusus PNS (`status_asn_id = 1`) sesuai UU ASN & BKN: 58 tahun (Pelaksana/Pengawas/Administrator/JF Pertama-Muda-Terampil), 60 tahun (Pimpinan Tinggi Eselon II/JF Madya/Guru/Dokter/Pengawas Sekolah), dan 65 tahun (JF Utama) dengan perhitungan TMT Pensiun pada tanggal 1 bulan berikutnya (`calculateBupInfo`).
  - Menambahkan method `processAutoPensiun` untuk otomatis menangguhkan login cPanel, mencatat `pensiun_at`, memindahkan data ke Kotak Sampah (*Soft Delete*), dan mengirim notifikasi audit ke Telegram Admin.
  - Menambahkan Spark CLI Command baru: `php spark email:auto-pensiun` (dengan opsi simulasi `--dry-run`).
  - Mengintegrasikan pengecekan Auto Pensiun ke dalam siklus *cron job* harian/bulanan `php spark sync:all`.
- **Pembaruan Halaman Kotak Sampah (`trash.php`)**:
  - Menyederhanakan tampilan tabel menjadi 3 kolom utama: **Akun / Pengguna** (Email & Nama), **Tgl Dihapus & Sisa Retensi 30 Hari**, dan **Aksi** (Pulihkan / Hapus Permanen).
  - Menambahkan badge indikator hitung mundur masa retensi 30 hari (*countdown*) sebelum akun dihapus permanen oleh sistem.
  - Memastikan pelepasan seluruh atribut kepegawaian ASN secara bersih saat akun ditandai pensiun / masuk ke kotak sampah.

---

# [31 Agustus 2026] — Normalisasi Jabatan Penelaah Teknis Kebijakan & Pembersihan Penugasan Subag

- **Pembersihan Penugasan Seksi & Subag pada Penelaah Teknis Kebijakan**:
  - Menghapus embel-embel seksi atau subbagian penugasan internal pada jabatan `PENELAAH TEKNIS KEBIJAKAN` dan staf pelaksana lainnya (seperti `PENELAAH TEKNIS KEBIJAKAN SEKSI PEMERINTAHAN` $\rightarrow$ `PENELAAH TEKNIS KEBIJAKAN`, `PENGADMINISTRASI PERKANTORAN SUBAG UMUM...` $\rightarrow$ `PENGADMINISTRASI PERKANTORAN`).
- **Koreksi Typo & Singkatan**:
  - Menambahkan penanganan otomatis koreksi variasi ejaan kata `TEKHNIS` $\rightarrow$ **`TEKNIS`**.

---

# [31 Agustus 2026] — Perbaikan Glitch Tooltip pada Donut Chart Status ASN & TTE

- **Optimalisasi Tooltip Donut Chart**:
  - Menonaktifkan *floating tooltip* bawaan ApexCharts (`tooltip: { enabled: false }`) pada grafik donut Status TTE dan Status ASN di halaman Detail Unit Kerja (`unit_kerja_detail.php`), Dashboard (`home/index.php`), Monitoring Web OPD (`web_opd/index.php`), dan Web Desa/Kelurahan (`web_desa_kelurahan/index.php`) untuk menghilangkan *glitch/flickering* saat kursor diarahkan ke irisan chart.
  - Memanfaatkan indikator dinamis di bagian tengah lingkaran (*center donut label*) yang secara responsif menampilkan rincian nama kategori dan jumlah akun.
  - Menambahkan aturan `pointer-events: none !important;` pada kelas `.apexcharts-tooltip` di `input.css`.

---

# [31 Agustus 2026] — Normalisasi Jabatan Kelurahan/Kecamatan & Pembersihan Penugasan Seksi

- **Pembersihan Nama Kelurahan/Kecamatan pada Jabatan Struktural**:
  - Menghapus akhiran nama Kelurahan atau Kecamatan pada jabatan struktural Kasi dan Kasubag (seperti `KEPALA SEKSI PEMERINTAHAN KELURAHAN BALANGNIPA` $\rightarrow$ `KEPALA SEKSI PEMERINTAHAN`, `KEPALA SUB BAGIAN PROGRAM DAN KEUANGAN KEC. SINJAI UTARA` $\rightarrow$ `KEPALA SUB BAGIAN PROGRAM DAN KEUANGAN`).
- **Pembersihan Penugasan Seksi pada Staf Pelaksana**:
  - Menghapus embel-embel seksi/bidang penugasan pada staf pelaksana (*"PENGOLAH DATA DAN INFORMASI SEKSI PEMBANGUNAN..."* $\rightarrow$ *"PENGOLAH DATA DAN INFORMASI"*).
- **Koreksi Typo**:
  - Menambahkan koreksi otomatis kesalahan penulisan kata `PELAYANAAN` $\rightarrow$ **`PELAYANAN`**.

---

# [31 Agustus 2026] — Standarisasi Jabatan Fungsional Kesehatan & Jenjang Keterampilan

- **Koreksi Ejaan Profesi Fisioterapis**:
  - Menambahkan koreksi otomatis typo ejaan `FISIOTRAPI` $\rightarrow$ **`FISIOTERAPIS`**.
- **Standarisasi Jenjang Keterampilan Format Lama**:
  - Mengonversi format lama `Pelaksana Lanjutan` $\rightarrow$ **`Mahir`**, `Pelaksana Pemula` $\rightarrow$ **`Pemula`**, dan `[Profesi] Pelaksana` $\rightarrow$ **`[Profesi] Terampil`**.
- **Konversi Nomenklatur Profesi Kesehatan**:
  - Mengonversi nomenklatur profesi `Perawat Gigi` menjadi **`Terapis Gigi dan Mulut`** sesuai dengan PermenPAN-RB No. 37 Tahun 2019.
- **Perluasan Rumpun Fungsional Kesehatan**:
  - Memperluas daftar profesi fungsional tenaga kesehatan yang dinormalisasi secara otomatis mencakup *Fisioterapis, Pranata Laboratorium Kesehatan, Terapis Gigi dan Mulut, Radiografer, Asisten Apoteker, Refraksionis Optisien, Perekam Medis, Teknisi Elektromedis, Okupasi Terapis, Terapis Wicara, dll.*

---

# [31 Agustus 2026] — Pemetaan Presisi 10 Bagian Setda & Normalisasi Kasubag TU Pimpinan

- **Penyempurnaan Pemetaan 10 Bagian Sekretariat Daerah**:
  - Memperluas aturan kata kunci subbagian (seperti Subbagian TU Pimpinan, BUMD/BLUD, Penyusunan Program, Perbendaharaan) ke masing-masing Bagian Setda terkait.
  - Memperketat regex pimpinan utama Setda (`isTopSetdaLeader`) agar tidak keliru menandai `Kepala Sub Bagian TU Pimpinan Sekda, Staf Ahli dan Kepegawaian` sebagai pimpinan puncak, sehingga dapat dipetakan secara akurat ke *Bagian Umum dan Perlengkapan*.
- **Normalisasi Jabatan Kasubag TU Pimpinan**:
  - Menjaga nomenklatur `KEPALA SUB BAGIAN TATA USAHA PIMPINAN SEKDA, STAF AHLI DAN KEPEGAWAIAN` agar tidak diubah menjadi `KEPALA TATA USAHA`.

---

# [31 Agustus 2026] — Filter Dengan / Tanpa Sub-Unit pada Detail Unit Kerja

- **Filter Cakupan Sub-Unit pada Detail Unit Kerja**:
  - Menambahkan filter `Dengan Sub Unit` (mencakup unit induk dan sub-unit) dan `Tanpa Sub Unit` (hanya unit induk) pada halaman Detail Unit Kerja yang memiliki child unit (seperti Setda, Dinkes, Disdik, Kecamatan).
  - Menyesuaikan kalkulasi statistik (Total Email, TTE Aktif/Expired, Status ASN) serta fitur ekspor laporan (PDF, Excel, CSV) agar mematuhi filter sub-unit yang dipilih.

---

# [31 Agustus 2026] — Standarisasi Frasa BARANG/JASA & Pemetaan Bagian Pengadaan Barang dan Jasa

- **Standarisasi Frasa BARANG/JASA**:
  - Mengonversi frasa `BARANG/JASA` menjadi `BARANG DAN JASA` sebelum pemrosesan pemecahan tanda garis miring, mencegah hilangnya kata `JASA` pada jabatan seperti `Kepala Sub Bagian Pengelolaan Barang/jasa` $\rightarrow$ `KEPALA SUB BAGIAN PENGELOLAAN BARANG DAN JASA`.
- **Penyempurnaan Pemetaan Unit Bagian Pengadaan Barang dan Jasa**:
  - Memperluas regex pengenalan `PENGADAAN` agar secara fleksibel mencakup variasi penulisan `BARANG JASA` tanpa konjungsi `DAN`.

---

# [31 Agustus 2026] — Pemetaan Otomatis 10 Bagian Sekretariat Daerah dari Subbagian & Jabatan Fungsional

- **Pemetaan Otomatis 10 Child Unit (Bagian) Setda**:
  - Menambahkan aturan pencocokan cerdas berdasarkan kata kunci Subbagian dan Jabatan Fungsional pada `syncPegawaiFromApi` sehingga seluruh pejabat dan staf di lingkungan Sekretariat Daerah otomatis dipetakan ke 10 Bagian Setda terkait (seperti `KEPALA SUB BAGIAN PROTOKOL` $\rightarrow$ `BAGIAN PROTOKOL DAN KOMUNIKASI PIMPINAN`, Subbagian PBJ $\rightarrow$ `BAGIAN PENGADAAN BARANG DAN JASA`, Subbagian Rumah Tangga $\rightarrow$ `BAGIAN UMUM DAN PERLENGKAPAN`, dsb.).

---

# [31 Agustus 2026] — Nomenklatur Kepala Bagian & Kasubag TU Kepegawaian Sekretariat DPRD

- **Pemeliharaan Nama Lengkap Kepala Bagian Setwan & RSUD**:
  - Mempertahankan nama lengkap Bagian pada Sekretariat DPRD (seperti `KEPALA BAGIAN UMUM DAN KEUANGAN`, `KEPALA BAGIAN PERSIDANGAN DAN PERUNDANG-UNDANGAN`, `KEPALA BAGIAN FASILITASI PENGANGGARAN DAN PENGAWASAN`) dan RSUD (`KEPALA BAGIAN TATA USAHA`) karena entitas tersebut tidak memiliki child unit terpisah.
- **Standarisasi Kasubag Tata Usaha dan Kepegawaian**:
  - Menetapkan nama baku `KEPALA SUB BAGIAN TATA USAHA DAN KEPEGAWAIAN` pada Sekretariat DPRD / OPD Induk dan membersihkan akhiran instansi (seperti `KEPALA TATA USAHA DAN KEPEGAWAIAN SEKRETARIAT DPRD KAB. SINJAI` $\rightarrow$ `KEPALA SUB BAGIAN TATA USAHA DAN KEPEGAWAIAN`).

---

# [31 Agustus 2026] — Pembersihan Nama Bidang Induk pada Kasi & Satpol PP/BPBD pada Kabid

- **Pembersihan Nama Bidang Induk pada Jabatan Seksi & Subbagian**:
  - Menghapus akhiran nama bidang/bagian/sekretariat induk yang tersemat pada nama seksi (seperti `KEPALA SEKSI PEMBINAAN DAN PENCEGAHAN KEBAKARAN BIDANG PEMADAM KEBAKARAN` $\rightarrow$ `KEPALA SEKSI PEMBINAAN DAN PENCEGAHAN KEBAKARAN`).
- **Pembersihan Nama Instansi Satpol PP & BPBD pada Jabatan Kepala Bidang**:
  - Menghapus akhiran nama instansi Satpol PP & Damkar serta BPBD pada jabatan Kepala Bidang (seperti `KEPALA BIDANG PENEGAKAN PERDA SATPOL PP DAN PEMADAM KEBAKARAN` $\rightarrow$ `KEPALA BIDANG PENEGAKAN PERDA`).

---

# [31 Agustus 2026] — Standarisasi Nomenklatur Baku UPTD Laboratorium Lingkungan DLHK

- **Standarisasi UPTD Laboratorium Lingkungan DLHK**:
  - Menstandarisasi penamaan UPTD Laboratorium di bawah DLHK ke format baku nasional (**Permen LHK No. P.23/2020**) menjadi **`KEPALA UPTD LABORATORIUM LINGKUNGAN`** dan **`KEPALA TATA USAHA UPTD LABORATORIUM LINGKUNGAN`**.

---

# [31 Agustus 2026] — Pembersihan Embel-Embel Sub.Koordinator & Standarisasi UPTD Lab DLHK

- **Pembersihan Embel-Embel Sub.Koordinator pada JF Penyetaraan**:
  - Menghapus keterangan tugas subkoordinasi/struktural asal dengan variasi penulisan titik/spasi/garis miring (`Sub.Koordinator`, `Sub Koordinator`, `Sub-Koordinator`, `/Sub Koordinator`) yang menempel pada jabatan fungsional hasil penyetaraan birokrasi (seperti `Pengendali Dampak Lingkungan Ahli Muda Sub.Koordinator Kasi Pengelolaan Sampah` $\rightarrow$ `PENGENDALI DAMPAK LINGKUNGAN AHLI MUDA`).
- **Standarisasi UPTD Laboratorium DLHK**:
  - Menstandarisasi penamaan UPTD Laboratorium di bawah DLHK menjadi `KEPALA UPTD LABORATORIUM LINGKUNGAN HIDUP` dan `KEPALA TATA USAHA UPTD LABORATORIUM LINGKUNGAN HIDUP`.

---

# [31 Agustus 2026] — Standarisasi Singkatan BBI (Balai Benih Ikan) pada UPTD Perikanan

- **Standarisasi Singkatan BBI**:
  - Menambahkan ekspansi otomatis singkatan `BBI` menjadi format baku **`BALAI BENIH IKAN`** (UPTD Balai Benih Ikan Dinas Perikanan).
  - Mengonversi `KEPALA TATA USAHA BBI` / `KEPALA TU BBI` / `KTU BBI` $\rightarrow$ **`KEPALA TATA USAHA UPTD BALAI BENIH IKAN`**.
  - Mengonversi `KEPALA BBI` / `KEPALA UPTD BBI` $\rightarrow$ **`KEPALA UPTD BALAI BENIH IKAN`**.

---

# [31 Agustus 2026] — Koreksi Typo Nomenklatur PUSKSEWAN menjadi PUSKESWAN

- **Koreksi Typo PUSKSEWAN**:
  - Menambahkan penanganan otomatis koreksi typo SIMPEG dari `PUSKSEWAN` atau `PUSKES WAN` menjadi format baku **`PUSKESWAN`** (Pusat Kesehatan Hewan), seperti `KEPALA TATA USAHA UPTD PUSKSEWAN` $\rightarrow$ `KEPALA TATA USAHA UPTD PUSKESWAN`.

---

# [31 Agustus 2026] — Penyesuaian Urutan Parser SSCASN & Pembersihan Suffix OPD

- **Penyesuaian Urutan Parser SSCASN & Pembersihan Suffix OPD**:
  - Mengubah urutan eksekusi standarisasi format SSCASN (`AHLI PERTAMA - [PROFESI] [OPD]`) agar berjalan mendahului pembersihan lokasi, sehingga format API SIMPEG seperti `AHLI PERTAMA - MEDIK VETERINERDINAS PETERNAKAN DAN KESEHATAN HEWANDINAS PETERNAKAN DAN KESEHATAN HEWAN` terkonversi bersih menjadi **`MEDIK VETERINER AHLI PERTAMA`**.
  - Memperluas daftar profesi keahlian untuk rumpun veteriner dan peternakan.

---

# [31 Agustus 2026] — Pembersihan Duplikasi Nama OPD & Penggabungan String pada Jabatan Fungsional

- **Pembersihan Duplikasi & Penggabungan String Nama OPD**:
  - Menangani anomali data SIMPEG berupa penggabungan/duplikasi nama OPD di tengah nama jabatan fungsional (seperti `MEDIK VETERINERDINAS PETERNAKAN DAN KESEHATAN HEWANDINAS PETERNAKAN DAN KESEHATAN HEWAN AHLI PERTAMA` $\rightarrow$ `MEDIK VETERINER AHLI PERTAMA`).
  - Menambahkan pemisahan otomatis kata kunci instansi yang menempel tanpa spasi (*`VETERINERDINAS`* $\rightarrow$ *`VETERINER DINAS`*).
  - Menambahkan dukungan jabatan fungsional rumpun peternakan & kesehatan hewan (`MEDIK VETERINER`, `PARAMEDIK VETERINER`, `PENGAWAS BIBIT TERNAK`, `PENGAWAS MUTU PAKAN`).

---

# [31 Agustus 2026] — Standarisasi Nomenklatur UPTD pada Jabatan Kepala Tata Usaha

- **Standarisasi UPT ke UPTD pada Kepala Tata Usaha**:
  - Menyelaraskan singkatan `UPT` menjadi `UPTD` pada jabatan `KEPALA TATA USAHA` teknis perangkat daerah (seperti `KEPALA TATA USAHA UPT PENGOLAHAN HASIL PETERNAKAN` $\rightarrow$ `KEPALA TATA USAHA UPTD PENGOLAHAN HASIL PETERNAKAN` dan `KEPALA TATA USAHA UPT PENGOLAHAN LOGAM` $\rightarrow$ `KEPALA TATA USAHA UPTD PENGOLAHAN LOGAM`).

---

# [31 Agustus 2026] — Pembersihan Prefix Jabatan Fungsional (JF/JFT/JFU) & Standarisasi KEPALA TU

- **Pembersihan Prefix Jabatan Fungsional**:
  - Menghapus prefix teknis `JF.`, `JF`, `JFT.`, `JFT`, `JFU.`, `JFU` di awal nama jabatan (seperti `JF PENGUJI KENDARAAN BERMOTOR PENYELIA` $\rightarrow$ `PENGUJI KENDARAAN BERMOTOR PENYELIA`).
- **Standarisasi Singkatan KEPALA TU**:
  - Menambahkan variasi singkatan `KEPALA TU` ke dalam pola ekspansi baku **`KEPALA TATA USAHA`** (seperti `KEPALA TU UPTD TERMINAL DAN PERPARKIRAN` $\rightarrow$ `KEPALA TATA USAHA UPTD TERMINAL DAN PERPARKIRAN`).

---

# [31 Agustus 2026] — Standarisasi Nomenklatur Baku Kepala UPTD pada Perangkat Daerah

- **Standarisasi Nomenklatur KEPALA UPTD**:
  - Menyelaraskan seluruh variasi penulisan `KEPALA UPT` menjadi format yuridis baku perangkat daerah **`KEPALA UPTD`** (seperti `KEPALA UPTD RADIO SUARA BERSATU DAN TV SINJAI` dan `KEPALA UPTD PERLINDUNGAN PEREMPUAN DAN ANAK`).
  - Mempertahankan format ringkas `KEPALA PUSKESMAS` dan `KEPALA TATA USAHA` pada UPTD Puskesmas, serta `KEPALA SEKOLAH` pada Satuan Pendidikan.
  - Mempertahankan `KEPALA SUB BAGIAN TATA USAHA` khusus untuk RS Pratama Bulupancing.

---

# [31 Agustus 2026] — Pemeliharaan Nomenklatur Lengkap UPTD pada OPD Teknis Non-Child Unit

- **Pemeliharaan Nama UPTD pada OPD Teknis**:
  - Mempertahankan nama lengkap UPTD teknis pada kolom jabatan (seperti `KEPALA UPT RADIO SUARA BERSATU DAN TV SINJAI`, `KEPALA UPT PERLINDUNGAN PEREMPUAN DAN ANAK`, dan `KEPALA TATA USAHA RADIO SINJAI BERSATU DAN TV SINJAI`) karena UPTD tersebut berada langsung di bawah dinas induk dan tidak dibuatkan entitas child unit terpisah.
  - Mempertahankan format ringkas `KEPALA PUSKESMAS` dan `KEPALA TATA USAHA` pada UPTD Puskesmas, serta `KEPALA SEKOLAH` pada Satuan Pendidikan karena nama unit kerjanya sudah tertera mandiri pada kolom Unit Kerja.
  - Menetapkan `KEPALA SUB BAGIAN TATA USAHA` khusus untuk RS Pratama Bulupancing.

---

# [31 Agustus 2026] — Penyesuaian Nomenklatur Tata Usaha RS Pratama & UPTD/Puskesmas

- **Penyesuaian Nomenklatur Tata Usaha**:
  - Menetapkan nama jabatan baku **`KEPALA SUB BAGIAN TATA USAHA`** khusus untuk pejabat tata usaha pada UPT RSUD Kelas D Pratama.
  - Menetapkan nama jabatan baku **`KEPALA TATA USAHA`** untuk pejabat tata usaha di lingkungan UPTD Teknis dan UPTD Puskesmas.
  - Mempertahankan format **`KEPALA SUB BAGIAN UMUM DAN KEPEGAWAIAN`** dan **`KEPALA SUB BAGIAN PROGRAM DAN KEUANGAN`** pada pejabat Eselon IV.a Sekretariat OPD Induk.

---

# [31 Agustus 2026] — Standarisasi Jabatan Kepala Tata Usaha (KTU) di Lingkungan UPTD & Puskesmas

- **Standarisasi Jabatan Kepala Tata Usaha (KTU)**:
  - Menetapkan nama jabatan baku **`KEPALA TATA USAHA`** untuk seluruh pejabat tata usaha di lingkungan UPTD Teknis, UPTD Puskesmas, dan UPT RSUD Pratama.
  - Membedakan secara tegas pejabat tata usaha UPTD/Puskesmas (`KEPALA TATA USAHA`) dengan pejabat struktural Eselon IV.a di Sekretariat OPD Induk (`KEPALA SUB BAGIAN UMUM DAN KEPEGAWAIAN`, `KEPALA SUB BAGIAN PROGRAM DAN KEUANGAN`).
  - Membersihkan suffix unit kerja teknis seperti `RADIO`, `TV`, `BALAI`, `LOKA` dari nama jabatan struktural.

---

# [31 Agustus 2026] — Koreksi Pencocokan Sub-Unit Berbasis Batas Kata Utuh (Word Boundary)

- **Pencegahan Tabrakan Substring Antar-Nama Sub-Unit**:
  - Menghapus pencarian string tanpa spasi yang sempat menyebabkan tabrakan karakter antar-kata (seperti kata `ASKA` yang tidak sengaja terbentuk di antara `...PUSKESMAS KAMPALA...`), sehingga akun Kepala Puskesmas Kampala (Asrul) sempat keliru terpetakan ke Puskesmas Aska.
  - Menerapkan pencocokan berbasis batas kata utuh (*word boundary regex* `\b[NAMA]\b`) untuk memastikan resolusi unit kerja selalu presisi dan tidak tertukar.

---

# [31 Agustus 2026] — Standarisasi Jabatan Kepala UPTD Teknis & Peningkatan Keandalan Sinkronisasi SIMPEG

- **Standarisasi Jabatan Pimpinan UPTD Teknis**:
  - Menetapkan nama jabatan baku **`KEPALA UPTD`** untuk seluruh pimpinan UPTD teknis non-sekolah dan non-puskesmas (misal: UPT Perlindungan Perempuan dan Anak, UPT Radio Suara Bersatu dan TV Sinjai, UPTD Labkesda, UPTD Metrologi, dsb.).
- **Peningkatan Keandalan Sinkronisasi Akun Pegawai**:
  - Menambahkan pengiriman parameter `email` bersama `nip` pada AJAX sinkronisasi di halaman Detail Unit Kerja (`unit_kerja_detail.php`), sehingga akun yang belum memiliki NIP di database tetap dapat disinkronkan secara mulus.
  - Memastikan update data pasca-sinkronisasi dieksekusi berdasarkan Primary Key ID akun dan kolom NIP yang kosong otomatis terisi dari respon API SIMPEG.

---

# [31 Agustus 2026] — Normalisasi Jabatan Struktural RSUD Pratama & Pemetaan Sub-Unit Dinas Kesehatan

- **Normalisasi Jabatan Struktural & Pembersihan Suffix Unit Kerja**:
  - Membersihkan suffix unit kerja langsung pada jabatan struktural (seperti `UPTD RSUD Bulupaccing` pada `Kepala Seksi Pelayanan Penunjang`) sehingga menghasilkan nama jabatan ringkas dan baku (`KEPALA SEKSI PELAYANAN PENUNJANG`).
  - Mengoreksi penulisan ejaan `BULUPACCING` $\rightarrow$ `BULUPANCING`.
- **Pemetaan Sub-Unit UPT RSUD Kelas D Pratama**:
  - Memastikan pencocokan sub-unit UPT RSUD Kelas D Pratama mengenali variasi penulisan `BULUPACCING` pada jabatan penempatan di SIMPEG.

---

# [31 Agustus 2026] — Disambiguasi Jenjang Pangkat Guru dan Koreksi Ejaan Sekolah Dasar

- **Disambiguasi Level Pangkat Era Lama vs Jenjang Sekolah**:
  - Menangani variasi penulisan pangkat `tk I` (tanpa tanda titik) agar tidak salah teridentifikasi sebagai jenjang sekolah Taman Kanak-kanak (*TK*), sehingga pencocokan ke Sekolah Dasar (seperti `SD NEG. NO. 185 MACCONGI` pada akun Hermiwaty) dapat berjalan akurat.
  - Memastikan token prefix `SDN`, `SMPN`, `SMAN`, `TKN` memiliki spasi pemisah standar untuk menjaga keutuhan penomoran sekolah.
- **Penyempurnaan Regex & Ejaan Nama Sekolah**:
  - Memperbaiki penanganan typo penomoran SD di SIMPEG (seperti `SDN No. 253 Tarangkeke` yang secara resmi adalah `SD NEG. NO. 235 TARANGKEKE`) agar tidak terjadi duplikasi nomor pada saat proses pencocokan string regex.
  - Memastikan variasi nama SD seperti `Macconggi` langsung terpetakan ke `SD NEG. NO. 185 MACCONGI`.

---

# [30 Agustus 2026] — Penyempurnaan Pemetaan Otomatis Sub-Unit Kerja Sekolah (SD, SMP, Satap, TK)

- **Penyempurnaan Algoritma Pemetaan Sekolah ke Sub-Unit Dinas Pendidikan**:
  - Menyelaraskan pencocokan penomoran UPTD SD Negeri (`SDN [nomor]` $\rightarrow$ `SD NEG. NO. [nomor]`) agar langsung terpetakan secara presisi tanpa terhalang perbedaan penulisan nama desa/kabupaten pada SIMPEG.
  - Melengkapi tabel relasi SMP format per-kecamatan di seluruh 9 kecamatan ke UPTD SMP Negeri resmi Kabupaten Sinjai (UPTD SMPN 1 s.d. 40 Sinjai).
  - Melengkapi pemetaan SMP Satu Atap (Satap) seperti Satap Sinjai Tengah/Kanrung (UPTD SMPN 28), Satap Burung Loe (UPTD SMPN 38), Satap Tasosso (UPTD SMPN 39), Satap Terasa (UPTD SMPN 40), Satap Balappangi (UPTD SMPN 36), Satap Palangka (UPTD SMPN 37), dan Satap Kanalo (UPTD SMPN 35).
  - Menambahkan koreksi typo SIMPEG dan alias khusus penamaan sekolah (contoh: SDN 253 Tarangkeke $\rightarrow$ SD NEG. NO. 235 TARANGKEKE, SDN Pussanti $\rightarrow$ SD NEG. NO. 76 PUSANTI, SDN 277 Balang $\rightarrow$ SD NEG. NO. 227 BALANG, TK Pertiwi V Mangarabombang $\rightarrow$ TK NEGERI V SINJAI TIMUR).

---

# [30 Agustus 2026] — Penyempurnaan Normalisasi Jabatan Fungsional Guru & Tenaga Pendidik

- **Penyempurnaan Logika Normalisasi Jabatan Guru**:
  - Menghapus karakter spasi tak kasat mata (*non-breaking space* `\u00A0` dan `\u200B`) pada awal/akhir teks jabatan dari SIMPEG.
  - Memperbaiki typo SIMPEG seperti `GURUR MUDA` $\rightarrow$ `GURU AHLI MUDA`, `PENGELOLAH` $\rightarrow$ `PENGOLAH`, dan `PENJASKES` $\rightarrow$ `PENJASORKES`.
  - Membersihkan sisa akhiran jenjang tingkat lama (`Tk. I`, `Tingkat. I`, `Tk,I`) pada jabatan guru.
  - Menstandarisasi formasi guru mata pelajaran & pamong belajar tanpa kata *"Ahli"* (seperti `Guru Kelas Pertama`, `Guru Matematika Pertama`, `Pamong Belajar Madya`) menjadi format baku PermenPAN-RB & BKN (`GURU KELAS AHLI PERTAMA`, `PAMONG BELAJAR AHLI MADYA`), dengan proteksi pengecualian kata *Sekolah Menengah Pertama* (SMP).

---

# [30 Agustus 2026] — Tampilan NIP pada Tabel Akun Pegawai

- **Penyajian NIP Pegawai di Bawah Nama**:
  - Menampilkan NIP dengan format teks monospace (`font-mono text-slate-500`) tepat di bawah nama pegawai pada tabel daftar akun di halaman Detail Unit Kerja (`unit_kerja_detail.php`), Daftar Email Utama (`index.php`), dan Detail Eselon (`eselon_detail.php`).

---

# [30 Agustus 2026] — Penambahan Kolom Jabatan pada Pencarian Unit Kerja

- **Pencarian Berdasarkan Jabatan pada Detail Unit Kerja**:
  - Menambahkan pencocokan kolom `emails.jabatan` pada *query filter* `getUnitKerjaDetail` di `EmailService`.
  - Menyelaraskan filter pencarian kolom `jabatan` pada semua fungsi ekspor unit kerja (`generateUnitKerjaPdf`, `generateAccountDetailPdf`, `generateUnitKerjaExcel`, dan `generateUnitKerjaCsv`) di `EmailExportService`.

---

# [30 Agustus 2026] — Perbaikan Bug Normalisasi Unit Kerja Sekolah

- **Fix: "Tk." (Tingkat Pangkat) Salah Terdeteksi sebagai TK (Taman Kanak-kanak)**:
  - Memperbaiki bug pada fungsi `normalizeForMatching` di `EmailService` di mana singkatan `"Tk."` (Tingkat, misal *Guru Dewasa Tk. I*) berubah menjadi `"TK 1"` setelah normalisasi titik, sehingga `searchIsTk = true` dan seluruh child SD/SMP di-skip tanpa diperiksa.
  - Solusi: menambahkan langkah `preg_replace('/\bTK\.\s*(?=[IVXLCDM\d])/i', 'TINGKAT ', $s)` sebelum `str_replace('.')` untuk mengubah `"Tk. I/II/III"` → `"TINGKAT 1/2/3"` sebelum konversi romawi, sehingga tidak bertabrakan dengan deteksi jenis unit TK sekolah.

---

# [30 Agustus 2026] — Normalisasi Unit Kerja Sekolah

- **Penambahan Unit Kerja Sekolah Baru (Dinas Pendidikan)**:
  - Menambahkan `TK PERTIWI BONTOSALAMA SINJAI BARAT` sebagai child unit kerja Dinas Pendidikan (`parent_id = 348`), berdasarkan verifikasi `jabatan_grup` API SIMPEG untuk NIP 196708041987032005.
  - Menambahkan `TK PERTIWI PALAE SINJAI SELATAN` sebagai child unit kerja Dinas Pendidikan (`parent_id = 348`), berdasarkan verifikasi `jabatan_grup` API SIMPEG untuk NIP 197904232007012008.
  - Menambahkan `SD NEG. NO. 27 TONDONG` sebagai child unit kerja Dinas Pendidikan, berdasarkan verifikasi `jabatan_grup` API SIMPEG untuk NIP 197307101999032010.
- **Perbaikan Penulisan Nama Unit Kerja**:
  - Mengoreksi `SD NEG. NO. 27 TONDONG SINJAI TIMUR` → `SD NEG. NO. 27 TONDONG` agar konsisten dengan format penamaan SD lainnya (tanpa keterangan wilayah).
  - Mengoreksi `TK NEGERI XI PANRENG` → `TK NEGERI XI PANRENG SINJAI UTARA` agar konsisten dengan format penamaan TK lainnya (dengan keterangan wilayah). Kecamatan Sinjai Utara dikonfirmasi dari data `jabatan_grup` API SIMPEG.

---

# [30 Agustus 2026]

- **Penyelarasan Modal Hasil Sinkronisasi Global**:
  - Mengganti seluruh popup `alert()` browser pada proses sinkronisasi data pegawai dan TTE dengan modal hasil sinkronisasi terpadu `showSyncResult` (`global-sync-result-modal`) dan modal error terpadu `showGlobalError`.
- **Perbaikan Prioritas Normalisasi Jabatan Pimpinan (Lurah vs Camat)**:
  - Menetapkan prioritas judul jabatan definitif (`LURAH`, `CAMAT`, `KEPALA BAGIAN`, `KEPALA DINAS`, `KEPALA BADAN`, `KEPALA SATUAN`, `DIREKTUR`, `INSPEKTUR`) di atas inferensi unit induk, mencegah jabatan Lurah tertukar menjadi Camat saat berada di bawah unit induk Kantor Kecamatan.
  - Memastikan resolusi sub-unit anak dieksekusi sebelum normalisasi jabatan pimpinan.
- **Integrasi Bidang `jabatan_grup` Respon API SIMPEG untuk Pemetaan Child Unit**:
  - Memanfaatkan bidang `jabatan_grup` dari respon API SIMPEG sebagai parameter pencocokan hierarki sub-unit anak (Kelurahan, Bagian Setda, UPTD RSUD/Puskesmas) dengan akurasi tinggi.
- **Perbaikan TypeError pada Kelola Unit Kerja**:
  - Memperbaiki `TypeError: count()` pada `app/Views/unit_kerja/manage.php` dengan menyelaraskan pengikatan data `$data['unit_kerja_list']` di `UnitKerjaController` serta menambahkan *null-safe check*.
- **Penyempurnaan Tampilan Halaman Pimpinan & Skeleton Loading**:
  - Menghapus tampilan teks NIP pada tabel pimpinan untuk tampilan yang lebih bersih dan proporsional.
  - Menerapkan animasi *skeleton loading placeholder* (`animate-pulse`) pada sel jabatan dan unit kerja saat proses *fetch* data per baris berlangsung.
- **Penanganan Rate Limit API Eksternal (Exponential Backoff & Jitter)**:
  - Menerapkan mekanisme proteksi request cerdas `requestWithRetry` pada `PegawaiApi` (SIMPEG) dan `BsreApi` (BSrE) dengan jeda bertingkat (*exponential backoff*) 1.5s, 3s, 6s (+ jitter acak) saat menerima status HTTP `429` (Too Many Requests), `503` (Service Unavailable), atau `504` (Gateway Timeout).
  - Menetapkan jeda mikro aman (`100ms`, ~10 req/s) di setiap iterasi worker untuk menjaga kestabilan server API.
- **Standarisasi Nomenklatur Jabatan Fungsional & Pelaksana (PermenPAN-RB & BKN SIASN)**:
  - Menyeragamkan seluruh format awalan tanda hubung SSCASN (`[JENJANG] - [PROFESI]`) menjadi format baku resmi SK Jabatan BKN (`[NAMA PROFESI] [JENJANG]`), contoh: `APOTEKER AHLI PERTAMA`, `ASISTEN APOTEKER TERAMPIL`, `PERAWAT TERAMPIL`, `PERAWAT AHLI PERTAMA`, `GURU KELAS AHLI PERTAMA`, `AUDITOR AHLI PERTAMA`, `PRANATA KOMPUTER AHLI PERTAMA`.
  - Mengonversi jenjang fungsional era lama ke format modern: `Guru Pratama` $\rightarrow$ `GURU AHLI PERTAMA`, `Guru Dewasa` $\rightarrow$ `GURU AHLI MUDA`, `Perawat Pelaksana Lanjutan` $\rightarrow$ `PERAWAT MAHIR`, `Bidan Pelaksana` $\rightarrow$ `BIDAN TERAMPIL`.
  - Mengoreksi otomatis kesalahan ketik (*typo*) master data: `Penelah` $\rightarrow$ `PENELAAH`, `Pengola` $\rightarrow$ `PENGOLAH`, `Pengadministrasian` $\rightarrow$ `PENGADMINISTRASI`, `Tehnis` $\rightarrow$ `TEKNIS`, `Pkn` $\rightarrow$ `PPKN`.
- **Standarisasi Nomenklatur Instansi Khusus**:
  - Satpol PP & Damkar: Standarisasi format pimpinan menjadi `KEPALA SATUAN` dan sekretaris menjadi `SEKRETARIS SATUAN`.
  - Inspektorat Daerah: Standarisasi format pimpinan menjadi `INSPEKTUR`, sekretaris menjadi `SEKRETARIS INSPEKTORAT`, `INSPEKTUR PEMBANTU WILAYAH I s.d. IV`, `INSPEKTUR PEMBANTU INVESTIGASI`, serta pembersihan otomatis embel-embel instansi pada Kasubag.
  - Perangkat Daerah Lainnya: Format ringkas `KEPALA DINAS`, `KEPALA BADAN`, `KEPALA BAGIAN`, `DIREKTUR`, `CAMAT`, `LURAH`.
- **Penyediaan Wrapper Executable CLI `./sync`**:
  - Menambahkan skrip executable `./sync` di root proyek untuk memudahkan eksekusi berbagai mode sinkronisasi via terminal (`./sync pegawai`, `./sync tte`, `./sync unit "<opd>"`, `./sync tte-unit "<opd>"`, `./sync daily/weekly/monthly`).
- **Integrasi Sinkronisasi Mutasi Unit Kerja & Hierarki Parent-Child**:
  - Sinkronisasi otomatis `unit_kerja_id` berdasarkan pemetaan `unit_id` SIMPEG saat terjadi mutasi atau alih tugas pegawai ke OPD baru.
  - Proteksi penempatan *child unit*: akun yang telah ditempatkan pada sub-unit spesifik (Sekolah, Puskesmas, Kelurahan/Desa) tidak akan ditimpa atau diturunkan ke unit induk (*parent*) selama masih dalam rumpun OPD yang sama.

---

# [29 Agustus 2026]

- **Penyederhanaan UI & Pembersihan Kode Migrasi ASN (`PNS`, `PPPK`, `PPPK PW`)**:
  - Menghapus form filter bar pada halaman daftar Pegawai PNS (`/email/pns`), PPPK (`/email/pppk`), dan PPPK Paruh Waktu (`/email/pppk-pw`) untuk tampilan yang lebih bersih, fokus, dan cepat.
  - Menghapus logic, service (`generatePnsExcel`, `generatePnsCsv`), controller method (`exportPnsExcel`, `exportPnsCsv`), serta rute endpoint ekspor PNS untuk efisiensi kode dan perampingan arsitektur.
  - Membersihkan berkas command migrasi dan file redundan (`CheckPensiun.php`, `MatchPegawaiNip.php`, data CSV sementara, dan cache JSON lokal) setelah seluruh proses rekonsiliasi NIP dan penangguhan pensiun selesai dijalankan di production.
  - Memfokuskan fungsionalitas header halaman pada tombol aksi sinkronisasi utama: **Sync TTE** dan **Sync Pegawai**.
- **Penyelarasan & Integrasi Cron Bulanan Sinkronisasi Pegawai SIMPEG**:
  - Membatasi logika sinkronisasi data pegawai (`syncPegawaiFromApi`, `sync:pegawai-unit`, dan cron bulanan `sync:all --monthly`) secara ketat hanya untuk akun berstatus **PNS** (`status_asn_id = 1`), secara otomatis mengabaikan akun **PPPK** dan **PPPK Paruh Waktu** agar formasi khusus mereka tidak tertimpa.
  - Memperbarui mekanisme normalisasi nama jabatan pada seluruh jalur sinkronisasi (`PegawaiSyncService` & `EmailService`): membersihkan tanda titik/koma liar di akhir, menghapus spasi ganda, serta merapikan spasi tanda hubung (` - `) dan garis miring (`/`).
  - Mengintegrasikan proses pembaruan dan normalisasi nama jabatan langsung ke dalam cron bulanan (`sync:all --monthly`) melalui background queue worker tanpa memerlukan command terpisah.

## API Gateway & Integrasi Hierarki Unit Kerja
- **Penyempurnaan Respon API Gateway**:
  - Menambahkan field `unit_kerja` (nama unit langsung/sub-unit) dan `parent_unit_kerja` (nama OPD induk) pada seluruh endpoint API Gateway (`/api/v1/emails`, `/api/v1/pns`, `/api/v1/pppk`, `/api/v1/pppk-pw`, dan `/api/v1/unit/{id}`).
  - Memfasilitasi pemetaan relasi hierarkis *child* (seperti UPTD Sekolah/Puskesmas/Kelurahan/Desa) dan *parent* (Dinas/Kecamatan/Setda), khususnya untuk pegawai kategori PPPK Paruh Waktu dan ASN lainnya.
  - Menambahkan parameter query filter baru `unit_kerja` dan `parent_unit_kerja` untuk fleksibilitas pencarian data via API.
- **Pembaruan Halaman Dokumentasi API**:
  - Menyelaraskan teks judul, deskripsi, dan contoh respon JSON pada halaman dokumentasi API Gateway (`/api-gateway`) agar padat, ringkas, dan *to the point*.
  - Melengkapi tabel parameter filter dengan parameter pencarian unit kerja dan OPD induk.

---

# [28 Agustus 2026]

## Notifikasi Telegram & Penyelarasan Background Sync
- **Konsolidasi Notifikasi Selesai & Laporan**:
  - Mengubah alur pengiriman notifikasi `SINKRONISASI SELESAI` agar dieksekusi oleh queue worker (`sync_summary`) setelah seluruh antrean proses latar belakang (*background queue*) benar-benar tuntas diproses.
  - Menggabungkan laporan temuan/peringatan (TTE expired, kuota >90%, domain website desa <30 hari) ke dalam satu pesan ringkasan penutup komprehensif.
  - Menerapkan prinsip *alert-only* pada kondisi aman (menghilangkan baris status aman yang redundan agar pesan tetap ringkas).
- **Standardisasi Format Pesan (Header — Content — Footer)**:
  - **Header**: Judul ringkas dan huruf kapital tebal dengan emoji semantik status.
  - **Content**: Struktur informasi profil akun terstandar 4 baris (`👤 Nama`, `📧 Email`, `💼 Jabatan`, `🏛️ Unit Kerja`) dan metrik kunci yang padat tanpa spasi kosong berlebih.
  - **Footer**: Timestamp otomatis terstandar dengan zona waktu WITA (`🕒 [Tanggal, Jam] WITA`).
- **Penyelarasan Sinkronisasi Bulanan & Pembersihan**:
  - Menambahkan sinkronisasi status sertifikat TTE & data kepegawaian SIMPEG untuk seluruh pegawai ASN (non-pimpinan) dengan proteksi jeda mikro (80ms) dan backoff adaptif saat menghadapi potensi rate limit.
  - Mengelompokkan laporan temuan TTE pegawai expired pada sinkronisasi bulanan berbasis per Unit Kerja (OPD).
  - Menampilkan jumlah data pegawai yang berhasil diselaraskan pada notifikasi penutup sinkronisasi bulanan (`👥 Sukses: [Jumlah] Data Pegawai`).
  - Menjamin ketahanan pembentukan pesan Telegram (*null-safety*) agar notifikasi ringkasan akhir selalu berhasil terkirim.
  - Memperbaiki penanganan error saat pembersihan akun pensiun permanen (>30 hari) agar rekaman database lokal tetap terhapus tuntas meskipun akun sudah tidak ada di cPanel.
  - Menyederhanakan format notifikasi pembersihan akun menjadi daftar ringkas (bullet) agar pesan tidak terlalu panjang.
- **Penyelarasan Jadwal Sinkronisasi**:
  - Memindahkan pemantauan masa aktif domain website desa/kelurahan dari bulanan ke rutinitas mingguan bersama sinkronisasi cPanel.
  - Menyederhanakan baris peringatan masa aktif website desa dengan hanya menampilkan nama domain dan sisa hari agar lebih ringkas.
- **Penyederhanaan Label Dasbor**:
  - Memperbarui label pada card *Terakhir Sinkronisasi* di dasbor menjadi lebih ringkas dan to the point: **Kuota Email**, **Sertifikat TTE**, **Data Pegawai**, dan **Website Desa**.

## Antarmuka, Pembuatan Akun & Standarisasi Data
- **Pembuatan Akun Tunggal**:
  - Mengarahkan (*redirect*) otomatis langsung ke halaman detail akun yang baru dibuat setelah proses pembuatan berhasil.
  - Menjadikan kolom NIP dan Status ASN sebagai kolom opsional (dengan opsi default `Non-ASN`) untuk mengakomodasi pembuatan akun staf / perangkat desa non-ASN.
  - Menyesuaikan logika pembuatan sandi awal otomatis agar tetap menghasilkan pola acak yang aman ketika NIP tidak diisi.
- **Standardisasi Status ASN**:
  - Menyeragamkan seluruh opsi dropdown Status ASN di seluruh aplikasi (`email/create.php`, `batch/create.php`, `email/edit_profile.php`, `email/unit_kerja_detail.php`) menjadi pilihan baku: `Non-ASN` (default), `PNS`, `PPPK`, dan `PPPK PARUH WAKTU`.
  - Menampilkan badge `NON-ASN` secara konsisten pada halaman Detail Akun dan tabel Unit Kerja ketika data status ASN kosong.
- **Penyempurnaan Filter Halaman Pimpinan & Monitoring Website**:
  - Menerapkan tombol reset filter yang tampil dinamis (hanya muncul saat kolom pencarian atau filter status terisi) pada halaman Pimpinan, Pimpinan Desa, Monitoring Website OPD, dan Monitoring Website Desa/Kelurahan.
  - Merapikan tata letak grid filter agar konsisten di seluruh *viewport* (mobile, tablet, desktop) dengan penguncian tinggi elemen 40px (`h-[40px]`).
  - Menstandarkan teks *placeholder* kotak pencarian menjadi `Cari...`.
- **Pencarian Unit Kerja & Penanganan Desa Kembar**:
  - Mengoptimalkan konfigurasi pencarian Choices.js dengan `ignoreLocation: true` dan limit 100 hasil agar pencarian kata kunci di posisi mana pun (seperti "kehutanan") langsung menampilkan pilihan yang relevan.
  - Menambahkan data `DESA PATTONGKO` untuk Kecamatan Tellu Limpoe ke tabel `unit_kerja` dan menyertakan nama kecamatan khusus untuk unit kerja Desa/Kelurahan pada dropdown agar pilihan tidak tertukar.
- **Penyempurnaan Dokumentasi API Gateway**:
  - Menambahkan fitur intip (*reveal*) dan tombol salin instan untuk Bearer Header Authorization.
  - Mengganti notifikasi salin URL berbasis browser alert dengan umpan balik indikator centang (*smooth inline feedback*).
  - Menyederhanakan seluruh teks dan *copywriting* halaman dokumentasi agar padat dan *to the point*.
  - Melengkapi contoh respon JSON dengan properti `api_unit_id` sesuai output aktual API.
- **Audit Pesan Sukses Seluruh Sistem**:
  - Menstandarkan dan menyederhanakan seluruh pesan flash sukses di berbagai Controller agar lebih singkat, profesional, dan kontekstual.

---

# [6 Agustus 2026]

## Antarmuka & Standardisasi Data
- **Standardisasi Badge Status**: Menyeragamkan label, warna, dan ikon badge status (Available, Created, Failed, Existing, Unavailable, Duplicate, Checking...) pada fitur Pembuatan Tunggal dan Massal.
- **Auto-Uppercase**: Memastikan penamaan (kolom `name`) selalu menggunakan huruf kapital (*uppercase*), baik secara visual di UI (`placeholder` dan form input) maupun logika penyimpanan (`.toUpperCase()`) sebelum dikirim ke server.

## Keamanan & Refactoring
- **Sentralisasi `generatePassword`**: Memindahkan logika generator password ke berkas *shared* `public/js/utils.js` agar seragam antara pembuatan tunggal dan batch. Memperbaiki bug pembuatan *alt-password* yang tidak berjalan di batch.
- **Validasi Server-Side & Audit Log**:
    - Menambahkan validasi ketat (minimal 8 karakter) di sisi server untuk pergantian password (`EmailController` dan `EmailListController`).
    - Mendelegasikan aksi ke `EmailService::updatePassword` untuk mencegah duplikasi pemanggilan cPanel API.
    - Mengintegrasikan pencatatan ke Audit Log (`log_audit`) setiap ada pergantian password.
- **Pembersihan Model**: Menghapus duplikasi properti `nik` dan `nip` pada `allowedFields` di `EmailModel`.

---

# [28 Juli 2026]

## Penyederhanaan Platform Website Desa / Kelurahan

- **Migrasi Pembaruan Data Kelurahan di Production**:
    - Menambahkan migrasi khusus `SetKelurahanPlatformToKominfo.php` untuk memastikan seluruh record Kelurahan di database production secara otomatis diperbarui platform-nya ke **`KOMINFO`**.
- **Pembaruan Tampilan Dashboard & Website**:
    - Memindahkan tag `<style>` pada halaman Dashboard (`home/index.php`) ke section `styles` agar tidak menimbulkan spasi kosong (*extra space*) di atas teks judul Dashboard.
    - Mengubah label tombol **Sync Expiration** menjadi **Sync** pada halaman Website Desa/Kelurahan (`index.php`).
- **Sederhanakan Kategori Platform**:
    - Mengubah dan menyederhanakan opsi master platform website desa/kelurahan dari 4 opsi (*SIDEKA-NG*, *OPENSID*, *PIHAK KETIGA*, *KOMINFO*) menjadi 2 opsi utama: **`KOMINFO`** dan **`MANDIRI`**.
- **Migrasi Data Platform**:
    - Menambahkan berkas migrasi `SimplifyPlatformsToKominfoAndMandiri.php` untuk memindahkan data website ber-platform *SIDEKA-NG* serta seluruh **Kelurahan** ke **`KOMINFO`**, serta *OPENSID* & *PIHAK KETIGA* (pada Desa) ke **`MANDIRI`**.
- **Penyesuaian Berkas Terkait**:
    - Memperbarui `WebDesaKelurahanController` & `WebsiteService` untuk menyesuaikan urutan filter serta statistik pengelompokan platform.
    - Memperbarui `WebDesaKelurahanSeeder` untuk memetakan data `SIDEKA` ke `KOMINFO` dan platform lainnya ke `MANDIRI`.

---

# [27 Juli 2026]

## Fitur Filter Tanpa Password & Batch Update Password (Detail Unit Kerja)

- **Filter Status Password**:
    - Mengubah filter password menjadi dropdown `<select>` (Semua Status, Tanpa Password, Ada Password) pada halaman Detail Unit Kerja (`unit_kerja_detail.php`).
    - Filter hanya diterapkan saat tombol **Filter** diklik (tanpa pemicu *auto-submit*).
    - Memperbarui `EmailService->getUnitKerjaDetail()` untuk mendukung penyaringan status password (`password_status`).

- **Batch Update Password**:
    - Menambahkan tombol dan modal **Batch Update Password** pada halaman Detail Unit Kerja.
    - Mendukung dua mode pembaruan password massal:
      - **Auto per Akun**: Generate password unik secara otomatis untuk tiap akun berdasar NIP & Nama Pemilik (menggunakan logika persis seperti di `edit_password.php`).
      - **Manual Seragam**: Menentukan 1 password tunggal yang diterapkan seragam ke seluruh akun yang tampil.
    - Menambahkan endpoint API `POST email/api_batch_update_password` di `EmailListController` yang memicu pembaruan ke server cPanel dan pembaruan database lokal.
    - Menampilkan progress bar real-time per akun (indikator persentase, status item aktif, animasi spinner) beserta ringkasan hasil (berhasil/gagal).
    - Memperbarui styling & struktur modal batch password agar konsisten dengan standar UI aplikasi (penggunaan `openModal` / `closeModal` helper dan footer button standar).

## Penyempurnaan Format Notifikasi Telegram & UI Landing Page

- **Pembaruan Beranda (`landing.php`)**:
    - Menghapus badge `"Portal Layanan Digital Resmi"` di halaman depan karena sistem ini merupakan sistem internal non-publik.

- **Hapus Duplikat Timestamp**:
    - Menghapus `addKeyValue('Waktu', date(...))` di `BackupCommand.php` yang duplikat dengan timestamp otomatis dari `build()`.
    - Menghapus `addText("🕒 " . date(...))` di `EmailService.php` yang juga duplikat.

- **Sederhanakan Konten Notifikasi**:
    - Menghapus `addText` redundant (teks yang sudah terwakili oleh title) di: `QueueWorker`, `SyncAllCommand`, `EmailController` (pensiun & delete), `TrashController` (restore & force delete).
    - Menghapus `addKeyValue('Status', 'Auto Backup...')` di `BackupCommand` yang tidak informatif.
    - Menggabungkan dua key-value `ID Job` dan `Tipe` menjadi satu baris `Job: #id — type` di `QueueWorker`.
    - Memperbarui title `SyncAllCommand` start notification agar menyertakan nama mode langsung (`SINKRONISASI HARIAN BERJALAN`).

- **Perbaikan Format Profil (`addUserProfile`)**:
    - NIP/NIK dipindah ke baris tersendiri dengan emoji 🪪 (sebelumnya inline dengan nama).
    - Nama, jabatan, dan unit kerja kini otomatis di-*uppercase* via `mb_strtoupper`.
    - NIK dihapus dari semua notifikasi Telegram (privasi) — hanya NIP yang ditampilkan.

- **Hapus Garis Divider**:
    - `addDivider()` dikosongkan di `TelegramMessageBuilder` — garis panjang tidak lagi muncul di bawah header, tanpa perlu mengubah semua caller.
    - Menyederhanakan logika spasi di `build()` (hapus kondisi khusus `title → divider`).

---

# [22 Juli 2026]


## Penyesuaian Ikon Upload & Identifier NIP Batch

- **Dukungan Identifier NIP pada Operasi Edit Massal**:
    - Menambahkan opsi pengenal **NIP** pada modul **Edit Akun Massal** (`batch/update.php`) dan **Edit PK Massal** (`batch/pk.php`).
    - Memperbarui pencarian massal di `EmailBatchService->processBatchUpdate()` untuk memproses pencarian berdasarkan NIP secara presisi (`whereIn('nip', $chunk)`) dengan penanganan pembersihan karakter pemisah (spasi, titik, garis hubung).

- **Perbaikan Sinkronisasi Pegawai & TTE (Unit Kerja Detail)**:
    - **Penanganan Status NO DATA**: Mengubah respons saat data tidak ditemukan di SIMPEG API agar dikembalikan sebagai `success: true` (`no_data: true`), sehingga dihitung sebagai proses sukses/terproses dan tidak lagi dihitung sebagai item gagal (`FAILED`).
    - **Perbaikan Respons Data Identik**: Memperbarui `EmailService->syncPegawaiFromApi()` agar mengembalikan `success: true` (`message: 'Data sudah terbaru'`) ketika data di SIMPEG API identik dengan DB lokal, mencegah frontend menandai status sebagai `FAILED`.
    - **Stabilitas cURL SIMPEG**: Menambahkan `timeout => 15` dan `verify => false` pada `PegawaiApi.php` untuk mencegah kesalahan jabat tangan SSL di lingkungan produksi.
    - **Pemicu Refresh Modal**: Memperbarui `global-sync-result-modal` di `layouts/main.php` sehingga peremajaan halaman (`location.reload()`) dipicu persis saat pengguna mengklik tombol **OK** pada modal hasil sinkronisasi.
    - **Penghapusan Kontainer Toast Kosong**: Menambahkan `MutationObserver` pada `toast-container` di `layouts/main.php` (dengan pemantauan perubahan class & style) agar kontainer pembungkus ikut dihapus secara instan dari DOM ketika semua banner toast telah ditutup atau disembunyikan (termasuk kelas `hidden` yang ditambahkan oleh utilitas Flowbite).
    - **Alert NO DATA Detail Akun**: Menambahkan penanganan modal alert "Data Tidak Ditemukan" pada `syncSinglePegawai` di `public/js/sync-helper.js` saat koneksi API berhasil namun NIP tidak ditemukan di database SIMPEG, dengan menampilkan pesan kesalahan yang ramah pengguna dan memuat nomor NIP pegawai yang bersangkutan.
    - **Tabel Riwayat Laporan**: Menambahkan kelas `whitespace-nowrap` pada header dan sel kolom **Waktu Mulai** di [history.php](file:///Users/abedzul/Desktop/htdocs/sinjai-emails/app/Views/email/exports/history.php) agar teks waktu/tanggal tidak dibungkus (wrap) ke baris baru.

- **Peningkatan Visual Form Impor Excel (Batch)**:
    - Menambahkan ikon FontAwesome `<i class="fas fa-file-excel"></i>` pada header card "Impor dari Excel (XLSX)".
    - Menambahkan ikon FontAwesome `<i class="fas fa-upload"></i>` pada label field input "File Excel".
    - Perubahan diterapkan pada seluruh halaman berorientasi batch: **Buat Akun Massal** (`batch/create.php`), **Edit Akun Massal** (`batch/update.php`), **Edit PK Massal** (`batch/pk.php`), dan **Buat Unit Kerja Massal** (`unit_kerja/batch_create.php`).

# [21 Juli 2026]

## Penyesuaian Aksi & Penangguhan Akun

- **Pemberhentian & Pemindahan Akun (`Pensiun / Pindah / Keluar`)**:
    - Memperbarui penamaan label dan notifikasi konfirmasi dari "Pensiun" menjadi **"Pensiun / Pindah / Keluar"** pada seluruh tampilan detail dan tabel utama.
    - Menggeser posisi tombol-tombol aksi di halaman detail (`detail.php` dan `admin_detail.php`) ke sudut kanan paling bawah secara rapi.
    - Mengintegrasikan pemanggilan API cPanel (`suspend_email_login` & `unsuspend_email_login`) secara wajib (*mandatory*), di mana proses akan dibatalkan dengan notifikasi error jika API cPanel gagal.
    - Menyesuaikan format notifikasi Telegram untuk pemberitahuan penangguhan akun (Pensiun/Pindah/Keluar) serta pemulihan akun (*restore*).

- **Standardisasi Notifikasi Telegram (3-Tier)**:
    - Melakukan *refactoring* ekstensif pada `TelegramMessageBuilder` untuk menghapus anomali *double/triple spacing* dan spasi ganda.
    - Menetapkan struktur 3-Tier konsisten di seluruh notifikasi sistem: Header, Divider, Body (dengan Label tebal otomatis), dan Footer/Timestamp.
    - Menerapkan format pesan yang terstandar pada controller: `EmailController`, `TrashController`, `BatchController`, `SyncAllCommand`, `QueueWorker`, `BackupCommand`, `AlertService`, dan `EmailService`.
    - Menggubah *wording* pesan peringatan menjadi lebih formal (contoh: "dibumihanguskan" menjadi "dihapus secara permanen").

## Refactor & Konsistensi Tampilan

- **Refactor Global Konsistensi Desain UI**:
    - **Card Border Radius**: Memperbarui kelengkungan seluruh kontainer kartu utama dan pembungkus form dari `rounded-lg` menjadi `rounded-2xl` agar lebih premium dan modern.
    - **Tipografi Kontras Tinggi**: Menyeragamkan elemen label input dan header tabel menggunakan gaya modern: `text-[10px] font-bold text-slate-400 uppercase tracking-widest`.
    - **Aksi Tabel Minimalis**: Mengganti pewarnaan tombol 'Hapus' di dalam seluruh tabel menjadi abu-abu netral secara default, dengan implementasi efek hover `.btn-table-danger` (merah) kustom pada `input.css` agar tabel tidak mencolok dan mata fokus pada data.
- **Penyelarasan Teks Placeholder**:
    - Memangkas kata 'contoh' dan melakukan standarisasi *Sentence case* pada atribut `placeholder` di seluruh form sistem.
    - Menyederhanakan seluruh placeholder pada form filter pencarian (yang sebelumnya panjang) menjadi cukup `"Cari..."` agar antarmuka lebih bersih dan to-the-point.
    - Menerapkan *class* Tailwind `placeholder-slate-400` pada seluruh field `<input>` dan `<textarea>` secara global untuk menjaga konsistensi warna yang elegan.

## Perbaikan Bug & Ketahanan Data

- **Fix Scroll Terkunci di Mobile (Tabel)**:
    - Menghapus kelas bawaan Tailwind `touch-pan-x` dari seluruh elemen kontainer tabel (seperti di modul *Email*, *Helpdesk*, dan *Audit Trail*).
    - Sebelumnya kelas ini memblokir perilaku sentuhan layar secara vertikal sehingga pengguna ponsel pintar tidak bisa menggulir layar web (scroll) ke bawah jika jari mereka berada di atas area tabel.

- **Validasi Ketat Duplikat NIK & NIP (`EmailService.php`)**:
    - Menambahkan validasi keamanan tambahan di form *Edit Profil* dan *Tambah Akun Baru*. Sistem kini otomatis memeriksa apakah NIP/NIK baru yang diinputkan telah terdaftar pada entitas akun/email pengguna lain.
    - Menampilkan *flash error message* yang jelas (contoh: *"NIP sudah digunakan oleh akun lain (budi@sinjaikab.go.id)"*).
- **Keterbacaan Filter Laporan (`EmailExportController.php`)**:
    - Memperbaiki tampilan paramter pencarian di halaman *Riwayat Laporan* yang sebelumnya hanya menampilkan angka ID database dari *Status ASN* (contoh: `ASN: 1`). Sistem kini membaca referensi master dan menampilkannya sebagai nama status yang jelas (contoh: `ASN: PNS`).
    - Menyederhanakan judul halaman dari "Riwayat Export Laporan" menjadi "Riwayat Laporan".

---

# [20 Juli 2026]

## Refactor & Penghapusan Fitur

- **Penghapusan Fitur Registrasi BSrE**:
    - Menghapus endpoint `POST /bsre/register` dari `Routes.php`.
    - Menghapus method `registerUser()` dari `BsreController.php` dan pustaka `BsreApi.php`.
    - Menghapus tombol pendaftaran BSrE ("Daftarkan BSrE" & "Register BSrE" batch) beserta fungsi JavaScript `registerBsreUser()` & `batchRegisterBsre()` pada tampilan detail akun (`detail.php`) dan detail unit kerja (`unit_kerja_detail.php`).
    - Menghapus berkas skrip uji coba redundan `public/test_bsre_reg.php`.

## Desain & Responsivitas Tampilan

- **Peningkatan Responsivitas Layout (Mobile-First & Touch Optimization)**:
    - **Layout Utama (`main.php`)**: Menyesuaikan padding konten utama pada smartphone (`p-4 sm:p-6`) agar lebih lega di layar kecil.
    - **Form Filter Row-by-Row**: Menghapus tombol toggle collapsible pada tampilan mobile sehingga seluruh field filter dan pencarian tampil terbuka secara urut baris per baris (*row-by-row*) dengan `gap-y-4 gap-x-4` yang konsisten pada halaman Email (`email/index.php`), Detail Unit Kerja (`unit_kerja_detail.php`), dan Audit Trail (`audit_log/index.php`).
    - **Grup Tombol Non-Scroll (`flex-wrap`)**: Menghilangkan scroll horizontal pada seluruh grup tombol aksi di modul Email, Detail Akun, Unit Kerja, dan Helpdesk. Tombol aksi kini menyesuaikan lebar layar secara alami (*flex-wrap*) tanpa bantuan scrollbar.
    - **Tabel Responsif Standar Tailwind**: Menggunakan utility class bawaan Tailwind CSS (`overflow-x-auto touch-pan-x`) serta batas lebar minimum (`min-w-[650px]` / `min-w-[700px]`) pada tabel data di modul Email, Unit Kerja, Audit Trail, dan Helpdesk untuk menjamin keterbacaan data di smartphone.
    - **Grafik Donut & Bar Responsif**: Menyesuaikan skala kontainer, padding (`p-4 sm:p-6`), dan scrollbar legenda grafik ApexCharts pada Dashboard (`home/index.php`), Detail Unit Kerja (`unit_kerja_detail.php`), Monitoring Web OPD (`web_opd/index.php`), dan Web Desa/Kelurahan (`web_desa_kelurahan/index.php`) untuk kenyamanan tampilan smartphone.

## Perbaikan Bug

- **Fix: Status Ekspor PDF Menunggu Terus (`Routes.php`, `QueueWorker.php`, `EmailExportController.php`)**:
    - Memperbaiki panggilan AJAX trigger worker di `history.php` yang memanggil `/api_trigger_queue` tetapi rute terdaftar menggunakan format `camelCase` (`apiTriggerQueue`), yang menyebabkan respons 404 dan antrean worker tidak pernah berjalan otomatis dari browser.
    - Memperbaiki ketidaksesuaian tipe payload job (`exportPdf` vs `export_pdf` & `exportAccountDetailPdf` vs `export_account_detail_pdf`) pada `EmailExportController.php` dan `QueueWorker.php` agar job ekspor PDF dapat diproses secara sempurna hingga selesai (*COMPLETED*).
- **Fix: Error 404 Halaman Hilang pada Ekspor CSV & File Ekspor Unit Kerja (`Routes.php`)**:
    - Memperbaiki ketidaksesuaian penamaan rute URL ekspor di `Routes.php` yang sebelumnya hanya mendaftarkan format `camelCase` (`exportUnitKerjaCsv`, `exportPnsExcel`, dsb.) sementara tampilan *view* dan skrip JS memanggil rute format `snake_case` (`export_unit_kerja_csv`, `export_single_perjanjian_kerja_pdf`, `eselon_detail`, dsb.).
    - Menambahkan rute alias *snake_case* pada `Routes.php` sehingga seluruh tautan ekspor (CSV, Excel, PDF, ZIP) dan detail eselon dapat diakses dengan normal tanpa error 404.
- **Fix: Layout Berantakan pada Halaman Detail Unit Kerja (`unit_kerja_detail.php`)**:
    - Memperbaiki tag HTML berlisensial/terbuka ganda pada bagian grup tombol header dan merapikan grid metrik unit kerja (`grid-cols-3 w-full lg:w-auto`) agar tampilan desktop dan tablet menjadi presisi dan simetris kembali.
- **Fix: Error "Invalid file" pada Halaman Email (Unit Kerja, Eselon, PNS, PPPK)**:
    - Memperbaiki ketidaksesuaian penamaan file *view* pada `EmailListController.php` yang sebelumnya menggunakan format *camelCase* (`email/unitKerjaList`) padahal file fisiknya menggunakan *snake_case* (`unit_kerja_list.php`).
    - Hal ini menyelesaikan masalah halaman error saat membuka navigasi pada modul Email.

---

# [10 Juli 2026]
## Refactor & Konsistensi Kode

- **UI: Peningkatan Lebar Filter Penggunaan Disk (`email/index.php`)**:
    - Meningkatkan lebar dropdown filter "Penggunaan Disk" di halaman daftar email (menjadi `lg:col-span-3` dan `md:col-span-4`).
    - Menyesuaikan lebar filter "Pencarian" menjadi `lg:col-span-5` dan `md:col-span-3` agar layout grid grid-cols-12 tetap seimbang dan presisi.

- **Fix: Pemulihan Kolom Database `pimpinan_desa` dari `pimpinanDesa`**:
    - Memperbaiki kesalahan penggantian nama kolom database `pimpinan_desa` (snake_case) menjadi `pimpinanDesa` (camelCase) yang tidak sengaja dilakukan oleh skrip penggantian method.
    - Mengembalikan ke format `pimpinan_desa` di seluruh berkas model, query SQL/ActiveRecord, array key, dan param view.
    - Mencegah error database: `Unknown column 'pimpinanDesa' in 'field list'` (DatabaseException #1054).

- **Fix: Pemulihan URI Route ke `snake_case` (`Routes.php`)**:
    - Mengembalikan seluruh URI path route (parameter pertama fungsi `$routes->get()`, `$routes->post()`, dsb.) ke format original `snake_case` (e.g. `sync_pegawai`, `process_batch_create`, `check_niknip`).
    - Hal ini menjamin kompabilitas penuh dengan seluruh pemanggilan AJAX fetch / XMLHttpRequest dari file frontend Javascript (`batch.js`, `sync-helper.js`, `unit-kerja-batch.js`) serta tag `<form>` pada Views.
    - Pemanggilan action method pada controller tetap dipertahankan menggunakan nama camelCase baru.

- **Refactor: Pembuatan `UnitKerjaService` dan Pemisahan Logika Bisnis (`UnitKerjaService.php` baru & `UnitKerjaController.php`)**:
    - Membuat `App\Domains\UnitKerja\Services\UnitKerjaService` untuk merangkum seluruh logika bisnis CRUD Unit Kerja, termasuk penanganan batch creation baik dari format textarea (newline-separated) maupun dari array JSON.
    - Merampingkan `UnitKerjaController` sehingga semua data-write dan data-mutation dialihkan sepenuhnya ke service.

- **Refactor: Pemindahan Manajemen Cache Dashboard ke Service (`DashboardService.php` & `HomeController.php`)**:
    - Memindahkan logika pengecekan dan penyimpanan cache dari `HomeController::dashboard()` ke `DashboardService::getSummaryData()`.
    - `HomeController` kini lebih ramping dan hanya mendelegasikan pengambilan data ke service.

- **Refactor: Pembuatan `AuthService` dan Pemisahan Logika Bisnis (`AuthService.php` baru & `AuthController.php`)**:
    - Membuat `App\Domains\Auth\Services\AuthService` sebagai tempat penampung seluruh logika bisnis autentikasi (pemeriksaan pendaftaran lokal, verifikasi SSO eksternal ke PegawaiApi, sinkronisasi nama, dan fallback otentikasi password lokal).
    - Merampingkan `AuthController::attemptLogin()` agar hanya menangani request input, inisialisasi session, pencatatan log audit, dan response redirect.

- **Standarisasi Penamaan Kelas Controller (Suffix `Controller`)**:
    - Melakukan rename/move pada 13 file controller yang sebelumnya tidak memiliki suffix `Controller` menjadi berakhiran `Controller` secara konsisten (e.g. `Home` -> `HomeController`, `Email` -> `EmailController`, `Bsre` -> `BsreController`, dsb.).
    - Memperbarui deklarasi nama kelas di dalam seluruh file tersebut dan memperbarui pemanggilannya di `app/Config/Routes.php`.

- **Standarisasi Penamaan Method (Refactor ke `camelCase`)**:
    - Mengubah 40+ method di controller yang sebelumnya menggunakan format `snake_case` menjadi `camelCase` (e.g. `edit_profile` -> `editProfile`, `eselon_list` -> `eselonList`, `export_unit_kerja_csv` -> `exportUnitKerjaCsv`, dsb.) untuk mengikuti standar PSR-12 dan konvensi CodeIgniter 4.
    - Menyelaraskan seluruh pemanggilan method di file route (`Routes.php`), internal service, dan model terkait.

- **Keamanan Route: Proteksi `api_trigger_queue` (`Routes.php`)**:
    - Route `GET /api_trigger_queue` sebelumnya tidak memiliki filter apapun dan bisa diakses oleh siapa saja tanpa autentikasi.
    - Ditambahkan filter `role:admin,super_admin` untuk membatasi akses hanya kepada administrator.

- **Keamanan Route: Konsolidasi Route BSrE (`Routes.php`)**:
    - Route `GET /bsre/check-status` sebelumnya berada di luar grup `bsre` sehingga bisa diakses oleh semua user yang login tanpa pembatasan role.
    - Dipindahkan ke dalam grup `bsre` yang sudah dilindungi filter `role:admin,super_admin`.

- **Refactor: Terpusatkan Manajemen Cache (`CacheService.php` baru)**:
    - Dibuat `App\Shared\Services\CacheService` sebagai titik terpusat untuk semua cache key dan TTL.
    - Mendefinisikan konstanta: `KEY_DASHBOARD_SUMMARY`, `KEY_EMAIL_SUMMARY`, `KEY_SYSTEM_HEALTH`, `TTL_DASHBOARD`, `TTL_EMAIL`, `TTL_HEALTH`.
    - Menyediakan method static: `invalidateDashboard()` dan `invalidateHealth()`.
    - Mengganti semua string literal cache key yang sebelumnya tersebar di **7 file berbeda** (`Email.php`, `EmailApi.php`, `TrashController.php`, `BatchController.php`, `EmailService.php`, `QueueWorker.php`, `SyncAllCommand.php`) dengan pemanggilan ke `CacheService`.

- **Refactor: Pisahkan Business Logic Helpdesk ke `HelpdeskService` (`HelpdeskService.php` baru)**:
    - Dibuat `App\Domains\Helpdesk\Services\HelpdeskService` dengan method `updateTicketStatus()`.
    - Logika cross-domain (insert ke `AssistanceModel` saat tiket selesai) dipindahkan dari `HelpdeskAdminController::updateStatus()` ke `HelpdeskService`.
    - `HelpdeskAdminController::updateStatus()` kini hanya bertugas menangani HTTP request dan response, delegasi ke service.

## Perbaikan & Ketahanan Sistem

- **Bugfix: BSrE Status API Selalu Offline di Production (`SystemHealthService.php`)**:
    - Ditemukan bahwa health check BSrE selalu mengembalikan status **DOWN** di lingkungan production meskipun server sebenarnya dapat dijangkau.
    - Penyebab: kode lama selalu melakukan TCP ping ke **port 443 (HTTPS)** secara hardcode, padahal `BSRE_BASE_URL` menggunakan skema `http://` yang seharusnya diuji di **port 80**.
    - Perbaikan: mengganti logika parsing host dengan `parse_url()` yang lebih andal, lalu menentukan port secara otomatis — menggunakan port eksplisit dari URL jika ada, atau fallback berdasarkan skema (`https` → 443, `http` → 80).
    - Sekarang mendukung semua format URL: `http://host`, `https://host`, maupun `http://host:PORT`.

- **Refactor Lookup Hosting Provider (`WebsiteService.php`) — Berbasis Hasil Diagnostik Production**:
    - Melakukan uji diagnostik langsung di server production via `public/iptest.php` dan menemukan bahwa:
        - `ipwhois.app` (HTTPS/port 443) ✅ **bekerja normal** — HTTP 200 dalam ~0.5 detik.
        - `ip-api.com` (HTTP/port 80) ❌ **port 80 diblokir** oleh firewall server production — errno 110 (Connection timed out).
        - `ipinfo.io` (HTTPS/port 443) ❌ **timeout** di server production.
    - Berdasarkan temuan di atas, seluruh logika fallback multi-endpoint (`ip-api.com` dan `ipinfo.io`) **dihapus** dan kode disederhanakan menjadi satu endpoint tunggal: `ipwhois.app`.
    - Menambahkan mekanisme **retry 3x dengan exponential backoff** (2s → 4s) khusus untuk `ipwhois.app` agar tahan terhadap kegagalan sementara.
    - Timeout per percobaan ditetapkan **5 detik** (naik dari 3 detik sebelumnya) agar memberi ruang yang cukup mengingat respons server bisa mencapai ~0.5 detik.
    - Memindahkan logika lookup ke method terpisah `resolveIspProvider()` agar `getHostingInfo()` lebih bersih dan mudah diuji.
    - Menghapus blok `sleep(2)` manual di `SyncAllCommand.php` yang tidak lagi diperlukan karena throttling sudah ditangani di dalam `resolveIspProvider()`.
    - Menambahkan format log yang konsisten: `IP-API Error for [domain] [endpoint]: [pesan] (percobaan X/Y)`.

- **Pembaruan Notifikasi Hasil Sinkronisasi Batch**:
    - Mengganti notifikasi `alert()` native browser (yang sering diblokir browser modern) dengan modal dialog kustom `showSyncResult(total, success, failed)` yang global didefinisikan di `main.php`.
    - Desain modal baru menggunakan warna hijau/merah/amber yang representatif lengkap dengan statistik Total, Berhasil, dan Gagal.
    - Diterapkan secara seragam di halaman: Website Desa, Pimpinan, Eselon, PNS List, PPPK List, PPPK Paruh Waktu, dan Detail Unit Kerja.
    - Menghapus komponen `syncProgressContainer` (progress bar) pada halaman Website Desa agar antarmuka lebih ringkas dan langsung memunculkan modal di akhir.

- **Penghapusan Dependensi & Migrasi Alpine.js ke Flowbite**:
    - Menghapus pemuatan Alpine.js CDN dari layout utama `main.php` untuk memangkas dependensi library pihak ketiga dan meningkatkan kecepatan load.
    - Merefaktor **User Dropdown** agar menggunakan **Flowbite Dropdown** (`data-dropdown-toggle`) secara murni tanpa reaktivitas inline Alpine.js.
    - Merefaktor **Global Flash Messages** agar menggunakan komponen **Flowbite Dismiss** (`data-dismiss-target`) dikombinasikan dengan timer transisi opacity vanilla JS (auto-dismiss 5 detik).

- **Ekstraksi & Refactor JavaScript Helper Global (`sync-helper.js`)**:
    - Membuat berkas javascript pembantu global baru `public/js/sync-helper.js` untuk memusatkan logika AJAX request, visual spinner/loading status, pewarnaan badge hasil, dan visualisasi error modal.
    - Mengekstraksi fungsi penanganan sinkronisasi status TTE (`syncSingleBsreStatus`, `syncAllBsreStatus`) dan sinkronisasi data pegawai (`syncSinglePegawai`) dari file PHP agar tidak terduplikasi.
    - Memotong ratusan baris kode JavaScript duplikat yang sebelumnya disematkan secara inline di dalam berkas views: `detail.php`, `eselon_detail.php`, `pimpinan.php`, `pimpinan_desa.php`, `pns_list.php`, `pppk_list.php`, dan `pppk_pw_list.php`.
    - **Penyempurnaan Keandalan JS**: Menambahkan *conditional safety wrapper* pada pemanggilan fungsi-fungsi eksternal seperti `getJsStatusColor` dan `showGlobalError`. Jika terjadi kegagalan muat script penunjang di luar layout utama, helper akan otomatis menggunakan *fallback* aman (default colors / alert bawaan) tanpa memicu error Javascript di konsol browser.
- **Pemantauan Layanan Eksternal (Health Check) Dashboard**:
    - Menerapkan **Metode Caching Hibrida** pada `SystemHealthService` untuk menyimpan status layanan eksternal (cPanel, BSrE, Pegawai API) di cache internal selama 5 menit guna menghilangkan overhead koneksi luar pada pemuatan halaman dashboard.
    - Menambahkan **Fallback On-Demand**: Jika cache kosong atau kedaluwarsa, dashboard API akan memicu pembaruan instan dan memperbarui cache secara synchronous.
    - Membuat perintah CLI baru `health:check-cache` (`HealthCheckCacheCommand.php`) agar cron job di production dapat merefresh status cache secara periodik di background tanpa membebani akses browser user.
    - Menerapkan **Inisialisasi Dinamis Berbasis API**: Layout dashboard (`home/index.php`) kini merender status layanan secara dinamis dari looping output API `/api/health-check` tanpa hardcoded daftar layanan di JavaScript.
    - Menambahkan **Mock Status & Dev Badge**: Di lingkungan localhost/development, status layanan disimulasikan sebagai `UP` (Online) dan di-render secara visual menggunakan warna indigo modern dengan label `"Online (Dev)"` untuk mempermudah pengujian visual oleh developer tanpa memicu lag.
    - Menambahkan detail penjelasan status rinci server pada attribute HTML `title` sewaktu di-hover.
    - Memperbarui dokumentasi `README.md` pada bagian Otomatisasi (Cron Job) dengan menambahkan instruksi konfigurasi cron job berkala untuk command `health:check-cache`.


- **Filter Inisialisasi Choices.js**: Membatasi inisialisasi Choices.js global di `main.php` hanya pada elemen select `.choices-search` yang memiliki **10 atau lebih pilihan**. Dropdown dengan sedikit opsi akan tetap menggunakan gaya dropdown native browser yang lebih hemat sumber daya dan ramah pengguna di perangkat mobile. Serta menambahkan pengaman *null-check* dan verifikasi *tagName* (`tagName === 'SELECT'`) agar tidak memicu error JS jika class tersebut disematkan pada elemen non-select. Diterapkan juga batas filter minimal 10 pilihan pada seluruh inisialisasi Choices.js manual/lokal di halaman Pimpinan, Pimpinan Desa, Assistance Form, Helpdesk Public Form, monitoring Website OPD (`web_opd/index.php`), serta monitoring Website Desa Kelurahan (`web_desa_kelurahan/index.php`). Menerapkan juga class `.choices-search` pada select filter Unit Kerja di halaman monitoring PNS (`pns_list.php`) dan halaman pengelolaan Unit Kerja (`unit_kerja/manage.php`) agar terintegrasi otomatis secara aman. **Perbaikan Tombol Reset**: Menambahkan penanganan *fallback* pada fungsi pembersih filter (`reset-filters`) di halaman monitoring agar ikut mengosongkan nilai (`value = ''`) pada select native browser jika instansiasi Choices.js di-bypass. **Placeholder Dinamis**: Menambahkan logika cerdas yang otomatis mendeteksi teks pilihan pertama (seperti "SEMUA UNIT KERJA" -> menjadi "Cari Unit Kerja...") untuk dijadikan placeholder pencarian di kotak dropdown Choices.js agar tampilan lebih rapi dan kontekstual. **Optimasi Asset Redundan**: Menghapus pemuatan script dan stylesheet Choices.js yang duplikat/redundran pada berkas `assistance/form.php` karena sudah dimuat secara terpusat oleh layout induk.











## Pembaruan Visual & UI
- **Penyelarasan Kolom & Tombol Website Desa**:
    - Mengubah nama kolom table header dari `Hosting & Server` menjadi `hosting / server` (huruf kecil) agar lebih konsisten dengan penulisan tag visual lainnya.
    - Menghapus tombol **Sync Data** per baris (individual sync) dari kolom Aksi pada tabel Website Desa, menyisakan hanya tombol Edit. Alur sinkronisasi kini dipusatkan sepenuhnya menggunakan tombol batch sync di header.

- **Penyelarasan Tampilan & Warna Placeholder**:
    - Menerapkan pewarnaan placeholder abu-abu konsisten (`placeholder-slate-400 font-medium text-slate-800`) secara menyeluruh pada kolom masukan teks pencarian filter di halaman monitoring: Email Monitoring (`email/index.php`), Detail Eselon (`email/eselon_detail.php`), Pimpinan (`email/pimpinan.php`), Pimpinan Desa (`email/pimpinan_desa.php`), Log Audit (`audit_log/index.php`), Pengelolaan Unit Kerja (`unit_kerja/manage.php`), Website OPD (`web_opd/index.php`), dan Website Desa Kelurahan (`web_desa_kelurahan/index.php`) agar senada dengan modul global search.


- **Kustomisasi Tema Warna Flowbite (Slate/Gray)**:
    - Mendefinisikan ulang warna `primary` di `tailwind.config.js` dengan shade warna Slate agar seluruh komponen bawaan Flowbite (yang menggunakan warna primer biru) secara otomatis mengikuti bahasa desain dashboard yang berwarna abu-abu gelap/slate.

- **Refaktor Komponen Modal Berbasis Flowbite Modal API**:
    - Memigrasikan seluruh logika reaktivitas open/close modal pada berkas `components/modal.php` ke pustaka **Flowbite Modal JS API** resmi, menggantikan transisi manual kelas CSS. Backdrops modal kini didorong secara dinamis menggunakan parameter Flowbite terpadu.

- **Refaktor Mobile Sidebar menggunakan Flowbite Drawer JS API**:
    - Mengganti overlay custom dan event listener manual untuk sidebar mobile dengan **Flowbite Drawer JS API**, menyederhanakan kode transisi off-canvas, penanganan scroll body, serta penutupan otomatis panel drawer saat navigasi di klik pada layar sentuh.


- **Implementasi Komponen Flowbite (Tooltip & Toggle Switch)**:
    - Menggantikan tooltip native browser (`title="..."`) yang kaku pada seluruh tombol aksi tabel utama (Edit, Detail, Edit PK, Dikelola Kominfo) di halaman Website Desa, Website OPD, PNS List, PPPK List, PPPK PW List, dan Detail Email menggunakan **Flowbite Tooltip** yang interaktif dan beranimasi halus.
    - Mengubah elemen dropdown pilihan `dikelola_kominfo` (YA/TIDAK) di formulir Website Desa menjadi komponen **Flowbite Toggle Switch** (saklar geser) yang lebih intuitif, dilengkapi dengan input tersembunyi agar integrasi post request tetap kompatibel.


- **Penyelarasan Teks Donut Chart (ApexCharts)**:
    - Menambahkan reset ukuran font untuk `.apexcharts-datalabel-value` dan `.apexcharts-datalabel-label` di `input.css` untuk membatalkan paksaan ukuran besar (`!important`) yang didorong oleh plugin Flowbite, mengembalikan ukuran teks total di tengah donut chart ke ukuran ideal bawaan (16px untuk nilai dan 10px untuk label).


- **Integrasi Penuh Flowbite CSS & JS**:
    - Menginstal paket npm `flowbite` dan mengonfigurasikannya ke dalam berkas `tailwind.config.js` sebagai plugin.
    - Menambahkan path `node_modules/flowbite/**/*.js` ke opsi `content` Tailwind config agar compiler dapat mengenali dan menghasilkan class component Flowbite.
    - Memuat Flowbite JS bundle dari CDN secara global di layout utama `main.php` untuk mendukung keaktifan komponen interaktif.


---

# [9 Juli 2026]


## Pembaruan Visual & UI
- **Penyelarasan Desain Halaman Publik**:
    - Mengubah intensitas bayangan kartu (*card shadow*) dari `shadow-2xl` menjadi `shadow-xl shadow-slate-200/50` di seluruh halaman publik (Verifikasi Akun, Verifikasi PDF, Helpdesk Form, Helpdesk Success, dan Halaman Error) agar senada dengan estetika landing page.
    - Menyelaraskan teks hak cipta pada footer kartu agar menggunakan warna abu-abu yang lebih halus (`text-slate-400` menggantikan `text-slate-700` yang sebelumnya terlalu kontras).
    - Menambahkan komponen footer kartu secara konsisten pada halaman *Helpdesk Form* dan *Helpdesk Success*.
- **Perbaikan HTML Bug**:
    - Menghapus tag penutup `</div>` ganda yang redundan di bagian akhir file `public_success.php` untuk memastikan struktur HTML tervalidasi bersih.
- **Penyempurnaan Halaman Monitoring & Daftar (Desa, OPD, Pimpinan & Kepala Desa)**:
    - Mengintegrasikan Choices.js pada dropdown filter form (Tipe, Platform, Status, dan Status TTE) untuk memberikan indikator dropdown yang jelas dan visual yang seragam.
    - Menambahkan blok gaya CSS kustom untuk merapikan Choices.js: menyelaraskan tinggi dropdown (38px), warna border (`#e2e8f0`), ukuran teks (`text-sm`), dan padding agar persis sama dengan input pencarian teks serta tombol reset sehingga berada di satu baris horizontal yang rapi di seluruh halaman monitoring.
    - Menghapus kelas `overflow-hidden` pada pembungkus kartu utama (`bg-white rounded-lg`) di halaman Website Desa, Website OPD, dan formulir pengaduan publik (`helpdesk/public_form.php`) untuk membiarkan menu pilihan dropdown Choices.js melayang di atas konten secara alami (*overflow visible*) tanpa terpotong (*clipped*). Khusus halaman formulir pengaduan publik, ditambahkan pula kelas `rounded-t-2xl` pada header dan `rounded-b-2xl` pada footer untuk menjaga kebulatan sudut kartu tetap estetik.
    - Menetapkan lebar kolom **Status TTE** pada tabel pimpinan dan kepala desa menjadi **`w-48`** (192px) untuk menjaga konsistensi lebar kolom dan mencegah layout bergeser (*jiggling*) ketika status diperbarui secara dinamis (seperti saat tombol sinkronisasi TTE ditekan).
    - Mengembalikan pembungkus padding kontainer `p-6` yang sebelumnya tidak sengaja terhapus di halaman Website OPD.
    - Menyederhanakan badge status domain pada tabel website agar hanya memiliki 2 status utama (AKTIF - hijau dan NONAKTIF - merah) demi kesederhanaan visual.
    - Mengimplementasikan **Pencarian & Filter Instan Client-Side (JavaScript)** pada halaman Website Desa, Website OPD, Daftar Pimpinan, dan Daftar Kepala Desa: Seluruh pencarian teks dan filter dropdown dilakukan secara langsung di browser secara instan tanpa memicu muat ulang halaman (*zero page reload*). Khusus pimpinan dan kepala desa, kueri pencarian teks kini juga mencakup pencarian berdasarkan **Nama Unit Kerja / Nama Instansi**, serta menyesuaikan placeholder input pencarian agar berbunyi *"Cari nama, NIP, NIK, email, atau unit kerja..."*.
    - Menghubungkan URL ekspor PDF secara dinamis di mana tautan unduhan PDF otomatis menyesuaikan dengan parameter kueri filter aktif saat ini di layar.
    - Meningkatkan batas baris data per halaman (*per page*) dari 100 menjadi **200** baris data untuk memastikan seluruh data langsung tampil dalam satu halaman utuh tanpa membutuhkan navigasi halaman (pagination). Hapus filter Kecamatan untuk menyederhanakan antarmuka pencarian.

## Optimasi Performa & API
- **Pelacakan & Pemantauan Hosting Website Desa**:
    - Menambahkan migrasi database baru (`2026-07-09-233000_AddHostingFieldsToWebDesaKelurahan`) untuk menyediakan kolom `ip_address`, `hosting_provider`, dan `hosting_status` pada tabel `web_desa_kelurahan`.
    - Mengintegrasikan API Pelacak **IP-API** (`http://ip-api.com`) secara otomatis untuk melacak penyedia server/ISP hosting (contoh: Kominfo, Hostinger, Niagahoster) berdasarkan IP address domain website desa.
    - Mengimplementasikan sistem optimasi cashing/rate-limit API: server tidak akan memanggil API IP-API jika IP domain yang ter-resolve tidak berubah dari IP yang saat ini disimpan di database.
    - Membuat sistem **Uji Port Server (HTTP/HTTPS)** otomatis via `fsockopen` untuk mendeteksi apakah server hosting aktif/online secara real-time (diwakili oleh indikator dot hijau/merah di sebelah IP).
    - Memperbarui halaman utama monitoring desa dan halaman form edit desa untuk menampilkan informasi IP Address, nama ISP/Provider, serta status port aktif.
    - Memperbarui perintah sinkronisasi otomatis CLI `php spark sync:all` agar menyinkronkan data domain dan data hosting (IP, ISP, Port) sekaligus.
    - Menambahkan tombol sync per-baris (ikon `fa-sync`) di kolom Aksi pada tabel website desa, sehingga pengguna dapat menyinkronkan data per website secara individual tanpa perlu menjalankan batch sync keseluruhan.

## Perbaikan Bug
- **Tombol Sync Expiration Website Desa tidak berfungsi**:
    - Memperbaiki logic controller `sync_expiration`: sebelumnya mengembalikan `status: error` ketika tidak ada data baru yang perlu diperbarui (misalnya domain tidak dapat di-resolve dan IP tidak berubah), padahal seharusnya tetap mengembalikan `status: success` dengan data terbaru dari database. Kini controller selalu merespons sukses selama record website ditemukan.
    - Memperbaiki `startBatchSync()` di JavaScript: progress container (`#syncProgressContainer`) sebelumnya tidak pernah ditampilkan karena class `hidden` tidak dihapus, dan counter (`X/80`) tidak diperbarui. Kini progress bar dan counter diperbarui secara real-time saat proses batch sync berjalan.
    - Menambahkan tombol sync per-baris yang sebelumnya tidak ada di kolom Aksi, menjadi penyebab utama tombol sync tidak bisa diklik dari tabel.
- **Resolusi Pemblokiran Halaman (Session / Connection Lock)**:
    - Merombak total `SystemHealthService` untuk mem-bypass panggilan API eksternal saat berjalan di mode lokal/development (`env('CI_ENVIRONMENT') === 'development'`). Hal ini menyelesaikan masalah "sangat lama berpindah halaman" di localhost akibat server built-in PHP yang single-threaded tertahan oleh request health check.
    - Mengganti panggilan fungsi API penuh yang berat di `SystemHealthService` (seperti mengambil ratusan akun email cPanel atau mengambil detail pegawai) menjadi uji konektivitas port jaringan soket cepat (**`fsockopen`**) dengan batas waktu (*timeout*) 1.5 detik. Hal ini membuat status layanan di dashboard termuat instan baik di local maupun production.
- **Toleransi SSL di Localhost**:
    - Mengonfigurasi `PegawaiApi.php` agar secara dinamis menonaktifkan verifikasi SSL (`'verify' => false`) saat berjalan di lingkungan localhost/development untuk menghindari kegagalan cURL SSL saat otentikasi lokal.

---

# [8 Juli 2026]

## Fitur Baru
- **Filter & Pagination Halaman Audit Trail**:
    - Menambahkan form filter pada halaman audit trail dengan tiga parameter: aksi (`action`), entitas (`entity`), dan pencarian nama/username pengguna.
    - Data kini dipaginasi 50 baris per halaman menggunakan komponen pagination standar, menggantikan hard limit 200 data sebelumnya.
    - Badge kolom "Aksi" kini berwarna berbeda per jenis: DELETE=merah, UPDATE=biru, CREATE=hijau, LOGIN=ungu, LOGOUT=abu-abu.
    - `AuditLogModel` diperbarui dengan method `applyFilters()`, `getDistinctActions()`, dan `getDistinctEntities()`.
- **Pencatatan Audit Trail untuk Ekspor Laporan**:
    - Menambahkan pemanggilan `log_audit()` otomatis untuk mencatat aksi `EXPORT` setiap kali pengguna mengunduh atau mengantrekan laporan (PDF, Excel, CSV, ZIP) dari berbagai modul (Email, Website Desa, Website OPD, Log Layanan).
- **Pencatatan Audit Trail untuk Login & Logout**:
    - Menambahkan pencatatan audit otomatis dengan aksi `LOGIN` dan `LOGOUT` pada saat pengguna masuk dan keluar dari sistem untuk meningkatkan visibilitas keamanan sistem.
- **Halaman Beranda / Landing Page Publik Baru**:
    - Memindahkan rute utama `/` keluar dari filter `auth` agar dapat diakses oleh publik sebagai landing page resmi yang modern dan responsif.
    - Membuat view `home/landing.php` dengan Hero section yang elegan, 3 card aksi cepat (Verifikasi PDF, Helpdesk Layanan, dan Portal Login Admin), serta melengkapinya dengan **OpenGraph & Twitter Card meta tags** untuk memuat berkas **`meta.png`** saat tautan dibagikan.
    - Menyelaraskan teks judul utama dan judul halaman pada landing page menjadi **"Pemerintah Kabupaten Sinjai"** (sebelumnya hanya "Kabupaten Sinjai") agar konsisten dengan halaman publik lainnya.

    - Menambahkan rute `/dashboard` terproteksi sebagai portal utama bagi administrator yang telah login, lengkap dengan auto-redirection dari halaman landing page jika sesi login aktif.
    - Menyederhanakan halaman login (`auth/login.php`) dengan menghapus tautan eksternal "Verifikasi PDF" dan "Helpdesk" (karena sudah diakomodasi di landing page), lalu menambahkan tautan navigasi balik **"Kembali ke Beranda"** serta memberikan atribut **`autofocus`** pada input username untuk kemudahan pengisian form.






## Penambahan Data
- **Tambah Platform Baru — KOMINFO**:
    - Menambahkan platform `KOMINFO` ke tabel `platforms` melalui migration `2026-07-08-153748_AddKominfoPlatform.php`.
    - Platform ini kini tersedia sebagai pilihan di dropdown form edit Website Desa & Kelurahan serta filter halaman monitoring.

## Perbaikan Bug
- **KOMINFO tidak muncul di chart distribusi platform**:
    - Menambahkan `KOMINFO` ke array sort order di `WebsiteService::getDesaKelurahanPlatformStats()` (posisi 1/pertama).
    - Tanpa entri ini, platform dengan urutan tak terdefinisi berpotensi tidak dirender dengan benar di chart ApexCharts.
- **Urutan dropdown platform** — KOMINFO ditampilkan sebagai pilihan pertama di:
    - Filter halaman index Website Desa & Kelurahan
    - Form edit Website Desa & Kelurahan
- **Konsistensi Judul Halaman & Judul Besar (H1)**:
    - Menyelaraskan `<title>` dan judul besar `<h1>` pada beberapa halaman agar sesuai dengan nama menu di sidebar:
        - **PNS**: `'Daftar PNS'` -> `'PNS'`
        - **PPPK**: `'PPPK Penuh Waktu'` -> `'PPPK'`
        - **PPPK PW**: `'PPPK Paruh Waktu'` -> `'PPPK PW'`
        - **Website Desa dan Kelurahan**: `'Website Desa & Kelurahan'` -> `'Website Desa dan Kelurahan'`
        - **User Login**: `'Manajemen User' / 'Manajemen User Login'` -> `'User Login'`
        - **Unit Kerja**: `'Master Data Unit Kerja'` -> `'Unit Kerja'`
        - **Kotak Sampah**: `'Manajemen Sampah (Soft Deleted)' / 'Manajemen Sampah'` -> `'Kotak Sampah'`
        - **API Gateway**: `'API Gateway Documentation'` -> `'API Gateway'`
    - Menghapus subjudul statis (keterangan/deskripsi di bawah `<h1>`) di halaman **API Gateway**, **Audit Trail**, **Kotak Sampah**, **Tukar Data Akun**, dan **Riwayat Laporan** agar tampilan lebih bersih dan konsisten.
- **Penyelarasan Rute API Gateway**:
    - Mengubah rute halaman dokumentasi API Gateway dari `/api-docs` menjadi `/api-gateway` agar selaras dan konsisten dengan teks menu sidebar "API Gateway".
- **Penyelarasan Rute Halaman Sesuai Nama Menu**:
    - Mengubah rute-rute daftar pegawai dan audit agar sesuai dengan nama halamannya:
        - **PNS**: `email/pns_list` -> `/email/pns`
        - **PPPK**: `email/pppk_list` -> `/email/pppk`
        - **PPPK PW**: `email/pppk_pw_list` -> `/email/pppk-pw`
        - **Eselon**: `email/eselon_list` -> `/email/eselon`
        - **Audit Trail**: `audit_logs` -> `/audit-trail`
    - Menyesuaikan semua tautan navigasi di sidebar serta form action/reset URL di view `audit_log/index.php`.

- **Restrukturisasi & Pengamanan Rute Eksekusi Batch**:
    - Memindahkan rute `batch_execute_update` dan `batch_execute_create` ke dalam grup `/batch` menjadi `/batch/execute_update` dan `/batch/execute_create`.
    - Perubahan ini membuat rute eksekusi batch otomatis dilindungi oleh filter `role:admin,super_admin` untuk keamanan yang lebih baik.





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
