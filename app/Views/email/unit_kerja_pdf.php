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
            margin-bottom: 20px;
            font-size: 14px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
        }
        th, td { 
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
        th:nth-child(1), td:nth-child(1) { 
            text-align: center;
            width: 5%;
        } 
        
        /* Kolom NIK/NIP */
        th:nth-child(2), td:nth-child(2) { width: 15%; }
        
        /* Kolom Nama */
        th:nth-child(3), td:nth-child(3) { width: 20%; } 
        
        <?php if ($show_unit_kerja_column): ?>
        /* Kolom Unit Kerja */
        th:nth-child(4), td:nth-child(4) { width: 20%; }
        <?php endif; ?>
        
        /* Kolom Email */
        th:nth-child(<?= $show_unit_kerja_column ? 5 : 4 ?>), td:nth-child(<?= $show_unit_kerja_column ? 5 : 4 ?>) { width: 20%; }
        
        /* Kolom Password */
        th:nth-child(<?= $show_unit_kerja_column ? 6 : 5 ?>), td:nth-child(<?= $show_unit_kerja_column ? 6 : 5 ?>) { width: 15%; }

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
        <img src="<?= $logo_src ?>" alt="Logo" class="logo"/>
        <h1>Daftar Akun Email<br><?= esc($unit_kerja['nama_unit_kerja']) ?></h1>
    </div>

    <p class="instruction">
        Untuk AKTIVASI AKUN, login menggunakan EMAIL dan PASSWORD melalui halaman sinjaikab.go.id/webmail
    </p>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>NIK/NIP</th>
                <th>Nama</th>
                <?php if ($show_unit_kerja_column): ?>
                <th>Unit Kerja</th>
                <?php endif; ?>
                <th>Email</th>
                <th>Password</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $nomor = 1;
            foreach ($emails as $email):
            ?>
                <tr>
                    <td><?= $nomor ?></td> 
                    <td><?= esc($email['nik_nip'] ?? 'N/A') ?></td>
                    <td><?= esc($email['name'] ?? 'N/A') ?></td>
                    <?php if ($show_unit_kerja_column): ?>
                    <td><?= esc($email['unit_kerja_name'] ?? 'N/A') ?></td>
                    <?php endif; ?>
                    <td><?= esc($email['email'] ?? 'N/A') ?></td>
                    <td><?= esc($email['password'] ?? 'N/A') ?></td>
                </tr>
            <?php
                $nomor++;
            endforeach;
            ?>
        </tbody>
    </table>
    
    <div class="footer">
        Bidang Aplikasi dan Informatika - Dinas Komunikasi Informatika dan Persandian Kabupaten Sinjai<br>
        Dibuat pada <?= date('d-m-Y H:i:s') ?>
    </div>
</body>
</html>
