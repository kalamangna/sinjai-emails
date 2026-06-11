<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CountExcel extends BaseCommand {
    protected $group = 'App';
    protected $name = 'app:countexcel';
    protected $description = 'Count Excel Rows';
    public function run(array $params) {
        $file = '/Users/abedzul/Downloads/batch-update.xlsx';
        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        CLI::write("Total rows: " . count($rows));
    }
}
