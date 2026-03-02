<!DOCTYPE html>
<html>

<head>
    <title>Data Website Desa & Kelurahan</title>
    <style>
        @page {
            margin: 10px 25px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 10px;
            font-size: 10px;
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

        h1 {
            color: #1e293b;
            text-align: center;
            font-size: 14px;
        }

        h2 {
            color: #64748b;
            text-align: center;
            font-size: 12px;
            margin-top: -10px;
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
            vertical-align: top;
        }

        th {
            background-color: #f8fafc;
        }

        .stats-container {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
            border-collapse: collapse;
        }

        .stats-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 8px 0;
            border-right: 1px solid #e2e8f0;
        }

        .stats-box:last-child {
            border-right: none;
        }

        .stats-box h3 {
            margin: 0;
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
        }

        .stats-box p {
            margin: 5px 0 0 0;
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
        }

        .chart-container {
            width: 100%;
            margin-bottom: 20px;
            text-align: center;
        }

        .chart-box {
            width: 30%;
            display: inline-block;
            vertical-align: top;
        }

        .chart-box img {
            width: 100%;
            height: auto;
        }

        /* Platform column text colors */
        .platform-sideka-ng-text {
            color: #2563eb;
            font-weight: bold;
        }

        .platform-opensid-text {
            color: #059669;
            font-weight: bold;
        }

        .platform-pihak-ketiga-text {
            color: #f59e0b;
            font-weight: bold;
        }

        .platform-default-text {
            color: #475569;
            font-weight: bold;
        }

        .row-kelurahan {
            background-color: #f1f5f9;
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
            color: #64748b;
        }

        .footer-info p,
        .footer-right p {
            margin: 2px 0;
        }

        .update-date {
            text-align: center;
            font-size: 10px;
            color: #475569;
            margin-top: 5px;
        }
    </style>

</head>



<body>

    <div class="header">

        <img src="<?= $logoSrc ?>" alt="Logo" class="logo" />

        <h1><?= esc($title) ?></h1>

        <h2><?= esc($subtitle) ?></h2>

        <p class="update-date">UPDATE PER: <?= strtoupper(esc($current_date)) ?></p>

    </div>

    <?php
    $total_web = count($websites);
    $aktif_web = 0;
    $nonaktif_web = 0;
    foreach ($websites as $w) {
        if (strtoupper($w['status'] ?? '') === 'AKTIF') {
            $aktif_web++;
        } else {
            $nonaktif_web++;
        }
    }
    ?>

    <div class="stats-container">
        <div class="stats-box">
            <h3>Total Website</h3>
            <p><?= $total_web ?></p>
        </div>
        <div class="stats-box">
            <h3>Status Aktif</h3>
            <p style="color: #047857;"><?= $aktif_web ?></p>
        </div>
        <div class="stats-box">
            <h3>Status Nonaktif</h3>
            <p style="color: #dc2626;"><?= $nonaktif_web ?></p>
        </div>
    </div>

    <table>

        <thead>

            <tr>

                <th style="width: 3%;">No.</th>

                <th style="width: 25%;">Desa/Kelurahan</th>

                <th style="width: 22%;">Domain</th>

                <th style="width: 12%;">Platform</th>

                <th style="width: 10%;">Status</th>

                <th style="width: 28%;">Keterangan</th>

            </tr>

        </thead>

        <tbody>

            <?php

            $nomor = 1;

            foreach ($websites as $website) :

                $row_class = (stripos($website['desa_kelurahan'], 'KELURAHAN') !== false) ? 'row-kelurahan' : '';

                $status_color = (strtoupper($website['status']) === 'AKTIF') ? '#047857' : '#dc2626';



                $platform_name_slug = strtolower(str_replace(' ', '-', $website['platform_name']));

                $platform_text_class = 'platform-default-text';

                if ($platform_name_slug === 'sideka-ng') {

                    $platform_text_class = 'platform-sideka-ng-text';
                } elseif ($platform_name_slug === 'opensid') {

                    $platform_text_class = 'platform-opensid-text';
                } elseif ($platform_name_slug === 'pihak-ketiga') {

                    $platform_text_class = 'platform-pihak-ketiga-text';
                }

            ?>

                <tr class="<?= $row_class ?>">

                    <td style="text-align: center;"><?= $nomor++ ?></td>

                    <td>
                        <strong><?= esc(strtoupper($website['desa_kelurahan'] ?? '')) ?: '-' ?></strong><br />
                        <span style="font-size: 8px; color: #64748b;"><?= esc(strtoupper($website['kecamatan'] ?? '')) ?: '-' ?></span>
                    </td>

                    <td>
                        <?php if (!empty($website['domain'])) :
                            $url = $website['domain'];
                            if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                                $url = "https://" . $url;
                            }
                        ?>
                            <a href="<?= esc($url) ?>"><?= esc($website['domain']) ?></a>
                        <?php else : ?>
                            -
                        <?php endif; ?>
                    </td>

                    <td class="<?= $platform_text_class ?>"><?= esc(strtoupper($website['platform_name'] ?? '')) ?: '-' ?></td>

                    <td style="color: <?= $status_color ?>; font-weight: bold;"><?= esc(strtoupper($website['status'] ?? '')) ?: '-' ?></td>

                    <td><?= esc($website['keterangan'] ?? '') ?: '-' ?></td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

    <div class="footer-info">


        <strong>Contact Person:</strong> 082188344982 (Dzul)



    </div>



    <div class="footer-right">

        <p>Aptika Diskominfo-SP Sinjai</p>

    </div>

</body>

</html>