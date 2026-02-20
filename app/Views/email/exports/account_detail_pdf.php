<!DOCTYPE html>
<html>

<head>
    <title>Daftar Akun Email & TTE - <?= esc($unit_kerja['nama_unit_kerja']) ?></title>
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
            vertical-align: top;
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
            width: 35%;
        }

        /* Kolom Email */
        th:nth-child(3),
        td:nth-child(3) {
            width: 25%;
        }

        /* Kolom Password */
        th:nth-child(4),
        td:nth-child(4) {
            width: 20%;
        }

        /* Kolom Status TTE */
        th:nth-child(5),
        td:nth-child(5) {
            width: 15%;
        }

        .tte-description {
            font-size: 9px;
            margin-top: 10px;
            width: 100%;
        }

        .tte-description p {
            margin: 0 0 5px 0;
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
            color: #555;
        }

        .tte-description li strong {
            display: inline-block;
            width: 100px;
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

        .footer-info {
            position: fixed;
            bottom: 0;
            left: 0;
            font-size: 9px;
            text-align: left;
        }

        .footer-right {
            position: fixed;
            bottom: 0;
            right: 0;
            font-size: 9px;
            text-align: right;
            color: #555;
        }

        .footer-info p,
        .footer-right p {
            margin: 2px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="<?= $logoSrc ?>" alt="Logo" class="logo" />
        <h1>DAFTAR AKUN EMAIL & TTE</h1>
        <h2><?= esc($unit_kerja['nama_unit_kerja']) ?></h2>
        <p style="text-align: center; font-size: 10px; color: #666; margin-top: -10px;">UPDATE PER: <?= strtoupper(esc($current_date)) ?></p>
        <p style="text-align: center; font-size: 11px; color: #cc0000; font-weight: bold; margin-top: 15px; margin-bottom: 20px; line-height: 1.4;">
            Untuk aktivasi akun, masukkan email dan password di halaman<span style="text-decoration: underline;">sinjaikab.go.id/webmail</span>
            <br>
            <span style="font-weight: normal; color: #555; font-size: 10px;">Hubungi admin jika ada kendala.</span>
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama</th>
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
                } elseif ($statusTte === 'EXPIRED') {
                    $color = '#dc3545'; // Red
                } elseif ($statusTte === 'NO_CERTIFICATE') {
                    $color = '#d39e00'; // Yellow
                } elseif ($statusTte === 'RENEW') {
                    $color = '#0d6efd'; // Blue
                } elseif ($statusTte === 'WAITING_FOR_VERIFICATION') {
                    $color = '#fd7e14'; // Orange
                } elseif ($statusTte === 'NEW') {
                    $color = '#6610f2'; // Indigo
                } elseif ($statusTte === 'NOT_REGISTERED') {
                    $color = '#e83e8c'; // Fuchsia
                } elseif ($statusTte === 'SUSPEND') {
                    $color = '#6f42c1'; // Purple
                } elseif ($statusTte === 'REVOKE') {
                    $color = '#6c757d'; // Gray
                } elseif ($statusTte === 'NOT SYNCED') {
                    $color = '#333'; // Dark Gray
                }

                echo '<tr>
                        <td>' . $nomor . '</td> 
                        <td><strong>' . esc(strtoupper($email['name'] ?? 'N/A')) . '</strong></td>
                        <td>' . esc($email['email'] ?? 'N/A') . '</td>
                        <td>' . esc($email['password'] ?? '') . '</td>
                        <td style="color: ' . $color . '; font-weight: bold;">' . esc($statusTte) . '</td>
                    </tr>';
                $nomor++;
            }
            ?>
        </tbody>
    </table>

    <div class="tte-description">
        <p><strong>Keterangan Status TTE</strong></p>
        <ul>
            <li><strong style="color: #198754;">ISSUE</strong> : Sertifikat Aktif / Siap TTE</li>
            <li><strong style="color: #dc3545;">EXPIRED</strong> : Masa Berlaku Habis</li>
            <li><strong style="color: #d39e00;">NO_CERTIFICATE</strong> : Belum Ada Sertifikat</li>
        </ul>
    </div>

    <div class="footer-info">
        <strong>Contact Person:</strong> 082188344982 (Dzul)
    </div>

    <div class="footer-right">
        <p>Aptika Diskominfo-SP Sinjai</p>
    </div>
</body>

</html>