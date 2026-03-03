<!DOCTYPE html>
<html>

<head>
    <title><?= esc($title) ?></title>
    <style>
        @page {
            size: portrait;
            margin: 20px 30px 40px 30px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            font-size: 10px;
            color: #334155;
            line-height: 1.4;
        }

        /* Typography */
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

        /* Table Styles */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .main-table th, .main-table td {
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

        /* Info Box */
        .info-box {
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
            padding: 12px;
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
            text-align: center;
        }

        .summary-metric {
            text-align: center;
        }

        .summary-label {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #1e293b;
        }

        /* Branding */
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

        /* Footer */
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

        .row-kelurahan {
            background-color: #f8fafc;
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
        <h1><?= esc($title) ?></h1>
        <h2><?= esc($subtitle) ?></h2>
        <p class="update-per">UPDATE PER: <?= strtoupper(esc($current_date)) ?></p>
    </div>

    <?php
    $total_web = count($websites);
    $aktif_web = 0;
    $nonaktif_web = 0;
    foreach ($websites as $w) {
        if (strtoupper($w['status'] ?? '') === 'AKTIF') $aktif_web++;
        else $nonaktif_web++;
    }
    ?>

    <div class="info-box">
        <table class="info-layout">
            <tr>
                <td style="width: 33.33%;">
                    <div class="summary-label">TOTAL WEBSITE</div>
                    <div class="summary-value"><?= $total_web ?></div>
                </td>
                <td style="width: 33.33%; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">
                    <div class="summary-label" style="color: #059669;">AKTIF</div>
                    <div class="summary-value" style="color: #059669;"><?= $aktif_web ?></div>
                </td>
                <td style="width: 33.33%;">
                    <div class="summary-label" style="color: #dc2626;">NONAKTIF</div>
                    <div class="summary-value" style="color: #dc2626;"><?= $nonaktif_web ?></div>
                </td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 20px; text-align: center;">No.</th>
                <th style="width: 150px;">Desa/Kelurahan</th>
                <th style="width: 150px;">Domain</th>
                <th style="width: 70px;">Platform</th>
                <th style="width: 50px;">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $nomor = 1;
            foreach ($websites as $website) :
                $row_class = (stripos($website['desa_kelurahan'], 'KELURAHAN') !== false) ? 'row-kelurahan' : '';
                $status_color = (strtoupper($website['status']) === 'AKTIF') ? '#059669' : '#dc2626';

                $pName = strtoupper($website['platform_name'] ?? '');
                $pColor = '#475569';
                if ($pName === 'SIDEKA-NG') $pColor = '#2563eb';
                elseif ($pName === 'OPENSID') $pColor = '#059669';
                elseif ($pName === 'PIHAK KETIGA') $pColor = '#d97706';
            ?>
                <tr class="<?= $row_class ?>">
                    <td style="text-align: center; color: #64748b;"><?= $nomor++ ?></td>
                    <td>
                        <strong><?= esc(strtoupper($website['desa_kelurahan'] ?? '')) ?></strong><br />
                        <span style="font-size: 8px; color: #64748b;"><?= esc(strtoupper($website['kecamatan'] ?? '')) ?></span>
                    </td>
                    <td style="color: #475569;">
                        <span><?= esc($website['domain'] ?? '') ?></span>
                    </td>
                    <td style="font-weight: bold; color: <?= $pColor ?>; font-size: 9px;"><?= esc($pName) ?></td>
                    <td style="color: <?= $status_color ?>; font-weight: bold; font-size: 9px;"><?= esc(strtoupper($website['status'] ?? '')) ?></td>
                    <td style="font-size: 9px; color: #64748b;"><?= esc($website['keterangan'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>
