<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perjanjian Kerja - <?= esc($email['name']) ?></title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }

        @font-face {
            font-family: "Bookman Old Style";
            src: url("BOOKOS.TTF") format("truetype");
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: "Bookman Old Style", serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 12pt;
            font-weight: bold;
            margin: 5px 0 0 0;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .nomor-surat {
            text-align: center;
            margin-top: 5px;
            margin-bottom: 30px;
        }

        .content {
            text-align: justify;
        }

        .bio-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .bio-table td {
            vertical-align: top;
            padding-bottom: 5px;
        }

        .bio-table .label {
            width: 200px;
        }

        .bio-table .separator {
            width: 20px;
            text-align: center;
        }

        .pasal-title {
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .pasal-content {
            margin-bottom: 10px;
        }

        ol {
            margin-top: 0;
            padding-left: 30px;
        }

        ol.alpha-list {
            list-style-type: lower-alpha;
        }

        ol.numeric-list {
            list-style-type: decimal;
        }

        .signature-section {
            margin-top: 50px;
            width: 100%;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            text-align: center;
        }

        .signature-table td {
            vertical-align: top;
            padding: 20px;
        }

        .signature-title {
            font-weight: bold;
        }

        .signature-name {
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="header">
        <img src="<?= $logoSrc ?>" alt="Garuda Pancasila">
        <h1>BUPATI SINJAI</h1>
        <h2>PERJANJIAN KERJA</h2>
    </div>

    <div class="nomor-surat">
        Nomor : 800.1.2.5/29.881/BKPSDMA
    </div>

    <div class="content">
        <p>Pada hari ini <strong><?= strtoupper(date('l')) ?></strong> tanggal <strong><?= strtoupper(date('d')) ?></strong> bulan <strong><?= strtoupper(date('F')) ?></strong> tahun <strong><?= strtoupper(date('Y')) ?></strong> yang bertandatangan di bawah ini :</p>

        <table class="bio-table">
            <tr>
                <td colspan="3"><strong>1. BUPATI SINJAI</strong> untuk selanjutnya disebut Pihak Kesatu.</td>
            </tr>
            <tr>
                <td colspan="3"><br><strong>2. Pihak Kedua:</strong></td>
            </tr>
            <tr>
                <td class="label">Nama</td>
                <td class="separator">:</td>
                <td><?= esc($email['name']) ?></td>
            </tr>
            <tr>
                <td class="label">Nomor Induk PPPK</td>
                <td class="separator">:</td>
                <td><?= esc($email['nik_nip']) ?></td>
            </tr>
            <tr>
                <td class="label">Tempat/Tanggal Lahir</td>
                <td class="separator">:</td>
                <td><?= esc($email['tempat_lahir'] ?? 'N/A') ?> / <?= esc($email['tanggal_lahir'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <td class="label">Pendidikan</td>
                <td class="separator">:</td>
                <td><?= esc($email['pendidikan'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2">dalam hal ini bertindak untuk dan atas nama diri sendiri, untuk selanjutnya disebut Pihak Kedua.</td>
            </tr>
        </table>

        <p>dalam hal ini bertindak untuk dan atas nama diri sendiri, untuk selanjutnya disebut Pihak Kedua.</p>

        <p>Pihak Kesatu dan Pihak Kedua sepakat untuk mengikatkan diri satu sama lain dalam Perjanjian Kerja dengan ketentuan sebagaimana dituangkan dalam Pasal-Pasal sebagai berikut :</p>

        <div class="pasal-title">Pasal 1<br>MASA PERJANJIAN KERJA, JABATAN, DAN UNIT KERJA</div>
        <div class="pasal-content">
            <p>Pihak Kesatu menerima dan mempekerjakan Pihak Kedua sebagai Pegawai Pemerintah dengan Perjanjian Kerja dengan ketentuan sebagai berikut :</p>
            <table class="bio-table">
                <tr>
                    <td class="label">Masa Perjanjian Kerja</td>
                    <td class="separator">:</td>
                    <td>01 MARET 2024 s/d 28 FEBRUARI 2025</td>
                </tr>
                <tr>
                    <td class="label">Jabatan</td>
                    <td class="separator">:</td>
                    <td><?= esc($email['jabatan'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td class="label">Masa Kerja sebelumnya</td>
                    <td class="separator">:</td>
                    <td>0 Tahun 0 Bulan</td>
                </tr>
                <tr>
                    <td class="label">Unit Kerja</td>
                    <td class="separator">:</td>
                    <td><?= esc($unit_kerja['nama_unit_kerja']) ?></td>
                </tr>
            </table>
        </div>

        <div class="pasal-title">Pasal 2<br>TUGAS PEKERJAAN</div>
        <div class="pasal-content">
            <ol class="numeric-list">
                <li>Pihak Kesatu membuat dan menetapkan tugas pekerjaan yang harus dilaksanakan oleh Pihak Kedua.</li>
                <li>Pihak Kedua wajib melaksanakan tugas pekerjaan yang diberikan Pihak Kesatu dengan sebaik-baiknya dengan rasa tanggung jawab.</li>
            </ol>
        </div>

        <div class="pasal-title">Pasal 3<br>TARGET KINERJA</div>
        <div class="pasal-content">
            <ol class="numeric-list">
                <li>Pihak Kesatu membuat dan menetapkan target kinerja bagi Pihak Kedua selama masa Perjanjian Kerja.</li>
                <li>Pihak Kedua wajib memenuhi target kinerja yang telah ditetapkan oleh Pihak Kesatu.</li>
                <li>Pihak Kesatu dan Pihak Kedua menandatangani target perjanjian kinerja sesuai peraturan perundang-undangan.</li>
            </ol>
        </div>

        <div class="pasal-title">Pasal 4<br>HARI KERJA DAN JAM KERJA</div>
        <div class="pasal-content">
            <p>Pihak Kedua wajib bekerja sesuai dengan hari kerja dan jam kerja yang berlaku di instansi Pihak Kesatu.</p>
        </div>

        <div class="pasal-title">Pasal 5<br>DISIPLIN</div>
        <div class="pasal-content">
            <ol class="numeric-list">
                <li>Pihak Kedua wajib mematuhi semua kewajiban dan larangan.</li>
                <li>Kewajiban bagi Pihak Kedua sebagaimana dimaksud pada ayat (1) meliputi :
                    <ol class="alpha-list">
                        <li>setia dan taat pada Pancasila, Undang-Undang Dasar Negara Republik Indonesia Tahun 1945, Negara Kesatuan Republik Indonesia, dan Pemerintah yang sah;</li>
                        <li>menjaga persatuan dan kesatuan bangsa;</li>
                        <li>melaksanakan kebijakan yang dirumuskan pejabat pemerintah yang berwenang;</li>
                        <li>menaati ketentuan peraturan perundang-undangan;</li>
                        <li>melaksanakan tugas kedinasan dengan penuh pengabdian, kejujuran, kesadaran, dan tanggung jawab;</li>
                        <li>menunjukkan integritas dan keteladanan dalam sikap, perilaku, ucapan, dan tindakan kepada setiap orang, baik di dalam maupun di luar kedinasan;</li>
                        <li>menyimpan rahasia jabatan dan hanya dapat mengemukakan rahasia jabatan sesuai dengan ketentuan peraturan perundang-undangan; dan</li>
                        <li>bersedia ditempatkan di seluruh wilayah Negara Kesatuan Republik Indonesia.</li>
                    </ol>
                </li>
                <li>Selain memenuhi kewajiban sebagaimana dimaksud dalam Pasal 5 ayat (2), Pihak Kedua wajib :
                    <ol class="alpha-list">
                        <li>mengucapkan sumpah/janji PPPK;</li>
                        <li>mengucapkan sumpah/janji jabatan;</li>
                        <li>setia dan taat sepenuhnya kepada Pancasila, Undang-Undang Dasar Negara Republik Indonesia Tahun 1945, Negara Kesatuan Republik Indonesia, dan Pemerintah;</li>
                        <li>menaati segala ketentuan peraturan perundang-undangan;</li>
                        <li>melaksanakan tugas kedinasan yang dipercayakan kepada PPPK dengan penuh pengabdian, kesadaran, dan tanggung jawab;</li>
                        <li>menjunjung tinggi kehormatan negara, Pemerintah, dan martabat PPPK;</li>
                        <li>mengutamakan kepentingan negara daripada kepentingan sendiri, seseorang, dan/atau golongan;</li>
                        <li>memegang rahasia jabatan yang menurut sifatnya atau menurut perintah harus dirahasiakan;</li>
                        <li>bekerja dengan jujur, tertib, cermat, dan bersemangat untuk kepentingan negara;</li>
                        <li>melaporkan dengan segera kepada atasannya apabila mengetahui ada hal yang dapat membahayakan atau merugikan negara atau Pemerintah terutama di bidang keamanan, keuangan, dan materil;</li>
                        <li>masuk kerja dan menaati ketentuan jam kerja;</li>
                        <li>mencapai sasaran kerja pegawai yang ditetapkan;</li>
                        <li>menggunakan dan memelihara barang-barang milik negara dengan sebaik-baiknya;</li>
                        <li>memberikan pelayanan sebaik-baiknya kepada masyarakat; dan</li>
                        <li>menaati peraturan kedinasan yang ditetapkan oleh pejabat yang berwenang.</li>
                    </ol>
                </li>
                <li>Larangan bagi Pihak Kedua sebagaimana dimaksud pada ayat (1) meliputi :
                    <ol class="alpha-list">
                        <li>menyalahgunakan wewenang;</li>
                        <li>menjadi perantara untuk mendapatkan keuntungan pribadi dan/atau orang lain dengan menggunakan kewenangan orang lain;</li>
                        <li>tanpa izin Pemerintah menjadi pegawai atau bekerja untuk negara lain dan/atau lembaga atau organisasi internasional;</li>
                        <li>bekerja pada perusahaan asing, konsultan asing, atau lembaga swadaya masyarakat asing;</li>
                        <li>memiliki, menjual, membeli, menggadaikan, menyewakan, atau meminjamkan barang-barang baik bergerak atau tidak bergerak, dokumen atau surat berharga milik negara secara tidak sah;</li>
                        <li>melakukan kegiatan bersama dengan atasan, teman sejawat, bawahan, atau orang lain di dalam maupun di luar lingkungan kerjanya dengan tujuan untuk keuntungan pribadi, golongan, atau pihak lain yang secara langsung atau tidak langsung merugikan negara;</li>
                        <li>memberikan atau menyanggupi akan memberi sesuatu kepada siapapun baik secara langsung atau tidak langsung dengan dalih apapun untuk diangkat dalam jabatan;</li>
                        <li>menerima hadiah atau sesuatu pemberian apa saja dari siapapun juga yang berhubungan dengan jabatan dan/atau pekerjaannya;</li>
                        <li>bertindak sewenang-wenang terhadap bawahannya;</li>
                        <li>melakukan suatu tindakan atau tidak melakukan suatu tindakan yang dapat menghalangi atau memprsulit salah satu pihak yang dilayani sehingga mengakibatkan kerugian bagi yang dilayani;</li>
                        <li>menghalangi berjalannya tugas kedinasan;</li>
                        <li>memberikan dukungan kepada calon Presiden/Wakil Presiden, DPR, DPD, atau DPRD dengan cara kampanye, menggunakan atribut ASN, mengerahkan ASN lain, dan menggunakan fasilitas negara.</li>
                    </ol>
                </li>
                <li>Pihak Kedua dilarang memberikan dukungan politik kepada calon Kepala Daerah/Wakil Kepala Daerah sesuai ketentuan peraturan perundang-undangan.</li>
                <li>Pihak Kedua yang tidak mematuhi kewajiban dan/atau melanggar larangan sebagaimana dimaksud pada ayat (2), ayat (3), ayat (4) dan ayat (5) dikenakan sanksi berupa :
                    <ol class="alpha-list">
                        <li>Sanksi ringan berupa teguran lisan.</li>
                        <li>Sanksi sedang berupa teguran tertulis atau pernyataan tidak puas secara tertulis.</li>
                        <li>Sanksi berat berupa pemutusan hubungan Perjanjian Kerja dengan hormat atau tidak dengan hormat.</li>
                    </ol>
                </li>
            </ol>
        </div>

        <div class="pasal-title">Pasal 6<br>GAJI DAN TUNJANGAN</div>
        <div class="pasal-content">
            <ol class="numeric-list">
                <li>Pihak Kedua berhak mendapat gaji dan tunjangan sesuai dengan ketentuan peraturan perundang-undangan.</li>
                <li>Pihak Kedua berhak menerima gaji dalam golongan IX sebesar <strong>Rp. 3.203.600</strong> (Tiga Juta Dua Ratus Tiga Ribu Enam Ratus Rupiah).</li>
                <li>Pihak Kedua berhak menerima tunjangan terdiri atas: tunjangan keluarga, tunjangan pangan, tunjangan jabatan fungsional, dan tunjangan lainnya.</li>
                <li>Besaran tunjangan Pihak Kedua sebagaimana dimaksud pada ayat(3) diberikan sesuai dengan ketentuan peraturan perundang-undangan.</li>
                <li>Pembayaran gaji dan tunjangan dilakukan sesuai ketentuan waktu pelaksanaan tugas.</li>
                <li>Pemberian gaji dan tunjangan Pihak Kedua dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</li>
                <li>Penerimaan gaji dan/atau tunjangan dapat dilakukan pemotongan sesuai ketentuan peraturan perundang-undangan.</li>
                <li>Pembayaran tunjangan sebagaimana dimaksud pada ayat (3) diberikan sesuai dengan kemampuan keuangan daerah.</li>
            </ol>
        </div>

        <div class="pasal-title">Pasal 7<br>CUTI</div>
        <div class="pasal-content">
            <ol class="numeric-list">
                <li>Pihak Kedua berhak mendapatkan cuti tahunan, cuti sakit, cuti melahirkan dan cuti bersama selama masa perjanjian kerja.</li>
                <li>Cuti sebagaimana dimaksud pada ayat (1) dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</li>
            </ol>
        </div>

        <div class="pasal-title">Pasal 8<br>PENGEMBANGAN KOMPETENSI</div>
        <div class="pasal-content">
            <ol class="numeric-list">
                <li>Pihak Kesatu memberikan pengembangan kompetensi kepada Pihak Kedua untuk mendukung pelaksanaan tugas selama masa Perjanjian Kerja dengan memperhatikan hasil penilaian kinerja Pihak Kedua.</li>
                <li>Pelaksanaan pengembangan kompetensi sebagaimana dimaksud pada ayat (1) dilaksanakan sesuai dengan peraturan perundang-undangan.</li>
            </ol>
        </div>

        <div class="pasal-title">Pasal 9<br>PENGHARGAAN</div>
        <div class="pasal-content">
            <ol class="numeric-list">
                <li>Pihak Kesatu memberikan penghargaan kepada Pihak Kedua berupa: tanda kehormatan, kesempatan prioritas untuk pengembangan kompetensi, dan/atau kesempatan menghadiri secara resmi acara kenegaraan.</li>
                <li>Pemberian penghargaan dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan dan penilaian kinerja.</li>
            </ol>
        </div>

        <div class="pasal-title">Pasal 10<br>PERLINDUNGAN</div>
        <div class="pasal-content">
            <ol class="numeric-list">
                <li>Pihak Kesatu wajib memberikan perlindungan bagi Pihak Kedua berupa: jaminan hari tua, jaminan kesehatan, jaminan kecelakaan kerja, jaminan kematian, dan bantuan hukum.</li>
                <li>Perlindungan dilakukan dengan mengikutsertakan Pihak Kedua dalam program sistem jaminan sosial nasional dan bantuan hukum dalam perkara kedinasan.</li>
                <li>Pemberian perlindungan dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</li>
            </ol>
        </div>

        <div class="pasal-title">Pasal 11<br>PEMUTUSAN HUBUNGAN PERJANJIAN KERJA</div>
        <div class="pasal-content">
            <p>Pihak Kesatu dan Pihak Kedua dapat melakukan pemutusan hubungan Perjanjian Kerja dengan ketentuan sebagai berikut :</p>
            <ol class="alpha-list">
                <li><strong>Pemutusan Hubungan Perjanjian Kerja dengan hormat</strong> dilakukan apabila: jangka waktu berakhir, meninggal dunia, mengajukan berhenti, atau perampingan organisasi.</li>
                <li><strong>Pemutusan Hubungan Perjanjian Kerja dengan hormat tidak atas permintaan sendiri</strong> dilakukan apabila: dihukum penjara paling singkat 2 tahun (tidak berencana), pelanggaran kewajiban/larangan, atau tidak memenuhi target kinerja.</li>
                <li><strong>Pemutusan Hubungan Perjanjian Kerja tidak dengan hormat</strong> dilakukan apabila: penyelewengan Pancasila/UUD 1945, tindak pidana jabatan, menjadi anggota parpol, atau dihukum penjara paling singkat 2 tahun (berencana).</li>
            </ol>
        </div>

        <div class="pasal-title">Pasal 12<br>PENYELESAIAN PERSELISIHAN</div>
        <div class="pasal-content">
            <p>Apabila dalam pelaksanaan Perjanjian Kerja ini terjadi perselisihan, maka Pihak Kesatu dan Pihak Kedua sepakat menyelesaikan perselisihan tersebut sesuai dengan ketentuan peraturan perundang-undangan.</p>
        </div>

        <div class="pasal-title">Pasal 13<br>LAIN-LAIN</div>
        <div class="pasal-content">
            <ol class="numeric-list">
                <li>Pihak Kedua bersedia melaksanakan seluruh ketentuan yang telah diatur dalam peraturan kedinasan dan peraturan lainnya yang berlaku di Pihak Kesatu.</li>
                <li>Pihak Kedua wajib menyimpan dan menjaga kerahasiaan baik dokumen maupun informasi milik Pihak Kesatu sesuai dengan ketentuan peraturan perundang-undangan.</li>
                <li>Pihak Kesatu dapat memperpanjang masa Perjanjian Kerja yang dilaksanakan sesuai dengan peraturan perundang-undangan.</li>
            </ol>
            <p>Demikian Perjanjian Kerja ini dibuat dalam rangkap 2 (dua) oleh Pihak Kesatu dan Pihak Kedua dalam keadaan sehat dan sadar serta tanpa pengaruh ataupun paksaan dari pihak manapun, masing-masing bermaterai cukup dan mempunyai kekuatan hukum yang sama.</p>
        </div>

        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td width="50%">
                        <div class="signature-title">PIHAK KESATU</div>
                    </td>
                    <td width="50%">
                        <div class="signature-title">PIHAK KEDUA</div>
                    </td>
                </tr>
                <tr>
                    <td width="50%">
                        ${ttd_pengirim}
                    </td>
                    <td width="50%">

                    </td>
                </tr>
                <tr>
                    <td width="50%">
                        <div class="signature-name">Dra. Hj. RATNAWATI ARIF, M.Si.</div>
                    </td>
                    <td width="50%">
                        <div class="signature-name"><?= esc($email['name']) ?></div>
                    </td>
                </tr>
            </table>
        </div>

    </div>

</body>

</html>