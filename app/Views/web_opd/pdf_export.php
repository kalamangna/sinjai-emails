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
            margin-top: 20px;
            font-size: 9px;
            text-align: left;
        }

        .footer-info p {
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
                <th style="width: 40%;">Unit Kerja (OPD)</th>
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
                    <td><strong><?= esc(strtoupper($website['nama_unit_kerja'] ?? '-')) ?></strong></td>
                    <td><?= esc($website['domain'] ?? '-') ?></td>
                    <td style="color: <?= $status_color ?>; font-weight: bold;"><?= esc(strtoupper($website['status'] ?? '-')) ?></td>
                    <td><?= esc($website['keterangan'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (isset($statusChart)): ?>
    <div class="chart-container">
        <div class="chart-box">
            <img src="<?= $statusChart ?>" alt="Status Chart">
        </div>
    </div>
    <?php endif; ?>

    <div class="footer-info">
        <p><strong>Contact Person</strong></p>
        <p style="color: #555;">082188344982 (Dzul)</p>
    </div>
</body>

</html>