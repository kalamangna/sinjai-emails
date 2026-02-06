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
            color: #333;
            text-align: center;
            font-size: 14px;
        }

        h2 {
            color: #555;
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
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
            word-wrap: break-word;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
        }

        .stats-container {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            /* Reverted margin for better spacing after removing platform stats */
            border: 1px solid #ddd;
            border-collapse: collapse;
        }

        .stats-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 8px 0;
            border-right: 1px solid #ddd;
        }

        .stats-box:last-child {
            border-right: none;
        }

        .stats-box h3 {
            margin: 0;
            font-size: 10px;
            color: #777;
            text-transform: uppercase;
        }

        .stats-box p {
            margin: 5px 0 0 0;
            font-size: 14px;
            font-weight: bold;
            color: #333;
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
            color: #0d6efd;
            font-weight: bold;
        }

        /* Bootstrap Primary */
        .platform-opensid-text {
            color: #0dcaf0;
            font-weight: bold;
        }

        /* Bootstrap Info */
        .platform-pihak-ketiga-text {
            color: #ffc107;
            font-weight: bold;
        }

        /* Bootstrap Warning */
        .platform-default-text {
            color: #6c757d;
            font-weight: bold;
        }

        /* Bootstrap Secondary */


        .row-desa {
            background-color: #e6ffe6;
        }

        /* Light green for Desa */
        .row-kelurahan {
            background-color: #e6f7ff;
        }

        /* Light blue for Kelurahan */

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

                <h1><?= esc($title) ?></h1>

                <h2><?= esc($subtitle) ?></h2>

                <p class="update-date">UPDATE PER: <?= strtoupper(esc($current_date)) ?></p>

            </div>

        

            <div class="stats-container">

                <div class="stats-box">

                    <h3>Total Website</h3>

                    <p><?= esc($stats['total'] ?? '-') ?></p>

                </div>

                <div class="stats-box">

                    <h3>Aktif</h3>

                    <p><?= esc($stats['aktif'] ?? '-') ?> <small>(<?= esc($stats['aktif_percentage'] ?? '-') ?>%)</small></p>

                </div>

                <div class="stats-box">

                    <h3>Nonaktif</h3>

                    <p><?= esc($stats['nonaktif'] ?? '-') ?> <small>(<?= esc($stats['nonaktif_percentage'] ?? '-') ?>%)</small></p>

                </div>

            </div>

        

            <table>

                <thead>

                    <tr>

                        <th style="width: 3%;">No.</th>

                        <th style="width: 20%;">Desa/Kelurahan</th>

                        <th style="width: 15%;">Kecamatan</th>

                        <th style="width: 22%;">Domain</th>

                        <th style="width: 15%;">Platform</th>

                        <th style="width: 10%;">Status</th>

                        <th style="width: 15%;">Keterangan</th>

                    </tr>

                </thead>

                <tbody>

                    <?php

                    $nomor = 1;

                    foreach ($websites as $website) :

                        $row_class = '';

                        if (stripos($website['desa_kelurahan'], 'DESA') !== false) {

                            $row_class = 'row-desa';

                        } elseif (stripos($website['desa_kelurahan'], 'KELURAHAN') !== false) {

                            $row_class = 'row-kelurahan';

                        }

        

                        $status_color = (strtoupper($website['status']) === 'AKTIF') ? '#198754' : '#dc3545';

        

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

                            <td><?= esc(strtoupper($website['desa_kelurahan'] ?? '-')) ?></td>

                            <td><?= esc(strtoupper($website['kecamatan'] ?? '-')) ?></td>

                            <td><?= esc($website['domain'] ?? '-') ?></td>

                            <td class="<?= $platform_text_class ?>"><?= esc(strtoupper($website['platform_name'] ?? '-')) ?></td>

                            <td style="color: <?= $status_color ?>; font-weight: bold;"><?= esc(strtoupper($website['status'] ?? '-')) ?></td>

                            <td><?= esc($website['keterangan'] ?? '-') ?></td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        

            <?php if (isset($statusChart) && isset($platformChart)): ?>

            <div class="chart-container">

                <div class="chart-box">

                    <img src="<?= $statusChart ?>" alt="Status Chart">

                </div>

                <div class="chart-box" style="margin-left: 3%;">

                    <img src="<?= $platformChart ?>" alt="Platform Chart">

                </div>

            </div>

            <?php endif; ?>

        

                <div class="footer-info">

        

                    <strong>Contact Person:</strong> 082188344982 (Dzul)

        

                </div>

        

            <div class="footer-right">

                <p>Aptika Diskominfo Sinjai</p>

            </div>

        </body>

</html>