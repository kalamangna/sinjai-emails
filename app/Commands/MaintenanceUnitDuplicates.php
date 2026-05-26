<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MaintenanceUnitDuplicates extends BaseCommand
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
    protected $name = 'maintenance:unit-duplicates';
    protected $description = 'Find and resolve duplicate unit kerja records.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        
        $units = $db->table('unit_kerja')->select('id, nama_unit_kerja, api_unit_id')->get()->getResultArray();
        
        $normalized = [];
        $toMerge = [];

        foreach ($units as $unit) {
            // Refined normalization: only remove commas, dots, and extra spaces
            $name = strtoupper(trim(str_replace([',', '.'], '', $unit['nama_unit_kerja'])));
            $name = preg_replace('/\s+/', ' ', $name); // Collapse multiple spaces
            
            // Manual Normalization for common misspellings/variations
            $name = str_replace('PERMUKIMAN', 'PEMUKIMAN', $name);
            $name = str_replace('HORTIKULTURA', 'HOLTIKULTURA', $name);
            $name = str_replace('USAHA MIKRO KECIL', 'USAHA KECIL', $name);
            $name = str_replace('PEMERINTAH KABUPATEN SINJAI', 'PEMERINTAH DAERAH KABUPATEN SINJAI', $name);
            
            $norm = $name;

            if (isset($normalized[$norm])) {
                $toMerge[$norm][] = $unit;
                if (count($toMerge[$norm]) === 1) {
                    array_unshift($toMerge[$norm], $normalized[$norm]);
                }
            } else {
                $normalized[$norm] = $unit;
            }
        }

        if (empty($toMerge)) {
            CLI::write("No obvious duplicates found.", 'green');
            return;
        }

        CLI::write("Potential Duplicates Found:", 'yellow');
        foreach ($toMerge as $norm => $items) {
            CLI::write("\nDuplicate Group: $norm", 'cyan');
            foreach ($items as $item) {
                $emailCount = $db->table('emails')->where('unit_kerja_id', $item['id'])->countAllResults();
                CLI::write("  - ID: {$item['id']}, Name: {$item['nama_unit_kerja']}, API ID: " . ($item['api_unit_id'] ?: 'NULL') . ", Emails: $emailCount");
            }
        }
        
        if (CLI::getOption('fix')) {
            $this->fixDuplicates($toMerge, $db);
        } else {
            CLI::write("\nRun with --fix to merge these duplicates automatically.", 'yellow');
        }
    }

    private function fixDuplicates($toMerge, $db)
    {
        CLI::write("\nStarting merge process (Prioritizing local data/names)...", 'yellow');
        
        foreach ($toMerge as $norm => $items) {
            // Decide which one to keep:
            // 1. Prefer the one with more emails (Most active local data)
            // 2. If equal, prefer the one with an API ID
            // 3. If still equal, prefer the lower ID
            
            usort($items, function($a, $b) use ($db) {
                $countA = $db->table('emails')->where('unit_kerja_id', $a['id'])->countAllResults();
                $countB = $db->table('emails')->where('unit_kerja_id', $b['id'])->countAllResults();
                
                if ($countA !== $countB) return $countB - $countA;

                if ($a['api_unit_id'] && !$b['api_unit_id']) return -1;
                if (!$a['api_unit_id'] && $b['api_unit_id']) return 1;
                
                return $a['id'] - $b['id'];
            });

            $keep = $items[0];
            $others = array_slice($items, 1);

            CLI::write("Merging into primary ID {$keep['id']} ({$keep['nama_unit_kerja']})", 'green');
            
            foreach ($others as $other) {
                CLI::print("  - Moving data from ID {$other['id']} ({$other['nama_unit_kerja']})... ");
                
                // Update emails
                $db->table('emails')->where('unit_kerja_id', $other['id'])->update(['unit_kerja_id' => $keep['id']]);
                
                // Update child units (parent_id)
                $db->table('unit_kerja')->where('parent_id', $other['id'])->update(['parent_id' => $keep['id']]);
                
                // Update web_opd
                $db->table('web_opd')->where('unit_kerja_id', $other['id'])->update(['unit_kerja_id' => $keep['id']]);

                // Transfer API ID if missing on primary record
                if ($other['api_unit_id'] && !$keep['api_unit_id']) {
                    $db->table('unit_kerja')->where('id', $keep['id'])->update(['api_unit_id' => $other['api_unit_id']]);
                    $keep['api_unit_id'] = $other['api_unit_id'];
                    CLI::print("(Transferred API ID) ");
                }

                // Delete the duplicate
                $db->table('unit_kerja')->where('id', $other['id'])->delete();
                
                CLI::write("DONE", 'green');
            }
        }
        
        CLI::write("\nMerge process completed!", 'green');
    }
}
