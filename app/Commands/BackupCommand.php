<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Shared\Libraries\TelegramLibrary;

class BackupCommand extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'app:backup';
    protected $description = 'Melakukan backup database otomatis dan menghapus backup lama.';

    public function run(array $params)
    {
        CLI::write('Memulai proses backup otomatis...', 'yellow');

        // Folder backup
        $backupDir = WRITEPATH . 'backups/';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Konfigurasi Database
        $db = \Config\Database::connect();
        $hostname = $db->hostname;
        $username = $db->username;
        $password = $db->password;
        $database = $db->database;
        $port = $db->port ?? 3306;

        // Nama file backup
        $date = date('Y-m-d_H-i-s');
        $filename = "db_backup_{$date}.sql.gz";
        $filepath = $backupDir . $filename;

        // Coba deteksi lokasi mysqldump (cPanel biasanya di /usr/bin/mysqldump)
        $mysqldump = file_exists('/usr/bin/mysqldump') ? '/usr/bin/mysqldump' : 'mysqldump';
        
        // Perintah mysqldump
        $passwordArg = empty($password) ? '' : "-p" . escapeshellarg($password);
        $command = escapeshellcmd($mysqldump) . " --no-tablespaces -h " . escapeshellarg($hostname) . " -P " . escapeshellarg($port) . " -u " . escapeshellarg($username) . " {$passwordArg} " . escapeshellarg($database) . " > " . escapeshellarg($filepath . '.tmp') . " 2>/dev/null";

        // Eksekusi mysqldump
        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        $telegram = new TelegramLibrary();

        if ($returnVar === 0 && file_exists($filepath . '.tmp')) {
            // Kompresi dengan gzip
            $gzip = file_exists('/usr/bin/gzip') ? '/usr/bin/gzip' : 'gzip';
            exec(escapeshellcmd($gzip) . " -c " . escapeshellarg($filepath . '.tmp') . " > " . escapeshellarg($filepath), $output, $zipReturn);
            unlink($filepath . '.tmp');

            if ($zipReturn !== 0 || !file_exists($filepath)) {
                CLI::error("Gagal melakukan kompresi backup!");
                return;
            }

            $filesize = filesize($filepath);
            $sizeFormatted = number_format($filesize / 1024 / 1024, 2) . ' MB';
            CLI::write("Backup berhasil disimpan di: {$filepath} ({$sizeFormatted})", 'green');

            // Hapus backup yang lebih tua dari 7 hari
            $this->cleanOldBackups($backupDir, 7);

            $msg = "✅ <b>BACKUP DATABASE BERHASIL</b>\n";
            $msg .= "File: <code>{$filename}</code>\n";
            $msg .= "Ukuran: <b>{$sizeFormatted}</b>\n";
            $msg .= "Waktu: " . date('d M Y, H:i:s');
            
            // Opsional: Jika ingin selalu lapor sukses, hilangkan komentar di bawah
            // $telegram->sendMessage($msg);
        } else {
            CLI::error("Backup gagal! Return Code: {$returnVar}");
            
            $msg = "🚨 <b>GAGAL BACKUP DATABASE</b>\n";
            $msg .= "Proses eksekusi <i>mysqldump</i> gagal pada " . date('d M Y, H:i:s') . ".\n";
            $msg .= "Mohon segera periksa server cPanel Anda!";
            $telegram->sendMessage($msg);
        }
    }

    private function cleanOldBackups($dir, $days)
    {
        $files = glob($dir . 'db_backup_*.sql.gz');
        $now = time();
        $deletedCount = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * $days) {
                    unlink($file);
                    $deletedCount++;
                }
            }
        }

        if ($deletedCount > 0) {
            CLI::write("Berhasil menghapus {$deletedCount} file backup lama (lebih dari {$days} hari).", 'cyan');
        }
    }
}
