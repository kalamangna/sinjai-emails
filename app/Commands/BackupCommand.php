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

        $telegram = new TelegramLibrary();

        try {
            $dumpSettings = [
                'compress' => \Ifsnop\Mysqldump\Mysqldump::GZIP,
                'no-data' => false,
                'add-drop-table' => true,
                'single-transaction' => true,
                'lock-tables' => false,
                'add-locks' => true,
                'extended-insert' => true,
                'disable-keys' => true,
                'skip-definer' => true,
                'skip-comments' => false,
                'skip-tz-utc' => false,
            ];

            $dsn = "mysql:host={$hostname};port={$port};dbname={$database}";
            $dump = new \Ifsnop\Mysqldump\Mysqldump($dsn, $username, $password, $dumpSettings);
            $dump->start($filepath);

            $size = filesize($filepath);
            $sizeFormatted = number_format($size / 1024 / 1024, 2) . ' MB';

            CLI::write("Backup berhasil disimpan di: {$filepath} ({$sizeFormatted})", 'green');

            // Hapus backup yang lebih tua dari 7 hari
            $this->cleanOldBackups($backupDir, 7);

            $msg = "✅ <b>BACKUP DATABASE BERHASIL</b>\n";
            $msg .= "----------------------------------------\n";
            $msg .= "<b>Waktu:</b> " . date('d/m/Y H:i:s') . "\n";
            $msg .= "<b>File:</b> {$filename}\n";
            $msg .= "<b>Ukuran:</b> {$sizeFormatted}\n";
            $msg .= "<b>Status:</b> Auto Backup (cPanel Compatible)\n";
            $msg .= "----------------------------------------";
            
            $telegram->sendMessage($msg);

        } catch (\Exception $e) {
            CLI::error("Backup gagal! Error: " . $e->getMessage());
            
            $msg = "🚨 <b>GAGAL BACKUP DATABASE</b>\n";
            $msg .= "----------------------------------------\n";
            $msg .= "<b>Waktu:</b> " . date('d/m/Y H:i:s') . "\n";
            $msg .= "<b>Error:</b> " . htmlspecialchars($e->getMessage()) . "\n";
            $msg .= "----------------------------------------";
            
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
