<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Shared\Models\ExportHistoryModel;

class CleanExportHistories extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'queue:clean-exports';
    protected $description = 'Clean up old export histories and delete their physical PDF files (older than 3 days).';

    public function run(array $params)
    {
        $historyModel = new ExportHistoryModel();
        $days = 3;
        
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        CLI::write("Finding export histories older than {$cutoffDate}...", 'yellow');

        $oldHistories = $historyModel->where('created_at <', $cutoffDate)->findAll();

        if (empty($oldHistories)) {
            CLI::write("No old histories found. Nothing to clean.", 'green');
            return;
        }

        $deletedCount = 0;
        foreach ($oldHistories as $history) {
            // Delete physical file if exists
            if (!empty($history['file_path'])) {
                $filePath = WRITEPATH . $history['file_path'];
                if (file_exists($filePath)) {
                    if (unlink($filePath)) {
                        CLI::write("Deleted file: " . $history['file_name'], 'green');
                    } else {
                        CLI::error("Failed to delete file: " . $history['file_name']);
                    }
                }
            }

            // Delete database record
            $historyModel->delete($history['id']);
            $deletedCount++;
        }

        CLI::write("Cleanup complete. Total records deleted: {$deletedCount}", 'green');
    }
}
