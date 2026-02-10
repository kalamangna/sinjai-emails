<!DOCTYPE html>
<html>

<head>
    <title>Daftar Email & TTE - <?= esc($unit_kerja['nama_unit_kerja']) ?></title>
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
            width: <?= ($showUnitKerjaColumn ? '30%' : '45%') ?>;
        }

        /* Kolom Email */
        th:nth-child(3),
        td:nth-child(3) {
            width: <?= ($showUnitKerjaColumn ? '25%' : '40%') ?>;
        }

        /* Kolom Unit Kerja */
        <?php if ($showUnitKerjaColumn): ?>th:nth-child(4),
        td:nth-child(4) {
            width: 30%;
        }

        <?php endif; ?>

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

        .update-date {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-top: -10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="<?= $logoSrc ?>" alt="Logo" class="logo" />
        <h1>DAFTAR EMAIL & TTE</h1>
        <h2><?= esc($unit_kerja['nama_unit_kerja']) ?></h2>
        <p class="update-date">UPDATE PER: <?= strtoupper(esc($current_date)) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama</th>
                <th>Email</th>
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
                    if (!empty($email['parent_unit_kerja_name'])) {
                        $unitKerjaContent = esc(strtoupper($email['parent_unit_kerja_name'])) . '<br><small style="color: #666;">' . esc(strtoupper($email['unit_kerja_name'] ?? '')) . '</small>';
                    } else {
                        $unitKerjaContent = esc(strtoupper($email['unit_kerja_name'] ?? 'N/A'));
                    }
                }

                echo '<tr>
                        <td>' . $nomor . '</td> 
                        <td><strong>' . esc(strtoupper($email['name'] ?? 'N/A')) . '</strong></td>
                        <td>' . esc($email['email'] ?? 'N/A') . '</td>
                        ' . ($showUnitKerjaColumn ? '<td>' . $unitKerjaContent . '</td>' : '') . '
                        <td style="color: ' . $color . '; font-weight: bold;">' . esc($statusTte) . '</td>
                    </tr>';
                $nomor++;
            }
            ?>
        </tbody>
    </table>

    <?php
    // Calculate Status Counts
    $statusCounts = [];
    $totalEmails = count($emails);
    
    // Status Labels Map
    $statusLabels = [
        'ISSUE' => 'Sertifikat Aktif / Siap TTE',
        'EXPIRED' => 'Masa Berlaku Habis',
        'RENEW' => 'Proses Pembaruan',
        'WAITING_FOR_VERIFICATION' => 'Menunggu Verifikasi',
        'NEW' => 'Belum Aktivasi',
        'NO_CERTIFICATE' => 'Belum Ada Sertifikat',
        'NOT_REGISTERED' => 'Pengguna Tidak Terdaftar',
        'SUSPEND' => 'Akun Ditangguhkan',
        'REVOKE' => 'Sertifikat Dicabut',
        'NOT SYNCED' => 'Belum Sinkronisasi',
        'UNKNOWN' => 'Status Tidak Diketahui'
    ];

    foreach ($emails as $email) {
        $status = !empty($email['bsre_status']) ? $email['bsre_status'] : 'NOT SYNCED';
        if (!isset($statusCounts[$status])) {
            $statusCounts[$status] = 0;
        }
        $statusCounts[$status]++;
    }
    
    // Sort: Move ISSUE to the top
    uksort($statusCounts, function ($a, $b) {
        if ($a === 'ISSUE') return -1;
        if ($b === 'ISSUE') return 1;
        return strcmp($a, $b);
    });
    ?>

    <div style="width: 100%; margin-bottom: 20px; page-break-inside: avoid; margin-top: 20px;">
        <div style="border: 1px solid #ddd; padding: 15px;">
            <h3 style="font-size: 12px; margin-top: 0; margin-bottom: 15px; text-align: center; border-bottom: 1px solid #eee; padding-bottom: 10px;">RINGKASAN STATUS TTE</h3>
            
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="width: 35%; border: none; text-align: center; vertical-align: middle; padding: 0;">
                        <?php if (isset($statusChart) && !empty($statusChart)): ?>
                            <img src="<?= $statusChart ?>" style="width: 180px; height: auto;">
                        <?php endif; ?>
                    </td>
                    <td style="width: 65%; border: none; vertical-align: middle; padding: 0 0 0 20px;">
                        <table style="width: 100%; font-size: 9px; margin-bottom: 0; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #f9f9f9;">
                                    <th style="border: 1px solid #ddd; padding: 5px; width: 70%;">Status</th>
                                    <th style="border: 1px solid #ddd; padding: 5px; text-align: right; width: 15%;">Jumlah</th>
                                    <th style="border: 1px solid #ddd; padding: 5px; text-align: right; width: 15%;">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($statusCounts as $status => $count): ?>
                                    <?php
                                        $color = '#000';
                                        if ($status === 'ISSUE') $color = '#198754';
                                        elseif (in_array($status, ['EXPIRED', 'REVOKE', 'SUSPEND'])) $color = '#dc3545';
                                        elseif (in_array($status, ['RENEW', 'WAITING_FOR_VERIFICATION', 'NEW'])) $color = '#0dcaf0';
                                        elseif (in_array($status, ['NO_CERTIFICATE', 'NOT_REGISTERED', 'NOT SYNCED'])) $color = '#d39e00';
                                        
                                        $label = $statusLabels[$status] ?? $status;
                                    ?>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 5px; color: <?= $color ?>; font-weight: bold;">
                                            <?= esc($status) ?> <span style="color: #666; font-weight: normal; font-size: 8px;">- <?= esc($label) ?></span>
                                        </td>
                                        <td style="border: 1px solid #ddd; padding: 5px; text-align: right; font-weight: bold;"><?= $count ?></td>
                                        <td style="border: 1px solid #ddd; padding: 5px; text-align: right;"><?= (int)(($count / $totalEmails) * 100) ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #f2f2f2; font-weight: bold;">
                                    <td style="border: 1px solid #ddd; padding: 5px;">TOTAL</td>
                                    <td style="border: 1px solid #ddd; padding: 5px; text-align: right;"><?= $totalEmails ?></td>
                                    <td style="border: 1px solid #ddd; padding: 5px; text-align: right;">100%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer-info">
        <strong>Contact Person:</strong> 082188344982 (Dzul)
    </div>

    <div class="footer-right">
        <p>Aptika Diskominfo Sinjai</p>
    </div>
</body>

</html>