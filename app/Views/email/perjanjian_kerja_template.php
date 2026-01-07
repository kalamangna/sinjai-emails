<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perjanjian Kerja - <?= esc(strtoupper($email['name'])) ?></title>
    <style>
        @page {
            margin: 1.5cm 2cm 2cm 2cm;
        }

        @font-face {
            font-family: 'Bookman Old Style';
            src: url(data:font/truetype;charset=utf-8;base64,<?= base64_encode(file_get_contents(FCPATH . 'fonts/BOOKOS.TTF')) ?>) format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            margin: 0;
            font-family: "Bookman Old Style", serif;
        }

        .kop-surat {
            text-align: center;
            margin-bottom: 30px;
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
            margin-top: 1rem;
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
            padding-bottom: 3px;
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
            display: table;
            width: 100%;
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signature-row {
            display: table-row;
        }

        .signature-cell {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .pihak {
            font-weight: bold;
        }

        .spasi-ttd {
            padding-left: 70px;
            height: 140px;
            vertical-align: middle;
        }

        .nama-penandatangan {
            font-weight: bold;
        }

        .paragraf {
            margin-top: 0.5rem;
            text-align: justify;
            line-height: 1.5;
        }

        /* Semantic List Structure */
        .list-container {
            display: table;
            width: 100%;
            margin-bottom: 3px; /* Consistent spacing */
            page-break-inside: avoid; /* Prevent item splitting */
        }

        .list-number {
            display: table-cell;
            width: 25px;
            vertical-align: top;
        }

        .list-content {
            display: table-cell;
            vertical-align: top;
            text-align: justify;
            line-height: 1.5;
        }

        /* Semantic Info Row Structure (Label : Value) */
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
        }

        .info-label {
            display: table-cell;
            width: 160px;
            vertical-align: top;
        }

        .info-separator {
            display: table-cell;
            width: 10px;
            vertical-align: top;
        }

        .info-value {
            display: table-cell;
            vertical-align: top;
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
        
        <div class="list-container">
            <div class="list-number">I.</div>
            <div class="list-content"><strong>BUPATI SINJAI</strong> untuk selanjutnya disebut Pihak Kesatu.</div>
        </div>
        
        <div class="list-container">
            <div class="list-number">II.</div>
            <div class="list-content">
                <div class="info-row">
                    <div class="info-label">Nama</div>
                    <div class="info-separator">:</div>
                    <div class="info-value">
                        <?php if (!empty($email['gelar_depan'])): ?>
                            <?= esc($email['gelar_depan']) ?>
                        <?php endif; ?>
                        <?= esc(strtoupper($email['name'])) ?><?php if (!empty($email['gelar_belakang'])): ?>, <?= esc($email['gelar_belakang']) ?>
                    <?php endif; ?>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Nomor Induk PPPK-PW</div>
                    <div class="info-separator">:</div>
                    <div class="info-value"><?= esc($email['nip'] ?? 'N/A') ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tempat/Tanggal Lahir</div>
                    <div class="info-separator">:</div>
                    <div class="info-value"><?= esc($email['tempat_lahir'] ?? 'N/A') ?> / <?= (isset($email['tanggal_lahir']) && $email['tanggal_lahir'] != '0000-00-00' && !empty($email['tanggal_lahir'])) ? date('d-m-Y', strtotime($email['tanggal_lahir'])) : 'N/A' ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Pendidikan</div>
                    <div class="info-separator">:</div>
                    <div class="info-value"><?= esc($email['pendidikan'] ?? 'N/A') ?></div>
                </div>
            </div>
        </div>

        <div class="list-container">
            <div class="list-number"></div>
            <div class="list-content">dalam hal ini bertindak untuk dan atas nama diri sendiri, untuk selanjutnya disebut Pihak Kedua.</div>
        </div>

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
                <div class="list-container">
                    <div class="list-number">a.</div>
                    <div class="list-content">
                        <div class="info-row">
                            <div class="info-label">Masa Perjanjian Kerja</div>
                            <div class="info-separator">:</div>
                            <div class="info-value">
                                <?= formatIndonesianDate($pk_data['tanggal_kontrak_awal'] ?? '0000-00-00') ?>
                                s/d
                                <?= formatIndonesianDate($pk_data['tanggal_kontrak_akhir'] ?? '0000-00-00') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="list-container">
                    <div class="list-number">b.</div>
                    <div class="list-content">
                        <div class="info-row">
                            <div class="info-label">Jabatan</div>
                            <div class="info-separator">:</div>
                            <div class="info-value"><?= esc(strtoupper($email['jabatan'] ?? 'N/A')) ?></div>
                        </div>
                    </div>
                </div>
                <div class="list-container">
                    <div class="list-number">c.</div>
                    <div class="list-content">
                        <div class="info-row">
                            <div class="info-label">Masa Kerja Sebelumnya</div>
                            <div class="info-separator">:</div>
                            <div class="info-value">0 Tahun 0 Bulan</div>
                        </div>
                    </div>
                </div>
                <div class="list-container">
                    <div class="list-number">d.</div>
                    <div class="list-content">
                        <div class="info-row">
                            <div class="info-label">Unit Kerja</div>
                            <div class="info-separator">:</div>
                            <div class="info-value"><?= esc(strtoupper($unit_kerja['nama_unit_kerja'])) ?> - PEMERINTAH KABUPATEN SINJAI</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 2 <br>
                TUGAS PEKERJAAN
            </h3>

            <div class="isi-bab">
                <div class="list-container">
                    <div class="list-number">(1)</div>
                    <div class="list-content">Pihak Kesatu membuat dan menetapkan tugas pekerjaan yang harus dilaksanakan oleh Pihak Kedua.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(2)</div>
                    <div class="list-content">Pihak Kedua wajib melaksanakan tugas pekerjaan yang diberikan Pihak Kesatu dengan sebaik-baiknya dengan rasa tanggung jawab.</div>
                </div>
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 3 <br>
                TARGET KINERJA
            </h3>

            <div class="isi-bab">
                <div class="list-container">
                    <div class="list-number">(1)</div>
                    <div class="list-content">Pihak Kesatu membuat dan menetapkan target kinerja bagi Pihak Kedua selama masa Perjanjian Kerja.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(2)</div>
                    <div class="list-content">Pihak Kedua wajib memenuhi target kinerja yang telah ditetapkan oleh Pihak Kesatu.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(3)</div>
                    <div class="list-content">Pihak Kesatu dan Pihak Kedua menandatangani target perjanjian kinerja sesuai peraturan perundang-undangan.</div>
                </div>
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
                
                    <div class="list-container"><div class="list-number">(1)</div><div class="list-content">Pihak Kedua wajib mematuhi semua kewajiban dan larangan.</div></div>
                    <div class="list-container"><div class="list-number">(2)</div><div class="list-content">
                            Kewajiban bagi Pihak Kedua sebagaimana dimaksud pada ayat (1) meliputi:
                            
                                <div class="list-container"><div class="list-number">a.</div><div class="list-content">setia dan taat pada Pancasila, Undang-Undang Dasar Negara Republik Indonesia Tahun 1945, Negara Kesatuan Republik Indonesia, dan Pemerintah yang sah;</div></div>
                                <div class="list-container"><div class="list-number">b.</div><div class="list-content">menjaga persatuan dan kesatuan bangsa;</div></div>
                                <div class="list-container"><div class="list-number">c.</div><div class="list-content">melaksanakan kebijakan yang dirumuskan pejabat pemerintah yang berwenang;</div></div>
                                <div class="list-container"><div class="list-number">d.</div><div class="list-content">menaati ketentuan peraturan perundang-undangan;</div></div>
                                <div class="list-container"><div class="list-number">e.</div><div class="list-content">melaksanakan tugas kedinasan dengan penuh pengabdian, kejujuran, kesadaran, dan tanggung jawab;</div></div>
                                <div class="list-container"><div class="list-number">f.</div><div class="list-content">menunjukkan integritas dan keteladanan dalam sikap, perilaku, ucapan, dan tindakan kepada setiap orang, baik di dalam maupun di luar kedinasan;</div></div>
                                <div class="list-container"><div class="list-number">g.</div><div class="list-content">menyimpan rahasia jabatan dan hanya dapat mengemukakan rahasia jabatan sesuai dengan ketentuan peraturan perundang-undangan; dan</div></div>
                                <div class="list-container"><div class="list-number">h.</div><div class="list-content">bersedia ditempatkan di seluruh wilayah Negara Kesatuan Republik Indonesia.</div></div>
                            
                        </div></div>
                    <div class="list-container"><div class="list-number">(3)</div><div class="list-content">
                            Selain memenuhi kewajiban sebagaimana dimaksud dalam Pasal 5 ayat (2), Pihak Kedua wajib:
                            
                                <div class="list-container"><div class="list-number">a.</div><div class="list-content">mengucapkan sumpah/janji PPPK;</div></div>
                                <div class="list-container"><div class="list-number">b.</div><div class="list-content">mengucapkan sumpah/janji jabatan;</div></div>
                                <div class="list-container"><div class="list-number">c.</div><div class="list-content">setia dan taat sepenuhnya kepada Pancasila, Undang-Undang Dasar Negara Republik Indonesia Tahun 1945, Negara Kesatuan Republik Indonesia, dan Pemerintah;</div></div>
                                <div class="list-container"><div class="list-number">d.</div><div class="list-content">menaati segala ketentuan peraturan perundang-undangan;</div></div>
                                <div class="list-container"><div class="list-number">e.</div><div class="list-content">melaksanakan tugas kedinasan yang dipercayakan kepada PPPK dengan penuh pengabdian, kesadaran, dan tanggung jawab;</div></div>
                                <div class="list-container"><div class="list-number">f.</div><div class="list-content">menjunjung tinggi kehormatan negara, Pemerintah, dan martabat PPPK;</div></div>
                                <div class="list-container"><div class="list-number">g.</div><div class="list-content">mengutamakan kepentingan negara daripada kepentingan sendiri, seseorang, dan/atau golongan;</div></div>
                                <div class="list-container"><div class="list-number">h.</div><div class="list-content">memegang rahasia jabatan yang menurut sifatnya atau menurut perintah harus dirahasiakan;</div></div>
                                <div class="list-container"><div class="list-number">i.</div><div class="list-content">bekerja dengan jujur, tertib, cermat, dan bersemangat untuk kepentingan negara;</div></div>
                                <div class="list-container"><div class="list-number">j.</div><div class="list-content">melaporkan dengan segera kepada atasannya apabila mengetahui ada hal yang dapat membahayakan atau merugikan negara atau Pemerintah terutama di bidang keamanan, keuangan, dan materiil;</div></div>
                                <div class="list-container"><div class="list-number">k.</div><div class="list-content">masuk kerja dan menaati ketentuan jam kerja;</div></div>
                                <div class="list-container"><div class="list-number">l.</div><div class="list-content">mencapai sasaran kerja pegawai yang ditetapkan;</div></div>
                                <div class="list-container"><div class="list-number">m.</div><div class="list-content">menggunakan dan memelihara barang-barang milik negara dengan sebaik-baiknya;</div></div>
                                <div class="list-container"><div class="list-number">n.</div><div class="list-content">memberikan pelayanan sebaik-baiknya kepada masyarakat; dan</div></div>
                                <div class="list-container"><div class="list-number">o.</div><div class="list-content">menaati peraturan kedinasan yang ditetapkan oleh pejabat yang berwenang.</div></div>
                            
                        </div></div>
                    <div class="list-container"><div class="list-number">(4)</div><div class="list-content">
                            Larangan bagi Pihak Kedua sebagaimana dimaksud pada ayat (1) meliputi:
                            
                                <div class="list-container"><div class="list-number">a.</div><div class="list-content">menyalahgunakan wewenang;</div></div>
                                <div class="list-container"><div class="list-number">b.</div><div class="list-content">menjadi perantara untuk mendapatkan keuntungan pribadi dan/atau orang lain dengan menggunakan kewenangan orang lain;</div></div>
                                <div class="list-container"><div class="list-number">c.</div><div class="list-content">tanpa izin Pemerintah menjadi pegawai atau bekerja untuk negara lain dan/atau lembaga atau organisasi internasional;</div></div>
                                <div class="list-container"><div class="list-number">d.</div><div class="list-content">bekerja pada perusahaan asing, konsultan asing, atau lembaga swadaya masyarakat asing;</div></div>
                                <div class="list-container"><div class="list-number">e.</div><div class="list-content">memiliki, menjual, membeli, menggadaikan, menyewakan, atau meminjamkan barang-barang baik bergerak atau tidak bergerak, dokumen atau surat berharga milik negara secara tidak sah;</div></div>
                                <div class="list-container"><div class="list-number">f.</div><div class="list-content">melakukan kegiatan bersama dengan atasan, teman sejawat, bawahan, atau orang lain di dalam maupun di luar lingkungan kerjanya dengan tujuan untuk keuntungan pribadi, golongan, atau pihak lain yang secara langsung atau tidak langsung merugikan negara;</div></div>
                                <div class="list-container"><div class="list-number">g.</div><div class="list-content">memberikan atau menyanggupi akan memberi sesuatu kepada siapa pun baik secara langsung atau tidak langsung dengan dalih apa pun untuk diangkat dalam jabatan;</div></div>
                                <div class="list-container"><div class="list-number">h.</div><div class="list-content">menerima hadiah atau sesuatu pemberian apa saja dari siapa pun juga yang berhubungan dengan jabatan dan/atau pekerjaannya;</div></div>
                                <div class="list-container"><div class="list-number">i.</div><div class="list-content">bertindak sewenang-wenang terhadap bawahannya;</div></div>
                                <div class="list-container"><div class="list-number">j.</div><div class="list-content">melakukan suatu tindakan atau tidak melakukan suatu tindakan yang dapat menghalangi atau mempersulit salah satu pihak yang dilayani sehingga mengakibatkan kerugian bagi yang dilayani;</div></div>
                                <div class="list-container"><div class="list-number">k.</div><div class="list-content">menghalangi berjalannya tugas kedinasan;</div></div>
                                <div class="list-container"><div class="list-number">l.</div><div class="list-content">
                                        memberikan dukungan kepada calon Presiden/Wakil Presiden, Dewan Perwakilan Rakyat, Dewan Perwakilan Daerah, atau Dewan Perwakilan Rakyat Daerah dengan cara:
                                        
                                            <div class="list-container"><div class="list-number">1)</div><div class="list-content">ikut serta sebagai pelaksana kampanye;</div></div>
                                            <div class="list-container"><div class="list-number">2)</div><div class="list-content">menjadi peserta kampanye dengan menggunakan atribut partai atau atribut Aparatur Sipil Negara;</div></div>
                                            <div class="list-container"><div class="list-number">3)</div><div class="list-content">sebagai peserta kampanye dengan mengerahkan Aparatur Sipil Negara lain; dan/atau</div></div>
                                            <div class="list-container"><div class="list-number">4)</div><div class="list-content">sebagai peserta kampanye dengan menggunakan fasilitas negara.</div></div>
                                        
                                    </div></div>

                                <div class="list-container"><div class="list-number">m.</div><div class="list-content">
                                        memberikan dukungan kepada calon Presiden/Wakil Presiden dengan cara:
                                        
                                            <div class="list-container"><div class="list-number">1)</div><div class="list-content">membuat keputusan dan/atau tindakan yang menguntungkan atau merugikan salah satu pasangan calon selama masa kampanye; dan/atau</div></div>
                                            <div class="list-container"><div class="list-number">2)</div><div class="list-content">mengadakan kegiatan yang mengarah kepada keberpihakan terhadap pasangan calon yang menjadi peserta pemilu sebelum, selama, dan/atau sesudah masa kampanye meliputi pertemuan, ajakan, imbauan, seruan, atau pemberian barang kepada Aparatur Sipil Negara dalam lingkungan unit kerjanya, anggota keluarga, dan masyarakat.</div></div>
                                        
                                    </div></div>
                                <div class="list-container"><div class="list-number">n.</div><div class="list-content">memberikan dukungan kepada calon anggota Dewan Perwakilan Daerah atau calon Kepala Daerah/Wakil Kepala Daerah dengan memberikan surat dukungan disertai fotokopi Kartu Tanda Penduduk atau Surat Keterangan Tanda Penduduk sesuai peraturan perundang-undangan; dan</div></div>
                                <div class="list-container"><div class="list-number">o.</div><div class="list-content">
                                        memberikan dukungan kepada calon Kepala Daerah/Wakil Kepala Daerah, dengan cara:
                                        
                                            <div class="list-container"><div class="list-number">1)</div><div class="list-content">terlibat dalam kegiatan kampanye untuk mendukung calon Kepala Daerah/Wakil Kepala Daerah;</div></div>
                                            <div class="list-container"><div class="list-number">2)</div><div class="list-content">menggunakan fasilitas yang terkait dengan jabatan dalam kegiatan kampanye;</div></div>
                                            <div class="list-container"><div class="list-number">3)</div><div class="list-content">membuat keputusan dan/atau tindakan yang menguntungkan atau merugikan salah satu pasangan calon selama masa kampanye; dan/atau</div></div>
                                            <div class="list-container"><div class="list-number">4)</div><div class="list-content">mengadakan kegiatan yang mengarah kepada keberpihakan terhadap pasangan calon yang menjadi peserta pemilu sebelum, selama, dan/atau sesudah masa kampanye meliputi pertemuan, ajakan, imbauan, seruan, atau pemberian barang kepada Aparatur Sipil Negara dalam lingkungan unit kerjanya, anggota keluarga, dan masyarakat.</div></div>
                                        
                                    </div></div>
                            
                        </div></div>
                    <div class="list-container"><div class="list-number">(5)</div><div class="list-content">Selain larangan sebagaimana dimaksud pada ayat (4), Pihak Kedua dilarang:
                            
                                <div class="list-container"><div class="list-number">a.</div><div class="list-content">melakukan hal-hal yang dapat menurunkan kehormatan atau martabat Negara dan Pemerintah;</div></div>
                                <div class="list-container"><div class="list-number">b.</div><div class="list-content">menyalahgunakan wewenangnya;</div></div>
                                <div class="list-container"><div class="list-number">c.</div><div class="list-content">tanpa izin Pemerintah Kabupaten Sinjai menjadi pegawai atau bekerja di tempat lain;</div></div>
                                <div class="list-container"><div class="list-number">d.</div><div class="list-content">melakukan pernikahan kedua, ketiga, dan bagi wanita tidak menjadi atau berkedudukan sebagai istri kedua, ketiga, dan seterusnya;</div></div>
                                <div class="list-container"><div class="list-number">e.</div><div class="list-content">menyalahgunakan barang-barang, uang, atau surat-surat berharga milik Negara dan Pemerintah;</div></div>
                                <div class="list-container"><div class="list-number">f.</div><div class="list-content">memiliki, menjual, membeli, menggadaikan, menyewakan, atau meminjamkan barang-barang, dokumen, atau surat-surat berharga milik Negara dan Pemerintah secara tidak sah;</div></div>
                                <div class="list-container"><div class="list-number">g.</div><div class="list-content">melakukan kegiatan bersama dengan atasan, teman sejawat, bawahan, atau orang lain di dalam maupun di luar lingkungan kerjanya dengan tujuan untuk keuntungan pribadi, golongan, atau pihak lain yang secara langsung atau tidak langsung merugikan Negara;</div></div>
                                <div class="list-container"><div class="list-number">h.</div><div class="list-content">menerima hadiah atau sesuatu pemberian berupa apa saja dari siapa pun juga yang diketahui atau patut dapat diduga bahwa pemberian itu bersangkutan atau mungkin bersangkutan dengan pekerjaan pegawai;</div></div>
                                <div class="list-container"><div class="list-number">i.</div><div class="list-content">memasuki tempat-tempat yang dapat mencemarkan kehormatan, kecuali untuk kepentingan tugas;</div></div>
                                <div class="list-container"><div class="list-number">j.</div><div class="list-content">melakukan suatu tindakan atau sengaja tidak melakukan suatu tindakan yang dapat berakibat menghalangi atau mempersulit salah satu pihak yang dilayaninya sehingga mengakibatkan kerugian bagi pihak yang dilayani;</div></div>
                                <div class="list-container"><div class="list-number">k.</div><div class="list-content">menghalangi berjalannya tugas kedinasan;</div></div>
                                <div class="list-container"><div class="list-number">l.</div><div class="list-content">membocorkan dan/atau memanfaatkan rahasia Negara yang diketahui karena kedudukan jabatan untuk kepentingan pribadi, golongan, atau pihak lain;</div></div>
                                <div class="list-container"><div class="list-number">m.</div><div class="list-content">bertindak selaku perantara bagi sesuatu pengusaha atau golongan untuk mendapatkan pekerjaan atau pesanan dari kantor/instansi Pemerintah; dan</div></div>
                                <div class="list-container"><div class="list-number">n.</div><div class="list-content">melakukan pungutan tidak sah dalam bentuk apa pun juga dalam melaksanakan tugasnya untuk kepentingan pribadi, golongan, atau pihak lain.</div></div>
                            
                        </div></div>
                    <div class="list-container"><div class="list-number">(6)</div><div class="list-content">
                            Pihak Kedua yang tidak mematuhi kewajiban dan/atau melanggar larangan sebagaimana dimaksud pada ayat (2), ayat (3), ayat (4) dan ayat (5) dikenakan sanksi berupa:
                            
                                <div class="list-container"><div class="list-number">a.</div><div class="list-content">
                                        Sanksi ringan:
                                        
                                            <div class="list-container"><div class="list-number">1)</div><div class="list-content">teguran lisan.</div></div>
                                        
                                    </div></div>
                                <div class="list-container"><div class="list-number">b.</div><div class="list-content">
                                        Sanksi sedang:
                                        
                                            <div class="list-container"><div class="list-number">1)</div><div class="list-content">teguran tertulis.</div></div>
                                            <div class="list-container"><div class="list-number">2)</div><div class="list-content">pernyataan tidak puas secara tertulis.</div></div>
                                        
                                    </div></div>
                                <div class="list-container"><div class="list-number">c.</div><div class="list-content">
                                        Sanksi berat:
                                        
                                            <div class="list-container"><div class="list-number">1)</div><div class="list-content">pemutusan hubungan Perjanjian Kerja dengan hormat;</div></div>
                                            <div class="list-container"><div class="list-number">2)</div><div class="list-content">pemutusan hubungan Perjanjian Kerja dengan hormat tidak atas permintaan sendiri; atau</div></div>
                                            <div class="list-container"><div class="list-number">3)</div><div class="list-content">pemutusan hubungan Perjanjian Kerja tidak dengan hormat.</div></div>
                                        
                                    </div></div>
                            
                        </div></div>
                
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 6 <br>
                GAJI
            </h3>

            <div class="isi-bab">
                <div class="list-container">
                    <div class="list-number">(1)</div>
                    <div class="list-content">Pihak Kedua berhak mendapat gaji sesuai dengan ketentuan peraturan perundang-undangan dan berdasarkan kemampuan keuangan daerah.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(2)</div>
                    <div class="list-content">Pihak Kedua berhak menerima gaji sebagai PPPK Paruh Waktu sebesar <?= (isset($pk_data['gaji_nominal']) && !empty($pk_data['gaji_nominal'])) ? "Rp. " . number_format($pk_data['gaji_nominal'], 0, ',', '.') : 'N/A' ?> (<?= esc($pk_data['gaji_terbilang'] ?? 'N/A') ?> Rupiah).</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(3)</div>
                    <div class="list-content">Besaran gaji Pihak Kedua sebagaimana dimaksud pada ayat (2) diberikan sesuai dengan ketentuan peraturan perundang-undangan dan berdasarkan kemampuan keuangan daerah.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(4)</div>
                    <div class="list-content">Pembayaran gaji sebagaimana dimaksud pada ayat (2) dilakukan sejak Pihak Kedua melaksanakan tugas yang dibuktikan dengan Surat Pernyataan Melaksanakan Tugas dari pimpinan unit kerja penempatan Pihak Kedua.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(5)</div>
                    <div class="list-content">Apabila Pihak Kedua melaksanakan tugas pada tanggal hari kerja pertama bulan berkenaan, gaji sebagaimana dimaksud pada ayat (2) dibayarkan mulai bulan berkenaan.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(6)</div>
                    <div class="list-content">Apabila Pihak Kedua melaksanakan tugas pada tanggal hari kerja kedua dan seterusnya pada bulan berkenaan, gaji sebagaimana dimaksud pada ayat (2) dibayarkan mulai bulan berikutnya.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(7)</div>
                    <div class="list-content">Pemberian gaji Pihak Kedua dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(8)</div>
                    <div class="list-content">Penerimaan gaji sebagaimana dimaksud pada ayat (2) dapat dilakukan pemotongan pada saat pembayaran sesuai ketentuan peraturan perundang-undangan.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(9)</div>
                    <div class="list-content">Pembayaran gaji sebagaimana dimaksud pada ayat (2) diberikan sesuai dengan kemampuan keuangan daerah.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(10)</div>
                    <div class="list-content">Pihak Kedua mendapatkan fasilitas dan pendapatan lain yang sah sesuai dengan ketentuan peraturan perundang-undangan dan kemampuan keuangan daerah.</div>
                </div>
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 7 <br>
                CUTI
            </h3>

            <div class="isi-bab">
                <div class="list-container">
                    <div class="list-number">(1)</div>
                    <div class="list-content">Pihak Kedua berhak mendapatkan cuti tahunan, cuti sakit, cuti melahirkan, dan cuti bersama selama masa perjanjian kerja.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(2)</div>
                    <div class="list-content">Cuti sebagaimana dimaksud pada ayat (1) dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</div>
                </div>
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 8 <br>
                PENGEMBANGAN KOMPETENSI
            </h3>

            <div class="isi-bab">
                <div class="list-container">
                    <div class="list-number">(1)</div>
                    <div class="list-content">Pihak Kesatu memberikan pengembangan kompetensi kepada Pihak Kedua untuk mendukung pelaksanaan tugas selama masa Perjanjian Kerja dengan memperhatikan hasil penilaian kinerja Pihak Kedua.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(2)</div>
                    <div class="list-content">Pelaksanaan pengembangan kompetensi sebagaimana dimaksud pada ayat (1) dilaksanakan sesuai dengan peraturan perundang-undangan.</div>
                </div>
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 9 <br>
                PENGHARGAAN
            </h3>

            <div class="isi-bab">
                <div class="list-container">
                    <div class="list-number">(1)</div>
                    <div class="list-content">
                        Pihak Kesatu memberikan penghargaan kepada Pihak Kedua berupa:
                        <div class="list-container">
                            <div class="list-number">a.</div>
                            <div class="list-content">tanda kehormatan;</div>
                        </div>
                        <div class="list-container">
                            <div class="list-number">b.</div>
                            <div class="list-content">kesempatan prioritas untuk pengembangan kompetensi; dan/atau</div>
                        </div>
                        <div class="list-container">
                            <div class="list-number">c.</div>
                            <div class="list-content">kesempatan menghadiri secara resmi dan/atau acara kenegaraan.</div>
                        </div>
                    </div>
                </div>
                <div class="list-container">
                    <div class="list-number">(2)</div>
                    <div class="list-content">Pemberian penghargaan kepada Pihak Kedua sebagaimana dimaksud pada ayat (1) huruf a dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(3)</div>
                    <div class="list-content">Pemberian penghargaan kepada Pihak Kedua sebagaimana dimaksud pada ayat (1) huruf b diberikan kepada Pihak Kedua apabila mempunyai penilaian kinerja yang paling baik.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(4)</div>
                    <div class="list-content">Pemberian penghargaan kepada Pihak Kedua sebagaimana dimaksud pada ayat (1) huruf c diberikan kepada Pihak Kedua setelah mendapatkan pertimbangan dari Tim Penilai Kinerja Pegawai Pemerintah dengan Perjanjian Kerja yang ada pada Pihak Kesatu.</div>
                </div>
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 10 <br>
                PERLINDUNGAN
            </h3>

            <div class="isi-bab">
                <div class="list-container">
                    <div class="list-number">(1)</div>
                    <div class="list-content">
                        Pihak Kesatu wajib memberikan perlindungan bagi Pihak Kedua berupa:
                        <div class="list-container">
                            <div class="list-number">a.</div>
                            <div class="list-content">jaminan kesehatan;</div>
                        </div>
                        <div class="list-container">
                            <div class="list-number">b.</div>
                            <div class="list-content">jaminan kecelakaan kerja; dan</div>
                        </div>
                        <div class="list-container">
                            <div class="list-number">c.</div>
                            <div class="list-content">jaminan kematian;</div>
                        </div>
                    </div>
                </div>
                <div class="list-container">
                    <div class="list-number">(2)</div>
                    <div class="list-content">Perlindungan sebagaimana dimaksud pada ayat (1) huruf a, huruf b, dan huruf c dilakukan dengan mengikutsertakan Pihak Kedua dalam program sistem jaminan sosial nasional.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(3)</div>
                    <div class="list-content">Pemberian perlindungan kepada Pihak Kedua sebagaimana dimaksud pada ayat (1) dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan.</div>
                </div>
            </div>
        </div>

        <div class="bab">
            <h3 class="judul-bab">
                Pasal 11 <br>
                PEMUTUSAN HUBUNGAN PERJANJIAN KERJA
            </h3>

            <div class="isi-bab">
                Pihak Kesatu dan Pihak Kedua dapat melakukan pemutusan hubungan Perjanjian Kerja dengan ketentuan sebagai berikut:
                <div class="list-container">
                    <div class="list-number">(1)</div>
                    <div class="list-content">Diangkat menjadi PPPK atau CPNS;</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(2)</div>
                    <div class="list-content">Mengundurkan diri;</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(3)</div>
                    <div class="list-content">Meninggal dunia;</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(4)</div>
                    <div class="list-content">Melakukan penyelewangan terhadap Pancasila dan Undang-Undang Dasar Negara Republik Indonesia Tahun 1945;</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(5)</div>
                    <div class="list-content">Mencapai Batas Usia Pensiun (BUP) jabatan;</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(6)</div>
                    <div class="list-content">Berakhirnya masa perjanjian kerja;</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(7)</div>
                    <div class="list-content">Terdampak perampingan organisasi atau kebijkan pemerintah;</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(8)</div>
                    <div class="list-content">Tidak cakap jasmani dan/atau Rohani, sehingga tidak dapat menjalankan tugas dan kewajiban;</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(9)</div>
                    <div class="list-content">Tidak berkinerja;</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(10)</div>
                    <div class="list-content">Melakukan pelanggaran disiplin tingkat berat;</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(11)</div>
                    <div class="list-content">Dipidana dengan pidana penjara berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap karena melakukan tindak pidana dengan pidana penjara paling singkat 2 (dua) tahun;</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(12)</div>
                    <div class="list-content">Dipidana dengan pidana penjara atau kurungan berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap kerena tetap melakukan tindak pidana kejahatan yang ada hubungannya dengan jabatan; dan/atau</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(13)</div>
                    <div class="list-content">Menjadi anggota dan/atau pengurus partai politik.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(14)</div>
                    <div class="list-content">
                        Pemutusan Hubungan Perjanjian Kerja dengan hormat dilakukan apabila:
                        <div class="list-container">
                            <div class="list-number">a.</div>
                            <div class="list-content">jangka waktu Perjanjian Kerja berakhir;</div>
                        </div>
                        <div class="list-container">
                            <div class="list-number">b.</div>
                            <div class="list-content">Pihak Kedua meninggal dunia;</div>
                        </div>
                        <div class="list-container">
                            <div class="list-number">c.</div>
                            <div class="list-content">Pihak Kedua mengajukan permohonan berhenti sebagai Pegawai Pemerintah dengan Perjanjian Kerja; atau</div>
                        </div>
                        <div class="list-container">
                            <div class="list-number">d.</div>
                            <div class="list-content">terjadi perampingan organisasi atau kebijakan Pemerintah yang mengakibatkan pengurangan Pegawai Pemerintah dengan Perjanjian Kerja pada Pihak Kesatu.</div>
                        </div>
                    </div>
                </div>
                <div class="list-container">
                    <div class="list-number">(15)</div>
                    <div class="list-content">
                        Pemutusan Hubungan Perjanjian Kerja dengan hormat tidak atas permintaan sendiri dilakukan apabila:
                        <div class="list-container">
                            <div class="list-number">a.</div>
                            <div class="list-content">Pihak Kedua dihukum penjara berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap karena melakukan tindak pidana penjara paling singkat 2 (dua) tahun dan tindak pidana dilakukan dengan tidak berencana;</div>
                        </div>
                        <div class="list-container">
                            <div class="list-number">b.</div>
                            <div class="list-content">Pihak Kedua melakukan pelanggaran kewajiban dan/atau larangan sebagaimana dimaksud dalam Pasal 5; atau</div>
                        </div>
                        <div class="list-container">
                            <div class="list-number">c.</div>
                            <div class="list-content">Pihak Kedua tidak dapat memenuhi target kinerja yang telah disepakati sesuai dengan Perjanjian Kerja.</div>
                        </div>
                    </div>
                </div>
                <div class="list-container">
                    <div class="list-number">(16)</div>
                    <div class="list-content">
                        Pemutusan Hubungan Perjanjian Kerja tidak dengan hormat dilakukan apabila:
                        <div class="list-container">
                            <div class="list-number">a.</div>
                            <div class="list-content">melakukan penyelewengan terhadap Pancasila dan/atau Undang-Undang Dasar Negara Republik Indonesia Tahun 1945;</div>
                        </div>
                        <div class="list-container">
                            <div class="list-number">b.</div>
                            <div class="list-content">dihukum penjara atau kurungan berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap karena melakukan tindak pidana kejahatan jabatan atau tindak pidana yang ada hubungannya dengan jabatan;</div>
                        </div>
                        <div class="list-container">
                            <div class="list-number">c.</div>
                            <div class="list-content">menjadi anggota dan/atau pengurus partai politik; atau</div>
                        </div>
                        <div class="list-container">
                            <div class="list-number">d.</div>
                            <div class="list-content">dihukum penjara berdasarkan putusan pengadilan yang telah memiliki kekuatan hukum tetap karena melakukan tindak pidana yang diancam pidana penjara paling singkat 2 (dua) tahun atau lebih dan tindak pidana tersebut dilakukan dengan berencana.</div>
                        </div>
                    </div>
                </div>
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
                <div class="list-container">
                    <div class="list-number">(1)</div>
                    <div class="list-content">Pihak Kedua bersedia melaksanakan seluruh ketentuan yang telah diatur dalam peraturan kedinasan dan peraturan lainnya yang berlaku di Pihak Kesatu.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(2)</div>
                    <div class="list-content">Pihak Kedua wajib menyimpan dan menjaga kerahasiaan baik dokumen maupun informasi milik Pihak Kesatu sesuai dengan ketentuan peraturan perundang-undangan.</div>
                </div>
                <div class="list-container">
                    <div class="list-number">(3)</div>
                    <div class="list-content">Pihak Kesatu dapat memperpanjang masa Perjanjian Kerja yang dilaksanakan sesuai dengan peraturan perundang-undangan.</div>
                </div>
            </div>
        </div>

        <div class="paragraf">
            Demikian Perjanjian Kerja ini dibuat dalam rangkap 2 (dua) oleh Pihak Kesatu dan Pihak Kedua dalam keadaan sehat dan sadar serta tanpa pengaruh ataupun paksaan dari pihak mana pun, masing-masing bermaterai cukup dan mempunyai kekuatan hukum yang sama.
        </div>

        <div class="area-ttd">
            <div class="signature-row">
                <div class="signature-cell pihak">
                    PIHAK KESATU
                </div>
                <div class="signature-cell pihak">
                    PIHAK KEDUA
                </div>
            </div>
            <div class="signature-row">
                <div class="signature-cell spasi-ttd">
                    ${ttd_pengirim2}
                </div>
                <div class="signature-cell spasi-ttd">
                    ${ttd_pengirim1}
                </div>
            </div>
            <div class="signature-row">
                <div class="signature-cell nama-penandatangan">
                    Dra. Hj. RATNAWATI ARIF, M.Si.
                </div>
                <div class="signature-cell nama-penandatangan">
                    <?php if (!empty($email['gelar_depan'])): ?>
                        <?= esc($email['gelar_depan']) ?>
                    <?php endif; ?>
                    <?= esc(strtoupper($email['name'])) ?><?php if (!empty($email['gelar_belakang'])): ?>, <?= esc($email['gelar_belakang']) ?><?php endif; ?>
                </div>
            </div>
        </div>
    </div>



</body>

</html>