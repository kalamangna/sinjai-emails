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
            background-color: #f1f5f9;
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
                $status_color = (strtoupper($website['status'] ?? '') === 'AKTIF') ? '#047857' : '#dc2626';
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
        <p>Aptika Diskominfo-SP Sinjai</p>
    </div>
</body>

</html>
