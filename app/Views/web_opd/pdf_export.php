<!DOCTYPE html>
<html>

<head>
    <title>Data Website OPD</title>
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
            width: 40%;
            margin: 0 auto;
            display: inline-block;
            vertical-align: top;
        }

        .chart-box img {
            width: 100%;
            height: auto;
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

        <h1><?= esc($title) ?></h1>

        <h2><?= esc($subtitle) ?></h2>

        <p class="update-date">UPDATE PER: <?= strtoupper(esc($current_date)) ?></p>

    </div>



    <div style="width: 100%; margin-bottom: 20px;">
        <div style="border: 1px solid #ddd; padding: 15px;">
            <h3 style="font-size: 12px; margin-top: 0; margin-bottom: 15px; text-align: center; border-bottom: 1px solid #eee; padding-bottom: 10px;">STATUS WEBSITE</h3>

            <table style="width: 100%; border: none;">
                <tr>
                    <td style="width: 45%; border: none; text-align: center; vertical-align: middle; padding: 0;">
                        <?php if (isset($statusChart) && !empty($statusChart)): ?>
                            <img src="<?= $statusChart ?>" style="width: 180px; height: auto;">
                        <?php endif; ?>
                    </td>
                    <td style="width: 55%; border: none; vertical-align: middle; padding: 0 0 0 20px;">
                        <table style="width: 100%; font-size: 9px; margin-bottom: 0; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #f9f9f9;">
                                    <th style="border: 1px solid #ddd; padding: 5px;">Status</th>
                                                                                <th style="border: 1px solid #ddd; padding: 5px; text-align: right;">Jumlah</th>                                    <th style="border: 1px solid #ddd; padding: 5px; text-align: right;">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 5px; color: #198754; font-weight: bold;">AKTIF</td>
                                    <td style="border: 1px solid #ddd; padding: 5px; text-align: right; font-weight: bold;"><?= number_format($stats['aktif']) ?></td>
                                    <td style="border: 1px solid #ddd; padding: 5px; text-align: right;"><?= (int)$stats['aktif_percentage'] ?>%</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 5px; color: #dc3545; font-weight: bold;">NONAKTIF</td>
                                    <td style="border: 1px solid #ddd; padding: 5px; text-align: right; font-weight: bold;"><?= number_format($stats['nonaktif']) ?></td>
                                    <td style="border: 1px solid #ddd; padding: 5px; text-align: right;"><?= (int)$stats['nonaktif_percentage'] ?>%</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #f2f2f2; font-weight: bold;">
                                    <td style="border: 1px solid #ddd; padding: 5px;">TOTAL</td>
                                    <td style="border: 1px solid #ddd; padding: 5px; text-align: right;"><?= number_format($stats['total']) ?></td>
                                    <td style="border: 1px solid #ddd; padding: 5px; text-align: right;">100%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>



    <table>

        <thead>

            <tr>

                <th style="width: 3%;">No.</th>

                <th style="width: 40%;">OPD</th>

                <th style="width: 27%;">Domain</th>

                <th style="width: 10%;">Status</th>

                <th style="width: 20%;">Keterangan</th>

            </tr>

        </thead>

        <tbody>

            <?php

            $nomor = 1;

            foreach ($websites as $website) :

                $status_color = (strtoupper($website['status'] ?? '') === 'AKTIF') ? '#198754' : '#dc3545';

            ?>

                <tr>

                    <td style="text-align: center;"><?= $nomor++ ?></td>

                    <td><strong><?= esc(strtoupper($website['nama_unit_kerja'] ?? '')) ?: '-' ?></strong></td>

                    <td><?= esc($website['domain'] ?? '') ?: '-' ?></td>

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

        <p>Aptika Diskominfo Sinjai</p>

    </div>

</body>

</html>