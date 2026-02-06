<!DOCTYPE html>
<html>

<head>
    <title><?= esc($title ?? 'Daftar Email & TTE Pimpinan') ?></title>
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

        /* Kolom Nama / Email */
        th:nth-child(2),
        td:nth-child(2) {
            width: 30%;
        }

        /* Kolom Jabatan */
        th:nth-child(3),
        td:nth-child(3) {
            width: 20%;
        }

        /* Kolom Unit Kerja */
        <?= ($showUnitKerjaColumn ? 'th:nth-child(4), td:nth-child(4) { width: 35%; }' : '') ?>

        /* Kolom Status TTE */
        th:nth-child(<?= ($showUnitKerjaColumn ? '5' : '4') ?>),
        td:nth-child(<?= ($showUnitKerjaColumn ? '5' : '4') ?>) {
            width: 10%;
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
        <h1><?= esc($title ?? 'DAFTAR EMAIL & TTE PIMPINAN') ?></h1>
        <h2><?= esc($subtitle ?? 'PEMERINTAH KABUPATEN SINJAI') ?></h2>
        <p style="text-align: center; font-size: 10px; color: #666; margin-top: -10px;">UPDATE PER: <?= strtoupper(esc($current_date)) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama / Email</th>
                <th>Jabatan</th>
                <?= ($showUnitKerjaColumn ? '<th>Unit Kerja</th>' : '') ?>
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

                // Prepare Unit Kerja content
                $unitKerjaContent = '';
                if ($showUnitKerjaColumn) {
                    $unitKerjaContent = esc(strtoupper($email['unit_kerja_name'] ?? 'N/A'));
                }

                echo '<tr>
                        <td>' . $nomor . '</td> 
                        <td>
                            <strong>' . esc(strtoupper($email['name'] ?? 'N/A')) . '</strong><br>
                            <span style="color: #555;">' . esc($email['email'] ?? 'N/A') . '</span>
                        </td>
                        <td>' . esc($email['jabatan'] ?? '-') . '</td>
                        ' . ($showUnitKerjaColumn ? '<td>' . $unitKerjaContent . '</td>' : '') . '
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
            <li><strong style="color: #0dcaf0;">RENEW</strong> : Proses Pembaruan</li>
            <li><strong style="color: #d39e00;">NO_CERTIFICATE</strong> : Belum Ada Sertifikat</li>
        </ul>
    </div>

    <div class="footer-info">
        <strong>Contact Person:</strong> 082188344982 (Dzul)
    </div>

    <div class="footer-right">
        <p>Aptika Diskominfo Sinjai</p>
    </div>
</body>

</html>