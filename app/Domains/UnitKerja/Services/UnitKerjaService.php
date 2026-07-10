<?php

namespace App\Domains\UnitKerja\Services;

use App\Domains\UnitKerja\Models\UnitKerjaModel;
use Exception;

class UnitKerjaService
{
    protected $unitKerjaModel;

    public function __construct()
    {
        $this->unitKerjaModel = new UnitKerjaModel();
    }

    /**
     * Create a single Unit Kerja
     */
    public function createUnitKerja(string $name, ?int $parentId): bool
    {
        $name = trim($name);
        if (empty($name)) {
            throw new Exception('Nama Unit Kerja tidak boleh kosong.');
        }

        return (bool) $this->unitKerjaModel->insert([
            'nama_unit_kerja' => $name,
            'parent_id'       => $parentId,
        ]);
    }

    /**
     * Update a single Unit Kerja
     */
    public function updateUnitKerja(int $id, string $name, ?int $parentId): bool
    {
        $name = trim($name);
        if (empty($name)) {
            throw new Exception('Nama Unit Kerja tidak boleh kosong.');
        }

        return (bool) $this->unitKerjaModel->update($id, [
            'nama_unit_kerja' => $name,
            'parent_id'       => $parentId,
        ]);
    }

    /**
     * Delete a single Unit Kerja
     */
    public function deleteUnitKerja(int $id): bool
    {
        return (bool) $this->unitKerjaModel->delete($id);
    }

    /**
     * Process batch creation from textarea/newline-separated text
     */
    public function createUnitKerjaBatchFromText(string $namesText, ?int $parentId): int
    {
        $namesArray = explode("\n", $namesText);
        $data = [];
        
        foreach ($namesArray as $name) {
            $trimmedName = trim($name);
            if (!empty($trimmedName)) {
                $data[] = [
                    'nama_unit_kerja' => $trimmedName,
                    'parent_id'       => $parentId,
                ];
            }
        }

        if (!empty($data)) {
            $inserted = $this->unitKerjaModel->insertBatch($data);
            return $inserted ? count($data) : 0;
        }

        return 0;
    }

    /**
     * Process batch creation from JSON payload
     */
    public function createUnitKerjaBatchFromJson(array $items): array
    {
        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($items as $item) {
            $name = trim($item['nama_unit_kerja'] ?? '');
            if (empty($name)) {
                $results[] = ['name' => 'N/A', 'success' => false, 'message' => 'Nama Unit Kerja kosong.'];
                $failCount++;
                continue;
            }

            $parentId = !empty($item['parent_id']) ? (int)$item['parent_id'] : null;

            try {
                $this->unitKerjaModel->insert([
                    'nama_unit_kerja' => $name,
                    'parent_id'       => $parentId
                ]);
                $results[] = ['name' => $name, 'success' => true, 'message' => 'Berhasil disimpan.'];
                $successCount++;
            } catch (\Throwable $e) {
                $results[] = ['name' => $name, 'success' => false, 'message' => $e->getMessage()];
                $failCount++;
            }
        }

        return [
            'results' => $results,
            'summary' => [
                'success' => $successCount,
                'fail'    => $failCount,
                'total'   => count($items)
            ]
        ];
    }
}
