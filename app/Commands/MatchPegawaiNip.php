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
        '--apply'         => 'Execute updates to the database. Without this flag, runs in simulation mode.',
        '--unit'          => 'Target specific Unit Kerja ID or Name (optional)',
        '--threshold'     => 'Similarity percentage threshold for fuzzy matching (default: 85)',
        '--include-fuzzy' => 'Automatically apply fuzzy matches without interactive prompt',
        '--cross-unit'    => 'Search across all Unit Kerja if employee is not found in assigned unit (e.g. employee transferred/mutated)',
        '--match-jabatan' => 'Match leadership / structural position accounts (e.g. Kadis, Sekda, Camat, Asisten, Kabag) based on active position',
        '--refresh'       => 'Force re-download master data from SIMPEG API instead of using local cache',
    ];

    private array $cachedApiPegawai = [];

    public function run(array $params)
    {
        $isApply = CLI::getOption('apply') !== null || in_array('--apply', $params) || isset($params['apply']);
        $threshold = (float)(CLI::getOption('threshold') ?? $this->findParamValue($params, 'threshold=') ?? 85);
        $isCrossUnit = CLI::getOption('cross-unit') !== null || in_array('--cross-unit', $params) || isset($params['cross-unit']);
        $isMatchJabatan = CLI::getOption('match-jabatan') !== null || in_array('--match-jabatan', $params) || isset($params['match-jabatan']) || true; // Aktif default
        
        // Robust parsing untuk unit filter (baik via positional $params[0], --unit=X, atau --unit X)
        $unitFilter = CLI::getOption('unit') ?? $params['unit'] ?? null;
        if (empty($unitFilter)) {
            $unitFilter = $this->findParamValue($params, 'unit=');
        }
        if (empty($unitFilter) && isset($params[0]) && !in_array($params[0], ['--apply', '-apply', '--dry-run', '--cross-unit', '--include-fuzzy', '--match-jabatan', '--refresh'])) {
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
        if ($isCrossUnit) {
            CLI::write("Pencarian Lintas Unit     : [AKTIF (Strict Uniqueness Guard)]", 'purple');
        }
        CLI::write("Pencocokan Akun Pimpinan  : [AKTIF (Jabatan Struktural SIMPEG)]", 'cyan');

        // 2. Resolve Unit Kerja Filter & Hierarki
        $targetUnitIds = [];
        $allUnits = $unitModel->findAll();
        $unitsById = [];
        $unitsByApiId = [];
        foreach ($allUnits as $u) {
            $unitsById[$u['id']] = $u;
            if (!empty($u['api_unit_id'])) {
                $unitsByApiId[$u['api_unit_id']] = $u;
            }
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
        $builder = $emailModel->select('emails.id, emails.email, emails.name, emails.nip, emails.jabatan, emails.unit_kerja_id, emails.status_asn_id, unit_kerja.nama_unit_kerja, unit_kerja.api_unit_id, unit_kerja.parent_id')
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
        // Jika ada akun tanpa unit_kerja_id atau opsi --cross-unit aktif, muat seluruh unit se-Kabupaten
        $hasUnitlessAccount = false;
        foreach ($accounts as $acc) {
            if (empty($acc['unit_kerja_id'])) {
                $hasUnitlessAccount = true;
                break;
            }
        }

        $neededApiUnits = [];
        if ($isCrossUnit || $isMatchJabatan || $hasUnitlessAccount) {
            foreach ($allUnits as $u) {
                if (!empty($u['api_unit_id'])) {
                    $neededApiUnits[$u['api_unit_id']] = true;
                }
            }
        } else {
            foreach ($accounts as $acc) {
                $uId = $acc['unit_kerja_id'] ?? null;
                if (!$uId || !isset($unitsById[$uId])) continue;

                $u = $unitsById[$uId];
                $apiUnitId = $u['api_unit_id'] ?: (isset($unitsById[$u['parent_id']]) ? $unitsById[$u['parent_id']]['api_unit_id'] : null);
                if (!empty($apiUnitId)) {
                    $neededApiUnits[$apiUnitId] = true;
                }
            }
        }

        $forceRefresh = CLI::getOption('refresh') !== null || in_array('--refresh', $params);
        $cacheFile = WRITEPATH . 'cache/simpeg_units_pegawai.json';
        $diskCache = [];

        if (!$forceRefresh && file_exists($cacheFile)) {
            $raw = @file_get_contents($cacheFile);
            $diskCache = json_decode($raw, true) ?: [];
        }

        $totalUnitsToFetch = count($neededApiUnits);
        $cachedCount = 0;
        foreach (array_keys($neededApiUnits) as $apiUnitId) {
            if (isset($diskCache[$apiUnitId]) && is_array($diskCache[$apiUnitId]['data']) && (time() - ($diskCache[$apiUnitId]['time'] ?? 0) < 86400)) {
                $cachedCount++;
            }
        }

        if ($cachedCount === $totalUnitsToFetch && $cachedCount > 0) {
            CLI::write("\n⚡ Memuat master data dari cache lokal (Instan: $cachedCount Unit Kerja)...", 'green');
        } else {
            CLI::write("\nMenyiapkan master data pegawai untuk $totalUnitsToFetch Unit Kerja dari API SIMPEG...", 'yellow');
        }

        $client = \Config\Services::curlrequest(['timeout' => 10, 'verify' => false]);
        $baseUrl = rtrim(env('PEGAWAI_BASE_URL') ?: 'https://apps.sinjaikab.go.id/api/pegawai', '/') . '/';

        $totalPegawaiFetched = 0;
        $unitIdx = 0;
        $allPegawaiList = [];
        $hasCacheUpdate = false;

        foreach (array_keys($neededApiUnits) as $apiUnitId) {
            $unitIdx++;

            // Cek apakah ada di disk cache yang valid (< 24 jam)
            if (!$forceRefresh && isset($diskCache[$apiUnitId]) && is_array($diskCache[$apiUnitId]['data']) && (time() - ($diskCache[$apiUnitId]['time'] ?? 0) < 86400)) {
                $pegawaiList = $diskCache[$apiUnitId]['data'];
            } else {
                $pegawaiList = $this->fetchApiPegawaiWithRetry($client, $baseUrl, $apiUnitId);
                $diskCache[$apiUnitId] = [
                    'time' => time(),
                    'data' => $pegawaiList,
                ];
                $hasCacheUpdate = true;
                @file_put_contents($cacheFile, json_encode($diskCache));

                $line = sprintf("⏳ Mengunduh data SIMPEG: [%d/%d] Unit %s (%d pegawai)   ", $unitIdx, $totalUnitsToFetch, $apiUnitId, count($pegawaiList));
                CLI::print("\r" . str_pad($line, 70));
                usleep(30000); // 30ms delay
            }

            $this->cachedApiPegawai[$apiUnitId] = $pegawaiList;
            $totalPegawaiFetched += count($pegawaiList);

            foreach ($pegawaiList as $p) {
                if (empty($p['unit_id'])) {
                    $p['unit_id'] = $apiUnitId;
                }
                $allPegawaiList[] = $p;
            }
        }

        if ($hasCacheUpdate) {
            @file_put_contents($cacheFile, json_encode($diskCache));
        }

        CLI::write("\n✓ Master data kepegawaian siap: $totalPegawaiFetched pegawai dari SIMPEG.\n", 'green');

        // Bangun indeks keunikan nama se-Kabupaten untuk proteksi Lintas Unit
        $globalExactNameIndex = [];
        foreach ($allPegawaiList as $p) {
            $pNorm = $this->normalizeName($p['nama'] ?? '');
            if (!empty($pNorm)) {
                if (!isset($globalExactNameIndex[$pNorm])) {
                    $globalExactNameIndex[$pNorm] = [];
                }
                $globalExactNameIndex[$pNorm][] = $p;
            }
        }

        $exactMatches = [];
        $leadershipMatches = [];
        $crossUnitMatches = [];
        $fuzzyMatches = [];
        $duplicateConflicts = [];
        $claimedNipsInBatch = [];
        $unmatched = [];

        CLI::write("Memulai pencocokan data...", 'yellow');

        foreach ($accounts as $account) {
            $accName = trim($account['name'] ?? '');
            $accEmail = $account['email'];
            $accJabatan = trim($account['jabatan'] ?? '');
            $unitId = $account['unit_kerja_id'];
            $unit = $unitsById[$unitId] ?? null;

            // Jika nama akun kosong, ambil dari username email
            if (empty($accName)) {
                $accName = explode('@', $accEmail)[0];
                $accName = str_replace(['.', '_', '-'], ' ', $accName);
            }

            $normAccName = $this->normalizeName($accName);

            // Tentukan api_unit_id untuk unit kerja asal
            $apiUnitId = null;
            if ($unit) {
                $apiUnitId = $unit['api_unit_id'] ?: (isset($unitsById[$unit['parent_id']]) ? $unitsById[$unit['parent_id']]['api_unit_id'] : null);
            }

            $pegawaiList = !empty($apiUnitId) ? ($this->cachedApiPegawai[$apiUnitId] ?? []) : [];
            
            // Tahap 1: Cocokkan di dalam Unit Kerja saat ini (jika ada unit_id)
            $matchResult = !empty($pegawaiList) ? $this->findBestMatch($normAccName, $pegawaiList, $threshold) : ['type' => 'NONE'];
            $isCross = false;
            $isLeadership = false;
            $leadershipRole = null;
            $crossRejectReason = null;

            // Tahap 2: Jika tidak ketemu di unit asal (atau akun tidak memiliki unit_kerja_id) dan pencarian se-kabupaten aktif
            $allowGlobalSearch = $isCrossUnit || empty($account['unit_kerja_id']);
            if ($matchResult['type'] === 'NONE' && $allowGlobalSearch && !empty($allPegawaiList)) {
                if (isset($globalExactNameIndex[$normAccName])) {
                    $candidates = $globalExactNameIndex[$normAccName];
                    if (count($candidates) === 1) {
                        $matchResult = [
                            'type'    => 'EXACT',
                            'pegawai' => $candidates[0],
                            'score'   => 100,
                        ];
                        $isCross = true;
                    } else {
                        // Homonim: Coba resolusi berdasarkan tahun lahir pada username email (contoh: abdullah1967@, ernawati70@)
                        $emailYear = $this->extractYearFromEmail($accEmail);
                        $yearMatched = false;
                        if ($emailYear !== null) {
                            $yearCandidates = [];
                            foreach ($candidates as $cand) {
                                $candYear = (int)substr($cand['nip'] ?? '', 0, 4);
                                if ($candYear === $emailYear) {
                                    $yearCandidates[] = $cand;
                                }
                            }
                            if (count($yearCandidates) === 1) {
                                $matchResult = [
                                    'type'    => 'EXACT',
                                    'pegawai' => $yearCandidates[0],
                                    'score'   => 100,
                                ];
                                $isCross = true;
                                $yearMatched = true;
                            }
                        }

                        if (!$yearMatched) {
                            $crossRejectReason = "Ambigu Lintas Unit: Ditemukan " . count($candidates) . " pegawai bernama sama di OPD berbeda";
                        }
                    }
                }
            }

            // Tahap 3: Jika belum ketemu, cek apakah ini akun Pimpinan / Jabatan Struktural (Kadis, Sekda, Camat, Asisten, dll.)
            if ($matchResult['type'] === 'NONE' && $isMatchJabatan) {
                $leadMatch = $this->findLeadershipMatch($accEmail, $account['name'] ?? '', $accJabatan, $pegawaiList, $allPegawaiList, $unit);
                if ($leadMatch !== null) {
                    $matchResult = [
                        'type'    => 'EXACT',
                        'pegawai' => $leadMatch['pegawai'],
                        'score'   => 100,
                    ];
                    $isLeadership = true;
                    $leadershipRole = $leadMatch['position'];
                }
            }

            if (!empty($crossRejectReason) && $matchResult['type'] === 'NONE') {
                $unmatched[] = [
                    'account' => $account,
                    'reason'  => $crossRejectReason,
                ];
                continue;
            }

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

                if ($isLeadership) {
                    $newApiUnitId = $targetPeg['unit_id'] ?? null;
                    $newUnit = $newApiUnitId && isset($unitsByApiId[$newApiUnitId]) ? $unitsByApiId[$newApiUnitId] : null;

                    $leadershipMatches[] = [
                        'account'  => $account,
                        'matched'  => $targetPeg,
                        'position' => $leadershipRole,
                        'new_unit' => $newUnit,
                        'score'    => 100,
                    ];
                } elseif ($isCross) {
                    $newApiUnitId = $targetPeg['unit_id'] ?? null;
                    $newUnit = $newApiUnitId && isset($unitsByApiId[$newApiUnitId]) ? $unitsByApiId[$newApiUnitId] : null;

                    $crossUnitMatches[] = [
                        'account'  => $account,
                        'matched'  => $targetPeg,
                        'new_unit' => $newUnit,
                        'score'    => $matchResult['score'],
                    ];
                } elseif ($matchResult['type'] === 'EXACT') {
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
                    'reason'  => empty($apiUnitId) ? 'Unit belum ter-mapping ke SIMPEG / Tanpa Unit' : (empty($pegawaiList) ? 'Tidak ada data pegawai dari API unit' : 'Tidak ada nama yang cocok di unit ini'),
                ];
            }
        }

        // Tampilkan Ringkasan
        CLI::write("\n================ HASIL SIMULASI PENCOCOKAN ================", 'green');
        CLI::write("Total Akun Dievaluasi : " . $totalAccounts, 'yellow');
        CLI::write("Cocok Sempurna (100%) : " . count($exactMatches), 'green');
        if (!empty($leadershipMatches)) {
            CLI::write("Cocok Akun Pimpinan   : " . count($leadershipMatches) . " (Jabatan Struktural SIMPEG)", 'cyan');
        }
        if (!empty($crossUnitMatches)) {
            CLI::write("Cocok Lintas Unit     : " . count($crossUnitMatches) . " (Mutasi / Resolusi OPD)", 'purple');
        }
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

        // Tampilkan Sampel Leadership Matches
        if (!empty($leadershipMatches)) {
            CLI::write("--- [SAMPEL AKUN PIMPINAN / JABATAN STRUKTURAL] ---", 'cyan');
            foreach (array_slice($leadershipMatches, 0, 10) as $l) {
                $acc = $l['account'];
                $peg = $l['matched'];
                $unitStr = $l['new_unit']['nama_unit_kerja'] ?? ($peg['jabatan_grup'] ?? '-');
                CLI::write(sprintf(
                    "• [%s] %s\n  Jabatan : %s\n  Pejabat : %s | NIP: %s (%s)\n  Unit    : %s",
                    $acc['email'],
                    $acc['name'] ?: '-',
                    $l['position'],
                    $peg['nama'],
                    $peg['nip'],
                    $peg['jabatan_nama'] ?? '-',
                    $unitStr
                ), 'light_cyan');
            }
            if (count($leadershipMatches) > 10) {
                CLI::write("... dan " . (count($leadershipMatches) - 10) . " akun pimpinan lainnya.\n");
            } else {
                CLI::write("");
            }
        }

        // Tampilkan Sampel Cross-Unit Matches
        if (!empty($crossUnitMatches)) {
            CLI::write("--- [SAMPEL 10 COCOK LINTAS UNIT (MUTASI / RESOLUSI OPD)] ---", 'purple');
            foreach (array_slice($crossUnitMatches, 0, 10) as $c) {
                $acc = $c['account'];
                $peg = $c['matched'];
                $oldUnitName = $acc['nama_unit_kerja'] ?: 'Tanpa Unit';
                $newUnitName = $c['new_unit']['nama_unit_kerja'] ?? ('SIMPEG Unit: ' . ($peg['unit_id'] ?? '-'));
                CLI::write(sprintf(
                    "• [%s] %s\n  NIP: %s | %s (%s)\n  Unit Lama: %s  ==>  Unit Baru: %s (Score: %.1f%%)",
                    $acc['email'],
                    $acc['name'] ?: '-',
                    $peg['nip'],
                    $peg['nama'],
                    $peg['jabatan_nama'] ?? '-',
                    $oldUnitName,
                    $newUnitName,
                    $c['score']
                ), 'light_purple');
            }
            if (count($crossUnitMatches) > 10) {
                CLI::write("... dan " . (count($crossUnitMatches) - 10) . " akun lainnya cocok lintas unit.\n");
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
            CLI::write("\n================ MENERAPKAN PERUBAHAN ================", 'yellow');
            $appliedExactCount = 0;
            $appliedLeadCount = 0;
            $appliedFuzzyCount = 0;
            $appliedCrossCount = 0;

            // 1. Update Exact Matches (100% Cocok)
            if (!empty($exactMatches)) {
                CLI::write("Memperbarui " . count($exactMatches) . " akun Cocok Sempurna (100%)...", 'cyan');
                foreach ($exactMatches as $item) {
                    $this->applyAccountUpdate($emailModel, $item['account'], $item['matched'], $pnsId);
                    $appliedExactCount++;
                }
                CLI::write("✓ Selesai: $appliedExactCount akun Cocok Sempurna berhasil diperbarui ke database.\n", 'green');
            }

            // 2. Konfirmasi & Update Leadership Matches
            if (!empty($leadershipMatches)) {
                CLI::write("----------------------------------------------------------", 'cyan');
                CLI::write("Terdapat " . count($leadershipMatches) . " akun Pimpinan / Jabatan Struktural.", 'cyan');
                $leadChoice = CLI::prompt('Apakah Anda ingin menerapkan pembaruan untuk akun pimpinan ini?', ['y', 'a', 'n']);
                $leadChoice = strtolower(trim($leadChoice));

                if ($leadChoice === 'a' || $leadChoice === 'all' || $leadChoice === 'y' || $leadChoice === 'yes') {
                    foreach ($leadershipMatches as $l) {
                        $newUnitId = $l['new_unit']['id'] ?? null;
                        $this->applyAccountUpdate($emailModel, $l['account'], $l['matched'], $pnsId, $newUnitId);
                        $appliedLeadCount++;
                    }
                    CLI::write("✓ Selesai: $appliedLeadCount akun pimpinan berhasil diperbarui ke database.\n", 'green');
                } else {
                    CLI::write("Akun pimpinan dilewati.", 'light_gray');
                }
            }

            // 3. Konfirmasi & Update Cross-Unit Matches
            if (!empty($crossUnitMatches)) {
                CLI::write("----------------------------------------------------------", 'purple');
                CLI::write("Terdapat " . count($crossUnitMatches) . " akun Cocok Lintas Unit / Resolusi OPD.", 'purple');
                $crossChoice = CLI::prompt('Apakah Anda ingin menerapkan akun lintas unit ini dan menyelaraskan Unit Kerja barunya?', ['y', 'a', 'n']);
                $crossChoice = strtolower(trim($crossChoice));

                if ($crossChoice === 'a' || $crossChoice === 'all' || $crossChoice === 'y' || $crossChoice === 'yes') {
                    foreach ($crossUnitMatches as $c) {
                        $newUnitId = $c['new_unit']['id'] ?? null;
                        $this->applyAccountUpdate($emailModel, $c['account'], $c['matched'], $pnsId, $newUnitId);
                        $appliedCrossCount++;
                    }
                    CLI::write("✓ Selesai: $appliedCrossCount akun lintas unit berhasil diperbarui dan diselaraskan unit kerjanya.\n", 'green');
                } else {
                    CLI::write("Akun lintas unit dilewati.", 'light_gray');
                }
            }

            // 4. Konfirmasi & Update Fuzzy Matches
            $includeFuzzy = CLI::getOption('include-fuzzy') !== null || in_array('--include-fuzzy', $params);

            if (!empty($fuzzyMatches)) {
                CLI::write("----------------------------------------------------------", 'yellow');
                CLI::write("Terdapat " . count($fuzzyMatches) . " akun Cocok Mirip (Fuzzy Match).", 'yellow');

                if ($includeFuzzy) {
                    CLI::write("Opsi --include-fuzzy aktif: Menerapkan semua akun fuzzy...", 'cyan');
                    foreach ($fuzzyMatches as $item) {
                        $this->applyAccountUpdate($emailModel, $item['account'], $item['matched'], $pnsId);
                        $appliedFuzzyCount++;
                    }
                    CLI::write("✓ Selesai: $appliedFuzzyCount akun fuzzy berhasil diperbarui ke database.", 'green');
                } else {
                    $fuzzyChoice = CLI::prompt('Apakah Anda ingin meninjau & mengonfirmasi akun fuzzy ini? (y=tinjau per akun, a=terapkan semua, n=lewati semua)', ['y', 'a', 'n']);
                    $fuzzyChoice = strtolower(trim($fuzzyChoice));

                    if ($fuzzyChoice === 'a' || $fuzzyChoice === 'all') {
                        foreach ($fuzzyMatches as $item) {
                            $this->applyAccountUpdate($emailModel, $item['account'], $item['matched'], $pnsId);
                            $appliedFuzzyCount++;
                        }
                        CLI::write("✓ Selesai: $appliedFuzzyCount akun fuzzy berhasil diperbarui ke database.", 'green');
                    } elseif ($fuzzyChoice === 'y' || $fuzzyChoice === 'yes') {
                        $autoAllRemaining = false;
                        foreach ($fuzzyMatches as $idx => $item) {
                            $num = $idx + 1;
                            $tot = count($fuzzyMatches);
                            $acc = $item['account'];
                            $peg = $item['matched'];
                            $score = number_format($item['score'], 1);

                            if (!$autoAllRemaining) {
                                CLI::write("\n[$num/$tot] Konfirmasi Akun Fuzzy (Score: {$score}%):", 'cyan');
                                CLI::write("  • Email      : {$acc['email']}");
                                CLI::write("  • Nama Akun  : " . ($acc['name'] ?: '-'));
                                CLI::write("  • Unit Kerja : " . ($acc['nama_unit_kerja'] ?: '-'));
                                CLI::write("  ===> CALON SIMPEG :");
                                CLI::write("  • NIP        : {$peg['nip']}", 'green');
                                CLI::write("  • Nama SIMPEG: {$peg['nama']}", 'green');
                                CLI::write("  • Jabatan    : " . ($peg['jabatan_nama'] ?? '-'), 'green');
                                CLI::write("  • Gol / Pkt  : " . ($peg['pangkat_golruang'] ?? '-') . " / " . ($peg['pangkat_nama'] ?? '-'), 'green');

                                $ans = CLI::prompt("Cocokkan akun ini ke NIP {$peg['nip']}? (y=ya, n=lewati, a=terapkan semua sisa, q=berhenti)", ['y', 'n', 'a', 'q']);
                                $ans = strtolower(trim($ans));

                                if ($ans === 'q') {
                                    CLI::write("Proses review akun fuzzy dihentikan.", 'yellow');
                                    break;
                                } elseif ($ans === 'a') {
                                    $autoAllRemaining = true;
                                } elseif ($ans !== 'y') {
                                    CLI::write("Dilewati.", 'light_gray');
                                    continue;
                                }
                            }

                            $this->applyAccountUpdate($emailModel, $acc, $peg, $pnsId);
                            $appliedFuzzyCount++;
                            CLI::write("✓ [{$acc['email']}] Berhasil diperbarui ke NIP {$peg['nip']}", 'green');
                        }
                    } else {
                        CLI::write("Akun fuzzy dilewati (tidak ada perubahan database untuk akun fuzzy).", 'light_gray');
                    }
                }
            }

            $totalApplied = $appliedExactCount + $appliedLeadCount + $appliedFuzzyCount + $appliedCrossCount;
            CLI::write("\n==========================================================", 'green');
            CLI::write("TOTAL PEMBARUAN BERHASIL : $totalApplied Akun", 'green');
            CLI::write("• Cocok Sempurna (100%) : $appliedExactCount Akun", 'green');
            if (!empty($appliedLeadCount)) {
                CLI::write("• Cocok Akun Pimpinan   : $appliedLeadCount Akun", 'cyan');
            }
            if (!empty($appliedCrossCount)) {
                CLI::write("• Cocok Lintas Unit     : $appliedCrossCount Akun", 'purple');
            }
            CLI::write("• Cocok Mirip (Fuzzy)   : $appliedFuzzyCount Akun", 'cyan');
            CLI::write("==========================================================\n", 'green');
        } else {
            CLI::write("💡 Tips: Untuk menerapkan hasil ke database, jalankan:", 'yellow');
            $unitArg = '';
            if (!empty($unitFilter)) {
                $unitArg = is_numeric($unitFilter) ? "$unitFilter " : (strpos($unitFilter, ' ') !== false ? "\"$unitFilter\" " : "$unitFilter ");
            }
            $crossFlag = $isCrossUnit ? '--cross-unit ' : '';
            CLI::write("php spark sync:match-nip {$unitArg}{$crossFlag}--apply\n", 'cyan');
        }
    }

    private function applyAccountUpdate(EmailModel $emailModel, array $account, array $pegawai, int $pnsId, ?int $newUnitId = null): void
    {
        $updateData = [
            'nip'              => $pegawai['nip'],
            'status_asn_id'    => $pnsId,
            'pangkat_golruang' => $pegawai['pangkat_golruang'] ?? null,
            'pangkat_nama'     => $pegawai['pangkat_nama'] ?? null,
        ];

        if (!empty($pegawai['jabatan_nama'])) {
            $updateData['jabatan'] = mb_strtoupper($pegawai['jabatan_nama'], 'UTF-8');
        }

        if (empty($account['name']) && !empty($pegawai['nama'])) {
            $updateData['name'] = $pegawai['nama'];
        }

        if (!empty($newUnitId)) {
            $updateData['unit_kerja_id'] = $newUnitId;
        }

        $emailModel->update($account['id'], $updateData);
    }

    private function findLeadershipMatch(string $email, string $rawName, string $rawJabatan, array $unitPegawaiList, array $allPegawaiList, ?array $unit): ?array
    {
        $username = strtolower(explode('@', $email)[0]);
        $cleanName = mb_strtoupper(trim($rawName), 'UTF-8');
        $cleanJabatan = mb_strtoupper(trim($rawJabatan), 'UTF-8');
        $normName = $this->normalizeName($rawName);
        $normNameNoAndi = trim(preg_replace('/^ANDI\s+/i', '', $normName));
        
        // 1. Pola Sekda (Sekretaris Daerah)
        if (strpos($username, 'sekda') !== false || strpos($cleanName, 'SEKRETARIS DAERAH') !== false || strpos($cleanJabatan, 'SEKRETARIS DAERAH') !== false) {
            foreach ($allPegawaiList as $p) {
                if (stripos($p['jabatan_nama'] ?? '', 'Sekretaris Daerah') !== false) {
                    return ['pegawai' => $p, 'position' => 'Sekretaris Daerah'];
                }
            }
        }

        // 2. Pola Asisten (Asisten 1, 2, 3 di Setda)
        if (preg_match('/asisten(\d|satu|dua|tiga)/i', $username) || strpos($cleanName, 'ASISTEN') !== false || strpos($cleanJabatan, 'ASISTEN') !== false) {
            $num = '';
            if (strpos($username, '1') !== false || strpos($username, 'satu') !== false || strpos($cleanName, ' I') !== false || strpos($cleanJabatan, ' I') !== false) $num = 'Pemerintahan';
            if (strpos($username, '2') !== false || strpos($username, 'dua') !== false || strpos($cleanName, ' II') !== false || strpos($cleanJabatan, ' II') !== false) $num = 'Perekonomian';
            if (strpos($username, '3') !== false || strpos($username, 'tiga') !== false || strpos($cleanName, ' III') !== false || strpos($cleanJabatan, ' III') !== false) $num = 'Administrasi';

            if (!empty($num)) {
                foreach ($allPegawaiList as $p) {
                    if (stripos($p['jabatan_nama'] ?? '', 'Asisten') !== false && stripos($p['jabatan_nama'] ?? '', $num) !== false) {
                        return ['pegawai' => $p, 'position' => $p['jabatan_nama']];
                    }
                }
            }
        }

        // 3. Pola Camat (Contoh: andinasrun@sinjaikab.go.id - Jabatan: CAMAT -> NASRUN, S.IP. Camat Sinjai Barat)
        if (strpos($username, 'camat') !== false || strpos($cleanName, 'CAMAT') !== false || strpos($cleanJabatan, 'CAMAT') !== false) {
            $camatList = [];
            foreach ($allPegawaiList as $p) {
                $jab = $p['jabatan_nama'] ?? '';
                if (preg_match('/\bCamat\b/i', $jab) && stripos($jab, 'Sekretaris') === false && stripos($jab, 'Seksi') === false) {
                    $camatList[] = $p;
                }
            }

            // Jika ada kecocokan nama camat (misal NASRUN dengan ANDI NASRUN)
            foreach ($camatList as $c) {
                $cNorm = $this->normalizeName($c['nama'] ?? '');
                $cNormNoAndi = trim(preg_replace('/^ANDI\s+/i', '', $cNorm));

                if ($normName === $cNorm || $normNameNoAndi === $cNormNoAndi || $normNameNoAndi === $cNorm || $normName === $cNormNoAndi) {
                    return ['pegawai' => $c, 'position' => $c['jabatan_nama']];
                }
            }

            // Jika akun ada di unit kerja kecamatan tertentu
            if (!empty($unitPegawaiList)) {
                foreach ($unitPegawaiList as $p) {
                    $jab = $p['jabatan_nama'] ?? '';
                    if (preg_match('/\bCamat\b/i', $jab) && stripos($jab, 'Sekretaris') === false && stripos($jab, 'Seksi') === false) {
                        return ['pegawai' => $p, 'position' => $jab];
                    }
                }
            }
        }

        // 4. Pola Kepala Dinas / Kepala Badan / Kadis / Kaban pada Unit Kerja
        if (strpos($username, 'kadis') !== false || strpos($username, 'kaban') !== false || strpos($cleanName, 'KEPALA DINAS') !== false || strpos($cleanName, 'KEPALA BADAN') !== false || strpos($cleanJabatan, 'KEPALA DINAS') !== false || strpos($cleanJabatan, 'KEPALA BADAN') !== false) {
            $searchList = !empty($unitPegawaiList) ? $unitPegawaiList : $allPegawaiList;
            foreach ($searchList as $p) {
                $jab = $p['jabatan_nama'] ?? '';
                if (stripos($jab, 'Kepala Dinas') !== false || stripos($jab, 'Kepala Badan') !== false) {
                    $pNorm = $this->normalizeName($p['nama'] ?? '');
                    $pNormNoAndi = trim(preg_replace('/^ANDI\s+/i', '', $pNorm));
                    if (empty($normName) || $normName === $pNorm || $normNameNoAndi === $pNormNoAndi || strpos($username, 'kadis') !== false || strpos($username, 'kaban') !== false) {
                        return ['pegawai' => $p, 'position' => $jab];
                    }
                }
            }
        }

        // 5. Pola Inspektur Daerah
        if (strpos($username, 'inspektur') !== false || strpos($cleanName, 'INSPEKTUR') !== false || strpos($cleanJabatan, 'INSPEKTUR') !== false) {
            $searchList = !empty($unitPegawaiList) ? $unitPegawaiList : $allPegawaiList;
            foreach ($searchList as $p) {
                $jab = $p['jabatan_nama'] ?? '';
                if (stripos($jab, 'Inspektur Daerah') !== false || (stripos($jab, 'Inspektur') !== false && stripos($jab, 'Pembantu') === false)) {
                    return ['pegawai' => $p, 'position' => $jab];
                }
            }
        }

        // 6. Pola Sekretaris Dinas / Sekretaris Badan (Sekdis / Sekban)
        if (strpos($username, 'sekdis') !== false || strpos($username, 'sekban') !== false || strpos($cleanName, 'SEKRETARIS DINAS') !== false || strpos($cleanName, 'SEKRETARIS BADAN') !== false || strpos($cleanJabatan, 'SEKRETARIS') !== false) {
            $searchList = !empty($unitPegawaiList) ? $unitPegawaiList : $allPegawaiList;
            foreach ($searchList as $p) {
                $jab = $p['jabatan_nama'] ?? '';
                if (stripos($jab, 'Sekretaris') !== false && stripos($jab, 'Daerah') === false && stripos($jab, 'Camat') === false) {
                    $pNorm = $this->normalizeName($p['nama'] ?? '');
                    $pNormNoAndi = trim(preg_replace('/^ANDI\s+/i', '', $pNorm));
                    if (empty($normName) || $normName === $pNorm || $normNameNoAndi === $pNormNoAndi) {
                        return ['pegawai' => $p, 'position' => $jab];
                    }
                }
            }
        }

        // 7. Pola Kepala Bagian (Kabag)
        if (strpos($username, 'kabag') !== false || strpos($cleanName, 'KEPALA BAGIAN') !== false || strpos($cleanJabatan, 'KEPALA BAGIAN') !== false) {
            $searchList = !empty($unitPegawaiList) ? $unitPegawaiList : $allPegawaiList;
            foreach ($searchList as $p) {
                $jab = $p['jabatan_nama'] ?? '';
                if (stripos($jab, 'Kepala Bagian') !== false) {
                    $pNorm = $this->normalizeName($p['nama'] ?? '');
                    $pNormNoAndi = trim(preg_replace('/^ANDI\s+/i', '', $pNorm));
                    if (empty($normName) || $normName === $pNorm || $normNameNoAndi === $pNormNoAndi) {
                        return ['pegawai' => $p, 'position' => $jab];
                    }
                }
            }
        }

        // 8. Pola Kepala Puskesmas / Kapus
        if (strpos($username, 'kapus') !== false || strpos($cleanName, 'KEPALA PUSKESMAS') !== false || strpos($cleanJabatan, 'KEPALA PUSKESMAS') !== false) {
            $searchList = !empty($unitPegawaiList) ? $unitPegawaiList : $allPegawaiList;
            foreach ($searchList as $p) {
                $jab = $p['jabatan_nama'] ?? '';
                if (stripos($jab, 'Kepala Puskesmas') !== false || stripos($jab, 'Kepala UPTD') !== false) {
                    $pNorm = $this->normalizeName($p['nama'] ?? '');
                    $pNormNoAndi = trim(preg_replace('/^ANDI\s+/i', '', $pNorm));
                    if (empty($normName) || $normName === $pNorm || $normNameNoAndi === $pNormNoAndi) {
                        return ['pegawai' => $p, 'position' => $jab];
                    }
                }
            }
        }

        // 9. Pola Lurah
        if (strpos($username, 'lurah') !== false || strpos($cleanName, 'LURAH') !== false || strpos($cleanJabatan, 'LURAH') !== false) {
            $searchList = !empty($unitPegawaiList) ? $unitPegawaiList : $allPegawaiList;
            foreach ($searchList as $p) {
                $jab = $p['jabatan_nama'] ?? '';
                if (preg_match('/\bLurah\b/i', $jab)) {
                    $pNorm = $this->normalizeName($p['nama'] ?? '');
                    $pNormNoAndi = trim(preg_replace('/^ANDI\s+/i', '', $pNorm));
                    if (empty($normName) || $normName === $pNorm || $normNameNoAndi === $pNormNoAndi) {
                        return ['pegawai' => $p, 'position' => $jab];
                    }
                }
            }
        }

        return null;
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
                    sleep(1);
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
        $normNoAndi = trim(preg_replace('/^ANDI\s+/i', '', $normAccName));

        foreach ($pegawaiList as $peg) {
            $pegName = $peg['nama'] ?? '';
            $normPegName = $this->normalizeName($pegName);
            $normPegNoAndi = trim(preg_replace('/^ANDI\s+/i', '', $normPegName));

            // 1. Exact Match setelah normalisasi
            if ($normAccName === $normPegName || (!empty($normNoAndi) && $normNoAndi === $normPegNoAndi)) {
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

        // 1. Hapus semua teks setelah koma (koma adalah pemisah standar nama dengan gelar-gelar belakang di SIMPEG)
        if (strpos($name, ',') !== false) {
            $name = substr($name, 0, strpos($name, ','));
        }

        $name = str_replace([',.', '.,', ';', '`', '\'', '"', '(', ')', '[', ']'], ' ', $name);

        // 2. Pisahkan gelar depan yang menempel (contoh: "DR.ANDI" -> "DR ANDI", "HJ.NURLINA" -> "HJ NURLINA")
        $name = preg_replace('/\b(PROF|DRS|DRA|DR|IR|HJ|H|DRH)\.?\s+/i', ' ', $name);

        // 3. Hapus titik di dalam singkatan/gelar (misal "S.KOM." -> "SKOM", "S.IP." -> "SIP", "M.A.P" -> "MAP", "S.P." -> "SP")
        $name = preg_replace('/(?<=[A-Z0-9])\.(?=[A-Z0-9])/i', '', $name);
        $name = preg_replace('/[\.,]/', ' ', $name);
        $name = preg_replace('/\s+/', ' ', trim($name));

        // Daftar gelar akademik, keagamaan, profesi, dan kehormatan
        $titles = [
            'PROF', 'DR', 'DRA', 'DRS', 'IR', 'H', 'HJ', 'HAJI', 'HAJJAH', 'DRH',
            'SKOM', 'SPD', 'SSOS', 'SSTP', 'SE', 'SH', 'SSI', 'SKM', 'STRKEB', 'STRGZ', 'STRTRA', 'STR', 'STRIP',
            'SAP', 'SIP', 'ST', 'SP', 'SAG', 'SKEP', 'STP', 'SS', 'SKED', 'SIKOM', 'SFARM', 'SPT', 'SPI', 'SM', 'SPKP', 'STPAR', 'SEI',
            'MSI', 'MPD', 'MM', 'MKOM', 'MAP', 'MTRAP', 'MKES', 'MH', 'MAG', 'MAK', 'MT', 'MIKOM', 'MP', 'MKM', 'MSC', 'MANIMSC', 'MLING',
            'AMD', 'AMDKEB', 'AMDKEP', 'AMDKL', 'AMDPK', 'AMKG', 'AMTEK', 'AMDPI', 'AMDRAD', 'AMKL',
            'NS', 'APT', 'GR', 'AP', 'SEK', 'IP', 'CGCAE', 'CGRE'
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

    private function extractYearFromEmail(string $email): ?int
    {
        $user = explode('@', $email)[0];
        if (preg_match('/(19\d{2}|20\d{2})$/', $user, $m)) {
            return (int)$m[1];
        }
        if (preg_match('/(\d{2})$/', $user, $m)) {
            $two = (int)$m[1];
            if ($two >= 40 && $two <= 99) return 1900 + $two;
            if ($two >= 0 && $two <= 30) return 2000 + $two;
        }
        return null;
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
