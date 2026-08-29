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
    protected $description = 'Simulate or apply automatic NIP matching for PNS accounts using SIMPEG API.';
    protected $usage = 'sync:match-nip [options]';
    protected $options = [
        '--apply'       => 'Execute updates to the database. Without this flag, runs in simulation mode.',
        '--unit'        => 'Target specific Unit Kerja ID (optional)',
        '--threshold'   => 'Similarity percentage threshold for fuzzy matching (default: 85)',
    ];

    private array $cachedApiPegawai = [];

    public function run(array $params)
    {
        $isApply = CLI::getOption('apply') !== null || in_array('--apply', $params);
        $unitFilter = CLI::getOption('unit') ?? $this->findParamValue($params, 'unit=');
        $threshold = (float)(CLI::getOption('threshold') ?? $this->findParamValue($params, 'threshold=') ?? 85);

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

        // 1. Ambil ID status PNS
        $pnsStatus = $statusAsnModel->where('nama_status_asn', 'PNS')->first();
        $pnsId = $pnsStatus['id'] ?? 1;

        // 2. Query akun PNS tanpa NIP
        $builder = $emailModel->select('emails.id, emails.email, emails.name, emails.nip, emails.unit_kerja_id, emails.status_asn_id, unit_kerja.nama_unit_kerja, unit_kerja.api_unit_id, unit_kerja.parent_id')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
            ->where('emails.deleted_at IS NULL')
            ->groupStart()
                ->where('emails.status_asn_id', $pnsId)
                ->orWhere('emails.status_asn_id IS NULL')
            ->groupEnd()
            ->groupStart()
                ->where('emails.nip IS NULL')
                ->orWhere('emails.nip', '')
            ->groupEnd();

        if (!empty($unitFilter)) {
            $builder->where('emails.unit_kerja_id', $unitFilter);
        }

        $accounts = $builder->findAll();
        $totalAccounts = count($accounts);

        CLI::write("Total akun PNS tanpa NIP dievaluasi: $totalAccounts", 'cyan');
        if ($totalAccounts === 0) {
            CLI::write("Semua akun PNS sudah memiliki NIP atau tidak ada data yang memenuhi kriteria.", 'green');
            return;
        }

        // 3. Pre-fetch unit data & mapping
        $allUnits = $unitModel->findAll();
        $unitsById = [];
        foreach ($allUnits as $u) {
            $unitsById[$u['id']] = $u;
        }

        // Kumpulkan semua api_unit_id yang dibutuhkan
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
        CLI::write("Mengambil master data pegawai untuk $totalUnitsToFetch Unit Kerja dari API SIMPEG...", 'yellow');

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

            if ($matchResult['type'] === 'EXACT') {
                $exactMatches[] = [
                    'account' => $account,
                    'matched' => $matchResult['pegawai'],
                    'score'   => 100,
                ];
            } elseif ($matchResult['type'] === 'FUZZY') {
                $fuzzyMatches[] = [
                    'account' => $account,
                    'matched' => $matchResult['pegawai'],
                    'score'   => $matchResult['score'],
                ];
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
            CLI::write("php spark sync:match-nip --apply\n", 'cyan');
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

        // Daftar gelar akademik, keagamaan, dan kehormatan umum
        $titles = [
            'PROF.', 'PROF', 'DR.', 'DRA.', 'DRS.', 'DR', 'DRA', 'DRS',
            'IR.', 'IR', 'H.', 'HJ.', 'H', 'HJ',
            'S.KOM.', 'S.KOM', 'S.PD.', 'S.PD', 'S.SOS.', 'S.SOS',
            'S.STP.', 'S.STP', 'S.E.', 'S.E', 'S.H.', 'S.H', 'S.SI.', 'S.SI',
            'S.KM.', 'S.KM', 'S.TR.KEB.', 'S.TR.KEB', 'S.TR.GZ.', 'S.TR.GZ',
            'S.AP.', 'S.AP', 'S.IP.', 'S.IP', 'S.T.', 'S.T', 'S.P.', 'S.P',
            'S.AG.', 'S.AG', 'S.KEP.', 'S.KEP', 'NS.', 'NS',
            'M.SI.', 'M.SI', 'M.PD.', 'M.PD', 'M.M.', 'M.M', 'M.KOM.', 'M.KOM',
            'M.AP.', 'M.AP', 'M.TR.AP.', 'M.TR.AP', 'M.KES.', 'M.KES', 'M.H.', 'M.H', 'M.AG.', 'M.AG',
            'A.MD.', 'A.MD', 'A.MD.KEB', 'A.MD.KEP', 'AMD.', 'AMD',
            'SKM', 'SE', 'SH', 'ST', 'SI', 'SP', 'MM', 'MSI', 'MPD', 'MH', 'MAP'
        ];

        // Hapus karakter pemisah umum
        $name = str_replace([',', ';', '`', '\''], ' ', $name);

        // Hapus gelar dengan regex atau replace kata per kata
        $words = preg_split('/\s+/', $name);
        $cleanWords = [];

        foreach ($words as $w) {
            $cleanW = trim($w, '. ');
            if (empty($cleanW)) continue;

            if (in_array($cleanW, $titles) || in_array($w, $titles)) {
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

        return implode(' ', $cleanWords);
    }

    private function findParamValue(array $params, string $prefix): ?string
    {
        foreach ($params as $param) {
            if (strpos($param, $prefix) === 0) {
                return substr($param, strlen($prefix));
            }
        }
        return null;
    }
}
