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
            margin: 1cm 2cm 2.5cm 2cm;
            size: A4;
        }

        @font-face {
            font-family: 'Bookman Old Style';
            src: url(data:font/truetype;charset=utf-8;base64,<?= base64_encode(file_get_contents(FCPATH . 'fonts/BOOKOS.TTF')) ?>) format('truetype');
            font-weight: normal;
            font-style: normal;
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

        .text-bold {
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
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }

        .kop-title {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
        }

        .kop-subtitle {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 10px 0 0 0;
        }

        .kop-number {
            font-size: 11pt;
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

        /* Spacing for content paragraphs/tables following headers */
        /* .pasal-content {
            margin-bottom: 10px;
        } */

        /* LIST SYSTEMS (Table-based) */
        /* Level 1: (1), (2) ... */
        .list-row {
            margin-bottom: 2px;
        }

        .num-col {
            width: 35px;
            /* Fixed width for alignment */
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

        /* .info-val {
            text-align: justify;
        } */

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
            height: 150px;
            vertical-align: middle;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
    <?php
    // Construct full name dengan titles
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
        <div class="kop-subtitle text-uppercase">PERJANJIAN KERJA</div>
        <div class="kop-number">Nomor : 800.1.2.5/29.<?= esc($pk_data['nomor'] ?? 'N/A') ?>/PPPK-PW/BKPSDMA</div>
    </div>

    <!-- PREAMBLE -->
    <div class="text-justify">
        Pada hari ini <strong>JUMAT</strong> tanggal <strong>DUA</strong> bulan <strong>JANUARI</strong> tahun <strong>DUA RIBU DUA PULUH ENAM</strong> yang bertandatangan di bawah ini:
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
                        <td class="info-val"><?= esc($fullName) ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Nomor Induk PPPK-PW</td>
                        <td class="info-sep">:</td>
                        <td class="info-val"><?= esc($email['nip'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Tempat/Tanggal Lahir</td>
                        <td class="info-sep">:</td>
                        <td class="info-val">
                            <?= esc($email['tempat_lahir'] ?? 'N/A') ?> /
                            <?= formatSingkat($email['tanggal_lahir'] ?? null) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">Pendidikan</td>
                        <td class="info-sep">:</td>
                        <td class="info-val"><?= esc($email['pendidikan'] ?? 'N/A') ?></td>
                    </tr>
                </table>
                <div style="margin-top: 5px;">
                    dalam hal ini bertindak untuk dan atas nama diri sendiri, untuk selanjutnya disebut Pihak Kedua.
                </div>
            </td>
        </tr>
    </table>

    <div class="text-justify" style="margin-top: 10px;">
        Pihak Kesatu dan Pihak Kedua sepakat untuk mengikatkan diri satu sama lain dalam Perjanjian Kerja dengan ketentuan sebagaimana dituangkan dalam Pasal-Pasal sebagai berikut:
    </div>

    <!-- PASAL 1 -->
    <div class="pasal-header">
        Pasal 1 <br>
        MASA PERJANJIAN KERJA, JABATAN, DAN UNIT KERJA
    </div>
    <div class="text-justify pasal-content">
        Pihak Kesatu menerima dan mempekerjakan Pihak Kedua sebagai Pegawai Pemerintah dengan Perjanjian Kerja Paruh Waktu dengan ketentuan sebagai berikut:

        <table class="sub-list" style="page-break-inside: avoid;">
            <tr>
                <td class="sub-num-col">a.</td>
                <td>
                    <table class="info-table">
                        <tr>
                            <td class="info-label">Masa Perjanjian Kerja</td>
                            <td class="info-sep">:</td>
                            <td class="info-val">
                                <?= formatTanggal($pk_data['tanggal_kontrak_awal'] ?? '0000-00-00') ?>
                                s/d
                                <?= formatTanggal($pk_data['tanggal_kontrak_akhir'] ?? '0000-00-00') ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="sub-list" style="page-break-inside: avoid;">
            <tr>
                <td class="sub-num-col">b.</td>
                <td>
                    <table class="info-table">
                        <tr>
                            <td class="info-label">Jabatan</td>
                            <td class="info-sep">:</td>
                            <td class="info-val"><?= esc(strtoupper($email['jabatan'] ?? 'N/A')) ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="sub-list" style="page-break-inside: avoid;">
            <tr>
                <td class="sub-num-col">c.</td>
                <td>
                    <table class="info-table">
                        <tr>
                            <td class="info-label">Masa Kerja Sebelumnya</td>
                            <td class="info-sep">:</td>
                            <td class="info-val">0 Tahun 0 Bulan</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="sub-list" style="page-break-inside: avoid;">
            <tr>
                <td class="sub-num-col">d.</td>
                <td>
                    <table class="info-table">
                        <tr>
                            <td class="info-label">Unit Kerja</td>
                            <td class="info-sep">:</td>
                            <td class="info-val"><?= esc(strtoupper($unit_kerja['nama_unit_kerja'] ?? 'N/A')) ?> - PEMERINTAH KABUPATEN SINJAI</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-break"></div>

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
            <td class="text-justify">Pihak Kedua wajib melaksanakan tugas pekerjaan yang diberikan Pihak Kesatu dengan sebaik-baiknya dengan rasa tanggung jawab.</td>
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
            <td class="text-justify">Pihak Kesatu membuat dan menetapkan target kinerja bagi Pihak Kedua selama masa Perjanjian Kerja.</td>
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
        HAK, KEWAJIBAN, DAN DISIPLIN
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
        'setia dan taat pada Pancasila, Undang-Undang Dasar Negara Republik Indonesia Tahun 1945, Negara Kesatuan Republik Indonesia, dan Pemerintah yang sah;',
        'menjaga persatuan dan kesatuan bangsa;',
        'melaksanakan kebijakan yang dirumuskan pejabat pemerintah yang berwenang;',
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
                <td class="num-col"></td> <!-- Indent -->
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
        'mengucapkan sumpah/janji PPPK;',
        'mengucapkan sumpah/janji jabatan;',
        'setia dan taat sepenuhnya kepada Pancasila, Undang-Undang Dasar Negara Republik Indonesia Tahun 1945, Negara Kesatuan Republik Indonesia, dan Pemerintah;',
        'menaati segala ketentuan peraturan perundang-undangan;',
        'melaksanakan tugas kedinasan yang dipercayakan kepada PPPK dengan penuh pengabdian, kesadaran, dan tanggung jawab;',
        'menjunjung tinggi kehormatan negara, Pemerintah, dan martabat PPPK;',
        'mengutamakan kepentingan negara daripada kepentingan sendiri, seseorang, dan/atau golongan;',
        'memegang rahasia jabatan yang menurut sifatnya atau menurut perintah harus dirahasiakan;',
        'bekerja dengan jujur, tertib, cermat, dan bersemangat untuk kepentingan negara;',
        'melaporkan dengan segera kepada atasannya apabila mengetahui ada hal yang dapat membahayakan atau merugikan negara atau Pemerintah terutama di bidang keamanan, keuangan, dan materiil;',
        'masuk kerja dan menaati ketentuan jam kerja;',
        'mencapai sasaran kerja pegawai yang ditetapkan;',
        'menggunakan dan memelihara barang-barang milik negara dengan sebaik-baiknya;',
        'memberikan pelayanan sebaik-baiknya kepada masyarakat; dan',
        'menaati peraturan kedinasan yang ditetapkan oleh pejabat yang berwenang.'
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
        'menjadi perantara untuk mendapatkan keuntungan pribadi dan/atau orang lain dengan menggunakan kewenangan orang lain;',
        'tanpa izin Pemerintah menjadi pegawai atau bekerja untuk negara lain dan/atau lembaga atau organisasi internasional;',
        'bekerja pada perusahaan asing, konsultan asing, atau lembaga swadaya masyarakat asing;',
        'memiliki, menjual, membeli, menggadaikan, menyewakan, atau meminjamkan barang-barang baik bergerak atau tidak bergerak, dokumen atau surat berharga milik negara secara tidak sah;',
        'melakukan kegiatan bersama dengan atasan, teman sejawat, bawahan, atau orang lain di dalam maupun di luar lingkungan kerjanya dengan tujuan untuk keuntungan pribadi, golongan, atau pihak lain yang secara langsung atau tidak langsung merugikan negara;',
        'memberikan atau menyanggupi akan memberi sesuatu kepada siapa pun baik secara langsung atau tidak langsung dengan dalih apa pun untuk diangkat dalam jabatan;',
        'menerima hadiah atau sesuatu pemberian apa saja dari siapa pun juga yang berhubungan dengan jabatan dan/atau pekerjaannya;',
        'bertindak sewenang-wenang terhadap bawahannya;',
        'melakukan suatu tindakan atau tidak melakukan suatu tindakan yang dapat menghalangi atau mempersulit salah satu pihak yang dilayani sehingga mengakibatkan kerugian bagi yang dilayani;',
        'menghalangi berjalannya tugas kedinasan;'
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

    <!-- Item L -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col"><?= $alpha++ ?>.</td>
                        <td class="text-justify">
                            memberikan dukungan kepada calon Presiden/Wakil Presiden, Dewan Perwakilan Rakyat, Dewan Perwakilan Daerah, atau Dewan Perwakilan Rakyat Daerah dengan cara:

                            <!-- Angka 1 -->
                            <table class="sub-sub-list" style="margin-top: 2px;">
                                <tr>
                                    <td class="sub-sub-num-col">1)</td>
                                    <td>ikut serta sebagai pelaksana kampanye;</td>
                                </tr>
                            </table>
                            <!-- Angka 2 -->
                            <table class="sub-sub-list" style="margin-top: 2px;">
                                <tr>
                                    <td class="sub-sub-num-col">2)</td>
                                    <td>menjadi peserta kampanye dengan menggunakan atribut partai atau atribut Aparatur Sipil Negara;</td>
                                </tr>
                            </table>
                            <!-- Angka 3 -->
                            <table class="sub-sub-list" style="margin-top: 2px;">
                                <tr>
                                    <td class="sub-sub-num-col">3)</td>
                                    <td>sebagai peserta kampanye dengan mengerahkan Aparatur Sipil Negara lain; dan/atau</td>
                                </tr>
                            </table>
                            <!-- Angka 4 -->
                            <table class="sub-sub-list" style="margin-top: 2px;">
                                <tr>
                                    <td class="sub-sub-num-col">4)</td>
                                    <td>sebagai peserta kampanye dengan menggunakan fasilitas negara.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Item M -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col"><?= $alpha++ ?>.</td>
                        <td class="text-justify">
                            memberikan dukungan kepada calon Presiden/Wakil Presiden dengan cara:

                            <!-- Angka 1 -->
                            <table class="sub-sub-list" style="margin-top: 2px;">
                                <tr>
                                    <td class="sub-sub-num-col">1)</td>
                                    <td>membuat keputusan dan/atau tindakan yang menguntungkan atau merugikan salah satu pasangan calon selama masa kampanye; dan/atau</td>
                                </tr>
                            </table>
                            <!-- Angka 2 -->
                            <table class="sub-sub-list" style="margin-top: 2px;">
                                <tr>
                                    <td class="sub-sub-num-col">2)</td>
                                    <td>mengadakan kegiatan yang mengarah kepada keberpihakan terhadap pasangan calon yang menjadi peserta pemilu sebelum, selama, dan/atau sesudah masa kampanye meliputi pertemuan, ajakan, imbauan, seruan, atau pemberian barang kepada Aparatur Sipil Negara dalam lingkungan unit kerjanya, anggota keluarga, dan masyarakat.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Item N -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col"><?= $alpha++ ?>.</td>
                        <td class="text-justify">memberikan dukungan kepada calon anggota Dewan Perwakilan Daerah atau calon Kepala Daerah/Wakil Kepala Daerah dengan memberikan surat dukungan disertai fotokopi Kartu Tanda Penduduk atau Surat Keterangan Tanda Penduduk sesuai peraturan perundang-undangan; dan</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Item O -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col"><?= $alpha++ ?>.</td>
                        <td class="text-justify">
                            memberikan dukungan kepada calon Kepala Daerah/Wakil Kepala Daerah, dengan cara:

                            <!-- Angka 1 -->
                            <table class="sub-sub-list" style="margin-top: 2px;">
                                <tr>
                                    <td class="sub-sub-num-col">1)</td>
                                    <td>terlibat dalam kegiatan kampanye untuk mendukung calon Kepala Daerah/Wakil Kepala Daerah;</td>
                                </tr>
                            </table>
                            <!-- Angka 2 -->
                            <table class="sub-sub-list" style="margin-top: 2px;">
                                <tr>
                                    <td class="sub-sub-num-col">2)</td>
                                    <td>menggunakan fasilitas yang terkait dengan jabatan dalam kegiatan kampanye;</td>
                                </tr>
                            </table>
                            <!-- Angka 3 -->
                            <table class="sub-sub-list" style="margin-top: 2px;">
                                <tr>
                                    <td class="sub-sub-num-col">3)</td>
                                    <td>membuat keputusan dan/atau tindakan yang menguntungkan atau merugikan salah satu pasangan calon selama masa kampanye; dan/atau</td>
                                </tr>
                            </table>
                            <!-- Angka 4 -->
                            <table class="sub-sub-list" style="margin-top: 2px;">
                                <tr>
                                    <td class="sub-sub-num-col">4)</td>
                                    <td>mengadakan kegiatan yang mengarah kepada keberpihakan terhadap pasangan calon yang menjadi peserta pemilu sebelum, selama, dan/atau sesudah masa kampanye meliputi pertemuan, ajakan, imbauan, seruan, atau pemberian barang kepada Aparatur Sipil Negara dalam lingkungan unit kerjanya, anggota keluarga, dan masyarakat.</td>
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
                Selain larangan sebagaimana dimaksud pada ayat (4), Pihak Kedua dilarang:
            </td>
        </tr>
    </table>
    <?php
    $laranganTambahan = [
        'melakukan hal-hal yang dapat menurunkan kehormatan atau martabat Negara dan Pemerintah;',
        'menyalahgunakan wewenangnya;',
        'tanpa izin Pemerintah Kabupaten Sinjai menjadi pegawai atau bekerja di tempat lain;',
        'melakukan pernikahan kedua, ketiga, dan bagi wanita tidak menjadi atau berkedudukan sebagai istri kedua, ketiga, dan seterusnya;',
        'menyalahgunakan barang-barang, uang, atau surat-surat berharga milik Negara dan Pemerintah;',
        'memiliki, menjual, membeli, menggadaikan, menyewakan, atau meminjamkan barang-barang, dokumen, atau surat-surat berharga milik Negara dan Pemerintah secara tidak sah;',
        'melakukan kegiatan bersama dengan atasan, teman sejawat, bawahan, atau orang lain di dalam maupun di luar lingkungan kerjanya dengan tujuan untuk keuntungan pribadi, golongan, atau pihak lain yang secara langsung atau tidak langsung merugikan Negara;',
        'menerima hadiah atau sesuatu pemberian berupa apa saja dari siapa pun juga yang diketahui atau patut dapat diduga bahwa pemberian itu bersangkutan atau mungkin bersangkutan dengan pekerjaan pegawai;',
        'memasuki tempat-tempat yang dapat mencemarkan kehormatan, kecuali untuk kepentingan tugas;',
        'melakukan suatu tindakan atau sengaja tidak melakukan suatu tindakan yang dapat berakibat menghalangi atau mempersulit salah satu pihak yang dilayaninya sehingga mengakibatkan kerugian bagi pihak yang dilayani;',
        'menghalangi berjalannya tugas kedinasan;',
        'membocorkan dan/atau memanfaatkan rahasia Negara yang diketahui karena kedudukan jabatan untuk kepentingan pribadi, golongan, atau pihak lain;',
        'bertindak selaku perantara bagi sesuatu pengusaha atau golongan untuk mendapatkan pekerjaan atau pesanan dari kantor/instansi Pemerintah; dan',
        'melakukan pungutan tidak sah dalam bentuk apa pun juga dalam melaksanakan tugasnya untuk kepentingan pribadi, golongan, atau pihak lain.'
    ];
    $alpha = 'a';
    foreach ($laranganTambahan as $item): ?>
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

    <!-- Ayat (6) -->
    <table class="pasal-content">
        <tr>
            <td class="num-col">(6)</td>
            <td class="text-justify">
                Pihak Kedua yang tidak mematuhi kewajiban dan/atau melanggar larangan sebagaimana dimaksud pada ayat (2), ayat (3), ayat (4) dan ayat (5) dikenakan sanksi berupa:
            </td>
        </tr>
    </table>

    <!-- Sanksi a -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">a.</td>
                        <td>
                            Sanksi ringan:
                            <table class="sub-sub-list" style="margin-top: 2px;">
                                <tr>
                                    <td class="sub-sub-num-col">1)</td>
                                    <td>teguran lisan.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- Sanksi b -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">b.</td>
                        <td>
                            Sanksi sedang:
                            <table class="sub-sub-list" style="margin-top: 2px;">
                                <tr>
                                    <td class="sub-sub-num-col">1)</td>
                                    <td>teguran tertulis.</td>
                                </tr>
                            </table>
                            <table class="sub-sub-list" style="margin-top: 2px;">
                                <tr>
                                    <td class="sub-sub-num-col">2)</td>
                                    <td>pernyataan tidak puas secara tertulis.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- Sanksi c -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">c.</td>
                        <td>
                            Sanksi berat:
                            <table class="sub-sub-list" style="margin-top: 2px;">
                                <tr>
                                    <td class="sub-sub-num-col">1)</td>
                                    <td>pemutusan hubungan Perjanjian Kerja dengan hormat;</td>
                                </tr>
                            </table>
                            <table class="sub-sub-list" style="margin-top: 2px;">
                                <tr>
                                    <td class="sub-sub-num-col">2)</td>
                                    <td>pemutusan hubungan Perjanjian Kerja dengan hormat tidak atas permintaan sendiri; atau</td>
                                </tr>
                            </table>
                            <table class="sub-sub-list" style="margin-top: 2px;">
                                <tr>
                                    <td class="sub-sub-num-col">3)</td>
                                    <td>pemutusan hubungan Perjanjian Kerja tidak dengan hormat.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- PASAL 6 -->
    <div class="pasal-header">
        Pasal 6 <br>
        GAJI
    </div>
    <table class="pasal-content">
        <tr class="keep-together">
            <td class="num-col">(1)</td>
            <td class="text-justify">Pihak Kedua berhak mendapat gaji sesuai dengan ketentuan peraturan perundang-undangan dan berdasarkan kemampuan keuangan daerah.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(2)</td>
            <td class="text-justify">
                Pihak Kedua berhak menerima gaji sebagai PPPK Paruh Waktu sebesar <?= (isset($pk_data['gaji_nominal']) && !empty($pk_data['gaji_nominal'])) ? "Rp. " . number_format($pk_data['gaji_nominal'], 0, ',', '.') : 'N/A' ?> (<?= esc($pk_data['gaji_terbilang'] ?? 'N/A') ?> Rupiah).
            </td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(3)</td>
            <td class="text-justify">Besaran gaji Pihak Kedua sebagaimana dimaksud pada ayat (2) diberikan sesuai dengan ketentuan peraturan perundang-undangan dan berdasarkan kemampuan keuangan daerah.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(4)</td>
            <td class="text-justify">Pembayaran gaji sebagaimana dimaksud pada ayat (2) dilakukan sejak Pihak Kedua melaksanakan tugas yang dibuktikan dengan Surat Pernyataan Melaksanakan Tugas dari pimpinan unit kerja penempatan Pihak Kedua.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(5)</td>
            <td class="text-justify">Apabila Pihak Kedua melaksanakan tugas pada tanggal hari kerja pertama bulan berkenaan, gaji sebagaimana dimaksud pada ayat (2) dibayarkan mulai bulan berkenaan.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(6)</td>
            <td class="text-justify">Apabila Pihak Kedua melaksanakan tugas pada tanggal hari kerja kedua dan seterusnya pada bulan berkenaan, gaji sebagaimana dimaksud pada ayat (2) dibayarkan mulai bulan berikutnya.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(7)</td>
            <td class="text-justify">Pemberian gaji Pihak Kedua dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(8)</td>
            <td class="text-justify">Penerimaan gaji sebagaimana dimaksud pada ayat (2) dapat dilakukan pemotongan pada saat pembayaran sesuai ketentuan peraturan perundang-undangan.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(9)</td>
            <td class="text-justify">Pembayaran gaji sebagaimana dimaksud pada ayat (2) diberikan sesuai dengan kemampuan keuangan daerah.</td>
        </tr>
        <tr class="keep-together">
            <td class="num-col">(10)</td>
            <td class="text-justify">Pihak Kedua mendapatkan fasilitas dan pendapatan lain yang sah sesuai dengan ketentuan peraturan perundang-undangan dan kemampuan keuangan daerah.</td>
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
            <td class="text-justify">Pihak Kedua berhak mendapatkan cuti tahunan, cuti sakit, cuti melahirkan, dan cuti bersama selama masa perjanjian kerja.</td>
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
            <td class="text-justify">Pihak Kesatu memberikan pengembangan kompetensi kepada Pihak Kedua untuk mendukung pelaksanaan tugas selama masa Perjanjian Kerja dengan memperhatikan hasil penilaian kinerja Pihak Kedua.</td>
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

    <!-- Ayat (1) -->
    <table class="pasal-content">
        <tr>
            <td class="num-col">(1)</td>
            <td class="text-justify">
                Pihak Kesatu memberikan penghargaan kepada Pihak Kedua berupa:
            </td>
        </tr>
    </table>
    <!-- Item a -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">a.</td>
                        <td>tanda kehormatan;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- Item b -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">b.</td>
                        <td>kesempatan prioritas untuk pengembangan kompetensi; dan/atau</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- Item c -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">c.</td>
                        <td>kesempatan menghadiri secara resmi dan/atau acara kenegaraan.</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Ayat (2) -->
    <table class="pasal-content">
        <tr>
            <td class="num-col">(2)</td>
            <td class="text-justify">Pemberian penghargaan kepada Pihak Kedua sebagaimana dimaksud pada ayat (1) huruf a dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</td>
        </tr>
    </table>

    <!-- Ayat (3) -->
    <table class="pasal-content">
        <tr>
            <td class="num-col">(3)</td>
            <td class="text-justify">Pemberian penghargaan kepada Pihak Kedua sebagaimana dimaksud pada ayat (1) huruf b diberikan kepada Pihak Kedua apabila mempunyai penilaian kinerja yang paling baik.</td>
        </tr>
    </table>

    <!-- Ayat (4) -->
    <table class="pasal-content">
        <tr>
            <td class="num-col">(4)</td>
            <td class="text-justify">Pemberian penghargaan kepada Pihak Kedua sebagaimana dimaksud pada ayat (1) huruf c diberikan kepada Pihak Kedua setelah mendapatkan pertimbangan dari Tim Penilai Kinerja Pegawai Pemerintah dengan Perjanjian Kerja yang ada pada Pihak Kesatu.</td>
        </tr>
    </table>

    <!-- PASAL 10 -->
    <div class="pasal-header">
        Pasal 10 <br>
        PERLINDUNGAN
    </div>

    <!-- Ayat (1) -->
    <table class="pasal-content">
        <tr>
            <td class="num-col">(1)</td>
            <td class="text-justify">
                Pihak Kesatu wajib memberikan perlindungan bagi Pihak Kedua berupa:
            </td>
        </tr>
    </table>
    <!-- Item a -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">a.</td>
                        <td>jaminan kesehatan;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- Item b -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">b.</td>
                        <td>jaminan kecelakaan kerja; dan</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- Item c -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">c.</td>
                        <td>jaminan kematian;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Ayat (2) -->
    <table class="pasal-content">
        <tr>
            <td class="num-col">(2)</td>
            <td class="text-justify">Perlindungan sebagaimana dimaksud pada ayat (1) huruf a, huruf b, dan huruf c dilakukan dengan mengikutsertakan Pihak Kedua dalam program sistem jaminan sosial nasional.</td>
        </tr>
    </table>

    <!-- Ayat (3) -->
    <table class="pasal-content">
        <tr>
            <td class="num-col">(3)</td>
            <td class="text-justify">Pemberian perlindungan kepada Pihak Kedua sebagaimana dimaksud pada ayat (1) dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</td>
        </tr>
    </table>

    <!-- PASAL 11 -->
    <div class="pasal-header">
        Pasal 11 <br>
        PEMUTUSAN HUBUNGAN PERJANJIAN KERJA
    </div>
    <div class="text-justify pasal-content">
        Pihak Kesatu dan Pihak Kedua dapat melakukan pemutusan hubungan Perjanjian Kerja dengan ketentuan sebagai berikut:
    </div>

    <?php
    $phkItems = [
        'Diangkat menjadi PPPK atau CPNS;',
        'Mengundurkan diri;',
        'Meninggal dunia;',
        'Melakukan penyelewengan terhadap Pancasila dan Undang-Undang Dasar Negara Republik Indonesia Tahun 1945;',
        'Mencapai Batas Usia Pensiun (BUP) jabatan;',
        'Berakhirnya masa perjanjian kerja;',
        'Terdampak perampingan organisasi atau kebijakan pemerintah;',
        'Tidak cakap jasmani dan/atau rohani, sehingga tidak dapat menjalankan tugas dan kewajiban;',
        'Tidak berkinerja;',
        'Melakukan pelanggaran disiplin tingkat berat;',
        'Dipidana dengan pidana penjara berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap karena melakukan tindak pidana dengan pidana penjara paling singkat 2 (dua) tahun;',
        'Dipidana dengan pidana penjara atau kurungan berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap karena tetap melakukan tindak pidana kejahatan yang ada hubungannya dengan jabatan; dan/atau',
        'Menjadi anggota dan/atau pengurus partai politik.'
    ];
    $num = 1;
    foreach ($phkItems as $item): ?>
        <table class="list-row" style="page-break-inside: avoid;">
            <tr>
                <td class="num-col">(<?= $num++ ?>)</td>
                <td class="text-justify"><?= $item ?></td>
            </tr>
        </table>
    <?php endforeach; ?>

    <!-- Item 14 -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col">(<?= $num++ ?>)</td>
            <td class="text-justify">
                Pemutusan Hubungan Perjanjian Kerja dengan hormat dilakukan apabila:
            </td>
        </tr>
    </table>
    <!-- 14a -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">a.</td>
                        <td>jangka waktu Perjanjian Kerja berakhir;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- 14b -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">b.</td>
                        <td>Pihak Kedua meninggal dunia;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- 14c -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">c.</td>
                        <td>Pihak Kedua mencapai Batas Usia Pensiun (BUP) jabatan;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- 14d -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">d.</td>
                        <td>Pihak Kedua mengajukan permohonan berhenti sebagai Pegawai Pemerintah dengan Perjanjian Kerja; atau</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- 14e -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">e.</td>
                        <td>terjadi perampingan organisasi atau kebijakan Pemerintah yang mengakibatkan pengurangan Pegawai Pemerintah dengan Perjanjian Kerja pada Pihak Kesatu.</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Item 15 -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col">(<?= $num++ ?>)</td>
            <td class="text-justify">
                Pemutusan Hubungan Perjanjian Kerja dengan hormat tidak atas permintaan sendiri dilakukan apabila:
            </td>
        </tr>
    </table>
    <!-- 15a -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">a.</td>
                        <td>Pihak Kedua dihukum penjara berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap karena melakukan tindak pidana penjara paling singkat 2 (dua) tahun dan tindak pidana dilakukan dengan tidak berencana;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- 15b -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">b.</td>
                        <td>Pihak Kedua melakukan pelanggaran kewajiban dan/atau larangan sebagaimana dimaksud dalam Pasal 5; atau</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- 15c -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">c.</td>
                        <td>Pihak Kedua tidak dapat memenuhi target kinerja yang telah disepakati sesuai dengan Perjanjian Kerja.</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Item 16 -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col">(<?= $num++ ?>)</td>
            <td class="text-justify">
                Pemutusan Hubungan Perjanjian Kerja tidak dengan hormat dilakukan apabila:
            </td>
        </tr>
    </table>
    <!-- 16a -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">a.</td>
                        <td>melakukan penyelewengan terhadap Pancasila dan/atau Undang-Undang Dasar Negara Republik Indonesia Tahun 1945;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- 16b -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">b.</td>
                        <td>dihukum penjara atau kurungan berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap karena melakukan tindak pidana kejahatan jabatan atau tindak pidana yang ada hubungannya dengan jabatan;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- 16c -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">c.</td>
                        <td>menjadi anggota dan/atau pengurus partai politik; atau</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- 16d -->
    <table class="list-row" style="page-break-inside: avoid;">
        <tr>
            <td class="num-col"></td>
            <td>
                <table class="sub-list">
                    <tr>
                        <td class="sub-num-col">d.</td>
                        <td>dihukum penjara berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap karena melakukan tindak pidana yang diancam pidana penjara paling singkat 2 (dua) tahun atau lebih dan tindak pidana tersebut dilakukan dengan berencana.</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- PASAL 12 -->
    <div class="pasal-header">
        Pasal 12 <br>
        PENYELESAIAN PERSELISIHAN
    </div>
    <div class="text-justify pasal-content">
        Apabila dalam pelaksanaan Perjanjian Kerja ini terjadi perselisihan, maka Pihak Kesatu dan Pihak Kedua sepakat menyelesaikan perselisihan tersebut sesuai dengan ketentuan peraturan perundang-undangan.
    </div>

    <div class="page-break"></div>

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
            <td class="text-justify">Pihak Kesatu dapat memperpanjang masa Perjanjian Kerja yang dilaksanakan sesuai dengan peraturan perundang-undangan.</td>
        </tr>
    </table>

    <div class="text-justify" style="margin-top: 15px;">
        Demikian Perjanjian Kerja ini dibuat dalam rangkap 2 (dua) oleh Pihak Kesatu dan Pihak Kedua dalam keadaan sehat dan sadar serta tanpa pengaruh ataupun paksaan dari pihak mana pun, masing-masing bermaterai cukup dan mempunyai kekuatan hukum yang sama.
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