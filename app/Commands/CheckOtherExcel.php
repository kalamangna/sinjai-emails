<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CheckOtherExcel extends BaseCommand {
    protected $group = 'App';
    protected $name = 'app:checkotherexcel';
    protected $description = 'Check other Excel';
    public function run(array $params) {
        $files = [
            '/Users/abedzul/Downloads/email_tte_generator.xlsx',
            '/Users/abedzul/Downloads/TTE DISDIK.xlsx',
            '/Users/abedzul/Downloads/DATA DISKOMINFO.xlsx',
            '/Users/abedzul/Downloads/HASIL OLAH DATA Tahap 2.xlsx'
        ];
        
        foreach ($files as $file) {
            CLI::write("=== $file ===");
            try {
                $spreadsheet = IOFactory::load($file);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                CLI::write("Total rows: " . count($rows));
                if (count($rows) > 0) {
                    CLI::write("Header: " . implode(" | ", array_map(function($v) { return substr((string)$v, 0, 30); }, $rows[0])));
                }
            } catch (\Exception $e) {}
        }
    }
}
