<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <title>Daftar Email & TTE - <?= esc($unit_kerja['nama_unit_kerja']) ?></title>
    <style>
        @page {
            margin: 20px 30px 40px 30px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            font-size: 10px;
            color: #334155;
            line-height: 1.4;
        }

        h1 {
            color: #0f172a;
            text-align: center;
            font-size: 16px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        h2 {
            color: #334155;
            text-align: center;
            font-size: 12px;
            margin: 5px 0 15px 0;
            text-transform: uppercase;
            font-weight: bold;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .main-table th,
        .main-table td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        .main-table th {
            background-color: #f1f5f9;
            color: #475569;
            text-transform: uppercase;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        /* Fixed Column Widths */
        .col-no {
            width: 20px;
            text-align: center;
        }

        .col-tte {
            width: 80px;
        }

        <?php if (!$showUnitKerjaColumn): ?>.col-nip {
            width: 100px;
        }

        <?php endif; ?>.info-box {
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
            padding: 10px;
            background-color: #f8fafc;
            border-radius: 6px;
        }

        .info-layout {
            width: 100%;
            border: none;
            margin: 0;
        }

        .info-layout td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            max-width: 60px;
            margin-bottom: 10px;
        }

        .update-per {
            text-align: center;
            font-size: 10px;
            color: #64748b;
            font-weight: bold;
            margin-top: -10px;
        }

        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 30px;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 5px;
        }

        .footer-content {
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="footer">
        <table class="footer-content" style="border: none;">
            <tr>
                <td style="border: none; text-align: left; padding: 0;">Contact Person: 082188344982 (Dzul)</td>
                <td style="border: none; text-align: right; padding: 0; font-weight: bold;">Aptika Diskominfo-SP Sinjai</td>
            </tr>
        </table>
    </div>

    <div class="header">
        <img src="<?= $logoSrc ?>" alt="Logo" class="logo" />
        <h1>MONITORING EMAIL & TTE</h1>
        <h2><?= esc($unit_kerja['nama_unit_kerja']) ?></h2>
        <p class="update-per">UPDATE PER: <?= strtoupper(esc($current_date)) ?></p>
    </div>

    <?php
    $totalEmail = count($emails);
    $issueCount = 0;
    $expiredCount = 0;
    foreach ($emails as $email) {
        $st = $email['bsre_status'] ?? '';
        if ($st === 'ISSUE') $issueCount++;
        elseif ($st === 'EXPIRED') $expiredCount++;
    }
    ?>

    <div class="info-box">
        <table class="info-layout">
            <tr>
                <!-- Summary Section -->
                <td style="width: 14%; text-align: center;">
                    <div style="font-size: 9px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">TOTAL EMAIL</div>
                    <div style="font-size: 18px; font-weight: bold; color: #1e293b; text-align: center;"><?= $totalEmail ?></div>
                </td>
                <td style="width: 18%; border-left: 1px solid #e2e8f0; text-align: center;">
                    <div style="font-size: 9px; font-weight: bold; color: #059669; text-transform: uppercase; margin-bottom: 4px;">TTE AKTIF</div>
                    <div style="font-size: 18px; font-weight: bold; color: #059669; text-align: center;"><?= $issueCount ?> <span style="font-size: 10px; color: #64748b; font-weight: normal;">(<?= $totalEmail > 0 ? round(($issueCount / $totalEmail) * 100) : 0 ?>%)</span></div>
                </td>
                <td style="width: 18%; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; text-align: center;">
                    <div style="font-size: 9px; font-weight: bold; color: #dc2626; text-transform: uppercase; margin-bottom: 4px;">TTE EXPIRED</div>
                    <div style="font-size: 18px; font-weight: bold; color: #dc2626; text-align: center;"><?= $expiredCount ?> <span style="font-size: 10px; color: #64748b; font-weight: normal;">(<?= $totalEmail > 0 ? round(($expiredCount / $totalEmail) * 100) : 0 ?>%)</span></div>
                </td>

                <!-- Legend Section -->
                <td style="width: 50%; padding-left: 15px; text-align: left; vertical-align: middle;">
                    <div style="font-size: 9px; font-weight: bold; color: #475569; margin-bottom: 4px; text-transform: uppercase;">Keterangan Status TTE</div>
                    <table style="border: none; margin: 0; font-size: 9px; width: 100%;">
                        <tr>
                            <td style="border: none; padding: 1px 0; width: 85px;"><strong style="color: #059669;">ISSUE</strong></td>
                            <td style="border: none; padding: 1px 0;">: Sertifikat aktif, siap digunakan untuk TTE</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 1px 0;"><strong style="color: #dc2626;">EXPIRED</strong></td>
                            <td style="border: none; padding: 1px 0;">: Sertifikat kedaluwarsa, hubungi admin untuk pembaruan</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 1px 0;"><strong style="color: #f59e0b;">NO_CERTIFICATE</strong></td>
                            <td style="border: none; padding: 1px 0;">: Belum memiliki sertifikat, cek email untuk aktivasi</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 20px; text-align: center;">No.</th>
                <th class="col-nama">Nama <?= ($showUnitKerjaColumn ? '/ NIP' : '') ?></th>
                <?php if (!$showUnitKerjaColumn): ?>
                    <th style="width: 100px;">NIP</th>
                <?php endif; ?>
                <th class="col-email">Email</th>
                <?php if ($showUnitKerjaColumn): ?>
                    <th class="col-unit">Unit Kerja</th>
                <?php endif; ?>
                <th style="width: 80px;">Status TTE</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $nomor = 1;
            foreach ($emails as $email):
                $statusTte = !empty($email['bsre_status']) ? $email['bsre_status'] : 'NOT_SYNCED';

                // Color logic
                $color = '#94a3b8';
                if ($statusTte === 'ISSUE') $color = '#059669';
                elseif (in_array($statusTte, ['EXPIRED', 'NOT_REGISTERED', 'SUSPEND', 'REVOKE'])) $color = '#dc2626';
                elseif (in_array($statusTte, ['NO_CERTIFICATE', 'RENEW', 'WAITING_FOR_VERIFICATION'])) $color = '#d97706';
                elseif ($statusTte === 'NEW') $color = '#0d9488';

                $unitKerjaContent = '';
                if ($showUnitKerjaColumn) {
                    if (!empty($email['parent_unit_kerja_name'])) {
                        $unitKerjaContent = esc(strtoupper($email['unit_kerja_name'] ?? '')) . '<br><small style="color: #64748b; font-size: 8px;">' . esc(strtoupper($email['parent_unit_kerja_name'])) . '</small>';
                    } else {
                        $unitKerjaContent = esc(strtoupper($email['unit_kerja_name'] ?? ''));
                    }
                }
            ?>
                <tr>
                    <td style="text-align: center; color: #64748b;"><?= $nomor++ ?></td>
                    <td>
                        <strong><?= esc(strtoupper($email['name'] ?? '')) ?></strong>
                        <?php if ($showUnitKerjaColumn && !empty($email['nip'])): ?>
                            <br><small style="color: #64748b; font-size: 8px;"><?= esc($email['nip']) ?></small>
                        <?php endif; ?>
                    </td>
                    <?php if (!$showUnitKerjaColumn): ?>
                        <td><?= esc($email['nip'] ?: '') ?></td>
                    <?php endif; ?>
                    <td style="color: #475569;"><span><?= esc($email['email'] ?? '') ?></span></td>
                    <?php if ($showUnitKerjaColumn): ?>
                        <td><?= $unitKerjaContent ?></td>
                    <?php endif; ?>
                    <td style="color: <?= $color ?>; font-weight: bold; font-size: 9px;"><?= esc($statusTte) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>