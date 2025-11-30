<!DOCTYPE html>
<html>

<head>
    <title>Akun Email - <?= esc($unit_kerja['nama_unit_kerja']) ?></title>
    <style>
        @page {
            margin: 10px 25px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 10px;
            font-size: 10px;
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
            margin-bottom: 20px;
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
            overflow-wrap: break-word;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Kolom No. */
        th:nth-child(1),
        td:nth-child(1) {
            text-align: center;
            width: 4%;
        }

        /* Kolom Nama */
        th:nth-child(2),
        td:nth-child(2) {
            width: 15%;
        }

        /* Kolom NIK */
        th:nth-child(3),
        td:nth-child(3) {
            width: 12%;
        }

        /* Kolom NIP */
        th:nth-child(4),
        td:nth-child(4) {
            width: 12%;
        }

        /* Kolom Jenis Formasi */
        th:nth-child(5),
        td:nth-child(5) {
            width: 10%;
        }

        /* Kolom Unit Kerja */
        <?= ($showUnitKerjaColumn ? 'th:nth-child(6), td:nth-child(6) { width: 15%; }' : '') ?>

        /* Kolom Email */
        th:nth-child(<?= ($showUnitKerjaColumn ? '7' : '6') ?>),
        td:nth-child(<?= ($showUnitKerjaColumn ? '7' : '6') ?>) {
            width: 17%;
        }

        /* Kolom Password */
        th:nth-child(<?= ($showUnitKerjaColumn ? '8' : '7') ?>),
        td:nth-child(<?= ($showUnitKerjaColumn ? '8' : '7') ?>) {
            width: <?= ($showUnitKerjaColumn ? '15%' : '30%') ?>;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            color: #777;
            position: fixed;
            bottom: 10px;
            right: 20px;
            left: 20px;
            line-height: 1.2;
        }

        .instruction {
            text-align: center;
            font-weight: bold;
            font-size: 1.1em;
            margin-bottom: 15px;
            padding: 8px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
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
    </style>
</head>

<body>
    <div class="header">
        <img src="<?= $logoSrc ?>" alt="Logo" class="logo" />
        <h1>DAFTAR AKUN EMAIL</h1>
        <h2><?= esc($unit_kerja['nama_unit_kerja']) ?></h2>
    </div>

    <p class="instruction">
        Untuk AKTIVASI AKUN, login menggunakan EMAIL dan PASSWORD melalui halaman sinjaikab.go.id/webmail
    </p>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama</th>
                <th>NIK</th>
                <th>NIP</th>
                <?= ($showUnitKerjaColumn ? '<th>Unit Kerja</th>' : '') ?>
                <th>Email</th>
                <th>Password</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $nomor = 1;
            foreach ($emails as $email) {
                echo '<tr>
                        <td>' . $nomor . '</td> 
                        <td>' . esc($email['name'] ?? 'N/A') . '</td>
                        <td>' . esc($email['nik'] ?? 'N/A') . '</td>
                        <td>' . esc($email['nip'] ?? 'N/A') . '</td>
                        ' . ($showUnitKerjaColumn ? '<td>' . esc($email['unit_kerja_name'] ?? 'N/A') . '</td>' : '') . '
                        <td>' . esc($email['email'] ?? 'N/A') . '</td>
                        <td>' . esc($email['password'] ?? 'N/A') . '</td>
                    </tr>';
                $nomor++;
            }
            ?>
        </tbody>
    </table>

    <div class="footer">
        Bidang Aplikasi dan Informatika - Dinas Komunikasi Informatika dan Persandian Kabupaten Sinjai<br>
        Dibuat pada <?= date('d-m-Y H:i:s') ?>
    </div>
</body>

</html>