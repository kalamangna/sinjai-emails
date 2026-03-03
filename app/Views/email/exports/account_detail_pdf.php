<!DOCTYPE html>
<html>

<head>
    <title>Daftar Akun Email & TTE - <?= esc($unit_kerja['nama_unit_kerja']) ?></title>
    <style>
        @page {
            size: landscape;
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
            padding: 6px 4px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        .main-table th {
            background-color: #f1f5f9;
            color: #475569;
            text-transform: uppercase;
            font-size: 8px;
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

        .info-box {
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

        .activation-text {
            font-size: 13px;
            color: #b91c1c;
            font-weight: bold;
            margin: 0;
        }

        .activation-link {
            display: inline-block;
            margin-top: 5px;
            font-size: 16px;
            color: #b91c1c;
            text-decoration: underline;
        }

        .activation-sub {
            font-weight: normal;
            color: #64748b;
            font-size: 11px;
            margin-top: 5px;
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
        <h1>DAFTAR AKUN EMAIL & TTE</h1>
        <h2><?= esc($unit_kerja['nama_unit_kerja']) ?></h2>
        <p class="update-per">UPDATE PER: <?= strtoupper(esc($current_date)) ?></p>
    </div>

    <div class="info-box">
        <table class="info-layout">
            <tr>
                <!-- Activation Section -->
                <td style="width: 55%; vertical-align: middle;">
                    <p class="activation-text">Untuk aktivasi akun, silakan akses:</p>
                    <a href="https://sinjaikab.go.id/webmail" class="activation-link">sinjaikab.go.id/webmail</a>
                    <p class="activation-sub">Masukkan email dan password. Hubungi admin jika ada kendala.</p>
                </td>

                <!-- Legend Section -->
                <td style="width: 45%; padding-left: 10px; border-left: 1px solid #e2e8f0; vertical-align: top;">
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
                <th>Nama</th>
                <th>NIP</th>
                <th>NIK</th>
                <th>Email</th>
                <th>Password</th>
                <?php if ($showUnitKerjaColumn): ?>
                    <th>Unit Kerja</th>
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
                    <td><strong><?= esc(strtoupper($email['name'] ?? '')) ?></strong></td>
                    <td><?= esc($email['nip'] ?: '') ?></td>
                    <td><?= esc($email['nik'] ?: '') ?></td>
                    <td style="color: #475569;"><span><?= esc($email['email'] ?? '') ?></span></td>
                    <td style="font-family: monospace; font-size: 9px;"><?= esc($email['password'] ?? '') ?></td>
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