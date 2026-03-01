<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Perjanjian Kerja - <?= esc($email['name'] ?? 'Pegawai') ?></title>
    <style>
        /* 
           LAYOUT & TYPOGRAPHY
           -------------------
           Using standard A4 legal margins.
           Font embedded directly for portability.
        */
        @page {
            margin: 2cm 2cm 2.5cm 2cm;
            size: A4;
        }

        @font-face {
            font-family: 'Bookman Old Style';
            src: url(data:font/truetype;charset=utf-8;base64,<?= base64_encode(file_get_contents(FCPATH . 'fonts/bookmanoldstyle.ttf')) ?>) format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Bookman Old Style';
            src: url(data:font/truetype;charset=utf-8;base64,<?= base64_encode(file_get_contents(FCPATH . 'fonts/bookmanoldstyle_bold.ttf')) ?>) format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        @font-face {
            font-family: 'Bookman Old Style';
            src: url(data:font/truetype;charset=utf-8;base64,<?= base64_encode(file_get_contents(FCPATH . 'fonts/bookmanoldstyle_italic.ttf')) ?>) format('truetype');
            font-weight: normal;
            font-style: italic;
        }

        @font-face {
            font-family: 'Bookman Old Style';
            src: url(data:font/truetype;charset=utf-8;base64,<?= base64_encode(file_get_contents(FCPATH . 'fonts/bookmanoldstyle_bolditalic.ttf')) ?>) format('truetype');
            font-weight: bold;
            font-style: italic;
        }

        body {
            font-family: "Bookman Old Style", serif;
            font-size: 10pt;
            margin: 0;
            line-height: 1.5;
            color: #000;
        }

        /* 
           TABLE-BASED LAYOUT ENGINE 
           -------------------------
           Using tables instead of floats/flex/grid for maximum Dompdf compatibility.
        */
        table {
            width: 100%;
            border-collapse: collapse;
            border: 0;
            margin: 0;
        }

        td {
            vertical-align: top;
            padding: 0;
        }

        /* Utility Classes */
        .text-center {
            text-align: center;
        }

        .text-justify {
            text-align: justify;
        }

        .text-bold,
        strong {
            font-weight: bold;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        .no-margin {
            margin: 0;
        }

        /* Prevent splitting logical blocks (Ayat/Sub-points) */
        .keep-together {
            page-break-inside: avoid;
        }

        /* KOP SURAT */
        .kop-container {
            margin-bottom: 25px;
            text-align: center;
        }

        .kop-img {
            width: 2cm;
            height: auto;
            margin-bottom: 10px;
        }

        .kop-title {
            font-size: 8pt;
            font-weight: bold;
            margin: 0;
        }

        .kop-subtitle {
            font-size: 10pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 20px 0 0 0;
        }

        .kop-number {
            font-size: 10pt;
            margin: 0;
        }

        /* ARTICLE HEADERS (BAB/PASAL) */
        .pasal-header {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        /* LIST SYSTEMS (Table-based) */
        .list-row {
            margin-bottom: 2px;
        }

        .num-col {
            width: 35px;
            text-align: left;
        }

        /* Level 2: a, b ... */
        .sub-list {
            width: 100%;
            margin-top: 0;
        }

        .sub-num-col {
            width: 30px;
        }

        /* Level 3: 1), 2) ... */
        .sub-sub-list {
            width: 100%;
        }

        .sub-sub-num-col {
            width: 30px;
        }

        /* DATA INFO TABLES (Label : Value) */
        .info-table {
            width: 100%;
        }

        .info-label {
            width: 170px;
        }

        .info-sep {
            width: 15px;
            text-align: center;
        }

        /* SIGNATURES */
        .signature-table {
            margin-top: 40px;
            width: 100%;
            page-break-inside: avoid;
        }

        .sig-cell {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .sig-space {
            height: 120px;
            vertical-align: middle;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
    <?php
    // Construct full name with titles
    $fullName = strtoupper($email['name'] ?? '');
    if (!empty($email['gelar_depan'])) {
        $fullName = $email['gelar_depan'] . ' ' . $fullName;
    }
    if (!empty($email['gelar_belakang'])) {
        $fullName .= ', ' . $email['gelar_belakang'];
    }
    ?>
</head>

<body>

    <!-- HEADER / KOP -->
    <div class="kop-container">
        <img src="<?= $logoSrc ?>" alt="Garuda" class="kop-img">
        <div class="kop-title text-uppercase">BUPATI SINJAI</div>
        <div class="kop-title text-uppercase">PROVINSI SULAWESI SELATAN</div>
        <div class="kop-subtitle text-uppercase">PERPANJANGAN PERJANJIAN KERJA</div>
        <div class="kop-number">Nomor : 800.1.2.5/29.<?= esc($pk_data['nomor'] ?? 'N/A') ?>/PPPK/2026/BKPSDMA</div>
    </div>

    <!-- PREAMBLE -->
    <div class="text-justify">
        Pada hari ini <strong>SENIN</strong> tanggal <strong>TIGA</strong> bulan <strong>MARET</strong> tahun <strong>DUA RIBU DUA PULUH ENAM</strong> yang bertandatangan di bawah ini:
    </div>

    <!-- PARTIES (PIHAK) -->
    <table style="margin-top: 10px;">
        <!-- Pihak 1 -->
        <tr>
            <td class="num-col">I.</td>
            <td class="text-justify">
                <strong>BUPATI SINJAI</strong> untuk selanjutnya disebut Pihak Kesatu.
            </td>
        </tr>
        <!-- Spacer -->
        <tr>
            <td colspan="2" style="height: 5px;"></td>
        </tr>

        <!-- Pihak 2 -->
        <tr>
            <td class="num-col">II.</td>
            <td class="text-justify">
                <table class="info-table">
                    <tr>
                        <td class="info-label">Nama</td>
                        <td class="info-sep">:</td>
                        <td class="info-val"><strong><?= esc($fullName) ?></strong></td>
                    </tr>
                    <tr>
                        <td class="info-label">Nomor Induk PPPK</td>
                        <td class="info-sep">:</td>
                        <td class="info-val"><?= esc($email['nip'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Tempat/Tgl. lahir</td>
                        <td class="info-sep">:</td>
                        <td class="info-val">
                            <?= esc($email['tempat_lahir'] ?? 'N/A') ?> /
                            <?= formatTanggal($email['tanggal_lahir'] ?? null) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">Pendidikan</td>
                        <td class="info-sep">:</td>
                        <td class="info-val text-uppercase"><?= esc($email['pendidikan'] ?? 'N/A') ?></td>
                    </tr>
                </table>
                <div style="margin-top: 5px;">
                    dalam hal ini bertindak untuk dan atas nama diri sendiri, untuk selanjutnya disebut Pihak Kedua.
                </div>
            </td>
        </tr>
    </table>

    <div class="text-justify" style="margin-top: 10px;">
        Pihak Kesatu dan Pihak Kedua sepakat untuk mengikatkan diri satu sama lain dalam Perpanjangan Perjanjian Kerja dengan ketentuan sebagaimana dituangkan dalam Pasal-Pasal sebagai berikut:
    </div>

    <!-- PASAL 1 -->
    <div class="pasal-header">
        Pasal 1 <br>
        MASA PERPANJANGAN PERJANJIAN KERJA, JABATAN, DAN UNIT KERJA
    </div>
    <div class="text-justify pasal-content">
        Pihak Kesatu menerima dan mempekerjakan Pihak Kedua sebagai Pegawai Pemerintah dengan Perpanjangan Perjanjian Kerja dengan ketentuan sebagai berikut:

        <table class="sub-list" style="page-break-inside: avoid; margin-top: 5px;">
            <tr>
                <td class="sub-num-col">a.</td>
                <td>
                    <table class="info-table">
                        <tr>
                            <td class="info-label" style="width: 240px;">Masa Perpanjangan Perjanjian Kerja</td>
                            <td class="info-sep">:</td>
                            <td class="info-val">
                                <strong><?= formatStrip($pk_data['tanggal_kontrak_awal'] ?? '2026-03-01') ?></strong>
                                s.d.
                                <strong><?= formatStrip($pk_data['tanggal_kontrak_akhir'] ?? '2029-02-28') ?></strong>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="sub-num-col">b.</td>
                <td>
                    <table class="info-table">
                        <tr>
                            <td class="info-label" style="width: 240px;">Jabatan</td>
                            <td class="info-sep">:</td>
                            <td class="info-val"><?= esc(strtoupper($email['jabatan'] ?? 'N/A')) ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="sub-num-col">c.</td>
                <td>
                    <table class="info-table">
                        <tr>
                            <td class="info-label" style="width: 240px;">Masa Kerja Sebelumnya</td>
                            <td class="info-sep">:</td>
                            <td class="info-val">1 tahun (<?= formatStrip('2025-03-01') ?> s.d. <?= formatStrip('2026-02-28') ?>)</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="sub-num-col">d.</td>
                <td>
                    <table class="info-table">
                        <tr>
                            <td class="info-label" style="width: 240px;">Unit Kerja</td>
                            <td class="info-sep">:</td>
                            <td class="info-val"><?= esc(strtoupper($unit_kerja['nama_unit_kerja'] ?? 'N/A')) ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- PASAL 2 -->
    <div class="pasal-header">
        Pasal 2 <br>
        TUGAS PEKERJAAN
    </div>
    <table class="pasal-content">
        <tr class="keep-together">
            <td class="num-col">(1)</td>
            <td class="text-justify">Pihak Kesatu membuat dan menetapkan tugas pekerjaan yang harus dilaksanakan oleh Pihak Kedua.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(2)</td>
            <td class="text-justify">Pihak Kedua wajib melaksanakan tugas pekerjaan yang diberikan Pihak Kesatu dengan sebaik baiknya dan rasa tanggung jawab.</td>
        </tr>
    </table>

    <!-- PASAL 3 -->
    <div class="pasal-header">
        Pasal 3 <br>
        TARGET KINERJA
    </div>
    <table class="pasal-content">
        <tr class="keep-together">
            <td class="num-col">(1)</td>
            <td class="text-justify">Pihak Kesatu membuat dan menetapkan target kinerja bagi Pihak Kedua selama masa Perpanjangan Perjanjian Kerja.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(2)</td>
            <td class="text-justify">Pihak Kedua wajib memenuhi target kinerja yang telah ditetapkan oleh Pihak Kesatu.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(3)</td>
            <td class="text-justify">Pihak Kesatu dan Pihak Kedua menandatangani target perjanjian kinerja sesuai peraturan perundang-undangan.</td>
        </tr>
    </table>

    <!-- PASAL 4 -->
    <div class="pasal-header">
        Pasal 4 <br>
        HARI KERJA DAN JAM KERJA
    </div>
    <div class="text-justify pasal-content">
        Pihak Kedua wajib bekerja sesuai dengan hari kerja dan jam kerja yang berlaku di instansi Pihak Kesatu.
    </div>

    <!-- PASAL 5 -->
    <div class="pasal-header">
        Pasal 5 <br>
        DISIPLIN
    </div>

    <!-- Ayat (1) -->
    <table class="pasal-content">
        <tr>
            <td class="num-col">(1)</td>
            <td class="text-justify">Pihak Kedua wajib mematuhi semua kewajiban dan larangan.</td>
        </tr>
    </table>

    <!-- Ayat (2) -->
    <table class="pasal-content">
        <tr>
            <td class="num-col">(2)</td>
            <td class="text-justify">
                Kewajiban bagi Pihak Kedua sebagaimana dimaksud pada ayat (1) meliputi:
            </td>
        </tr>
    </table>
    <?php
    $items = [
        'setia dan taat pada Pancasila, Undang-Undang Dasar Negara Republik Indonesia Tahun 1945, Negara Kesatuan Republik Indonesia dan pemerintah;',
        'menjaga persatuan dan kesatuan bangsa;',
        'melaksanakan kebijakan yang ditetapkan oleh pejabat pemerintah yang berwenang;',
        'menaati ketentuan peraturan perundang-undangan;',
        'melaksanakan tugas kedinasan dengan penuh pengabdian, kejujuran, kesadaran, dan tanggung jawab;',
        'menunjukkan integritas dan keteladanan dalam sikap, perilaku, ucapan, dan tindakan kepada setiap orang, baik di dalam maupun di luar kedinasan;',
        'menyimpan rahasia jabatan dan hanya dapat mengemukakan rahasia jabatan sesuai dengan ketentuan peraturan perundang-undangan; dan',
        'bersedia ditempatkan di seluruh wilayah Negara Kesatuan Republik Indonesia.'
    ];
    $alpha = 'a';
    foreach ($items as $item): ?>
        <table class="list-row" style="page-break-inside: avoid;">
            <tr>
                <td class="num-col"></td>
                <td>
                    <table class="sub-list">
                        <tr>
                            <td class="sub-num-col"><?= $alpha++ ?>.</td>
                            <td class="text-justify"><?= $item ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    <?php endforeach; ?>

    <!-- Ayat (3) -->
    <table class="pasal-content">
        <tr>
            <td class="num-col">(3)</td>
            <td class="text-justify">
                Selain memenuhi kewajiban sebagaimana dimaksud dalam Pasal 5 ayat (2), Pihak Kedua wajib:
            </td>
        </tr>
    </table>
    <?php
    $items = [
        'menghadiri dan mengucapkan sumpah / janji PPPK;',
        'menghadiri dan mengucapkan sumpah / janji jabatan;',
        'mengutamakan kepentingan Negara daripada kepentingan pribadi, seseorang, dan/atau golongan;',
        'melaporkan dengan segera kepada atasan apabila mengetahui ada hal yang dapat membahayakan keamanan negara atau merugikan keuangan negara;',
        'masuk kerja dan menaati ketentuan jam kerja;',
        'menggunakan dan memelihara barang-barang milik negara dengan sebaik-baiknya; dan',
        'menolak segala bentuk pemberian yang berkaitan dengan tugas dan fungsi kecuali penghasilan sesuai dengan ketentuan peraturan perundang-undangan.'
    ];
    $alpha = 'a';
    foreach ($items as $item): ?>
        <table class="list-row" style="page-break-inside: avoid;">
            <tr>
                <td class="num-col"></td>
                <td>
                    <table class="sub-list">
                        <tr>
                            <td class="sub-num-col"><?= $alpha++ ?>.</td>
                            <td class="text-justify"><?= $item ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    <?php endforeach; ?>

    <!-- Ayat (4) -->
    <table class="pasal-content">
        <tr>
            <td class="num-col">(4)</td>
            <td class="text-justify">
                Larangan bagi Pihak Kedua sebagaimana dimaksud pada ayat (1) meliputi:
            </td>
        </tr>
    </table>
    <?php
    $simpleItems = [
        'menyalahgunakan wewenang;',
        'menjadi perantara untuk mendapatkan keuntungan pribadi dan/ atau orang lain dengan menggunakan kewenangan orang lain yang diduga terjadi konflik kepentingan dengan jabatan;',
        'menjadi pegawai atau bekerja untuk negara lain;',
        'bekerja pada lembaga atau organisasi internasional tanpa izin atau tanpa ditugaskan oleh Pejabat Pembina Kepegawaian;',
        'bekerja pada perusahaan asing, konsultan asing, atau lembaga swadaya masyarakat asing kecuali ditugaskan oleh Pejabat Pembina Kepegawaian;',
        'memiliki, menjual, membeli, menggadaikan, menyewakan, atau meminjamkan barang-barang baik bergerak atau tidak bergerak, dokumen atau surat berharga milik negara secara tidak sah;',
        'melakukan pungutan di luar ketentuan;',
        'melakukan kegiatan yang merugikan negara;',
        'menghalangi berjalannya tugas kedinasan;',
        'menerima hadiah yang berhubungan dengan jabatan dan/atau pekerjaan;',
        'meminta sesuatu yang berhubungan dengan jabatan;',
        'melakukan tindakan atau tidak melakukan tindakan yang dapat mengakibatkan kerugian bagi yang dilayani;'
    ];
    $alpha = 'a';
    foreach ($simpleItems as $item): ?>
        <table class="list-row" style="page-break-inside: avoid;">
            <tr>
                <td class="num-col"></td>
                <td>
                    <table class="sub-list">
                        <tr>
                            <td class="sub-num-col"><?= $alpha++ ?>.</td>
                            <td class="text-justify"><?= $item ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    <?php endforeach; ?>

    <!-- Item M - Politik -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col"><?= $alpha++ ?>.</td>
                        <td class="text-justify">
                            memberikan dukungan kepada Calon Presiden/Wakil Presiden, Calon Kepala Daerah/Wakil Kepala Daerah, Calon Anggota Dewan Perwakilan Rakyat, Calon Anggota Dewan Perwakilan Daerah, atau Calon Anggota Dewan Perwakilan Rakyat Daerah dengan cara:
                            <table class="sub-sub-list" style="margin-top: 2px;">
                                <tr>
                                    <td class="sub-sub-num-col">1)</td>
                                    <td>ikut kampanye;</td>
                                </tr>
                                <tr>
                                    <td class="sub-sub-num-col">2)</td>
                                    <td>menjadi peserta kampanye dengan menggunakan atribut partai atau atribut PPPK;</td>
                                </tr>
                                <tr>
                                    <td class="sub-sub-num-col">3)</td>
                                    <td>sebagai peserta kampanye dengan mengerahkan PPPK lain;</td>
                                </tr>
                                <tr>
                                    <td class="sub-sub-num-col">4)</td>
                                    <td>sebagai peserta kampanye dengan menggunakan fasilitas Negara;</td>
                                </tr>
                                <tr>
                                    <td class="sub-sub-num-col">5)</td>
                                    <td>membuat keputusan dan/atau tindakan yang menguntungkan atau merugikan salah satu pasangan calon sebelum, selama dan sesudah masa kampanye;</td>
                                </tr>
                                <tr>
                                    <td class="sub-sub-num-col">6)</td>
                                    <td>mengadakan kegiatan yang mengarah kepada keberpihakan terhadap pasangan calon yang menjadi peserta pemilu sebelum, selama, dan, sesudah masa kampanye meliputi pertemuan, ajakan, himbauan, seruan, atau pemberian barang kepada PPPK dalam lingkungan unit kerjanya, anggota keluarga, dan masyarakat; dan</td>
                                </tr>
                                <tr>
                                    <td class="sub-sub-num-col">7)</td>
                                    <td>memberikan surat dukungan disertai foto kopi Kartu Tanda Penduduk atau Surat Keterangan Tanda Penduduk.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Ayat (5) -->
    <table class="pasal-content">
        <tr>
            <td class="num-col">(5)</td>
            <td class="text-justify">
                Pihak Kedua yang tidak mematuhi kewajiban dan/atau melanggar larangan sebagaimana dimaksud pada ayat (2), ayat (3), ayat (4) dan diberikan sanksi berupa:
            </td>
        </tr>
    </table>

    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">a.</td>
                        <td>Sanksi ringan berupa: <br> 1) teguran lisan</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">b.</td>
                        <td>Sanksi sedang berupa: <br> 1) teguran tertulis; atau <br> 2) pernyataan tidak puas secara tertulis</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">c.</td>
                        <td>Sanksi berat berupa: <br> 1) pemutusan hubungan Perpanjangan Perjanjian Kerja dengan hormat; <br> 2) pemutusan hubungan Perpanjangan Perjanjian Kerja dengan hormat tidak atas permintaan sendiri; atau <br> 3) pemutusan hubungan Perpanjangan Perjanjian Kerja tidak dengan hormat.</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- PASAL 6 -->
    <div class="pasal-header">
        Pasal 6 <br>
        GAJI DAN TUNJANGAN
    </div>
    <table class="pasal-content">
        <tr class="keep-together">
            <td class="num-col">(1)</td>
            <td class="text-justify">Pihak Kedua berhak mendapat gaji dan tunjangan sesuai dengan ketentuan peraturan perundang-undangan.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(2)</td>
            <td class="text-justify">
                Pihak Kedua berhak menerima gaji dalam golongan <strong><?= esc($email['golongan'] ?? '-') ?></strong> sebesar <strong>Rp. <?= number_format($pk_data['gaji_nominal'] ?? 3203600, 0, ',', '.') ?></strong> (<?= esc($pk_data['gaji_terbilang'] ?? '-') ?> Rupiah).
            </td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(3)</td>
            <td class="text-justify">Pihak Kedua berhak menerima tunjangan terdiri atas: <br> a. tunjangan keluarga; <br> b. tunjangan pangan; <br> c. tunjangan jabatan fungsional; dan/atau <br> d. tunjangan lainnya</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(4)</td>
            <td class="text-justify">Besaran tunjangan Pihak Kedua sebagaimana dimaksud pada ayat (3) diberikan sesuai dengan ketentuan peraturan perundang-undangan.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(5)</td>
            <td class="text-justify">Pembayaran gaji dan tunjangan sebagaimana dimaksud pada ayat (2) dan ayat (3), dilakukan sejak Pihak Kedua melaksanakan tugas yang dibuktikan dengan surat pernyataan melaksanakan tugas dari pimpinan unit kerja penempatan Pihak Kedua.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(6)</td>
            <td class="text-justify">Apabila Pihak Kedua yang melaksanakan tugas pada tanggal hari kerja pertama bulan berkenaan, gaji dan tunjangan sebagaimana dimaksud pada ayat (2) dan ayat (3) dibayarkan mulai bulan berkenaan.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(7)</td>
            <td class="text-justify">Apabila Pihak Kedua yang melaksanakan tugas pada tanggal hari kerja kedua dan seterusnya pada bulan berkenaan, gaji dan tunjangan sebagaimana dimaksud pada ayat (2) dan ayat (3) dibayarkan mulai bulan berikutnya.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(8)</td>
            <td class="text-justify">Pembayaran gaji dan tunjangan Pihak Kedua dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(9)</td>
            <td class="text-justify">Penerimaan gaji dan/atau tunjangan sebagaimana dimaksud pada ayat (2) dan ayat (3), dapat dilakukan pemotongan pada saat pembayaran, sesuai ketentuan peraturan perundang-undangan.</td>
        </tr>
    </table>

    <!-- PASAL 7 -->
    <div class="pasal-header">
        Pasal 7 <br>
        CUTI
    </div>
    <table class="pasal-content">
        <tr class="keep-together">
            <td class="num-col">(1)</td>
            <td class="text-justify">Pihak Kedua berhak mendapatkan cuti tahunan, cuti sakit, cuti melahirkan, dan cuti bersama selama masa Perpanjangan Perpanjangan Perjanjian Kerja.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(2)</td>
            <td class="text-justify">Cuti sebagaimana dimaksud pada ayat (1) dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</td>
        </tr>
    </table>

    <!-- PASAL 8 -->
    <div class="pasal-header">
        Pasal 8 <br>
        PENGEMBANGAN KOMPETENSI
    </div>
    <table class="pasal-content">
        <tr class="keep-together">
            <td class="num-col">(1)</td>
            <td class="text-justify">Pihak Kesatu memberikan pengembangan kompetensi kepada Pihak Kedua untuk mendukung pelaksanaan tugas selama masa Perpanjangan Perjanjian Kerja dengan memperhatikan hasil penilaian kinerja Pihak Kedua.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(2)</td>
            <td class="text-justify">Pelaksanaan pengembangan kompetensi sebagaimana dimaksud pada ayat (1) dilaksanakan sesuai dengan peraturan perundang-undangan.</td>
        </tr>
    </table>

    <!-- PASAL 9 -->
    <div class="pasal-header">
        Pasal 9 <br>
        PENGHARGAAN
    </div>
    <table class="pasal-content">
        <tr class="keep-together">
            <td class="num-col">(1)</td>
            <td class="text-justify">Pihak Kesatu memberikan penghargaan kepada Pihak Kedua berupa: <br> a. tanda kehormatan; <br> b. kesempatan prioritas untuk pengembangan kompetensi; dan/atau <br> c. kesempatan menghadiri acara resmi dan/atau acara kenegaraan.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(2)</td>
            <td class="text-justify">Pemberian penghargaan kepada Pihak Kedua sebagaimana dimaksud pada ayat (1) huruf a dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(3)</td>
            <td class="text-justify">Pemberian penghargaan kepada Pihak Kedua sebagaimana dimaksud pada ayat (1) huruf b diberikan kepada Pihak Kedua apabila mempunyai penilaian kinerja yang paling baik.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(4)</td>
            <td class="text-justify">Pemberian penghargaan kepada Pihak Kedua sebagaimana dimaksud pada ayat (1) huruf c diberikan kepada Pihak Kedua setelah mendapatkan pertimbangan dari Tim Penilai Kinerja Pegawai Pemerintah dengan Perpanjangan Perjanjian Kerja yang ada pada Pihak Kesatu.</td>
        </tr>
    </table>

    <!-- PASAL 10 -->
    <div class="pasal-header">
        Pasal 10 <br>
        PERLINDUNGAN
    </div>
    <table class="pasal-content">
        <tr class="keep-together">
            <td class="num-col">(1)</td>
            <td class="text-justify">Pihak Kesatu wajib memberikan perlindungan bagi Pihak Kedua berupa: <br> a. jaminan hari tua; <br> b. jaminan kesehatan; <br> c. jaminan kecelakaan kerja; <br> d. jaminan kematian; dan <br> e. bantuan hukum.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(2)</td>
            <td class="text-justify">Perlindungan sebagaimana dimaksud pada ayat (1) huruf a, huruf b, huruf c, dan huruf d dilakukan dengan mengikutsertakan Pihak Kedua dalam program sistem jaminan sosial nasional.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(3)</td>
            <td class="text-justify">Perlindungan sebagaimana dimaksud pada ayat (1) huruf e diberikan kepada Pihak Kedua dalam perkara yang dihadapi di pengadilan terkait pelaksanaan tugas.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(4)</td>
            <td class="text-justify">Pemberian perlindungan kepada Pihak Kedua sebagaimana dimaksud pada ayat (1) dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</td>
        </tr>
    </table>

    <!-- PASAL 11 -->
    <div class="pasal-header">
        Pasal 11 <br>
        PEMUTUSAN HUBUNGAN PERPANJANGAN PERJANJIAN KERJA
    </div>
    <div class="text-justify pasal-content">
        Pihak Kesatu dan Pihak Kedua dapat melakukan pemutusan hubungan Perpanjangan Perjanjian Kerja dengan ketentuan sebagai berikut:
    </div>
    <table class="pasal-content">
        <tr class="keep-together">
            <td class="num-col">(1)</td>
            <td class="text-justify">Pemutusan hubungan Perpanjangan Perpanjangan Perjanjian Kerja dengan hormat dilakukan apabila: <br> a. Jangka waktu Perpanjangan Perpanjangan Perjanjian Kerja berakhir; <br> b. Pihak Kedua meninggal dunia; <br> c. Pihak Kedua mengajukan permohonan berhenti sebagai Pegawai Pemerintah dengan Perpanjangan Perjanjian Kerja; atau <br> d. Terjadi perampingan organisasi atau kebijakan pemerintah yang mengakibatkan pengurangan Pegawai Pemerintah dengan Perpanjangan Perjanjian Kerja pada Pihak Kesatu.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(2)</td>
            <td class="text-justify">Pemutusan hubungan Perpanjangan Perpanjangan Perjanjian Kerja dengan hormat tidak atas permintaan sendiri dilakukan apabila: <br> a. Pihak Kedua dihukum penjara berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap karena melakukan tindak pidana penjara paling singkat 2 (dua) tahun dan tindak pidana dilakukan dengan tidak berencana; <br> b. Pihak Kedua melakukan pelanggaran kewajiban dan/atau larangan sebagaimana yang dimaksud dalam Pasal 5; atau <br> c. Pihak Kedua tidak dapat memenuhi target kinerja yang telah disepakati sesuai dengan Perpanjangan Perjanjian Kerja.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(3)</td>
            <td class="text-justify">Pemutusan hubungan Perpanjangan Perjanjian Kerja tidak dengan hormat dilakukan apabila: <br> a. melakukan penyelewengan terhadap Pancasila dan/atau Undang-Undang Dasar Negara Republik Indonesia Tahun 1945; <br> b. dihukum penjara atau kurungan berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap karena melakukan tindak pidana kejahatan jabatan atau tindak pidana yang ada hubungannya dengan jabatan; <br> c. menjadi anggota dan/atau pengurus partai politik; atau <br> d. dihukum penjara berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap karena melakukan tindak pidana yang diancam pidana penjara paling singkat 2 (dua) tahun atau lebih dan tindak pidana tersebut dilakukan dengan berencana.</td>
        </tr>
    </table>

    <!-- PASAL 12 -->
    <div class="pasal-header">
        Pasal 12 <br>
        PENYELESAIAN PERSELISIHAN
    </div>
    <div class="text-justify pasal-content">
        Apabila dalam pelaksanaan Perpanjangan Perjanjian Kerja ini terjadi perselisihan, maka Pihak Kesatu dan Pihak Kedua sepakat menyelesaikan perselisihan tersebut sesuai dengan ketentuan peraturan perundang-undangan.
    </div>

    <!-- PASAL 13 -->
    <div class="pasal-header">
        Pasal 13 <br>
        LAIN-LAIN
    </div>
    <table class="pasal-content">
        <tr class="keep-together">
            <td class="num-col">(1)</td>
            <td class="text-justify">Pihak Kedua bersedia melaksanakan seluruh ketentuan yang telah diatur dalam peraturan kedinasan dan peraturan lainnya yang berlaku di Pihak Kesatu.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(2)</td>
            <td class="text-justify">Pihak Kedua wajib menyimpan dan menjaga kerahasiaan baik dokumen maupun informasi milik Pihak Kesatu sesuai dengan ketentuan peraturan perundang-undangan.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(3)</td>
            <td class="text-justify">Pihak Kesatu dapat memperpanjang masa Perpanjangan Perjanjian Kerja yang dilaksanakan sesuai dengan peraturan perundang-undangan.</td>
        </tr>
    </table>

    <div class="text-justify" style="margin-top: 15px;">
        Demikian Perpanjangan Perjanjian Kerja ini dibuat dalam rangkap 2 (dua) oleh Pihak Kesatu dan Pihak Kedua dalam keadaan sehat dan sadar serta tanpa pengaruh ataupun paksaan dari pihak manapun, masing-masing bermeterai cukup dan mempunyai kekuatan hukum yang sama.
    </div>

    <!-- SIGNATURES -->
    <table class="signature-table">
        <tr>
            <td class="sig-cell text-bold">PIHAK KESATU</td>
            <td class="sig-cell text-bold">PIHAK KEDUA</td>
        </tr>
        <tr>
            <td class="sig-cell sig-space">
                ${ttd_pengirim2}
            </td>
            <td class="sig-cell sig-space">
                ${ttd_pengirim1}
            </td>
        </tr>
        <tr>
            <td class="sig-cell text-bold">Dra. Hj. RATNAWATI ARIF, M.Si.</td>
            <td class="sig-cell text-bold"><?= esc($fullName) ?></td>
        </tr>
    </table>

</body>

</html>