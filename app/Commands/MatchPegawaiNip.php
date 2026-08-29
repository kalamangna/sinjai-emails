<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Domains\Email\Models\EmailModel;
use App\Domains\UnitKerja\Models\UnitKerjaModel;
use App\Shared\Models\StatusAsnModel;

class MatchPegawaiNip extends BaseCommand
{
    protected $group = 'App';
    protected $name = 'sync:match-nip';
    protected $description = 'Simulate or apply automatic NIP matching strictly for PNS accounts using SIMPEG API.';
    protected $usage = 'sync:match-nip [unit_id] [options]';
    protected $arguments = [
        'unit_id' => 'Target specific Unit Kerja ID or Name (optional)',
    ];
    protected $options = [
        '--apply'       => 'Execute updates to the database. Without this flag, runs in simulation mode.',
        '--unit'        => 'Target specific Unit Kerja ID or Name (optional)',
        '--threshold'   => 'Similarity percentage threshold for fuzzy matching (default: 85)',
    ];

    private array $cachedApiPegawai = [];

    public function run(array $params)
    {
        $isApply = CLI::getOption('apply') !== null || in_array('--apply', $params) || isset($params['apply']);
        $threshold = (float)(CLI::getOption('threshold') ?? $this->findParamValue($params, 'threshold=') ?? 85);
        
        // Robust parsing untuk unit filter (baik via positional $params[0], --unit=X, atau --unit X)
        $unitFilter = CLI::getOption('unit') ?? $params['unit'] ?? null;
        if (empty($unitFilter)) {
            $unitFilter = $this->findParamValue($params, 'unit=');
        }
        if (empty($unitFilter) && isset($params[0]) && !in_array($params[0], ['--apply', '-apply', '--dry-run'])) {
            $unitFilter = $params[0];
        }

        CLI::write("==========================================================", 'yellow');
        CLI::write("       SIMPEG NIP MATCHING & RECONCILIATION TOOL          ", 'yellow');
        CLI::write("==========================================================", 'yellow');

        if (!$isApply) {
            CLI::write("MODE: [SIMULASI / DRY-RUN] (Tidak ada perubahan database)", 'cyan');
            CLI::write("Gunakan opsi --apply jika ingin menerapkan perubahan ke database.", 'light_gray');
        } else {
            CLI::write("MODE: [LIVE UPDATE / APPLY] (Perubahan AKAN disimpan ke database!)", 'red');
            $confirm = CLI::prompt('Apakah Anda yakin ingin memperbarui database secara langsung?', ['y', 'n']);
            if (strtolower($confirm) !== 'y') {
                CLI::write("Dibatalkan oleh pengguna.", 'yellow');
                return;
            }
        }
        CLI::write("");

        $emailModel = new EmailModel();
        $unitModel = new UnitKerjaModel();
        $statusAsnModel = new StatusAsnModel();

        // 1. Pastikan yang dicek KHUSUS berstatus PNS
        $pnsStatus = $statusAsnModel->where('nama_status_asn', 'PNS')->first();
        $pnsId = $pnsStatus['id'] ?? 1;

        CLI::write("Target Status Kepegawaian : [PNS (ID: $pnsId)]", 'green');

        // 2. Resolve Unit Kerja Filter & Hierarki
        $targetUnitIds = [];
        $allUnits = $unitModel->findAll();
        $unitsById = [];
        foreach ($allUnits as $u) {
            $unitsById[$u['id']] = $u;
        }

        if (!empty($unitFilter)) {
            $targetUnit = null;
            if (is_numeric($unitFilter)) {
                $targetUnit = $unitsById[$unitFilter] ?? null;
            }

            if (!$targetUnit) {
                // Cari berdasarkan api_unit_id atau kecocokan nama
                foreach ($allUnits as $u) {
                    if ($u['api_unit_id'] == $unitFilter || stripos($u['nama_unit_kerja'], $unitFilter) !== false) {
                        $targetUnit = $u;
                        break;
                    }
                }
            }

            if (!$targetUnit) {
                CLI::error("Error: Unit Kerja '$unitFilter' tidak ditemukan di database.");
                return;
            }

            // Sertakan unit induk dan seluruh unit turunannya (child units)
            $targetUnitIds = [(int)$targetUnit['id']];
            $childUnits = $unitModel->where('parent_id', $targetUnit['id'])->findAll();
            foreach ($childUnits as $child) {
                $targetUnitIds[] = (int)$child['id'];
            }

            CLI::write("Filter Unit Kerja         : " . $targetUnit['nama_unit_kerja'] . " (ID: {$targetUnit['id']})", 'cyan');
            if (count($childUnits) > 0) {
                CLI::write("                            Termasuk " . count($childUnits) . " sub-unit/UPTD.", 'light_gray');
            }
        }

        // 3. Query akun KHUSUS PNS tanpa NIP
        $builder = $emailModel->select('emails.id, emails.email, emails.name, emails.nip, emails.unit_kerja_id, emails.status_asn_id, unit_kerja.nama_unit_kerja, unit_kerja.api_unit_id, unit_kerja.parent_id')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
            ->where('emails.deleted_at IS NULL')
            ->where('emails.status_asn_id', $pnsId) // STRICTLY PNS
            ->groupStart()
                ->where('emails.nip IS NULL')
                ->orWhere('emails.nip', '')
            ->groupEnd();

        if (!empty($targetUnitIds)) {
            $builder->whereIn('emails.unit_kerja_id', $targetUnitIds);
        }

        $accounts = $builder->findAll();
        $totalAccounts = count($accounts);

        CLI::write("Total akun PNS tanpa NIP  : $totalAccounts", 'yellow');
        if ($totalAccounts === 0) {
            CLI::write("\nSemua akun PNS pada filter ini sudah memiliki NIP lengkap!", 'green');
            return;
        }

        // 4. Pre-load semua NIP yang sudah ada di database untuk deteksi duplikat
        $existingNipRecords = $emailModel->select('id, email, name, nip')
            ->where('deleted_at IS NULL')
            ->where('nip IS NOT NULL')
            ->where('nip !=', '')
            ->findAll();

        $existingNipMap = [];
        foreach ($existingNipRecords as $rec) {
            $norm = str_replace([' ', '.', '-', '\''], '', $rec['nip']);
            $existingNipMap[$norm] = $rec;
        }

        // 5. Kumpulkan semua api_unit_id yang dibutuhkan
        $neededApiUnits = [];
        foreach ($accounts as $acc) {
            $uId = $acc['unit_kerja_id'] ?? null;
            if (!$uId || !isset($unitsById[$uId])) continue;

            $u = $unitsById[$uId];
            $apiUnitId = $u['api_unit_id'] ?: (isset($unitsById[$u['parent_id']]) ? $unitsById[$u['parent_id']]['api_unit_id'] : null);
            if (!empty($apiUnitId)) {
                $neededApiUnits[$apiUnitId] = true;
            }
        }

        $totalUnitsToFetch = count($neededApiUnits);
        CLI::write("\nMengambil master data pegawai untuk $totalUnitsToFetch Unit Kerja dari API SIMPEG...", 'yellow');

        $client = \Config\Services::curlrequest(['timeout' => 15, 'verify' => false]);
        $baseUrl = rtrim(env('PEGAWAI_BASE_URL') ?: 'https://apps.sinjaikab.go.id/api/pegawai', '/') . '/';

        $totalPegawaiFetched = 0;
        $unitIdx = 0;
        foreach (array_keys($neededApiUnits) as $apiUnitId) {
            $unitIdx++;
            $pegawaiList = $this->fetchApiPegawaiWithRetry($client, $baseUrl, $apiUnitId);
            $this->cachedApiPegawai[$apiUnitId] = $pegawaiList;
            $totalPegawaiFetched += count($pegawaiList);
            CLI::print("Progress: [$unitIdx/$totalUnitsToFetch] Unit $apiUnitId: " . count($pegawaiList) . " pegawai\r");
            usleep(200000); // 200ms delay antar unit
        }
        CLI::write("\nSelesai mengambil master data pegawai (Total: $totalPegawaiFetched pegawai dari SIMPEG).\n", 'green');

        $exactMatches = [];
        $fuzzyMatches = [];
        $duplicateConflicts = [];
        $claimedNipsInBatch = [];
        $unmatched = [];

        CLI::write("Memulai pencocokan data...", 'yellow');

        foreach ($accounts as $account) {
            $accName = trim($account['name'] ?? '');
            $accEmail = $account['email'];
            $unitId = $account['unit_kerja_id'];
            $unit = $unitsById[$unitId] ?? null;

            // Jika nama akun kosong, ambil dari username email
            if (empty($accName)) {
                $accName = explode('@', $accEmail)[0];
                $accName = str_replace(['.', '_', '-'], ' ', $accName);
            }

            $normAccName = $this->normalizeName($accName);

            // Tentukan api_unit_id
            $apiUnitId = null;
            if ($unit) {
                $apiUnitId = $unit['api_unit_id'] ?: (isset($unitsById[$unit['parent_id']]) ? $unitsById[$unit['parent_id']]['api_unit_id'] : null);
            }

            $pegawaiList = !empty($apiUnitId) ? ($this->cachedApiPegawai[$apiUnitId] ?? []) : [];
            $matchResult = $this->findBestMatch($normAccName, $pegawaiList, $threshold);

            if ($matchResult['type'] === 'EXACT' || $matchResult['type'] === 'FUZZY') {
                $targetPeg = $matchResult['pegawai'];
                $targetNipClean = str_replace([' ', '.', '-', '\''], '', $targetPeg['nip']);

                // Proteksi 1: Cek apakah NIP ini sudah ada di database pada akun lain
                if (isset($existingNipMap[$targetNipClean]) && $existingNipMap[$targetNipClean]['id'] != $account['id']) {
                    $owner = $existingNipMap[$targetNipClean];
                    $duplicateConflicts[] = [
                        'account'        => $account,
                        'matched'        => $targetPeg,
                        'existing_owner' => $owner,
                        'reason'         => "Sudah digunakan oleh akun: {$owner['email']}" . (!empty($owner['name']) ? " ({$owner['name']})" : ''),
                    ];
                    continue;
                }

                // Proteksi 2: Cek apakah NIP ini terduplikasi dengan akun lain di batch ini
                if (isset($claimedNipsInBatch[$targetNipClean])) {
                    $prior = $claimedNipsInBatch[$targetNipClean];
                    $duplicateConflicts[] = [
                        'account'        => $account,
                        'matched'        => $targetPeg,
                        'existing_owner' => $prior,
                        'reason'         => "Duplikasi NIP dalam batch evaluasi dengan: {$prior['email']}",
                    ];
                    continue;
                }

                // NIP aman dan unik
                $claimedNipsInBatch[$targetNipClean] = $account;

                if ($matchResult['type'] === 'EXACT') {
                    $exactMatches[] = [
                        'account' => $account,
                        'matched' => $targetPeg,
                        'score'   => 100,
                    ];
                } else {
                    $fuzzyMatches[] = [
                        'account' => $account,
                        'matched' => $targetPeg,
                        'score'   => $matchResult['score'],
                    ];
                }
            } else {
                $unmatched[] = [
                    'account' => $account,
                    'reason'  => empty($apiUnitId) ? 'Unit belum ter-mapping ke SIMPEG' : (empty($pegawaiList) ? 'Tidak ada data pegawai dari API unit' : 'Tidak ada nama yang cocok di unit ini'),
                ];
            }
        }

        // Tampilkan Ringkasan
        CLI::write("\n================ HASIL SIMULASI PENCOCOKAN ================", 'green');
        CLI::write("Total Akun Dievaluasi : " . $totalAccounts, 'yellow');
        CLI::write("Cocok Sempurna (100%) : " . count($exactMatches), 'green');
        CLI::write("Cocok Mirip (Fuzzy)   : " . count($fuzzyMatches), 'cyan');
        CLI::write("Konflik Duplikat NIP  : " . count($duplicateConflicts) . " (Dilewati demi keamanan)", !empty($duplicateConflicts) ? 'yellow' : 'light_gray');
        CLI::write("Belum Cocok           : " . count($unmatched), 'red');
        CLI::write("===========================================================\n");

        // Tampilkan Sampel Exact Matches
        if (!empty($exactMatches)) {
            CLI::write("--- [SAMPEL 10 COCOK SEMPURNA (100%)] ---", 'green');
            foreach (array_slice($exactMatches, 0, 10) as $m) {
                $acc = $m['account'];
                $peg = $m['matched'];
                CLI::write(sprintf(
                    "• [%s] %s  ==>  NIP: %s | %s (%s)",
                    $acc['email'],
                    $acc['name'] ?: '-',
                    $peg['nip'],
                    $peg['nama'],
                    $peg['jabatan_nama'] ?? '-'
                ), 'light_green');
            }
            if (count($exactMatches) > 10) {
                CLI::write("... dan " . (count($exactMatches) - 10) . " akun lainnya cocok 100%.\n");
            } else {
                CLI::write("");
            }
        }

        // Tampilkan Sampel Fuzzy Matches
        if (!empty($fuzzyMatches)) {
            CLI::write("--- [SAMPEL 10 COCOK MIRIP (FUZZY)] ---", 'cyan');
            foreach (array_slice($fuzzyMatches, 0, 10) as $m) {
                $acc = $m['account'];
                $peg = $m['matched'];
                CLI::write(sprintf(
                    "• [%s] %s  ==>  NIP: %s | %s (Score: %.1f%%)",
                    $acc['email'],
                    $acc['name'] ?: '-',
                    $peg['nip'],
                    $peg['nama'],
                    $m['score']
                ), 'light_cyan');
            }
            if (count($fuzzyMatches) > 10) {
                CLI::write("... dan " . (count($fuzzyMatches) - 10) . " akun lainnya cocok fuzzy.\n");
            } else {
                CLI::write("");
            }
        }

        // Tampilkan Sampel Konflik Duplikat NIP
        if (!empty($duplicateConflicts)) {
            CLI::write("--- [⚠️ SAMPEL KONFLIK NIP DUPLIKAT (DILEWATI)] ---", 'yellow');
            foreach (array_slice($duplicateConflicts, 0, 10) as $d) {
                $acc = $d['account'];
                $peg = $d['matched'];
                CLI::write(sprintf(
                    "• [%s] %s  ==>  NIP: %s | %s [%s]",
                    $acc['email'],
                    $acc['name'] ?: '-',
                    $peg['nip'],
                    $peg['nama'],
                    $d['reason']
                ), 'yellow');
            }
            if (count($duplicateConflicts) > 10) {
                CLI::write("... dan " . (count($duplicateConflicts) - 10) . " konflik lainnya dilewati.\n");
            } else {
                CLI::write("");
            }
        }

        // Tampilkan Sampel Unmatched
        if (!empty($unmatched)) {
            CLI::write("--- [SAMPEL 10 BELUM COCOK] ---", 'red');
            foreach (array_slice($unmatched, 0, 10) as $u) {
                $acc = $u['account'];
                CLI::write(sprintf(
                    "• [%s] %s | Unit: %s (%s)",
                    $acc['email'],
                    $acc['name'] ?: '-',
                    $acc['nama_unit_kerja'] ?: 'Tanpa Unit',
                    $u['reason']
                ), 'light_red');
            }
            if (count($unmatched) > 10) {
                CLI::write("... dan " . (count($unmatched) - 10) . " akun lainnya belum cocok.\n");
            } else {
                CLI::write("");
            }
        }

        // Eksekusi jika mode --apply
        if ($isApply) {
            CLI::write("\nMenerapkan perubahan ke database...", 'yellow');
            $appliedCount = 0;

            // Update exact matches
            foreach ($exactMatches as $item) {
                $acc = $item['account'];
                $peg = $item['matched'];

                $updateData = [
                    'nip'              => $peg['nip'],
                    'status_asn_id'    => $pnsId,
                    'pangkat_golruang' => $peg['pangkat_golruang'] ?? null,
                    'pangkat_nama'     => $peg['pangkat_nama'] ?? null,
                ];

                if (!empty($peg['jabatan_nama'])) {
                    $updateData['jabatan'] = mb_strtoupper($peg['jabatan_nama'], 'UTF-8');
                }

                $emailModel->update($acc['id'], $updateData);
                $appliedCount++;
            }

            CLI::write("Berhasil memperbarui $appliedCount akun dengan NIP dan data kepegawaian!", 'green');
        } else {
            CLI::write("💡 Tips: Untuk menerapkan hasil yang cocok 100% ke database, jalankan:", 'yellow');
            CLI::write("php spark sync:match-nip " . (!empty($unitFilter) ? "--unit=" . escapeshellarg($unitFilter) . " " : "") . "--apply\n", 'cyan');
        }
    }

