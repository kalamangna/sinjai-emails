<!DOCTYPE html>
<html>

<head>
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
            vertical-align: top;
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
    foreach ($emails as $email) {
        if (($email['bsre_status'] ?? '') === 'ISSUE') $issueCount++;
    }
    ?>

    <div class="info-box">
        <table class="info-layout">
            <tr>
                <!-- Summary Section -->
                <td style="width: 35%;">
                    <div style="font-size: 9px; font-weight: bold; color: #475569; margin-bottom: 4px; text-transform: uppercase;">Ringkasan Data</div>
                    <table style="border: none; margin: 0; font-size: 10px;">
                        <tr>
                            <td style="border: none; padding: 0; width: 80px; color: #64748b;">TOTAL EMAIL</td>
                            <td style="border: none; padding: 0; color: #1e293b;">: <strong><?= $totalEmail ?></strong></td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 0; color: #059669;">TTE AKTIF</td>
                            <td style="border: none; padding: 0; color: #059669;">: <strong><?= $issueCount ?></strong></td>
                        </tr>
                    </table>
                </td>

                <!-- Legend Section -->
                <td style="width: 65%; border-left: 1px solid #e2e8f0; padding-left: 15px;">
                    <div style="font-size: 9px; font-weight: bold; color: #475569; margin-bottom: 4px; text-transform: uppercase;">Keterangan Status TTE</div>
                    <div style="font-size: 9px;"><strong style="color: #059669; display: inline-block; width: 85px;">ISSUE</strong> : Sertifikat aktif & siap digunakan untuk TTE</div>
                    <div style="font-size: 9px;"><strong style="color: #dc2626; display: inline-block; width: 85px;">EXPIRED</strong> : Sertifikat kedaluwarsa, hubungi admin untuk pembaruan</div>
                    <div style="font-size: 9px;"><strong style="color: #f59e0b; display: inline-block; width: 85px;">NO_CERTIFICATE</strong> : Belum memiliki sertifikat, cek email untuk aktivasi</div>
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