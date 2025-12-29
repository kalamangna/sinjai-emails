<!DOCTYPE html>
<html>

<head>
    <title>Akun Email - <?= esc($unit_kerja['nama_unit_kerja']) ?></title>
    <style>
        @page {
            margin: 10px 25px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 10px;
            font-size: 10px;
        }

        h1 {
            color: #333;
            text-align: center;
            font-size: 14px;
        }

        h2 {
            color: #555;
            text-align: center;
            font-size: 12px;
            margin-top: -10px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Kolom No. */
        th:nth-child(1),
        td:nth-child(1) {
            text-align: center;
            width: 5%;
        }

        /* Kolom Nama */
        th:nth-child(2),
        td:nth-child(2) {
            width: 30%;
        }

        /* Kolom Unit Kerja */
        <?= ($showUnitKerjaColumn ? 'th:nth-child(3), td:nth-child(3) { width: 25%; }' : '') ?>

        /* Kolom Email */
        th:nth-child(<?= ($showUnitKerjaColumn ? '4' : '3') ?>),
        td:nth-child(<?= ($showUnitKerjaColumn ? '4' : '3') ?>) {
            width: 25%;
        }

        /* Kolom Status TTE */
        th:nth-child(<?= ($showUnitKerjaColumn ? '5' : '4') ?>),
        td:nth-child(<?= ($showUnitKerjaColumn ? '5' : '4') ?>) {
            width: 15%;
        }

        .tte-description {
            font-size: 9px;
            margin-top: 10px;
            color: #555;
            width: 100%;
        }

        .tte-description ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            width: 100%;
        }

        .tte-description li {
            margin-bottom: 2px;
            width: 100%;
        }

        .tte-description strong {
            display: inline-block;
            width: 140px;
            vertical-align: top;
        }

        .instruction {
            text-align: center;
            font-weight: bold;
            font-size: 1.1em;
            margin-bottom: 15px;
            padding: 8px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .logo {
            max-width: 80px;
            max-height: 80px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="<?= $logoSrc ?>" alt="Logo" class="logo" />
        <h1>DAFTAR AKUN EMAIL</h1>
        <h2><?= esc($unit_kerja['nama_unit_kerja']) ?></h2>
    </div>

    <p class="instruction">
        Untuk AKTIVASI AKUN, login menggunakan EMAIL dan PASSWORD melalui halaman sinjaikab.go.id/webmail
    </p>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama</th>
                <?= ($showUnitKerjaColumn ? '<th>Unit Kerja</th>' : '') ?>
                <th>Email</th>
                <th>Password</th>
                <th>Status TTE</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $nomor = 1;
            foreach ($emails as $email) {
                $statusTte = !empty($email['bsre_status']) ? $email['bsre_status'] : 'NOT SYNCED';

                // Color logic
                $color = '#000000'; // Default black
                if ($statusTte === 'ISSUE') {
                    $color = '#198754'; // Green
                } elseif (in_array($statusTte, ['EXPIRED', 'REVOKE', 'SUSPEND'])) {
                    $color = '#dc3545'; // Red
                } elseif (in_array($statusTte, ['RENEW', 'WAITING_FOR_VERIFICATION', 'NEW'])) {
                    $color = '#0dcaf0'; // Cyan/Info
                } elseif (in_array($statusTte, ['NO_CERTIFICATE', 'NOT_REGISTERED', 'NOT SYNCED'])) {
                    $color = '#d39e00'; // Yellow/Orange
                }

                echo '<tr>
                        <td>' . $nomor . '</td> 
                        <td>' . esc(strtoupper($email['name'] ?? 'N/A')) . '</td>
                        ' . ($showUnitKerjaColumn ? '<td>' . esc($email['unit_kerja_name'] ?? 'N/A') . '</td>' : '') . '
                                                                        <td>' . esc($email['email'] ?? 'N/A') . '</td>
                                                                        <td>' . esc($email['password'] ?? 'N/A') . '</td>
                                                                        <td style="color: ' . $color . '; font-weight: bold;">' . esc($statusTte) . '</td>                    </tr>';
                $nomor++;
            }
            ?>
        </tbody>
    </table>

    <div class="tte-description">
        <p><strong>Keterangan Status TTE:</strong></p>
        <ul>
            <li><strong>ISSUE</strong> : Sertifikat Aktif / Siap TTE</li>
            <li><strong>EXPIRED</strong> : Masa Berlaku Habis</li>
            <li><strong>RENEW</strong> : Proses Pembaruan</li>
            <li><strong>WAITING_FOR_VERIFICATION</strong> : Menunggu Verifikasi</li>
            <li><strong>NEW</strong> : Belum Aktivasi</li>
            <li><strong>NO_CERTIFICATE</strong> : Belum Ada Sertifikat</li>
            <li><strong>NOT_REGISTERED</strong> : Pengguna Tidak Terdaftar</li>
            <li><strong>SUSPEND</strong> : Akun Ditangguhkan</li>
            <li><strong>REVOKE</strong> : Sertifikat Dicabut</li>
            <li><strong>NOT SYNCED</strong> : Belum dilakukan sinkronisasi data dengan BSrE</li>
        </ul>
    </div>
</body>

</html>