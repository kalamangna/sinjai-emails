<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class ChunkReadFilter implements IReadFilter {
    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool {
        return $row <= 3; // Read first 3 rows
    }
}

class ReadExcelData extends BaseCommand {
    protected $group = 'App';
    protected $name = 'app:readexceldata';
    protected $description = 'Read Excel Data Fast';
    public function run(array $params) {
        $file = '/Users/abedzul/Downloads/batch-update.xlsx';
        
        CLI::write("=== $file ===");
        try {
            $reader = IOFactory::createReaderForFile($file);
            $reader->setReadDataOnly(true);
            $reader->setReadFilter(new ChunkReadFilter());
            
            $spreadsheet = $reader->load($file);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            foreach ($rows as $i => $row) {
                CLI::write("Row $i: " . implode(" | ", array_map(function($v) { return substr((string)$v, 0, 30); }, $row)));
            }
        } catch (\Exception $e) {
            CLI::error("Failed to read: " . $e->getMessage());
        }
    }
}
