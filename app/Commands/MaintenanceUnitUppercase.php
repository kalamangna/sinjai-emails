<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MaintenanceUnitUppercase extends BaseCommand
{
    protected $group = 'Maintenance';
    protected $name = 'maintenance:unit-uppercase';
    protected $description = 'Convert all unit kerja names to uppercase.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write("Updating unit_kerja table...", 'yellow');
        $db->query("UPDATE unit_kerja SET nama_unit_kerja = UPPER(nama_unit_kerja)");
        CLI::write("Affected rows: " . $db->affectedRows(), 'green');

        CLI::write("Updating emails table (denormalized columns)...", 'yellow');
        $db->query("UPDATE emails SET unit_kerja = UPPER(unit_kerja), sub_unit_kerja = UPPER(sub_unit_kerja)");
        CLI::write("Affected rows: " . $db->affectedRows(), 'green');

        CLI::write("Update finished.", 'cyan');
    }
}
