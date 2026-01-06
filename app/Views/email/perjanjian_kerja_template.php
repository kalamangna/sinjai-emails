<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perjanjian Kerja - <?= esc(strtoupper($email['name'])) ?></title>
    <style>
        @page {
            margin: 15px 25px;
        }

        body {
            margin: 15px 25px;
            font-family: "Times New Roman", serif;
        }

        .kop-surat {
            text-align: center;
        }

        .kop-surat img {
            width: 80px;
            height: auto;
        }

        .kop-surat h1 {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }

        .kop-surat h2 {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 1rem 0 0;
        }

        .kop-surat p {
            font-size: 11pt;
            margin: 0;
        }

        .isi-dokumen {
            margin-top: 2rem;
            font-size: 10pt;
            text-align: justify;
            line-height: 1.5;
        }

        .isi-dokumen table {
            width: 100%;
            border-collapse: collapse;
        }

        .isi-dokumen td {
            vertical-align: top;
        }

        .bab {
            margin-top: 1.5rem;
        }

        .judul-bab {
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .isi-bab {
            margin-top: 0.5rem;
            text-align: justify;
            line-height: 1.5;
        }

        .area-ttd {
            margin-top: 2rem;
            width: 100%;
        }

        .area-ttd table {
            width: 100%;
            border-collapse: collapse;
        }

        .area-ttd .pihak {
            text-align: center;
            font-weight: bold;
            width: 50%;
        }

        .area-ttd .spasi-ttd {
            text-align: center;
            height: 100px;
            vertical-align: middle;
        }

        .area-ttd .nama-penandatangan {
            text-align: center;
            font-weight: bold;
        }

        .paragraf {
            margin-top: 0.5rem;
            text-align: justify;
            line-height: 1.5;
        }
    </style>
    <?php
    // Function to format date to Indonesian format
    if (!function_exists('formatIndonesianDate')) {
        function formatIndonesianDate($dateString)
        {
            if (empty($dateString) || $dateString == '0000-00-00') {
                return '-';
            }
            $months = [
                '01' => 'JANUARI',
                '02' => 'FEBRUARI',
                '03' => 'MARET',
                '04' => 'APRIL',
                '05' => 'MEI',
                '06' => 'JUNI',
                '07' => 'JULI',
                '08' => 'AGUSTUS',
                '09' => 'SEPTEMBER',
                '10' => 'OKTOBER',
                '11' => 'NOVEMBER',
                '12' => 'DESEMBER'
            ];
            $timestamp = strtotime($dateString);
            $day = date('d', $timestamp);
            $month = $months[date('m', $timestamp)];
            $year = date('Y', $timestamp);
            return "$day $month $year";
        }
    }
    ?>
</head>

<body>
    <div class="kop-surat">
        <img src="<?= $logoSrc ?>" alt="Garuda Pancasila">
        <h1>BUPATI SINJAI</h1>
        <h2>PERJANJIAN KERJA</h2>
        <p>Nomor : 800.1.2.5/29.<?= esc($pk_data['nomor'] ?? 'N/A') ?>/PPPK-PW/BKPSDMA</p>
    </div>

    <div class="isi-dokumen">
        Pada hari ini <strong>JUMAT</strong> tanggal <strong>DUA</strong> bulan <strong>JANUARI</strong> tahun <strong>DUA RIBU DUA PULUH ENAM</strong> yang bertandatangan di bawah ini:
        <table>
            <tr>
                <td style="width: 25px;">I.</td>
                <td><strong>BUPATI SINJAI</strong> untuk selanjutnya disebut Pihak Kesatu.</td>
            </tr>
            <tr>
                <td>II.</td>
                <td>
                    <table>
                        <tr>
                            <td style="width: 150px;">Nama</td>
                            <td style="width: 10px;">:</td>
                            <td>
                                <?php if (!empty($email['gelar_depan'])): ?>
                                    <?= esc($email['gelar_depan']) ?>
                                <?php endif; ?>
                                <?= esc(strtoupper($email['name'])) ?><?php if (!empty($email['gelar_belakang'])): ?>, <?= esc($email['gelar_belakang']) ?>
                            <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Nomor Induk PPPK-PW</td>
                            <td>:</td>
                            <td><?= esc($email['nip'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td>Tempat/Tanggal Lahir</td>
                            <td>:</td>
                            <td><?= esc($email['tempat_lahir'] ?? 'N/A') ?> / <?= (isset($email['tanggal_lahir']) && $email['tanggal_lahir'] != '0000-00-00' && !empty($email['tanggal_lahir'])) ? date('d-m-Y', strtotime($email['tanggal_lahir'])) : 'N/A' ?></td>
                        </tr>
                        <tr>
                            <td>Pendidikan</td>
                            <td>:</td>
                            <td><?= esc($email['pendidikan'] ?? 'N/A') ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>dalam hal ini bertindak untuk dan atas nama diri sendiri, untuk selanjutnya disebut Pihak Kedua.</td>
            </tr>
        </table>

        <div class="paragraf">
            Pihak Kesatu dan Pihak Kedua sepakat untuk mengikatkan diri satu sama lain dalam Perjanjian Kerja dengan ketentuan sebagaimana dituangkan dalam Pasal-Pasal sebagai berikut:
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 1 <br>
                MASA PERJANJIAN KERJA, JABATAN, DAN UNIT KERJA
            </h3>

            <div class="isi-bab">
                Pihak Kesatu menerima dan mempekerjakan Pihak Kedua sebagai Pegawai Pemerintah dengan Perjanjian Kerja Paruh Waktu dengan ketentuan sebagai berikut:
                <table>
                    <tr>
                        <td style="width: 25px;">a.</td>
                        <td style="width: 150px;">Masa Perjanjian Kerja</td>
                        <td style="width: 10px;">:</td>
                        <td>
                            <?= formatIndonesianDate($pk_data['tanggal_kontrak_awal'] ?? '0000-00-00') ?>
                            s/d
                            <?= formatIndonesianDate($pk_data['tanggal_kontrak_akhir'] ?? '0000-00-00') ?>
                        </td>
                    </tr>
                    <tr>
                        <td>b.</td>
                        <td>Jabatan</td>
                        <td>:</td>
                        <td><?= esc(strtoupper($email['jabatan'] ?? 'N/A')) ?></td>
                    </tr>
                    <tr>
                        <td>c.</td>
                        <td>Masa Kerja Sebelumnya</td>
                        <td>:</td>
                        <td>0 Tahun 0 Bulan</td>
                    </tr>
                    <tr>
                        <td>d.</td>
                        <td>Unit Kerja</td>
                        <td>:</td>
                        <td><?= esc(strtoupper($unit_kerja['nama_unit_kerja'])) ?> - PEMERINTAH KABUPATEN SINJAI / SINJAI</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 2 <br>
                TUGAS PEKERJAAN
            </h3>

            <div class="isi-bab">
                <table>
                    <tr>
                        <td style="width: 25px;">(1)</td>
                        <td>Pihak Kesatu membuat dan menetapkan tugas pekerjaan yang harus dilaksanakan oleh Pihak Kedua.</td>
                    </tr>
                    <tr>
                        <td>(2)</td>
                        <td>Pihak Kedua wajib melaksanakan tugas pekerjaan yang diberikan Pihak Kesatu dengan sebaik-baiknya dengan rasa tanggung jawab.</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 3 <br>
                TARGET KINERJA
            </h3>

            <div class="isi-bab">
                <table>
                    <tr>
                        <td style="width: 25px;">(1)</td>
                        <td>Pihak Kesatu membuat dan menetapkan target kinerja bagi Pihak Kedua selama masa Perjanjian Kerja.</td>
                    </tr>
                    <tr>
                        <td>(2)</td>
                        <td>Pihak Kedua wajib memenuhi target kinerja yang telah ditetapkan oleh Pihak Kesatu.</td>
                    </tr>
                    <tr>
                        <td>(3)</td>
                        <td>Pihak Kesatu dan Pihak Kedua menandatangani target perjanjian kinerja sesuai peraturan perundang-undangan.</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 4 <br>
                HARI KERJA DAN JAM KERJA
            </h3>

            <div class="isi-bab">
                Pihak Kedua wajib bekerja sesuai dengan hari kerja dan jam kerja yang berlaku di instansi Pihak Kesatu.
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 5 <br>
                HAK, KEWAJIBAN, DAN DISIPLIN
            </h3>

            <div class="isi-bab">
                <table>
                    <tr>
                        <td style="width: 25px;">(1)</td>
                        <td>Pihak Kedua wajib mematuhi semua kewajiban dan larangan.</td>
                    </tr>
                    <tr>
                        <td>(2)</td>
                        <td>
                            Kewajiban bagi Pihak Kedua sebagaimana dimaksud pada ayat (1) meliputi:
                            <table>
                                <tr>
                                    <td style="width: 20px;">a.</td>
                                    <td>setia dan taat pada Pancasila, Undang-Undang Dasar Negara Republik Indonesia Tahun 1945, Negara Kesatuan Republik Indonesia, dan Pemerintah yang sah;</td>
                                </tr>
                                <tr>
                                    <td>b.</td>
                                    <td>menjaga persatuan dan kesatuan bangsa;</td>
                                </tr>
                                <tr>
                                    <td>c.</td>
                                    <td>melaksanakan kebijakan yang dirumuskan pejabat pemerintah yang berwenang;</td>
                                </tr>
                                <tr>
                                    <td>d.</td>
                                    <td>menaati ketentuan peraturan perundang-undangan;</td>
                                </tr>
                                <tr>
                                    <td>e.</td>
                                    <td>melaksanakan tugas kedinasan dengan penuh pengabdian, kejujuran, kesadaran, dan tanggung jawab;</td>
                                </tr>
                                <tr>
                                    <td>f.</td>
                                    <td>menunjukkan integritas dan keteladanan dalam sikap, perilaku, ucapan, dan tindakan kepada setiap orang, baik di dalam maupun di luar kedinasan;</td>
                                </tr>
                                <tr>
                                    <td>g.</td>
                                    <td>menyimpan rahasia jabatan dan hanya dapat mengemukakan rahasia jabatan sesuai dengan ketentuan peraturan perundang-undangan; dan</td>
                                </tr>
                                <tr>
                                    <td>h.</td>
                                    <td>bersedia ditempatkan di seluruh wilayah Negara Kesatuan Republik Indonesia.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 25px;">(3)</td>
                        <td>
                            Selain memenuhi kewajiban sebagaimana dimaksud dalam Pasal 5 ayat (2), Pihak Kedua wajib:
                            <table>
                                <tr>
                                    <td style="width: 20px;">a.</td>
                                    <td>mengucapkan sumpah/janji PPPK;</td>
                                </tr>
                                <tr>
                                    <td>b.</td>
                                    <td>mengucapkan sumpah/janji jabatan;</td>
                                </tr>
                                <tr>
                                    <td>c.</td>
                                    <td>setia dan taat sepenuhnya kepada Pancasila, Undang-Undang Dasar Negara Republik Indonesia Tahun 1945, Negara Kesatuan Republik Indonesia, dan Pemerintah;</td>
                                </tr>
                                <tr>
                                    <td>d.</td>
                                    <td>menaati segala ketentuan peraturan perundang-undangan;</td>
                                </tr>
                                <tr>
                                    <td>e.</td>
                                    <td>melaksanakan tugas kedinasan yang dipercayakan kepada PPPK dengan penuh pengabdian, kesadaran, dan tanggung jawab;</td>
                                </tr>
                                <tr>
                                    <td>f.</td>
                                    <td>menjunjung tinggi kehormatan negara, Pemerintah, dan martabat PPPK;</td>
                                </tr>
                                <tr>
                                    <td>g.</td>
                                    <td>mengutamakan kepentingan negara daripada kepentingan sendiri, seseorang, dan/atau golongan;</td>
                                </tr>
                                <tr>
                                    <td>h.</td>
                                    <td>memegang rahasia jabatan yang menurut sifatnya atau menurut perintah harus dirahasiakan;</td>
                                </tr>
                                <tr>
                                    <td>i.</td>
                                    <td>bekerja dengan jujur, tertib, cermat, dan bersemangat untuk kepentingan negara;</td>
                                </tr>
                                <tr>
                                    <td>j.</td>
                                    <td>melaporkan dengan segera kepada atasannya apabila mengetahui ada hal yang dapat membahayakan atau merugikan negara atau Pemerintah terutama di bidang keamanan, keuangan, dan materiil;</td>
                                </tr>
                                <tr>
                                    <td>k.</td>
                                    <td>masuk kerja dan menaati ketentuan jam kerja;</td>
                                </tr>
                                <tr>
                                    <td>l.</td>
                                    <td>mencapai sasaran kerja pegawai yang ditetapkan;</td>
                                </tr>
                                <tr>
                                    <td>m.</td>
                                    <td>menggunakan dan memelihara barang-barang milik negara dengan sebaik-baiknya;</td>
                                </tr>
                                <tr>
                                    <td>n.</td>
                                    <td>memberikan pelayanan sebaik-baiknya kepada masyarakat; dan</td>
                                </tr>
                                <tr>
                                    <td>o.</td>
                                    <td>menaati peraturan kedinasan yang ditetapkan oleh pejabat yang berwenang.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 25px;">(4)</td>
                        <td>
                            Larangan bagi Pihak Kedua sebagaimana dimaksud pada ayat (1) meliputi:
                            <table>
                                <tr>
                                    <td style="width: 20px;">a.</td>
                                    <td>menyalahgunakan wewenang;</td>
                                </tr>
                                <tr>
                                    <td>b.</td>
                                    <td>menjadi perantara untuk mendapatkan keuntungan pribadi dan/atau orang lain dengan menggunakan kewenangan orang lain;</td>
                                </tr>
                                <tr>
                                    <td>c.</td>
                                    <td>tanpa izin Pemerintah menjadi pegawai atau bekerja untuk negara lain dan/atau lembaga atau organisasi internasional;</td>
                                </tr>
                                <tr>
                                    <td>d.</td>
                                    <td>bekerja pada perusahaan asing, konsultan asing, atau lembaga swadaya masyarakat asing;</td>
                                </tr>
                                <tr>
                                    <td>e.</td>
                                    <td>memiliki, menjual, membeli, menggadaikan, menyewakan, atau meminjamkan barang-barang baik bergerak atau tidak bergerak, dokumen atau surat berharga milik negara secara tidak sah;</td>
                                </tr>
                                <tr>
                                    <td>f.</td>
                                    <td>melakukan kegiatan bersama dengan atasan, teman sejawat, bawahan, atau orang lain di dalam maupun di luar lingkungan kerjanya dengan tujuan untuk keuntungan pribadi, golongan, atau pihak lain yang secara langsung atau tidak langsung merugikan negara;</td>
                                </tr>
                                <tr>
                                    <td>g.</td>
                                    <td>memberikan atau menyanggupi akan memberi sesuatu kepada siapa pun baik secara langsung atau tidak langsung dengan dalih apa pun untuk diangkat dalam jabatan;</td>
                                </tr>
                                <tr>
                                    <td>h.</td>
                                    <td>menerima hadiah atau sesuatu pemberian apa saja dari siapa pun juga yang berhubungan dengan jabatan dan/atau pekerjaannya;</td>
                                </tr>
                                <tr>
                                    <td>i.</td>
                                    <td>bertindak sewenang-wenang terhadap bawahannya;</td>
                                </tr>
                                <tr>
                                    <td>j.</td>
                                    <td>melakukan suatu tindakan atau tidak melakukan suatu tindakan yang dapat menghalangi atau mempersulit salah satu pihak yang dilayani sehingga mengakibatkan kerugian bagi yang dilayani;</td>
                                </tr>
                                <tr>
                                    <td>k.</td>
                                    <td>menghalangi berjalannya tugas kedinasan;</td>
                                </tr>
                                <tr>
                                    <td>l.</td>
                                    <td>
                                        memberikan dukungan kepada calon Presiden/Wakil Presiden, Dewan Perwakilan Rakyat, Dewan Perwakilan Daerah, atau Dewan Perwakilan Rakyat Daerah dengan cara:
                                        <table>
                                            <tr>
                                                <td style="width: 20px;">1)</td>
                                                <td>ikut serta sebagai pelaksana kampanye;</td>
                                            </tr>
                                            <tr>
                                                <td>2)</td>
                                                <td>menjadi peserta kampanye dengan menggunakan atribut partai atau atribut Aparatur Sipil Negara;</td>
                                            </tr>
                                            <tr>
                                                <td>3)</td>
                                                <td>sebagai peserta kampanye dengan mengerahkan Aparatur Sipil Negara lain; dan/atau</td>
                                            </tr>
                                            <tr>
                                                <td>4)</td>
                                                <td>sebagai peserta kampanye dengan menggunakan fasilitas negara.</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>m.</td>
                                    <td>
                                        memberikan dukungan kepada calon Presiden/Wakil Presiden dengan cara:
                                        <table>
                                            <tr>
                                                <td style="width: 20px;">1)</td>
                                                <td>membuat keputusan dan/atau tindakan yang menguntungkan atau merugikan salah satu pasangan calon selama masa kampanye; dan/atau</td>
                                            </tr>
                                            <tr>
                                                <td>2)</td>
                                                <td>mengadakan kegiatan yang mengarah kepada keberpihakan terhadap pasangan calon yang menjadi peserta pemilu sebelum, selama, dan/atau sesudah masa kampanye meliputi pertemuan, ajakan, imbauan, seruan, atau pemberian barang kepada Aparatur Sipil Negara dalam lingkungan unit kerjanya, anggota keluarga, dan masyarakat.</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>n.</td>
                                    <td>memberikan dukungan kepada calon anggota Dewan Perwakilan Daerah atau calon Kepala Daerah/Wakil Kepala Daerah dengan memberikan surat dukungan disertai fotokopi Kartu Tanda Penduduk atau Surat Keterangan Tanda Penduduk sesuai peraturan perundang-undangan; dan</td>
                                </tr>
                                <tr>
                                    <td>o.</td>
                                    <td>
                                        memberikan dukungan kepada calon Kepala Daerah/Wakil Kepala Daerah, dengan cara:
                                        <table>
                                            <tr>
                                                <td style="width: 20px;">1)</td>
                                                <td>terlibat dalam kegiatan kampanye untuk mendukung calon Kepala Daerah/Wakil Kepala Daerah;</td>
                                            </tr>
                                            <tr>
                                                <td>2)</td>
                                                <td>menggunakan fasilitas yang terkait dengan jabatan dalam kegiatan kampanye;</td>
                                            </tr>
                                            <tr>
                                                <td>3)</td>
                                                <td>membuat keputusan dan/atau tindakan yang menguntungkan atau merugikan salah satu pasangan calon selama masa kampanye; dan/atau</td>
                                            </tr>
                                            <tr>
                                                <td>4)</td>
                                                <td>mengadakan kegiatan yang mengarah kepada keberpihakan terhadap pasangan calon yang menjadi peserta pemilu sebelum, selama, dan/atau sesudah masa kampanye meliputi pertemuan, ajakan, imbauan, seruan, atau pemberian barang kepada Aparatur Sipil Negara dalam lingkungan unit kerjanya, anggota keluarga, dan masyarakat.</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 25px;">(5)</td>
                        <td>Selain larangan sebagaimana dimaksud pada ayat (4), Pihak Kedua dilarang:
                            <table>
                                <tr>
                                    <td style="width: 20px;">a.</td>
                                    <td>melakukan hal-hal yang dapat menurunkan kehormatan atau martabat Negara dan Pemerintah;</td>
                                </tr>
                                <tr>
                                    <td>b.</td>
                                    <td>menyalahgunakan wewenangnya;</td>
                                </tr>
                                <tr>
                                    <td>c.</td>
                                    <td>tanpa izin Pemerintah Kabupaten Sinjai menjadi pegawai atau bekerja di tempat lain;</td>
                                </tr>
                                <tr>
                                    <td>d.</td>
                                    <td>melakukan pernikahan kedua, ketiga, dan bagi wanita tidak menjadi atau berkedudukan sebagai istri kedua, ketiga, dan seterusnya;</td>
                                </tr>
                                <tr>
                                    <td>e.</td>
                                    <td>menyalahgunakan barang-barang, uang, atau surat-surat berharga milik Negara dan Pemerintah;</td>
                                </tr>
                                <tr>
                                    <td>f.</td>
                                    <td>memiliki, menjual, membeli, menggadaikan, menyewakan, atau meminjamkan barang-barang, dokumen, atau surat-surat berharga milik Negara dan Pemerintah secara tidak sah;</td>
                                </tr>
                                <tr>
                                    <td>g.</td>
                                    <td>melakukan kegiatan bersama dengan atasan, teman sejawat, bawahan, atau orang lain di dalam maupun di luar lingkungan kerjanya dengan tujuan untuk keuntungan pribadi, golongan, atau pihak lain yang secara langsung atau tidak langsung merugikan Negara;</td>
                                </tr>
                                <tr>
                                    <td>h.</td>
                                    <td>menerima hadiah atau sesuatu pemberian berupa apa saja dari siapa pun juga yang diketahui atau patut dapat diduga bahwa pemberian itu bersangkutan atau mungkin bersangkutan dengan pekerjaan pegawai;</td>
                                </tr>
                                <tr>
                                    <td>i.</td>
                                    <td>memasuki tempat-tempat yang dapat mencemarkan kehormatan, kecuali untuk kepentingan tugas;</td>
                                </tr>
                                <tr>
                                    <td>j.</td>
                                    <td>melakukan suatu tindakan atau sengaja tidak melakukan suatu tindakan yang dapat berakibat menghalangi atau mempersulit salah satu pihak yang dilayaninya sehingga mengakibatkan kerugian bagi pihak yang dilayani;</td>
                                </tr>
                                <tr>
                                    <td>k.</td>
                                    <td>menghalangi berjalannya tugas kedinasan;</td>
                                </tr>
                                <tr>
                                    <td>l.</td>
                                    <td>membocorkan dan/atau memanfaatkan rahasia Negara yang diketahui karena kedudukan jabatan untuk kepentingan pribadi, golongan, atau pihak lain;</td>
                                </tr>
                                <tr>
                                    <td>m.</td>
                                    <td>bertindak selaku perantara bagi sesuatu pengusaha atau golongan untuk mendapatkan pekerjaan atau pesanan dari kantor/instansi Pemerintah; dan</td>
                                </tr>
                                <tr>
                                    <td>n.</td>
                                    <td>melakukan pungutan tidak sah dalam bentuk apa pun juga dalam melaksanakan tugasnya untuk kepentingan pribadi, golongan, atau pihak lain.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 25px;">(6)</td>
                        <td>
                            Pihak Kedua yang tidak mematuhi kewajiban dan/atau melanggar larangan sebagaimana dimaksud pada ayat (2), ayat (3), ayat (4) dan ayat (5) dikenakan sanksi berupa:
                            <table>
                                <tr>
                                    <td style="width: 20px;">a.</td>
                                    <td>
                                        Sanksi ringan:
                                        <table>
                                            <tr>
                                                <td style="width: 20px;">1)</td>
                                                <td>teguran lisan.</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>b.</td>
                                    <td>
                                        Sanksi sedang:
                                        <table>
                                            <tr>
                                                <td style="width: 20px;">1)</td>
                                                <td>teguran tertulis.</td>
                                            </tr>
                                            <tr>
                                                <td style="width: 20px;">2)</td>
                                                <td>pernyataan tidak puas secara tertulis.</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>c.</td>
                                    <td>
                                        Sanksi berat:
                                        <table>
                                            <tr>
                                                <td style="width: 20px;">1)</td>
                                                <td>pemutusan hubungan Perjanjian Kerja dengan hormat;</td>
                                            </tr>
                                            <tr>
                                                <td>2)</td>
                                                <td>pemutusan hubungan Perjanjian Kerja dengan hormat tidak atas permintaan sendiri; atau</td>
                                            </tr>
                                            <tr>
                                                <td>3)</td>
                                                <td>pemutusan hubungan Perjanjian Kerja tidak dengan hormat.</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 6 <br>
                GAJI
            </h3>

            <div class="isi-bab">
                <table>
                    <tr>
                        <td style="width: 25px;">(1)</td>
                        <td>Pihak Kedua berhak mendapat gaji dan tunjangan sesuai dengan ketentuan peraturan perundang-undangan dan berdasarkan kemampuan keuangan daerah.</td>
                    </tr>
                    <tr>
                        <td>(2)</td>
                        <td>Pihak Kedua berhak menerima gaji sebagai PPPK Paruh Waktu sebesar <?= (isset($pk_data['gaji_nominal']) && !empty($pk_data['gaji_nominal'])) ? "Rp. " . number_format($pk_data['gaji_nominal'], 0, ',', '.') : 'N/A' ?> (<?= esc($pk_data['gaji_terbilang'] ?? 'N/A') ?> Rupiah).</td>
                    </tr>
                    <tr>
                        <td>(3)</td>
                        <td>Besaran gaji Pihak Kedua sebagaimana dimaksud pada ayat (2) diberikan sesuai dengan ketentuan peraturan perundang-undangan dan berdasarkan kemampuan keuangan daerah.</td>
                    </tr>
                    <tr>
                        <td>(4)</td>
                        <td>Pembayaran gaji sebagaimana dimaksud pada ayat (2) dilakukan sejak Pihak Kedua melaksanakan tugas yang dibuktikan dengan Surat Pernyataan Melaksanakan Tugas dari pimpinan unit kerja penempatan Pihak Kedua.</td>
                    </tr>
                    <tr>
                        <td>(5)</td>
                        <td>Apabila Pihak Kedua melaksanakan tugas pada tanggal hari kerja pertama bulan berkenaan, gaji sebagaimana dimaksud pada ayat (2) dibayarkan mulai bulan berkenaan.</td>
                    </tr>
                    <tr>
                        <td>(6)</td>
                        <td>Apabila Pihak Kedua melaksanakan tugas pada tanggal hari kerja kedua dan seterusnya pada bulan berkenaan, gaji sebagaimana dimaksud pada ayat (2) dibayarkan mulai bulan berikutnya.</td>
                    </tr>
                    <tr>
                        <td>(7)</td>
                        <td>Pemberian gaji Pihak Kedua dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</td>
                    </tr>
                    <tr>
                        <td>(8)</td>
                        <td>Penerimaan gaji sebagaimana dimaksud pada ayat (2) dapat dilakukan pemotongan pada saat pembayaran sesuai ketentuan peraturan perundang-undangan.</td>
                    </tr>
                    <tr>
                        <td>(9)</td>
                        <td>Pembayaran gaji sebagaimana dimaksud pada ayat (2) diberikan sesuai dengan kemampuan keuangan daerah.</td>
                    </tr>
                    <tr>
                        <td>(10)</td>
                        <td>Pihak Kedua mendapatkan fasilitas dan pendapatan lain yang sah sesuai dengan ketentuan peraturan perundang-undangan dan kemampuan keuangan daerah.</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 7 <br>
                CUTI
            </h3>

            <div class="isi-bab">
                <table>
                    <tr>
                        <td style="width: 25px;">(1)</td>
                        <td>Pihak Kedua berhak mendapatkan cuti tahunan, cuti sakit, cuti melahirkan, dan cuti bersama selama masa perjanjian kerja.</td>
                    </tr>
                    <tr>
                        <td>(2)</td>
                        <td>Cuti sebagaimana dimaksud pada ayat (1) dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 8 <br>
                PENGEMBANGAN KOMPETENSI
            </h3>

            <div class="isi-bab">
                <table>
                    <tr>
                        <td style="width: 25px;">(1)</td>
                        <td>Pihak Kesatu memberikan pengembangan kompetensi kepada Pihak Kedua untuk mendukung pelaksanaan tugas selama masa Perjanjian Kerja dengan memperhatikan hasil penilaian kinerja Pihak Kedua.</td>
                    </tr>
                    <tr>
                        <td>(2)</td>
                        <td>Pelaksanaan pengembangan kompetensi sebagaimana dimaksud pada ayat (1) dilaksanakan sesuai dengan peraturan perundang-undangan.</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 9 <br>
                PENGHARGAAN
            </h3>

            <div class="isi-bab">
                <table>
                    <tr>
                        <td style="width: 25px;">(1)</td>
                        <td>
                            Pihak Kesatu memberikan penghargaan kepada Pihak Kedua berupa:
                            <table>
                                <tr>
                                    <td style="width: 20px;">a.</td>
                                    <td>tanda kehormatan;</td>
                                </tr>
                                <tr>
                                    <td>b.</td>
                                    <td>kesempatan prioritas untuk pengembangan kompetensi; dan/atau</td>
                                </tr>
                                <tr>
                                    <td>c.</td>
                                    <td>kesempatan menghadiri secara resmi dan/atau acara kenegaraan.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>(2)</td>
                        <td>Pemberian penghargaan kepada Pihak Kedua sebagaimana dimaksud pada ayat (1) huruf a dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</td>
                    </tr>
                    <tr>
                        <td>(3)</td>
                        <td>Pemberian penghargaan kepada Pihak Kedua sebagaimana dimaksud pada ayat (1) huruf b diberikan kepada Pihak Kedua apabila mempunyai penilaian kinerja yang paling baik.</td>
                    </tr>
                    <tr>
                        <td>(4)</td>
                        <td>Pemberian penghargaan kepada Pihak Kedua sebagaimana dimaksud pada ayat (1) huruf c diberikan kepada Pihak Kedua setelah mendapatkan pertimbangan dari Tim Penilai Kinerja Pegawai Pemerintah dengan Perjanjian Kerja yang ada pada Pihak Kesatu.</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 10 <br>
                PERLINDUNGAN
            </h3>

            <div class="isi-bab">
                <table>
                    <tr>
                        <td style="width: 25px;">(1)</td>
                        <td>
                            Pihak Kesatu wajib memberikan perlindungan bagi Pihak Kedua berupa:
                            <table>
                                <tr>
                                    <td style="width: 20px;">a.</td>
                                    <td>jaminan kesehatan;</td>
                                </tr>
                                <tr>
                                    <td>b.</td>
                                    <td>jaminan kecelakaan kerja; dan</td>
                                </tr>
                                <tr>
                                    <td>c.</td>
                                    <td>jaminan kematian;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>(2)</td>
                        <td>Perlindungan sebagaimana dimaksud pada ayat (1) huruf a, huruf b, dan huruf c dilakukan dengan mengikutsertakan Pihak Kedua dalam program sistem jaminan sosial nasional.</td>
                    </tr>
                    <tr>
                        <td>(3)</td>
                        <td>Pemberian perlindungan kepada Pihak Kedua sebagaimana dimaksud pada ayat (1) dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 11 <br>
                PEMUTUSAN HUBUNGAN PERJANJIAN KERJA
            </h3>

            <div class="isi-bab">
                Pihak Kesatu dan Pihak Kedua dapat melakukan pemutusan hubungan Perjanjian Kerja dengan ketentuan sebagai berikut:
                <table>
                    <tr>
                        <td style="width: 25px;">(1)</td>
                        <td>Diangkat menjadi PPPK atau CPNS;</td>
                    </tr>
                    <tr>
                        <td>(2)</td>
                        <td>Mengundurkan diri;</td>
                    </tr>
                    <tr>
                        <td>(3)</td>
                        <td>Meninggal dunia;</td>
                    </tr>
                    <tr>
                        <td>(4)</td>
                        <td>Melakukan penyelewangan terhadap Pancasila dan Undang-Undang Dasar Negara Republik Indonesia Tahun 1945;</td>
                    </tr>
                    <tr>
                        <td>(5)</td>
                        <td>Mencapai Batas Usia Pensiun (BUP) jabatan;</td>
                    </tr>
                    <tr>
                        <td>(6)</td>
                        <td>Berakhirnya masa perjanjian kerja;</td>
                    </tr>
                    <tr>
                        <td>(7)</td>
                        <td>Terdampak perampingan organisasi atau kebijkan pemerintah;</td>
                    </tr>
                    <tr>
                        <td>(8)</td>
                        <td>Tidak cakap jasmani dan/atau Rohani, sehingga tidak dapat menjalankan tugas dan kewajiban;</td>
                    </tr>
                    <tr>
                        <td>(9)</td>
                        <td>Tidak berkinerja;</td>
                    </tr>
                    <tr>
                        <td>(10)</td>
                        <td>Melakukan pelanggaran disiplin tingkat berat;</td>
                    </tr>
                    <tr>
                        <td>(11)</td>
                        <td>Dipidana dengan pidana penjara berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap karena melakukan tindak pidana dengan pidana penjara paling singkat 2 (dua) tahun;</td>
                    </tr>
                    <tr>
                        <td>(12)</td>
                        <td>Dipidana dengan pidana penjara atau kurungan berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap kerena tetap melakukan tindak pidana kejahatan yang ada hubungannya dengan jabatan; dan/atau</td>
                    </tr>
                    <tr>
                        <td>(13)</td>
                        <td>Menjadi anggota dan/atau pengurus partai politik.</td>
                    </tr>
                    <tr>
                        <td>(14)</td>
                        <td>
                            Pemutusan Hubungan Perjanjian Kerja dengan hormat dilakukan apabila:
                            <table>
                                <tr>
                                    <td style="width: 20px;">a.</td>
                                    <td>jangka waktu Perjanjian Kerja berakhir;</td>
                                </tr>
                                <tr>
                                    <td>b.</td>
                                    <td>Pihak Kedua meninggal dunia;</td>
                                </tr>
                                <tr>
                                    <td>c.</td>
                                    <td>Pihak Kedua mengajukan permohonan berhenti sebagai Pegawai Pemerintah dengan Perjanjian Kerja; atau</td>
                                </tr>
                                <tr>
                                    <td>d.</td>
                                    <td>terjadi perampingan organisasi atau kebijakan Pemerintah yang mengakibatkan pengurangan Pegawai Pemerintah dengan Perjanjian Kerja pada Pihak Kesatu.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>(15)</td>
                        <td>
                            Pemutusan Hubungan Perjanjian Kerja dengan hormat tidak atas permintaan sendiri dilakukan apabila:
                            <table>
                                <tr>
                                    <td style="width: 20px;">a.</td>
                                    <td>Pihak Kedua dihukum penjara berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap karena melakukan tindak pidana penjara paling singkat 2 (dua) tahun dan tindak pidana dilakukan dengan tidak berencana;</td>
                                </tr>
                                <tr>
                                    <td>b.</td>
                                    <td>Pihak Kedua melakukan pelanggaran kewajiban dan/atau larangan sebagaimana dimaksud dalam Pasal 5; atau</td>
                                </tr>
                                <tr>
                                    <td>c.</td>
                                    <td>Pihak Kedua tidak dapat memenuhi target kinerja yang telah disepakati sesuai dengan Perjanjian Kerja.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>(16)</td>
                        <td>
                            Pemutusan Hubungan Perjanjian Kerja tidak dengan hormat dilakukan apabila:
                            <table>
                                <tr>
                                    <td style="width: 20px;">a.</td>
                                    <td>melakukan penyelewengan terhadap Pancasila dan/atau Undang-Undang Dasar Negara Republik Indonesia Tahun 1945;</td>
                                </tr>
                                <tr>
                                    <td>b.</td>
                                    <td>dihukum penjara atau kurungan berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap karena melakukan tindak pidana kejahatan jabatan atau tindak pidana yang ada hubungannya dengan jabatan;</td>
                                </tr>
                                <tr>
                                    <td>c.</td>
                                    <td>menjadi anggota dan/atau pengurus partai politik; atau</td>
                                </tr>
                                <tr>
                                    <td>d.</td>
                                    <td>dihukum penjara berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap karena melakukan tindak pidana yang diancam pidana penjara paling singkat 2 (dua) tahun atau lebih dan tindak pidana tersebut dilakukan dengan berencana.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 12 <br>
                PENYELESAIAN PERSELISIHAN
            </h3>

            <div class="isi-bab">
                Apabila dalam pelaksanaan Perjanjian Kerja ini terjadi perselisihan, maka Pihak Kesatu dan Pihak Kedua sepakat menyelesaikan perselisihan tersebut sesuai dengan ketentuan peraturan perundang-undangan.
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 13 <br>
                LAIN-LAIN
            </h3>

            <div class="isi-bab">
                <table>
                    <tr>
                        <td style="width: 25px;">(1)</td>
                        <td>Pihak Kedua bersedia melaksanakan seluruh ketentuan yang telah diatur dalam peraturan kedinasan dan peraturan lainnya yang berlaku di Pihak Kesatu.</td>
                    </tr>
                    <tr>
                        <td>(2)</td>
                        <td>Pihak Kedua wajib menyimpan dan menjaga kerahasiaan baik dokumen maupun informasi milik Pihak Kesatu sesuai dengan ketentuan peraturan perundang-undangan.</td>
                    </tr>
                    <tr>
                        <td>(3)</td>
                        <td>Pihak Kesatu dapat memperpanjang masa Perjanjian Kerja yang dilaksanakan sesuai dengan peraturan perundang-undangan.</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="paragraf">
            Demikian Perjanjian Kerja ini dibuat dalam rangkap 2 (dua) oleh Pihak Kesatu dan Pihak Kedua dalam keadaan sehat dan sadar serta tanpa pengaruh ataupun paksaan dari pihak mana pun, masing-masing bermaterai cukup dan mempunyai kekuatan hukum yang sama.
        </div>

        <div class="area-ttd">
            <table>
                <tr>
                    <td class="pihak">
                        PIHAK KESATU
                    </td>
                    <td class="pihak">
                        PIHAK KEDUA
                    </td>
                </tr>
                <tr>
                    <td class="spasi-ttd">
                        ${ttd_pengirim2}
                    </td>
                    <td class="spasi-ttd">
                        ${ttd_pengirim1}
                    </td>
                </tr>
                <tr>
                    <td class="nama-penandatangan">
                        Dra. Hj. RATNAWATI ARIF, M.Si.
                    </td>
                    <td class="nama-penandatangan">
                        <?php if (!empty($email['gelar_depan'])): ?>
                            <?= esc($email['gelar_depan']) ?>
                        <?php endif; ?>
                        <?= esc(strtoupper($email['name'])) ?><?php if (!empty($email['gelar_belakang'])): ?>, <?= esc($email['gelar_belakang']) ?><?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>



</body>

</html>