    private function fetchApiPegawaiWithRetry($client, string $baseUrl, string $apiUnitId): array
    {
        $url = $baseUrl . 'get_pegawai?unit_id=' . urlencode($apiUnitId);

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $response = $client->get($url);
                $statusCode = $response->getStatusCode();

                if ($statusCode === 200) {
                    $data = json_decode($response->getBody(), true);
                    if (is_array($data)) {
                        return $data;
                    }
                } elseif ($statusCode === 429) {
                    sleep(2);
                }
            } catch (\Throwable $e) {
                sleep(1);
            }
        }

        return [];
    }

    private function findBestMatch(string $normAccName, array $pegawaiList, float $threshold): array
    {
        if (empty($normAccName) || empty($pegawaiList)) {
            return ['type' => 'NONE'];
        }

        $bestFuzzy = null;
        $bestScore = 0;

        foreach ($pegawaiList as $peg) {
            $pegName = $peg['nama'] ?? '';
            $normPegName = $this->normalizeName($pegName);

            // 1. Exact Match setelah normalisasi
            if ($normAccName === $normPegName) {
                return [
                    'type'    => 'EXACT',
                    'pegawai' => $peg,
                    'score'   => 100,
                ];
            }

            // 2. Exact match tanpa spasi (misal nama tersambung)
            $compactAcc = str_replace(' ', '', $normAccName);
            $compactPeg = str_replace(' ', '', $normPegName);
            if (strlen($compactAcc) > 4 && $compactAcc === $compactPeg) {
                return [
                    'type'    => 'EXACT',
                    'pegawai' => $peg,
                    'score'   => 100,
                ];
            }

            // 3. Hitung similarity
            similar_text($normAccName, $normPegName, $percent);

            if ($percent > $bestScore) {
                $bestScore = $percent;
                $bestFuzzy = $peg;
            }
        }

        if ($bestScore >= $threshold && $bestFuzzy !== null) {
            return [
                'type'    => 'FUZZY',
                'pegawai' => $bestFuzzy,
                'score'   => $bestScore,
            ];
        }

        return ['type' => 'NONE'];
    }

    private function normalizeName(string $name): string
    {
        $name = mb_strtoupper($name, 'UTF-8');
        $name = str_replace([',.', '.,', ';', '`', '\'', '"', '(', ')', '[', ']'], ' ', $name);

        // 1. Pisahkan gelar depan yang menempel (contoh: "DR.ANDI" -> "DR ANDI", "HJ.NURLINA" -> "HJ NURLINA")
        $name = preg_replace('/\b(PROF|DRS|DRA|DR|IR|HJ|H)\./i', ' $1 ', $name);

        // 2. Hapus titik di dalam singkatan/gelar (misal "S.KOM." -> "SKOM", "S.IP." -> "SIP", "M.A.P" -> "MAP", "S.P." -> "SP")
        $name = preg_replace('/(?<=[A-Z0-9])\.(?=[A-Z0-9])/i', '', $name);
        $name = preg_replace('/[\.,]/', ' ', $name);
        $name = preg_replace('/\s+/', ' ', trim($name));

        // Daftar gelar akademik, keagamaan, profesi, dan kehormatan
        $titles = [
            'PROF', 'DR', 'DRA', 'DRS', 'IR', 'H', 'HJ', 'HAJI', 'HAJJAH',
            'SKOM', 'SPD', 'SSOS', 'SSTP', 'SE', 'SH', 'SSI', 'SKM', 'STRKEB', 'STRGZ', 'STRTRA', 'STR',
            'SAP', 'SIP', 'ST', 'SP', 'SAG', 'SKEP', 'STP', 'SS', 'SKED', 'SIKOM', 'SFARM', 'SPT', 'SPI', 'SM',
            'MSI', 'MPD', 'MM', 'MKOM', 'MAP', 'MTRAP', 'MKES', 'MH', 'MAG', 'MAK', 'MT', 'MIKOM', 'MP', 'MKM', 'MSC',
            'AMD', 'AMDKEB', 'AMDKEP', 'AMDKL', 'AMDPK', 'AMKG', 'AMTEK',
            'NS', 'APT', 'GR', 'AP', 'SEK', 'IP'
        ];

        $words = explode(' ', $name);
        $cleanWords = [];

        foreach ($words as $w) {
            $cleanW = trim($w);
            if (empty($cleanW)) continue;

            if (in_array($cleanW, $titles)) {
                continue;
            }

            // Normalisasi singkatan nama umum
            if ($cleanW === 'MUH' || $cleanW === 'MUHAMMAD') {
                $cleanWords[] = 'MUHAMMAD';
            } elseif ($cleanW === 'ABD' || $cleanW === 'ABDUL') {
                $cleanWords[] = 'ABDUL';
            } elseif ($cleanW === 'ACH' || $cleanW === 'ACHMAD' || $cleanW === 'AHMAD') {
                $cleanWords[] = 'AHMAD';
            } else {
                $cleanWords[] = $cleanW;
            }
        }

        // Jika kata pertama adalah singkatan 'A' (misal: "A ILHAM" atau "A ARDIN"), ubah ke "ANDI"
        if (!empty($cleanWords) && ($cleanWords[0] === 'A' || $cleanWords[0] === 'ANDI')) {
            $cleanWords[0] = 'ANDI';
        }

        return implode(' ', $cleanWords);
    }

    private function findParamValue(array $params, string $prefix): ?string
    {
        foreach ($params as $key => $val) {
            if (is_string($key) && strpos($key, $prefix) === 0) {
                return trim(substr($key, strlen($prefix)), '"\' ');
            }
            if (is_string($val) && strpos($val, $prefix) === 0) {
                return trim(substr($val, strlen($prefix)), '"\' ');
            }
        }
        return null;
    }
}
