<!DOCTYPE html>
<html>

<head>
    <title><?= esc($title) ?></title>
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
            margin-bottom: 5px;
        }

        h2 {
            color: #555;
            text-align: center;
            font-size: 12px;
            margin-top: 0;
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
            margin-top: -5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="<?= $logoSrc ?>" alt="Logo" class="logo" />
        <h1><?= esc($title) ?></h1>
        <h2><?= esc($subtitle) ?></h2>
        <p class="update-date">Dicetak: <?= strtoupper(esc($current_date)) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 12%">Tanggal</th>
                <th style="width: 25%">Unit Kerja</th>
                <th style="width: 15%">Kategori</th>
                <th style="width: 10%">Metode</th>
                <th style="width: 20%">Layanan</th>
                <th style="width: 13%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $nomor = 1;
            foreach ($activities as $item):
            ?>
                <tr>
                    <td style="text-align: center;"><?= $nomor++ ?></td>
                    <td><?= date('d-m-Y', strtotime($item['tanggal_kegiatan'])) ?></td>
                    <td>
                        <strong><?= esc($item['agency_name']) ?></strong><br>
                        <small style="color: #666;"><?= esc($item['agency_type']) ?></small>
                    </td>
                    <td><?= esc($categoryMap[$item['category']] ?? 'Unknown') ?></td>
                    <td><?= esc($item['method']) ?></td>
                    <td>
                        <?php
                        $services = json_decode($item['services'], true);
                        if (!empty($services)) {
                            echo '<ul style="margin: 0; padding-left: 15px;">';
                            foreach ($services as $svc) {
                                echo '<li>' . esc($svc) . '</li>';
                            }
                            echo '</ul>';
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td><?= esc($item['keterangan']) ?></td>
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