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
            color: #1e293b;
            text-align: center;
            font-size: 14px;
        }

        h2 {
            color: #334155;
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
            border: 1px solid #e2e8f0;
            padding: 5px;
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: break-word;
            vertical-align: top;
        }

        th {
            background-color: #f8fafc;
        }

        /* Kolom No. */
        th:nth-child(1),
        td:nth-child(1) {
            text-align: center;
            width: 5%;
        }

        /* Kolom Nama / NIP */
        th:nth-child(2),
        td:nth-child(2) {
            width: <?= ($showUnitKerjaColumn ? '25%' : '35%') ?>;
        }

        /* Kolom Email */
        th:nth-child(3),
        td:nth-child(3) {
            width: <?= ($showUnitKerjaColumn ? '20%' : '25%') ?>;
        }

        /* Kolom Unit Kerja */
        <?php if ($showUnitKerjaColumn): ?>th:nth-child(4),
        td:nth-child(4) {
            width: 25%;
        }

        <?php endif; ?>

        /* Kolom Password */
        th:nth-child(<?= ($showUnitKerjaColumn ? '5' : '4') ?>),
        td:nth-child(<?= ($showUnitKerjaColumn ? '5' : '4') ?>) {
            width: 15%;
        }

        /* Kolom Status TTE */
        th:nth-child(<?= ($showUnitKerjaColumn ? '6' : '5') ?>),
        td:nth-child(<?= ($showUnitKerjaColumn ? '6' : '5') ?>) {
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
            color: #334155;
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
            color: #334155;
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
        <p style="text-align: center; font-size: 10px; color: #334155; margin-top: -10px;">UPDATE PER: <?= strtoupper(esc($current_date)) ?></p>
        <p style="text-align: center; font-size: 11px; color: #cc0000; font-weight: bold; margin-top: 15px; margin-bottom: 20px; line-height: 1.4;">
            Untuk aktivasi akun, masukkan email dan password di halaman<span style="text-decoration: underline;">sinjaikab.go.id/webmail</span>
            <br>
            <span style="font-weight: normal; color: #334155; font-size: 10px;">Hubungi admin jika ada kendala.</span>
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama / NIP</th>
                <th>Email</th>
                <?= ($showUnitKerjaColumn ? '<th>Unit Kerja</th>' : '') ?>
                <th>Password</th>
                <th>Status TTE</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $nomor = 1;
            foreach ($emails as $email) {
                $statusTte = !empty($email['bsre_status']) ? $email['bsre_status'] : 'NOT_SYNCED';

                // Color logic
                $color = '#9ca3af'; // Default slate-700
                if ($statusTte === 'ISSUE') {
                    $color = '#047857'; // Emerald-600
                } elseif ($statusTte === 'EXPIRED') {
                    $color = '#dc2626'; // Red-600
                } elseif ($statusTte === 'NO_CERTIFICATE') {
                    $color = '#f59e0b'; // Amber-500
                } elseif ($statusTte === 'RENEW') {
                    $color = '#6ee7b7'; // Amber-500
                } elseif ($statusTte === 'WAITING_FOR_VERIFICATION') {
                    $color = '#6ee7b7'; // Amber-500
                } elseif ($statusTte === 'NEW') {
                    $color = '#10b981'; // Blue-600
                } elseif ($statusTte === 'NOT_REGISTERED') {
                    $color = '#dc2626'; // Red-600
                } elseif ($statusTte === 'SUSPEND') {
                    $color = '#dc2626'; // Red-600
                } elseif ($statusTte === 'REVOKE') {
                    $color = '#dc2626'; // Red-600
                } elseif ($statusTte === 'NOT_SYNCED') {
                    $color = '#9ca3af'; // Slate-700
                }

                // Prepare Unit Kerja content
                $unitKerjaContent = '';
                if ($showUnitKerjaColumn) {
                    if (!empty($email['parent_unit_kerja_name'])) {
                        $unitKerjaContent = esc(strtoupper($email['unit_kerja_name'] ?? '')) . '<br><small style="color: #334155; font-size: 8px;">' . esc(strtoupper($email['parent_unit_kerja_name'])) . '</small>';
                    } else {
                        $unitKerjaContent = esc(strtoupper($email['unit_kerja_name'] ?? '-'));
                    }
                }

                echo '<tr>
                        <td style="text-align: center;">' . $nomor . '</td> 
                        <td>
                            <strong>' . esc(strtoupper($email['name'] ?? 'N/A')) . '</strong><br/>
                            <small style="color: #334155; font-size: 8px;">NIP: ' . esc($email['nip'] ?: '-') . '</small>
                        </td>
                        <td>' . esc($email['email'] ?? 'N/A') . '</td>
                        ' . ($showUnitKerjaColumn ? '<td>' . $unitKerjaContent . '</td>' : '') . '
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
            <li><strong style="color: #059669;">ISSUE</strong> : Sertifikat Aktif / Siap TTE</li>
            <li><strong style="color: #dc2626;">EXPIRED</strong> : Masa Berlaku Habis</li>
            <li><strong style="color: #f59e0b;">NO_CERTIFICATE</strong> : Belum Ada Sertifikat</li>
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