<!DOCTYPE html>
<html>
<head>
    <title>Monthly Report - <?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header img { max-height: 60px; margin-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header h2 { margin: 5px 0 0; font-size: 14px; font-weight: normal; }
        
        .section-title { font-size: 14px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        
        .stats-grid { width: 100%; margin-bottom: 20px; }
        .stats-grid td { width: 25%; text-align: center; padding: 10px; background-color: #f8f9fa; border: 1px solid #dee2e6; }
        .stats-value { font-size: 18px; font-weight: bold; display: block; }
        .stats-label { font-size: 10px; color: #6c757d; text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #dee2e6; padding: 6px; text-align: left; }
        th { background-color: #e9ecef; }
        
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 10px; color: #aaa; }
    </style>
</head>
<body>
    <div class="header">
        <?php if (!empty($logoSrc)): ?>
            <img src="<?= $logoSrc ?>" alt="Logo">
        <?php endif; ?>
        <h1>Laporan Bulanan Akun Email</h1>
        <h2>Periode: <?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></h2>
    </div>

    <div class="section-title">Ringkasan Utama</div>
    <table class="stats-grid">
        <tr>
            <td>
                <span class="stats-value"><?= number_format($total_emails) ?></span>
                <span class="stats-label">Total Emails</span>
            </td>
            <td>
                <span class="stats-value"><?= number_format(count($new_emails)) ?></span>
                <span class="stats-label">Baru Bulan Ini</span>
            </td>
            <td>
                <span class="stats-value"><?= number_format($active_emails) ?></span>
                <span class="stats-label">Aktif</span>
            </td>
            <td>
                <span class="stats-value"><?= number_format($suspended_emails) ?></span>
                <span class="stats-label">Suspended</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Statistik Status ASN</div>
    <table>
        <thead>
            <tr>
                <th>Status ASN</th>
                <th style="width: 100px; text-align: center;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($status_asn_stats as $stat): ?>
                <tr>
                    <td><?= esc($stat['nama_status_asn'] ?: 'Unknown') ?></td>
                    <td style="text-align: center;"><?= $stat['count'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="section-title">Statistik Status TTE (BSrE)</div>
    <table>
        <thead>
            <tr>
                <th>Status BSrE</th>
                <th style="width: 100px; text-align: center;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bsre_stats as $label => $count): ?>
                <tr>
                    <td><?= esc($label) ?></td>
                    <td style="text-align: center;"><?= $count ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="section-title">Top 10 Unit Kerja</div>
    <table>
        <thead>
            <tr>
                <th>Unit Kerja</th>
                <th style="width: 100px; text-align: center;">Jumlah Email</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($unit_kerja_stats as $stat): ?>
                <tr>
                    <td><?= esc($stat['nama_unit_kerja'] ?: 'Unknown/Unassigned') ?></td>
                    <td style="text-align: center;"><?= $stat['count'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (!empty($new_emails)): ?>
        <div class="section-title">Akun Baru Bulan Ini</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">No.</th>
                    <th>Email</th>
                    <th>Nama</th>
                    <th>Tanggal Dibuat</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($new_emails as $email): ?>
                    <tr>
                        <td style="text-align: center;"><?= $no++ ?></td>
                        <td><?= esc($email['email']) ?></td>
                        <td><?= esc(strtoupper($email['name'])) ?></td>
                        <td><?= date('d-m-Y H:i', strtotime($email['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="footer">
        Generated on <?= date('d F Y H:i:s') ?> by Email Management System
    </div>
</body>
</html>
