<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Shared\Models\AuditLogModel;

class AuditCleanCommand extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'audit:clean';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Membersihkan data audit log dan berkas log sistem yang melewati masa retensi (default: 90 hari).';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'audit:clean [options]';

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--days'    => 'Jumlah hari masa retensi (default: 90 hari).',
        '--dry-run' => 'Simulasi pembersihan tanpa menghapus data atau berkas nyata.',
    ];

    /**
     * Actually run the command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $isDryRun = array_key_exists('dry-run', $params) || CLI::getOption('dry-run');
        $daysOpt  = $params['days'] ?? CLI::getOption('days');
        $days     = ($daysOpt !== null && is_numeric($daysOpt) && (int)$daysOpt > 0) ? (int)$daysOpt : 90;

        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        CLI::write('====================================================', 'yellow');
        CLI::write('   SISTEM IDENTITAS DIGITAL — RETENSI LOG SISTEM    ', 'yellow');
        CLI::write('====================================================', 'yellow');
        CLI::write("Masa Retensi : {$days} Hari (Batas: {$cutoffDate})");
        if ($isDryRun) {
            CLI::write("Mode         : [DRY-RUN / SIMULASI]", 'light_cyan');
        } else {
            CLI::write("Mode         : [LIVE / PERMANEN]", 'light_green');
        }
        CLI::newLine();

        // 1. Pembersihan Tabel Database audit_logs
        $auditModel = new AuditLogModel();
        $dbCount = $auditModel->countOldLogs($days);

        CLI::write("1. Basis Data (audit_logs):", 'cyan');
        if ($dbCount > 0) {
            if ($isDryRun) {
                CLI::write("   [SIMULASI] Ditemukan {$dbCount} baris log yang siap dibersihkan.", 'yellow');
            } else {
                $deletedRows = $auditModel->purgeOldLogs($days);
                CLI::write("   [BERHASIL] {$deletedRows} baris log lama berhasil dihapus permanen.", 'green');
            }
        } else {
            CLI::write("   [BERSIH] Tidak ada baris log yang melewati batas {$days} hari.", 'green');
        }
        CLI::newLine();

        // 2. Pembersihan Berkas Log di Filesystem (writable/logs/)
        $logsDir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
        $fileCount = 0;
        $deletedFiles = 0;
        $cutoffTimestamp = strtotime("-{$days} days");

        CLI::write("2. Berkas Log Filesystem ({$logsDir}):", 'cyan');
        if (is_dir($logsDir)) {
            $files = glob($logsDir . 'log-*.log');
            if (!empty($files)) {
                foreach ($files as $filePath) {
                    $filename = basename($filePath);
                    
                    // Ekstrak tanggal dari format log-YYYY-MM-DD.log
                    if (preg_match('/^log-(\d{4}-\d{2}-\d{2})\.log$/', $filename, $matches)) {
                        $logDate = strtotime($matches[1]);
                        if ($logDate && $logDate < $cutoffTimestamp) {
                            $fileCount++;
                            if (!$isDryRun) {
                                if (@unlink($filePath)) {
                                    $deletedFiles++;
                                }
                            }
                        }
                    } elseif (filemtime($filePath) < $cutoffTimestamp) {
                        $fileCount++;
                        if (!$isDryRun) {
                            if (@unlink($filePath)) {
                                $deletedFiles++;
                            }
                        }
                    }
                }
            }

            if ($fileCount > 0) {
                if ($isDryRun) {
                    CLI::write("   [SIMULASI] Ditemukan {$fileCount} berkas log lama yang siap dihapus.", 'yellow');
                } else {
                    CLI::write("   [BERHASIL] {$deletedFiles} dari {$fileCount} berkas log lama berhasil dihapus.", 'green');
                }
            } else {
                CLI::write("   [BERSIH] Tidak ada berkas log yang melewati batas {$days} hari.", 'green');
            }
        } else {
            CLI::write("   [LEWATI] Direktori log tidak ditemukan.", 'yellow');
        }

        CLI::newLine();
        CLI::write('====================================================', 'yellow');
        CLI::write('   PEMBERSIHAN RETENSI LOG SELESAI                  ', 'yellow');
        CLI::write('====================================================', 'yellow');
    }
}
