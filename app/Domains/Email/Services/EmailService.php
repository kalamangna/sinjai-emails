<?php

namespace App\Domains\Email\Services;

use App\Domains\Email\Models\EmailModel;
use App\Domains\Email\Models\PkModel;
use App\Domains\UnitKerja\Models\UnitKerjaModel;
use App\Shared\Models\StatusAsnModel;
use App\Shared\Models\EselonModel;
use Exception;

class EmailService
{
    protected $emailModel;
    protected $unitKerjaModel;
    protected $statusAsnModel;
    protected $eselonModel;
    protected $pkModel;

    public function __construct()
    {
        $this->emailModel = new EmailModel();
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->statusAsnModel = new StatusAsnModel();
        $this->eselonModel = new EselonModel();
        $this->pkModel = new PkModel();
    }

    public function getGlobalNavigationData()
    {
        $parentUnitKerjaList = $this->unitKerjaModel->where('parent_id IS NULL')->orderBy('nama_unit_kerja', 'ASC')->asArray()->findAll();

        // Aggregate all unit_kerja emails count (including children) in one go
        $allUnits = $this->unitKerjaModel->select('id, parent_id')->asArray()->findAll();
        $unitMap = [];
        foreach ($allUnits as $u) {
            $parentId = $u['parent_id'] ?: $u['id'];
            if (!isset($unitMap[$parentId])) $unitMap[$parentId] = [];
            $unitMap[$parentId][] = $u['id'];
            if ($u['parent_id']) $unitMap[$u['id']][] = $u['id'];
        }

        $emailCountsByUnit = $this->emailModel->allowCallbacks(false)->select('unit_kerja_id, COUNT(id) as count')->groupBy('unit_kerja_id')->asArray()->findAll();
        $countMap = [];
        foreach ($emailCountsByUnit as $row) {
            $countMap[$row['unit_kerja_id']] = (int)$row['count'];
        }

        $unitKerjaList = [];
        foreach ($parentUnitKerjaList as $parentUnit) {
            $parentId = $parentUnit['id'];
            $childrenIds = $unitMap[$parentId] ?? [$parentId];
            $allUnitIds = array_unique($childrenIds);

            $emailCount = 0;
            foreach ($allUnitIds as $uid) {
                $emailCount += $countMap[$uid] ?? 0;
            }

            $parentUnit['email_count'] = $emailCount;
            $unitKerjaList[] = $parentUnit;
        }

        $allEselonOptions = $this->eselonModel->orderBy('nama_eselon', 'ASC')->asArray()->findAll();

        // Aggregate all eselon counts in one go
        $eselonCountsRaw = $this->emailModel->allowCallbacks(false)->select('eselon_id, COUNT(id) as count')->where('eselon_id IS NOT NULL')->groupBy('eselon_id')->asArray()->findAll();
        $eselonCountMap = [];
        foreach ($eselonCountsRaw as $row) {
            $eselonCountMap[$row['eselon_id']] = (int)$row['count'];
        }

        $eselonCounts = [];
        foreach ($allEselonOptions as $option) {
            $eselonCounts[] = [
                'id' => $option['id'],
                'name' => $option['nama_eselon'],
                'count' => $eselonCountMap[$option['id']] ?? 0
            ];
        }

        return [
            'unit_kerja_nav' => $unitKerjaList,
            'eselon_nav' => $eselonCounts
        ];
    }

    public function getEmailDashboardData($search = null, $bsre_status = null, $perPage = 100, $disk_usage = null)
    {
        $builder = $this->emailModel->withDetails();

        if (!empty($search)) {
            $builder->groupStart();
            
            // Normalize query for numeric search (NIP/NIK often have spaces or dots)
            $cleanSearch = str_replace([' ', '.', '-', '\''], '', $search);

            // Always allow searching by name and email
            $builder->like('email', $search)
                    ->orLike('name', $search);

            // If numeric and looks like NIK/NIP, use exact match on hash
            if (is_numeric($cleanSearch) && (strlen($cleanSearch) >= 10)) {
                $hash = $cleanSearch;
                $builder->orWhere('nik', $hash)
                        ->orWhere('nip', $hash);
            }
            
            $builder->groupEnd();
        }

        if ($bsre_status) {
            $builder->groupStart();
            
            if ($bsre_status === 'non_tte') {
                // Criteria for accounts that DO NOT need TTE
                $builder->groupStart()
                            ->where('nip IS NULL')
                            ->orWhere('nip', '')
                        ->groupEnd()
                        ->where('pimpinan', 0)
                        ->where('pimpinan_desa', 0)
                        ->groupStart()
                            ->where('unit_kerja_id IS NULL')
                            ->orWhere('unit_kerja_id', 0)
                        ->groupEnd();
            } else {
                // Criteria for accounts that NEED TTE
                $builder->groupStart()
                            ->groupStart()
                                ->where('nip IS NOT NULL')
                                ->where('nip !=', '')
                            ->groupEnd()
                            ->orWhere('pimpinan', 1)
                            ->orWhere('pimpinan_desa', 1)
                            ->orGroupStart()
                                ->where('unit_kerja_id IS NOT NULL')
                                ->where('unit_kerja_id !=', 0)
                            ->groupEnd()
                        ->groupEnd();

                if ($bsre_status === 'not_synced') {
                    $builder->groupStart()
                                ->where('bsre_status IS NULL')
                                ->orWhere('bsre_status', '')
                            ->groupEnd();
                } else {
                    $builder->where('bsre_status', $bsre_status);
                }
            }
            
            $builder->groupEnd();
        }

        // Filter berdasarkan penggunaan disk
        if (!empty($disk_usage)) {
            if ($disk_usage === 'critical') {
                $builder->where('emails.diskusedpercent_float >=', 85);
            } elseif ($disk_usage === 'full') {
                $builder->where('emails.diskusedpercent_float >=', 95);
            }
        }

        // Get filtered count BEFORE pagination
        $filtered_count = $builder->countAllResults(false);

        $builder->orderBy('mtime', 'DESC');

        $emails = $builder->paginate($perPage);
        $pager = $this->emailModel->pager;

        $counts = $this->emailModel->allowCallbacks(false)->select('COUNT(id) as total_emails, SUM(CASE WHEN suspended_login = 0 THEN 1 ELSE 0 END) as active_count, SUM(CASE WHEN suspended_login = 1 THEN 1 ELSE 0 END) as suspended_count, SUM(CASE WHEN bsre_status = "ISSUE" THEN 1 ELSE 0 END) as active_bsre_count')->asArray()->first();

        // Use cache for dashboard summaries
        $cache = \Config\Services::cache();
        $cacheKey = 'email_dashboard_summary';
        if (!$summaryData = $cache->get($cacheKey)) {
            $parentUnitKerjaList = $this->unitKerjaModel->where('parent_id IS NULL')->orderBy('nama_unit_kerja', 'ASC')->asArray()->findAll();

            $allUnits = $this->unitKerjaModel->select('id, parent_id')->asArray()->findAll();
            $unitMap = [];
            foreach ($allUnits as $u) {
                $parentId = $u['parent_id'] ?: $u['id'];
                if (!isset($unitMap[$parentId])) $unitMap[$parentId] = [];
                $unitMap[$parentId][] = $u['id'];
                if ($u['parent_id']) $unitMap[$u['id']][] = $u['id'];
            }

            $emailCountsByUnit = $this->emailModel->allowCallbacks(false)->select('unit_kerja_id, COUNT(id) as count')->groupBy('unit_kerja_id')->asArray()->findAll();
            $countMap = [];
            foreach ($emailCountsByUnit as $row) {
                $countMap[$row['unit_kerja_id']] = (int)$row['count'];
            }

            $unitKerjaList = [];
            foreach ($parentUnitKerjaList as $parentUnit) {
                $parentId = $parentUnit['id'];
                $childrenIds = $unitMap[$parentId] ?? [$parentId];
                $allUnitIds = array_unique($childrenIds);

                $emailCount = 0;
                foreach ($allUnitIds as $uid) {
                    $emailCount += $countMap[$uid] ?? 0;
                }

                $parentUnit['email_count'] = $emailCount;
                $unitKerjaList[] = $parentUnit;
            }

            // Optimize Status ASN Counts
            $allStatusAsnOptions = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->asArray()->findAll();
            $asnCountsRaw = $this->emailModel->allowCallbacks(false)->select('status_asn_id, COUNT(id) as count')->where('status_asn_id IS NOT NULL')->groupBy('status_asn_id')->asArray()->findAll();
            $asnCountMap = [];
            foreach ($asnCountsRaw as $row) {
                $asnCountMap[$row['status_asn_id']] = (int)$row['count'];
            }
            $statusAsnCounts = [];
            foreach ($allStatusAsnOptions as $option) {
                $statusAsnCounts[] = [
                    'id' => $option['id'],
                    'name' => $option['nama_status_asn'],
                    'count' => $asnCountMap[$option['id']] ?? 0
                ];
            }

            // Custom sort for Status ASN
            $asnOrder = ['PNS', 'PPPK', 'PPPK PARUH WAKTU'];
            usort($statusAsnCounts, function ($a, $b) use ($asnOrder) {
                $posA = array_search(strtoupper($a['name']), $asnOrder);
                $posB = array_search(strtoupper($b['name']), $asnOrder);

                if ($posA === false) $posA = 999;
                if ($posB === false) $posB = 999;

                if ($posA === $posB) return strcmp($a['name'], $b['name']);
                return $posA - $posB;
            });

            // Optimize Eselon Counts
            $allEselonOptions = $this->eselonModel->orderBy('nama_eselon', 'ASC')->asArray()->findAll();
            $eselonCountsRaw = $this->emailModel->allowCallbacks(false)->select('eselon_id, COUNT(id) as count')->where('eselon_id IS NOT NULL')->groupBy('eselon_id')->asArray()->findAll();
            $eselonCountMap = [];
            foreach ($eselonCountsRaw as $row) {
                $eselonCountMap[$row['eselon_id']] = (int)$row['count'];
            }
            $eselonCounts = [];
            foreach ($allEselonOptions as $option) {
                $eselonCounts[] = [
                    'id' => $option['id'],
                    'name' => $option['nama_eselon'],
                    'count' => $eselonCountMap[$option['id']] ?? 0
                ];
            }

            $rawBsreCounts = $this->emailModel->allowCallbacks(false)
                ->select('bsre_status, pimpinan, pimpinan_desa, nip, COUNT(id) as count')
                ->groupBy('bsre_status')
                ->groupBy('pimpinan')
                ->groupBy('pimpinan_desa')
                ->groupBy('nip')
                ->asArray()
                ->findAll();

            $bsre_status_labels = [
                'ISSUE' => 'ISSUE',
                'EXPIRED' => 'EXPIRED',
                'NO_CERTIFICATE' => 'NO_CERTIFICATE',
                'NOT_REGISTERED' => 'NOT_REGISTERED',
                'not_synced' => 'NOT_SYNCED',
                'non_tte' => 'NON_TTE'
            ];

            $bsreStatusCounts = [];
            $notSyncedCount = 0;
            $nonTteCount = 0;
            
            foreach ($rawBsreCounts as $row) {
                $isNeedTte = !empty($row['nip']) || ($row['pimpinan'] == 1) || ($row['pimpinan_desa'] == 1) || !empty($row['unit_kerja_id']);
                
                if (!$isNeedTte) {
                    $nonTteCount += $row['count'];
                } elseif (empty($row['bsre_status'])) {
                    $notSyncedCount += $row['count'];
                } else {
                    $status = $row['bsre_status'];
                    if (!isset($bsreStatusCounts[$status])) {
                        $bsreStatusCounts[$status] = [
                            'status' => $status,
                            'label' => $bsre_status_labels[$status] ?? $status,
                            'count' => 0
                        ];
                    }
                    $bsreStatusCounts[$status]['count'] += (int)$row['count'];
                }
            }
            
            // Convert to indexed array
            $bsreStatusCounts = array_values($bsreStatusCounts);

            if ($notSyncedCount > 0) {
                $bsreStatusCounts[] = [
                    'status' => 'not_synced',
                    'label' => 'NOT_SYNCED',
                    'count' => $notSyncedCount
                ];
            }
            
            if ($nonTteCount > 0) {
                $bsreStatusCounts[] = [
                    'status' => 'non_tte',
                    'label' => 'NON_TTE',
                    'count' => $nonTteCount
                ];
            }

            // Custom sort for BSrE Status
            $tteOrder = ['ISSUE', 'EXPIRED', 'NO_CERTIFICATE', 'NOT_REGISTERED', 'not_synced', 'non_tte'];
            usort($bsreStatusCounts, function ($a, $b) use ($tteOrder) {
                $posA = array_search($a['status'], $tteOrder);
                $posB = array_search($b['status'], $tteOrder);

                if ($posA === false) $posA = 999;
                if ($posB === false) $posB = 999;

                if ($posA === $posB) return strcmp($a['label'], $b['label']);
                return $posA - $posB;
            });

            $summaryData = [
                'unitKerjaList' => $unitKerjaList,
                'status_asn_counts' => $statusAsnCounts,
                'eselon_counts' => $eselonCounts,
                'bsre_status_counts' => $bsreStatusCounts,
                'bsre_status_labels' => $bsre_status_labels
            ];

            $cache->save($cacheKey, $summaryData, 600); // 10 mins cache
        }

        return [
            'emails' => $emails,
            'pager' => $pager,
            'total_emails' => $counts['total_emails'] ?? 0,
            'filtered_count' => $filtered_count,
            'active_count' => $counts['active_count'] ?? 0,
            'active_bsre_count' => $counts['active_bsre_count'] ?? 0,
            'unitKerjaList' => $summaryData['unitKerjaList'],
            'status_asn_counts' => $summaryData['status_asn_counts'],
            'eselon_counts' => $summaryData['eselon_counts'],
            'bsre_status_counts' => $summaryData['bsre_status_counts'],
            'bsre_status_labels' => $summaryData['bsre_status_labels']
        ];
    }

    public function getEmailDetail($username)
    {
        $email_detail = $this->emailModel->withDetails()->where('user', $username)->first();
        if (!$email_detail) {
            throw new Exception('Email tidak ditemukan di database lokal.');
        }

        $unit_kerja = null;
        if (!empty($email_detail['unit_kerja_id'])) {
            $unit_kerja = $this->unitKerjaModel->find($email_detail['unit_kerja_id']);
        }

        $parent_unit_kerja = null;
        if (!empty($unit_kerja['parent_id'])) {
            $parent_unit_kerja = $this->unitKerjaModel->find($unit_kerja['parent_id']);
        }

        $pk_data = $this->pkModel->where('email', $email_detail['email'])->first();

        return [
            'email' => $email_detail,
            'unit_kerja' => $unit_kerja,
            'parent_unit_kerja' => $parent_unit_kerja,
            'pk_data' => $pk_data,
            'unit_kerja_options' => $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->asArray()->findAll(),
            'status_asn_options' => $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->asArray()->findAll(),
            'eselon_options' => $this->eselonModel->orderBy('nama_eselon', 'ASC')->asArray()->findAll(),
        ];
    }

    public function getUnitKerjaDetail($unitKerjaId, $params = [])
    {
        $unitKerja = $this->unitKerjaModel->find($unitKerjaId);
        if (!$unitKerja) {
            throw new Exception('Unit Kerja not found.');
        }

        $children = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->asArray()->findAll();
        usort($children, function ($a, $b) {
            return strnatcasecmp($a['nama_unit_kerja'] ?? '', $b['nama_unit_kerja'] ?? '');
        });

        $childrenIds = array_column($children, 'id');
        $allUnitIds = array_merge([$unitKerjaId], $childrenIds);

        $perPage = $params['per_page'] ?? 100;
        $search = $params['search'] ?? null;
        $status_asn = $params['status_asn'] ?? null;
        $bsre_status = $params['bsre_status'] ?? null;
        $pimpinan_desa = $params['pimpinan_desa'] ?? 1;
        $password_status = $params['password_status'] ?? null;
        $sub_unit = $params['sub_unit'] ?? 'with';

        $targetUnitIds = (!empty($childrenIds) && $sub_unit === 'without') ? [$unitKerjaId] : $allUnitIds;

        $isKecamatan = stripos($unitKerja['nama_unit_kerja'], 'Kecamatan') !== false;

        // Start building the query for the emails list
        $emailBuilder = $this->emailModel->withDetails()->whereIn('emails.unit_kerja_id', $targetUnitIds);
        if ($isKecamatan && $pimpinan_desa == 0) {
            $emailBuilder->where('emails.pimpinan_desa', 0);
        }

        $applyFilters = function($builder) use ($search, $status_asn, $bsre_status, $password_status) {
            if ($search) {
                $builder->groupStart();
                $cleanSearch = str_replace([' ', '.', '-', '\''], '', $search);
                $builder->like('emails.email', $search)
                             ->orLike('emails.name', $search)
                             ->orLike('emails.jabatan', $search);
                if (is_numeric($cleanSearch) && (strlen($cleanSearch) >= 10)) {
                    $hash = $cleanSearch;
                    $builder->orWhere('emails.nik', $hash)
                                 ->orWhere('emails.nip', $hash);
                }
                $builder->groupEnd();
            }

            if ($status_asn) {
                $builder->where('emails.status_asn_id', $status_asn);
            }

            if ($bsre_status) {
                $builder->groupStart();
                if ($bsre_status === 'non_tte') {
                    $builder->groupStart()
                                    ->where('emails.nip IS NULL')
                                    ->orWhere('emails.nip', '')
                                ->groupEnd()
                                ->where('emails.pimpinan', 0)
                                ->where('emails.pimpinan_desa', 0)
                                ->groupStart()
                                    ->where('emails.unit_kerja_id IS NULL')
                                    ->orWhere('emails.unit_kerja_id', 0)
                                ->groupEnd();
                } else {
                    $builder->groupStart()
                                    ->groupStart()
                                        ->where('emails.nip IS NOT NULL')
                                        ->where('emails.nip !=', '')
                                    ->groupEnd()
                                    ->orWhere('emails.pimpinan', 1)
                                    ->orWhere('emails.pimpinan_desa', 1)
                                    ->orGroupStart()
                                        ->where('emails.unit_kerja_id IS NOT NULL')
                                        ->where('emails.unit_kerja_id !=', 0)
                                    ->groupEnd()
                                ->groupEnd();

                    if ($bsre_status === 'not_synced') {
                        $builder->groupStart()
                                        ->where('emails.bsre_status IS NULL')
                                        ->orWhere('emails.bsre_status', '')
                                    ->groupEnd();
                    } else {
                        $builder->where('emails.bsre_status', $bsre_status);
                    }
                }
                $builder->groupEnd();
            }

            if ($password_status === 'empty') {
                $builder->groupStart()
                    ->where('emails.password IS NULL')
                    ->orWhere('emails.password', '')
                ->groupEnd();
            } elseif ($password_status === 'filled') {
                $builder->where('emails.password IS NOT NULL')
                        ->where('emails.password !=', '');
            }
        };

        // Apply filters to the list query
        $applyFilters($emailBuilder);

        // Get filtered count BEFORE pagination
        $filtered_count = $emailBuilder->countAllResults(false);

        // Determine if we should show the Unit Kerja column (if there's more than one unit involved)
        $showUnitKerjaColumn = !empty($childrenIds) && $sub_unit !== 'without';

        // Sorting logic
        if ($showUnitKerjaColumn) {
            $emailBuilder->orderBy('emails.eselon_id IS NULL', 'ASC', false)
                        ->orderBy('emails.eselon_id', 'ASC')
                        ->orderBy('emails.status_asn_id IS NULL', 'ASC', false)
                        ->orderBy('emails.status_asn_id', 'ASC')
                        ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
                        ->orderBy('emails.name', 'ASC');
        } else {
            $emailBuilder->orderBy('emails.eselon_id IS NULL', 'ASC', false)
                        ->orderBy('emails.eselon_id', 'ASC')
                        ->orderBy('emails.status_asn_id IS NULL', 'ASC', false)
                        ->orderBy('emails.status_asn_id', 'ASC')
                        ->orderBy('emails.name', 'ASC');
        }

        $emails = $emailBuilder->paginate($perPage);

        $pager = $this->emailModel->pager;

        $bsre_status_options = [
            'ISSUE' => 'ISSUE',
            'EXPIRED' => 'EXPIRED',
            'NO_CERTIFICATE' => 'NO_CERTIFICATE',
            'NOT_REGISTERED' => 'NOT_REGISTERED',
            'not_synced' => 'NOT_SYNCED',
            'non_tte' => 'NON_TTE'
        ];

        $bsre_status_counts = [];

        // Calculate stats for the unit (affected by filters)
        $statsBuilder = $this->emailModel->whereIn('emails.unit_kerja_id', $targetUnitIds);
        if ($isKecamatan && $pimpinan_desa == 0) {
            $statsBuilder->where('emails.pimpinan_desa', 0);
        }
        $applyFilters($statsBuilder);

        $rawCounts = $statsBuilder->allowCallbacks(false)
            ->select('
                CASE 
                    WHEN (nip IS NULL OR nip = "") AND pimpinan = 0 AND pimpinan_desa = 0 AND (unit_kerja_id IS NULL OR unit_kerja_id = 0) THEN "non_tte"
                    WHEN (bsre_status IS NULL OR bsre_status = "") THEN "not_synced"
                    ELSE bsre_status 
                END as derived_status,
                COUNT(id) as count
            ')
            ->groupBy('derived_status')
            ->findAll();

        foreach ($rawCounts as $row) {
            $statusKey = $row['derived_status'];

            if (!isset($bsre_status_counts[$statusKey])) {
                $bsre_status_counts[$statusKey] = [
                    'label' => $bsre_status_options[$statusKey] ?? $statusKey,
                    'count' => 0
                ];
            }
            $bsre_status_counts[$statusKey]['count'] += (int)$row['count'];
        }

        $tteOrder = ['ISSUE', 'EXPIRED', 'NO_CERTIFICATE', 'NOT_REGISTERED', 'not_synced', 'non_tte'];
        uksort($bsre_status_counts, function ($a, $b) use ($tteOrder) {
            $posA = array_search($a, $tteOrder);
            $posB = array_search($b, $tteOrder);

            if ($posA === false) $posA = 999;
            if ($posB === false) $posB = 999;

            if ($posA === $posB) return strcmp($a, $b);
            return $posA - $posB;
        });

        $active_bsre_count = $bsre_status_counts['ISSUE']['count'] ?? 0;
        $total_emails_in_unit = array_sum(array_column($bsre_status_counts, 'count'));

        // Calculate ASN Status stats for the unit
        $asnStatsBuilder = $this->emailModel->whereIn('emails.unit_kerja_id', $targetUnitIds);
        if ($isKecamatan && $pimpinan_desa == 0) {
            $asnStatsBuilder->where('emails.pimpinan_desa', 0);
        }
        $applyFilters($asnStatsBuilder);
        $rawAsnStats = $asnStatsBuilder->allowCallbacks(false)
            ->select('status_asn.nama_status_asn as label, COUNT(emails.id) as count')
            ->join('status_asn', 'status_asn.id = emails.status_asn_id', 'left')
            ->groupBy('status_asn.nama_status_asn')
            ->findAll();

        $status_asn_stats = [];
        foreach ($rawAsnStats as $stat) {
            $status_asn_stats[] = [
                'label' => $stat['label'] ?: 'LAINNYA',
                'count' => (int)$stat['count']
            ];
        }

        // Custom sort for Status ASN
        $asnOrder = ['PNS', 'PPPK', 'PPPK PARUH WAKTU'];
        usort($status_asn_stats, function ($a, $b) use ($asnOrder) {
            $posA = array_search(strtoupper($a['label']), $asnOrder);
            $posB = array_search(strtoupper($b['label']), $asnOrder);

            if ($posA === false) $posA = 999;
            if ($posB === false) $posB = 999;

            if ($posA === $posB) return strcmp($a['label'], $b['label']);
            return $posA - $posB;
        });

        // Calculate actual active count (suspended_login = 0)
        $activeStatsBuilder = $this->emailModel->whereIn('emails.unit_kerja_id', $targetUnitIds);
        if ($isKecamatan && $pimpinan_desa == 0) {
            $activeStatsBuilder->where('emails.pimpinan_desa', 0);
        }
        $applyFilters($activeStatsBuilder);
        $active_count = $activeStatsBuilder->where('emails.suspended_login', 0)->countAllResults();

        return [
            'unit_kerja' => $unitKerja,
            'parent_unit' => !empty($unitKerja['parent_id']) ? $this->unitKerjaModel->find($unitKerja['parent_id']) : null,
            'child_units' => $children,
            'emails' => $emails,
            'total_emails' => $total_emails_in_unit,
            'filtered_count' => $filtered_count,
            'active_count' => $active_count,
            'active_bsre_count' => $active_bsre_count,
            'status_asn_stats' => $status_asn_stats,
            'showUnitKerjaColumn' => $showUnitKerjaColumn,
            'pager' => $pager,
            'status_asn_options' => $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll(),
            'bsre_status_options' => $bsre_status_options,
            'bsre_status_counts' => $bsre_status_counts,
            'sub_unit' => $sub_unit,
        ];
    }

    public function createSingleEmail(array $data)
    {
        $cpanelApi = new \App\Shared\Libraries\CpanelApi();

        $existing_email = $this->emailModel->where('email', $data['email'])->first();
        if ($existing_email) throw new Exception('Email already exists in local database.');

        if (!empty($data['nik'])) {
            $cleanNik = str_replace([' ', '.', '-', '\''], '', $data['nik']);
            $existing_nik = $this->emailModel->where('nik', $cleanNik)->first();
            if ($existing_nik) {
                throw new Exception('NIK already exists in local database.');
            }
            $data['nik'] = $cleanNik;
        }

        if (!empty($data['nip'])) {
            $cleanNip = str_replace([' ', '.', '-', '\''], '', $data['nip']);
            $existing_nip = $this->emailModel->where('nip', $cleanNip)->first();
            if ($existing_nip) {
                throw new Exception('NIP sudah digunakan oleh akun lain (' . $existing_nip['email'] . ').');
            }
            $data['nip'] = $cleanNip;
        }

        try {
            $cpanelApi->create_email_account($data['email'], $data['password'], $data['quota'] ?? 1024);
        } catch (\Throwable $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                throw new Exception('Email already exists on cPanel.');
            }
            throw $e;
        }

        $unitKerjaId = null;
        if (!empty($data['unitKerja'])) {
            $unit = $this->unitKerjaModel->where('nama_unit_kerja', $data['unitKerja'])->first();
            if ($unit) $unitKerjaId = $unit['id'];
        }

        try {
            $insertId = $this->emailModel->insert([
                'email'      => $data['email'],
                'user'       => explode('@', $data['email'])[0],
                'domain'     => explode('@', $data['email'])[1],
                'unit_kerja_id' => $unitKerjaId,
                'password'   => $data['password'] ?? null,
                'nik'        => $data['nik'] ?? null,
                'nip'        => $data['nip'] ?? null,
                'name'       => !empty($data['name']) ? mb_strtoupper($data['name'], 'UTF-8') : null,
                'jabatan'    => !empty($data['jabatan']) ? mb_strtoupper($data['jabatan'], 'UTF-8') : null,
                'status_asn_id' => $data['jenisFormasi'] ?? null,
                'gelar_depan' => $data['gelar_depan'] ?? null,
                'gelar_belakang' => $data['gelar_belakang'] ?? null,
                'tempat_lahir' => $data['tempat_lahir'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'pendidikan' => $data['pendidikan'] ?? null,
                'golongan' => $data['golongan'] ?? null,
            ]);

            if (!$insertId) {
                // If local insert fails, we should try to remove the cPanel account to keep them in sync
                try {
                    $cpanelApi->delete_email_account($data['email']);
                } catch (\Throwable $e2) {
                    log_message('error', 'Cleanup failed after local insert failure for ' . $data['email'] . ': ' . $e2->getMessage());
                }
                throw new Exception('Gagal menyimpan data ke database lokal.');
            }

            // Send Telegram Notification
            try {
                $unitKerjaName = '';
                if ($unitKerjaId) {
                    $unit = $this->unitKerjaModel->find($unitKerjaId);
                    $unitKerjaName = $unit['nama_unit_kerja'] ?? '';
                }

                $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
                $builder->setTitle('AKUN EMAIL BARU DIBUAT', '✅')
                        ->addDivider()
                        ->addUserProfile(
                            $data['name'] ?? '',
                            '',
                            $data['jabatan'] ?? '',
                            $unitKerjaName,
                            $data['email']
                        );

                $telegram = new \App\Shared\Libraries\TelegramLibrary();
                $telegram->sendMessage($builder->build());
            } catch (\Throwable $te) {
                log_message('error', 'Failed to send Telegram notification for new account: ' . $te->getMessage());
            }

            return $insertId;
        } catch (\Throwable $e) {
            // If it's a DB error (like duplicate NIK), also try to cleanup cPanel
            if (strpos($e->getMessage(), 'Duplicate entry') !== false || strpos($e->getMessage(), 'database') !== false) {
                try {
                    $cpanelApi->delete_email_account($data['email']);
                } catch (\Throwable $e2) {
                    log_message('error', 'Cleanup failed after DB exception for ' . $data['email'] . ': ' . $e2->getMessage());
                }
            }
            throw $e;
        }
    }

    public function updateProfileDetails(string $username, array $profileData)
    {
        $newUser = explode('@', $profileData['email'])[0];

        $db = \Config\Database::connect();
        $db->transStart();

        $sourceRecord = $this->emailModel->where('user', $username)->first();
        if (!$sourceRecord) throw new Exception('Akun asal tidak ditemukan.');

        if (!empty($profileData['nik'])) {
            $cleanNik = str_replace([' ', '.', '-', '\''], '', $profileData['nik']);
            $profileData['nik'] = $cleanNik;
            if ($cleanNik !== $sourceRecord['nik']) {
                $existingNik = $this->emailModel->where('nik', $cleanNik)->where('id !=', $sourceRecord['id'])->first();
                if ($existingNik) {
                    throw new Exception('NIK sudah digunakan oleh akun lain (' . $existingNik['email'] . ').');
                }
            }
        }

        if (!empty($profileData['nip'])) {
            $cleanNip = str_replace([' ', '.', '-', '\''], '', $profileData['nip']);
            $profileData['nip'] = $cleanNip;
            if ($cleanNip !== $sourceRecord['nip']) {
                $existingNip = $this->emailModel->where('nip', $cleanNip)->where('id !=', $sourceRecord['id'])->first();
                if ($existingNip) {
                    throw new Exception('NIP sudah digunakan oleh akun lain (' . $existingNip['email'] . ').');
                }
            }
        }

        // 1. Handle username change in cPanel if needed
        if ($newUser !== $username) {
            $cpanelApi = new \App\Shared\Libraries\CpanelApi();
            $result = $cpanelApi->rename_email_account($username . '@sinjaikab.go.id', $newUser);
            if (!$result['success']) {
                throw new Exception('Gagal mengubah username di cPanel: ' . $result['message']);
            }
        }

        // 2. Update primary record
        if ($this->emailModel->update($sourceRecord['id'], $profileData) === false) {
            $errors = $this->emailModel->errors();
            throw new Exception('Gagal menyimpan data utama. ' . implode(', ', $errors));
        }

        // 3. Sync personal data to other records sharing the same NIP
        if (!empty($profileData['nip'])) {
            $syncData = $profileData;
            unset($syncData['email'], $syncData['user'], $syncData['jabatan']);
            unset($syncData['unit_kerja_id'], $syncData['eselon_id']);
            unset($syncData['pimpinan'], $syncData['pimpinan_desa']);

            $cleanNip = str_replace([' ', '.', '-', "'"], '', $profileData['nip']);
            $this->emailModel->where('nip', $cleanNip)
                             ->where('id !=', $sourceRecord['id'])
                             ->set($syncData)
                             ->update();
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            $error = $db->error();
            throw new Exception('Gagal menyimpan data ke database. Detail: ' . ($error['message'] ?? 'Unknown SQL error'));
        }

        return $newUser;
    }

    public function updateEmailDetails($username, array $updateData)
    {
        $email = $this->emailModel->where('user', $username)->first();
        if (!$email) throw new Exception('Akun email tidak ditemukan.');

        return $this->emailModel->update($email['id'], $updateData);
    }

    public function updatePassword($username, $newPassword)
    {
        $cpanelApi = new \App\Shared\Libraries\CpanelApi();
        $email = $this->emailModel->where('user', $username)->first();
        if (!$email) throw new Exception('Akun email tidak ditemukan.');

        // Update on cPanel first
        $cpanelApi->change_password($email['email'], $newPassword);

        // If successful, update locally
        return $this->emailModel->update($email['id'], ['password' => $newPassword]);
    }

    public function getEselonDetail($eselonId, $params)
    {
        $eselon = $this->eselonModel->find($eselonId);
        if (!$eselon) {
            throw new Exception('Eselon not found.');
        }

        $perPage = $params['per_page'] ?? 100;
        $search = $params['search'] ?? null;
        $bsre_status = $params['bsre_status'] ?? null;

        $builder = $this->emailModel->withDetails()->where('emails.eselon_id', $eselonId);

        if ($search) {
            $builder->groupStart();
            $cleanSearch = str_replace([' ', '.', '-', '\''], '', $search);
            $builder->like('emails.email', $search)
                ->orLike('emails.name', $search);

            if (is_numeric($cleanSearch) && strlen($cleanSearch) >= 10) {
                $hash = $cleanSearch;
                $builder->orWhere('emails.nik', $hash)
                    ->orWhere('emails.nip', $hash);
            }
            $builder->groupEnd();
        }

        if ($bsre_status) {
            if ($bsre_status === 'not_synced') {
                $builder->groupStart()
                    ->where('emails.bsre_status', null)
                    ->orWhere('emails.bsre_status', '')
                    ->groupEnd();
            } else {
                $builder->where('emails.bsre_status', $bsre_status);
            }
        }

        $total_emails = $builder->countAllResults(false);
        
        $tempBuilder = clone $builder;
        $active_bsre_count = $tempBuilder->where('emails.bsre_status', 'ISSUE')->countAllResults(false);

        $emails = $builder->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
            ->orderBy('emails.jabatan', 'ASC')
            ->orderBy('emails.name', 'ASC')
            ->paginate($perPage);

        return [
            'eselon' => $eselon,
            'emails' => $emails,
            'total_emails' => $total_emails,
            'active_bsre_count' => $active_bsre_count,
            'pager' => $this->emailModel->pager,
        ];
    }

    public function getAsnList($asnStatusName, $params)
    {
        $statusAsn = $this->statusAsnModel->where('nama_status_asn', $asnStatusName)->asArray()->first();
        if (!$statusAsn) {
            throw new Exception("Status {$asnStatusName} belum dikonfigurasi di sistem.");
        }

        $hasNip = $params['has_nip'] ?? null;
        $parentUnitKerjaId = $params['parent_unit_kerja_id'] ?? null;
        $usePkJoin = $params['use_pk_join'] ?? false;
        $perPage = $params['per_page'] ?? 100;

        $builder = $this->emailModel->withDetails()->where('emails.status_asn_id', $statusAsn['id']);
        
        $countModel = new EmailModel();
        $countModel->where('emails.status_asn_id', $statusAsn['id']);

        if ($hasNip === 'yes') {
            $builder->where('emails.nip !=', '')->where('emails.nip IS NOT NULL');
            $countModel->where('emails.nip !=', '')->where('emails.nip IS NOT NULL');
        } elseif ($hasNip === 'no') {
            $builder->groupStart()->where('emails.nip', '')->orWhere('emails.nip', null)->groupEnd();
            $countModel->groupStart()->where('emails.nip', '')->orWhere('emails.nip', null)->groupEnd();
        }

        if (!empty($parentUnitKerjaId)) {
            $db = \Config\Database::connect();
            $builder->where('(unit_kerja.parent_id = ' . $db->escape($parentUnitKerjaId) . ' OR emails.unit_kerja_id = ' . $db->escape($parentUnitKerjaId) . ')');
            
            $countModel->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left');
            $countModel->where('(unit_kerja.parent_id = ' . $db->escape($parentUnitKerjaId) . ' OR emails.unit_kerja_id = ' . $db->escape($parentUnitKerjaId) . ')');
        }

        $bsreStatus = $params['bsre_status'] ?? null;
        if (!empty($bsreStatus)) {
            if ($bsreStatus === 'not_synced') {
                $builder->groupStart()->where('emails.bsre_status', null)->orWhere('emails.bsre_status', '')->groupEnd();
                $countModel->groupStart()->where('emails.bsre_status', null)->orWhere('emails.bsre_status', '')->groupEnd();
            } else {
                $builder->where('emails.bsre_status', $bsreStatus);
                $countModel->where('emails.bsre_status', $bsreStatus);
            }
        }

        if ($usePkJoin) {
            $builder->select('MIN(pk.nomor) as nomor_pk')
                    ->join('pk', 'pk.email = emails.email', 'left')
                    ->groupBy('emails.id, emails.name, emails.nip, emails.jabatan, emails.user, emails.email, emails.bsre_status, unit_kerja.nama_unit_kerja, parent_unit_kerja.nama_unit_kerja, status_asn.nama_status_asn, eselon.nama_eselon')
                    ->orderBy('CAST(MIN(pk.nomor) AS UNSIGNED)', 'ASC');
        } else {
            $builder->orderBy('emails.name', 'ASC');
        }

        $total_count = $countModel->countAllResults();
        $emails = $builder->paginate($perPage, 'default');

        return [
            'emails'      => $emails,
            'total_count' => $total_count,
            'pager'       => $this->emailModel->pager,
        ];
    }

    public function searchEmails(string $q): array
    {
        $cleanQ = str_replace([' ', '.', '-', "'"], '', $q);

        $builder = $this->emailModel
            ->select('emails.email, emails.name, emails.user, emails.nik, emails.nip, unit_kerja.nama_unit_kerja as unit_kerja_name')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left');

        $builder->groupStart()
                ->like('emails.email', $q)
                ->orLike('emails.name', $q);

        if (is_numeric($cleanQ) && strlen($cleanQ) >= 10) {
            $builder->orWhere('emails.nik', $cleanQ)
                    ->orWhere('emails.nip', $cleanQ);
        }

        $builder->groupEnd();

        return $builder->limit(10)->findAll();
    }

    public function getUnitEmails(int $unitKerjaId, array $params): array
    {
        $pkType = $params['pk_type'] ?? null;

        $statusPppk   = $this->statusAsnModel->where('nama_status_asn', 'PPPK')->asArray()->first();
        $statusPppkPw = $this->statusAsnModel->where('nama_status_asn', 'PPPK PARUH WAKTU')->asArray()->first();

        $allowedStatusIds = [];
        if ($pkType === 'pppk') {
            if ($statusPppk) $allowedStatusIds[] = $statusPppk['id'];
        } elseif ($pkType === 'pppk_pw') {
            if ($statusPppkPw) $allowedStatusIds[] = $statusPppkPw['id'];
        } else {
            if ($statusPppk)   $allowedStatusIds[] = $statusPppk['id'];
            if ($statusPppkPw) $allowedStatusIds[] = $statusPppkPw['id'];
        }

        if (empty($allowedStatusIds)) {
            throw new Exception('Status PPPK belum dikonfigurasi di sistem.');
        }

        $children   = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->asArray()->findAll();
        $allUnitIds = array_merge([$unitKerjaId], array_column($children, 'id'));

        $builder = $this->emailModel->withDetails()->whereIn('unit_kerja_id', $allUnitIds);
        $builder->whereIn('emails.status_asn_id', $allowedStatusIds);

        if (!empty($params['search'])) {
            $search = $params['search'];
            $builder->groupStart();
            if (is_numeric($search) && strlen($search) >= 10) {
                $builder->where('nik', $search)->orWhere('nip', $search);
            } else {
                $builder->like('email', $search)->orLike('name', $search);
            }
            $builder->groupEnd();
        }

        if (!empty($params['bsre_status'])) {
            $bsreStatus = $params['bsre_status'];
            if ($bsreStatus === 'not_synced') {
                $builder->groupStart()
                        ->where('emails.bsre_status', null)
                        ->orWhere('emails.bsre_status', '')
                        ->groupEnd();
            } else {
                $builder->where('emails.bsre_status', $bsreStatus);
            }
        }

        return $builder
            ->orderBy('emails.eselon_id IS NULL', 'ASC', false)
            ->orderBy('emails.eselon_id', 'ASC')
            ->orderBy('emails.status_asn_id IS NULL', 'ASC', false)
            ->orderBy('emails.status_asn_id', 'ASC')
            ->orderBy('emails.jabatan IS NULL', 'ASC', false)
            ->orderBy('emails.jabatan', 'ASC')
            ->orderBy('emails.name', 'ASC')
            ->findAll();
    }

    public function syncPegawaiFromApi(string $nip, ?string $fallbackEmail = null): array
    {
        $builder = $this->emailModel
            ->select('emails.*, unit_kerja.nama_unit_kerja')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left');

        if (!empty($nip)) {
            $builder->where('emails.nip', $nip);
        } elseif (!empty($fallbackEmail)) {
            $builder->where('emails.email', $fallbackEmail);
        }

        $currentEmail = $builder->first();

        // Guard: Pastikan hanya pegawai berstatus PNS (status_asn_id = 1) yang disinkronkan. Lewati hit API jika bukan PNS.
        if ($currentEmail && !empty($currentEmail['status_asn_id']) && (int)$currentEmail['status_asn_id'] !== 1) {
            return [
                'success' => true,
                'skipped' => true,
                'reason'  => 'non_pns',
                'message' => 'Hanya pegawai berstatus PNS yang dapat disinkronkan dari SIMPEG (API dilewati).',
                'current' => $currentEmail,
                'data'    => [
                    'jabatan'          => $currentEmail['jabatan'] ?? '-',
                    'pangkat_nama'     => $currentEmail['pangkat_nama'] ?? '-',
                    'pangkat_golruang' => $currentEmail['pangkat_golruang'] ?? '-',
                    'eselon_name'      => null,
                ]
            ];
        }

        $pegawaiApi = new \App\Shared\Libraries\PegawaiApi();
        $result     = $pegawaiApi->getPegawaiData($nip);

        if (!$result['success']) {
            return [
                'success'       => false,
                'code'          => $result['code'] ?? 500,
                'is_rate_limit' => ($result['code'] ?? 0) === 429 || stripos($result['message'] ?? '', 'Rate Limit') !== false,
                'message'       => $result['message'] ?? 'Gagal menghubungi API pegawai'
            ];
        }

        $data   = $result['data'];
        $source = $data;
        if (is_array($data) && isset($data[0])) {
            $definitif = null;
            foreach ($data as $item) {
                if (isset($item['jabatan_status_id']) && (int)$item['jabatan_status_id'] === 1) {
                    $definitif = $item;
                    break;
                }
            }
            $source = $definitif ?: $data[0];
        }

        $hasActualData = isset($source['jabatan_nama']) || isset($source['jabatan'])
                      || isset($source['pangkat_nama']) || isset($source['pangkat_golruang']);

        if (empty($data) || !$hasActualData) {
            $apiMessage = 'Data tidak ditemukan di API';
            if (is_array($data)) {
                $apiMessage = $data['message'] ?? $data['error'] ?? $data['msg'] ?? $apiMessage;
            }
            return [
                'success' => true,
                'no_data' => true,
                'updated' => false,
                'message' => $apiMessage,
                'data'    => [
                    'jabatan'          => $currentEmail['jabatan'] ?? '-',
                    'pangkat_nama'     => $currentEmail['pangkat_nama'] ?? '-',
                    'pangkat_golruang' => $currentEmail['pangkat_golruang'] ?? '-',
                ]
            ];
        }

        $isPimpinan = ($currentEmail['pimpinan'] ?? 0) == 1;
        $updateData = [];

        // 1. Sync Pangkat & Golongan
        if (isset($source['pangkat_nama']))    $updateData['pangkat_nama']    = trim($source['pangkat_nama']);
        if (isset($source['pangkat_golruang'])) $updateData['pangkat_golruang'] = trim($source['pangkat_golruang']);
        if (empty($currentEmail['nip']) && !empty($source['nip'])) {
            $updateData['nip'] = trim($source['nip']);
        }
        if (empty($currentEmail['name']) && !empty($source['nama'])) {
            $updateData['name'] = trim($source['nama']);
        }
        if (empty($currentEmail['status_asn_id'])) {
            $updateData['status_asn_id'] = 1;
        }

        // 2. Sync Unit Kerja jika terjadi mutasi / pindah tugas di SIMPEG
        $rawJabatan = $source['jabatan_nama'] ?? $source['jabatan'] ?? ($currentEmail['jabatan'] ?? null);
        $resolvedUnitName = $currentEmail['nama_unit_kerja'] ?? '';

        if (!empty($source['unit_id'])) {
            $targetUnit = $this->unitKerjaModel->where('api_unit_id', $source['unit_id'])->first();
            if ($targetUnit) {
                $resolvedUnitId = $targetUnit['id'];
                $resolvedUnitName = $targetUnit['nama_unit_kerja'];

                // Pimpinan Utama Setda (Sekda, Asisten, Staf Ahli, Bupati, Wabup) selalu berada langsung di SEKRETARIAT DAERAH (bukan sub-unit Bagian)
                $isTopSetdaLeader = false;
                $upperRawJab = strtoupper($rawJabatan ?? '');
                if ((preg_match('/\b(SEKRETARIS DAERAH|SEKDA)\b/i', $upperRawJab) && stripos($upperRawJab, 'TATA USAHA PIMPINAN') === false && stripos($upperRawJab, 'TU PIMPINAN') === false)
                    || (preg_match('/\bASISTEN\s+(?:PEMERINTAHAN|PEREKONOMIAN|ADMINISTRASI|BIDANG|SEKRETARIAT)\b/i', $upperRawJab) || (stripos($upperRawJab, 'ASISTEN') === 0 && stripos($upperRawJab, 'ASISTEN APOTEKER') === false))
                    || (preg_match('/\bSTAF\s+AHLI\s+BUPATI\b/i', $upperRawJab) || (stripos($upperRawJab, 'STAF AHLI') === 0))
                    || (stripos($upperRawJab, 'BUPATI') === 0 && stripos($upperRawJab, 'WAKIL BUPATI') === false)
                    || (stripos($upperRawJab, 'WAKIL BUPATI') === 0)) {
                    $isTopSetdaLeader = true;
                }

                // Cek apakah jabatan atau jabatan_grup dari API menyebutkan sub-unit khusus (misal Sekolah, Puskesmas, Kelurahan, Bagian) di bawah targetUnit
                $rawJabatanGrup = $source['jabatan_grup'] ?? '';
                $normalizeForMatching = function($str) {
                    $s = mb_strtoupper((string)$str, 'UTF-8');
                    $s = preg_replace('/\b(KAB\.\s*SINJAI|KABUPATEN\s*SINJAI|KAB\s*SINJAI)\b/i', '', $s);
                    $s = preg_replace('/\b((UPTD\s+)?(SD\s*NEG\.?\s*NO\.?|SD\s*NEGERI\s*NO\.?|SD\s*NEGERI|SD\s*NEG\.?|SDN\s*NO\.?|SDN))\b/i', 'SDN ', $s);
                    $s = preg_replace('/\b((UPTD\s+)?(SMP\s*NEGERI|SMPN))\b/i', 'SMPN ', $s);
                    $s = preg_replace('/\b((UPTD\s+)?(SMA\s*NEGERI|SMAN))\b/i', 'SMAN ', $s);
                    $s = preg_replace('/\b((UPTD\s+)?(TK\s*NEGERI|TK\s*PERTIWI|TK\s*PGRI|TK\s*DHARMA\s*WANITA|TK\s*AISYIYAH|TKN|TK\s*NEG\.?))\b/i', 'TKN ', $s);
                    $s = preg_replace('/\b(UPTD\s*PUSKESMAS|PUSKESMAS)\b/i', 'PUSKESMAS ', $s);
                    $s = preg_replace('/\b(UPTD\s*RSUD|RSUD)\b/i', 'RSUD ', $s);
                    $s = preg_replace('/\b(UPTD\s*LABKESDA|LABORATORIUM\s*KESEHATAN\s*DAERAH|LABKESDA)\b/i', 'LABKESDA ', $s);
                    $s = preg_replace('/\b(UPTD\s*IFK|INSTALASI\s*FARMASI\s*KABUPATEN|INSTALASI\s*FARMASI|IFK|GFK)\b/i', 'IFK ', $s);
                    $s = preg_replace('/\b(KANTOR\s*KELURAHAN|KELURAHAN|LURAH)\b/i', 'KELURAHAN ', $s);
                    $s = preg_replace('/\b(BAGIAN)\b/i', 'BAGIAN ', $s);
                    $s = preg_replace('/\b(KEC\.|KECAMATAN|KEC)\b/i', '', $s);
                    // Disambiguasi "Tk." / "Tk" (Tingkat/Level pangkat, misal "Guru Dewasa Tk. I", "Guru Muda tk I") agar tidak
                    // terbaca sebagai "TK" (Taman Kanak-kanak) setelah str_replace('.')
                    $s = preg_replace('/\bTK\.?\s*(?=[IVXLCDM\d])/i', 'TINGKAT ', $s);
                    $s = str_replace(['/', '-', '.', ',', 'NO.'], ' ', $s);
                    // Perbaikan ejaan nama tempat yang terkadang berbeda di SIMPEG vs data resmi
                    $spellingFix = [
                        'SANGIASERRI'   => 'SANGIASSERI',   // TK Pertiwi III Sangiaserri Sinjai Selatan
                        'SAOTENGAH'     => 'SATENGAH',      // SDN No. 10 Saotengah (DB: SATENGAH)
                        '253 TARANGKEKE'=> '235 TARANGKEKE',
                        'PAOLOTONGE'    => 'PALOTTONGNGENG',
                        'PAALOTONNGE'   => 'PALOTTONGNGENG',
                        'MACCONGGI'     => 'MACCONGI',
                        'CONGOE'        => 'CONGKOE',
                        'BATULEPPA'     => 'BATU LAPPA',
                        'PUSSANTI'      => 'PUSANTI',
                        '277 BALANG'    => '227 BALANG',
                        'BULUPACCING'   => 'BULUPANCING',
                    ];
                    foreach ($spellingFix as $wrong => $correct) {
                        $s = str_ireplace($wrong, $correct, $s);
                    }
                    // Konversi angka romawi baku ke arab untuk konsistensi penomoran sekolah (misal: TK I -> TK 1, TK XII -> TK 12)
                    $romanMap = [
                        '/\bXII\b/' => '12', '/\bXI\b/' => '11', '/\bX\b/' => '10',
                        '/\bIX\b/' => '9', '/\bVIII\b/' => '8', '/\bVII\b/' => '7',
                        '/\bVI\b/' => '6', '/\bV\b/' => '5', '/\bIV\b/' => '4',
                        '/\bIII\b/' => '3', '/\bII\b/' => '2', '/\bI\b/' => '1'
                    ];
                    $s = preg_replace(array_keys($romanMap), array_values($romanMap), $s);
                    $s = preg_replace('/\s+/', ' ', $s);
                    return trim($s);
                };

                $normSearch = $normalizeForMatching(($rawJabatan ?? '') . ' ' . $rawJabatanGrup);
                $normGrup = $normalizeForMatching($rawJabatanGrup ?? '');
                $cleanGrupStripped = trim(preg_replace('/^(SDN|SMPN|SMAN|TKN|PUSKESMAS|RSUD|LABKESDA|IFK|KELURAHAN|BAGIAN)\s+/i', '', $normGrup));

                $childUnits = !$isTopSetdaLeader ? $this->unitKerjaModel->where('parent_id', $targetUnit['id'])->findAll() : [];
                if (!empty($childUnits) && !empty($normSearch)) {
                    foreach ($childUnits as $child) {
                        $childName = strtoupper($child['nama_unit_kerja']);
                        $normChild = $normalizeForMatching($childName);

                        // Cegah cross-type mismatch (TK mencocokkan SMP/SD, SMP mencocokkan SD/TK, dsb)
                        $searchIsTk = (strpos($normSearch, 'TKN') !== false || preg_match('/\bTK\s+(?!TINGKAT)/i', $normSearch));
                        $searchIsSmp = (strpos($normSearch, 'SMPN') !== false || strpos($normSearch, 'SMP ') !== false);
                        $searchIsSd = (strpos($normSearch, 'SDN') !== false || strpos($normSearch, 'SD ') !== false);

                        $childIsTk = (strpos($normChild, 'TKN') !== false || strpos($childName, 'TK ') !== false);
                        $childIsSmp = (strpos($normChild, 'SMPN') !== false || strpos($childName, 'SMP') !== false);
                        $childIsSd = (strpos($normChild, 'SDN') !== false || strpos($childName, 'SD') !== false);

                        if ($searchIsTk && !$childIsTk) continue;
                        if ($searchIsSmp && !$childIsSmp) continue;
                        if ($searchIsSd && !$childIsSd) continue;

                        $cleanChildStripped = trim(preg_replace('/^(SDN|SMPN|SMAN|TKN|PUSKESMAS|RSUD|LABKESDA|IFK|KELURAHAN|BAGIAN)\s+/i', '', $normChild));

                        if (stripos($normSearch, $normChild) !== false 
                            || (!empty($cleanChildStripped) && preg_match('/\b' . preg_quote($cleanChildStripped, '/') . '\b/i', $normSearch))
                            || (!empty($cleanChildStripped) && strlen($cleanChildStripped) >= 5 && stripos($normSearch, $cleanChildStripped) !== false)) {
                            $resolvedUnitId = $child['id'];
                            $resolvedUnitName = $child['nama_unit_kerja'];
                            break;
                        }

                        // SD number-based matching: cocokkan nomor SD dari SIMPEG ke format DB
                        if ($searchIsSd && $childIsSd) {
                            if (preg_match('/\bSDN\s*(?:NO\s*)?(\d+)\b/i', $normSearch, $mSearch)
                                && preg_match('/\bSDN\s*(?:NO\s*)?(\d+)\b/i', $normChild, $mChild)) {
                                if ((int)$mSearch[1] === (int)$mChild[1]) {
                                    $resolvedUnitId = $child['id'];
                                    $resolvedUnitName = $child['nama_unit_kerja'];
                                    break;
                                }
                            }
                        }

                        // Reverse matching jika di database memuat nama kecamatan (misal: 'TK NEGERI BALLE SINJAI UTARA' sementara di SIMPEG hanya 'TK NEGERI BALLE')
                        if (!empty($normGrup) && strlen($normGrup) >= 5 && stripos($normChild, $normGrup) !== false) {
                            $resolvedUnitId = $child['id'];
                            $resolvedUnitName = $child['nama_unit_kerja'];
                            break;
                        }
                        if (!empty($cleanGrupStripped) && strlen($cleanGrupStripped) >= 4 && stripos($cleanChildStripped, $cleanGrupStripped) !== false) {
                            $resolvedUnitId = $child['id'];
                            $resolvedUnitName = $child['nama_unit_kerja'];
                            break;
                        }

                        // Mapping komprehensif Subbagian dan Jabatan Fungsional ke 10 Bagian Setda
                        if ((int)$targetUnit['id'] === 600) {
                            if (strpos($childName, 'PROTOKOL') !== false && preg_match('/\b(PROTOKOL|KOMUNIKASI PIMPINAN|DOKUMENTASI PIMPINAN|PRANATA HUMAS|HUMAS)\b/i', $normSearch)) {
                                $resolvedUnitId = $child['id'];
                                $resolvedUnitName = $child['nama_unit_kerja'];
                                break;
                            }
                            if (strpos($childName, 'PENGADAAN') !== false && preg_match('/\b(PENGADAAN|BARANG\s*(?:DAN\s*)?JASA|BARANG\/JASA|PBJ|LPSE)\b/i', $normSearch)) {
                                $resolvedUnitId = $child['id'];
                                $resolvedUnitName = $child['nama_unit_kerja'];
                                break;
                            }
                            if (strpos($childName, 'UMUM') !== false && preg_match('/\b(RUMAH TANGGA|PERLENGKAPAN|TU PIMPINAN|TATA USAHA PIMPINAN|TATA USAHA DAN KEPEGAWAIAN|KEPEGAWAIAN SETDA|BAGIAN UMUM)\b/i', $normSearch)) {
                                $resolvedUnitId = $child['id'];
                                $resolvedUnitName = $child['nama_unit_kerja'];
                                break;
                            }
                            if (strpos($childName, 'ORGANISASI') !== false && preg_match('/\b(ORGANISASI|KELEMBAGAAN|TATA LAKSANA|REFORMASI BIROKRASI|ANJAB)\b/i', $normSearch)) {
                                $resolvedUnitId = $child['id'];
                                $resolvedUnitName = $child['nama_unit_kerja'];
                                break;
                            }
                            if (strpos($childName, 'KESRA') !== false && preg_match('/\b(KESRA|KEMASYARAKATAN|KESEJAHTERAAN|BINA MENTAL|SPIRITUAL|SOSIAL KEMASYARAKATAN)\b/i', $normSearch)) {
                                $resolvedUnitId = $child['id'];
                                $resolvedUnitName = $child['nama_unit_kerja'];
                                break;
                            }
                            if (strpos($childName, 'PEREKONOMIAN') !== false && preg_match('/\b(PEREKONOMIAN|BUMD|BLUD|BADAN USAHA MILIK DAERAH|BADAN LAYANAN UMUM|SDA|SUMBER DAYA ALAM)\b/i', $normSearch)) {
                                $resolvedUnitId = $child['id'];
                                $resolvedUnitName = $child['nama_unit_kerja'];
                                break;
                            }
                            if (strpos($childName, 'PEMERINTAHAN') !== false && preg_match('/\b(PEMERINTAHAN|KEWILAYAHAN|OTONOMI DAERAH)\b/i', $normSearch)) {
                                $resolvedUnitId = $child['id'];
                                $resolvedUnitName = $child['nama_unit_kerja'];
                                break;
                            }
                            if (strpos($childName, 'PEMBANGUNAN') !== false && preg_match('/\b(PEMBANGUNAN|PENYUSUNAN PROGRAM|PENYUSUN PROGRAM|PENGENDALIAN PROGRAM|EVALUASI PROGRAM|EVALUASI DAN PELAPORAN)\b/i', $normSearch)) {
                                $resolvedUnitId = $child['id'];
                                $resolvedUnitName = $child['nama_unit_kerja'];
                                break;
                            }
                            if (strpos($childName, 'HUKUM') !== false && preg_match('/\b(HUKUM|PERUNDANG-UNDANGAN|BANTUAN HUKUM|DOKUMENTASI HUKUM|PENYULUH HUKUM|ANALIS HUKUM)\b/i', $normSearch)) {
                                $resolvedUnitId = $child['id'];
                                $resolvedUnitName = $child['nama_unit_kerja'];
                                break;
                            }
                            if (strpos($childName, 'PERENCANAAN') !== false && preg_match('/\b(PERENCANAAN|KEUANGAN|PERBENDAHARAAN|AKUNTANSI|PERENCANA)\b/i', $normSearch)) {
                                $resolvedUnitId = $child['id'];
                                $resolvedUnitName = $child['nama_unit_kerja'];
                                break;
                            }
                        }

                        // Mapping alias penomoran SMP per-kecamatan format lama SIMPEG
                        if ($searchIsSmp && $childIsSmp) {
                            // Tabel: [keyword_kecamatan => [nomor_kecamatan => nama_UPTD_kabupaten]]
                            $smpDistrictMap = [
                                'TELLULIMPOE'    => [
                                    '1' => 'UPTD SMP NEGERI 10 SINJAI',
                                    '2' => 'UPTD SMP NEGERI 19 SINJAI',
                                    '3' => 'UPTD SMP NEGERI 20 SINJAI',
                                    '4' => 'UPTD SMP NEGERI 33 SINJAI',
                                ],
                                'BULUPODDO'      => [
                                    '1' => 'UPTD SMP NEGERI 9 SINJAI',
                                    '2' => 'UPTD SMP NEGERI 13 SINJAI',
                                    '3' => 'UPTD SMP NEGERI 21 SINJAI',
                                    '4' => 'UPTD SMP NEGERI 36 SINJAI',
                                ],
                                'PULAU SEMBILAN' => [
                                    '1' => 'UPTD SMP NEGERI 14 SINJAI',
                                ],
                                'SINJAI BARAT'   => [
                                    '1' => 'UPTD SMP NEGERI 8 SINJAI',
                                    '2' => 'UPTD SMP NEGERI 12 SINJAI',
                                    '3' => 'UPTD SMP NEGERI 17 SINJAI',
                                    '4' => 'UPTD SMP NEGERI 26 SINJAI',
                                ],
                                'SINJAI BORONG'  => [
                                    '1' => 'UPTD SMP NEGERI 11 SINJAI',
                                    '2' => 'UPTD SMP NEGERI 18 SINJAI',
                                    '3' => 'UPTD SMP NEGERI 23 SINJAI',
                                ],
                                'SINJAI SELATAN' => [
                                    '1' => 'UPTD SMP NEGERI 3 SINJAI',
                                    '2' => 'UPTD SMP NEGERI 7 SINJAI',
                                    '3' => 'UPTD SMP NEGERI 15 SINJAI',
                                    '4' => 'UPTD SMP NEGERI 22 SINJAI',
                                    '5' => 'UPTD SMP NEGERI 24 SINJAI',
                                    '6' => 'UPTD SMP NEGERI 29 SINJAI',
                                    '7' => 'UPTD SMP NEGERI 30 SINJAI',
                                ],
                                'SINJAI TENGAH'  => [
                                    '1' => 'UPTD SMP NEGERI 5 SINJAI',
                                    '2' => 'UPTD SMP NEGERI 16 SINJAI',
                                    '3' => 'UPTD SMP NEGERI 25 SINJAI',
                                ],
                                'SINJAI TIMUR'   => [
                                    '1' => 'UPTD SMP NEGERI 4 SINJAI',
                                    '2' => 'UPTD SMP NEGERI 6 SINJAI',
                                    '3' => 'UPTD SMP NEGERI 27 SINJAI',
                                    '4' => 'UPTD SMP NEGERI 32 SINJAI',
                                ],
                                'SINJAI UTARA'   => [
                                    '1' => 'UPTD SMP NEGERI 1 SINJAI',
                                    '2' => 'UPTD SMP NEGERI 2 SINJAI',
                                    '3' => 'UPTD SMP NEGERI 3 SINJAI',
                                ],
                            ];
                            foreach ($smpDistrictMap as $kecKeyword => $numMap) {
                                if (strpos($normSearch, $kecKeyword) !== false) {
                                    foreach ($numMap as $num => $targetName) {
                                        if (preg_match('/\b(SMPN|SMP)\s*' . $num . '\b/i', $normSearch) && $childName === $targetName) {
                                            $resolvedUnitId = $child['id'];
                                            $resolvedUnitName = $child['nama_unit_kerja'];
                                            break 3;
                                        }
                                    }
                                }
                            }

                            // Mapping SMP Satap (Satu Atap) ke UPTD SMP Negeri Sinjai
                            $satapMap = [
                                'KARANGKO'      => 'UPTD SMP NEGERI 28 SINJAI',
                                'KANRUNG'       => 'UPTD SMP NEGERI 28 SINJAI',
                                'SINJAI TENGAH' => 'UPTD SMP NEGERI 28 SINJAI',
                                'PATTONGKO'     => 'UPTD SMP NEGERI 33 SINJAI',
                                'BURUNG LOE'    => 'UPTD SMP NEGERI 38 SINJAI',
                                'BURUNG LOE I'  => 'UPTD SMP NEGERI 38 SINJAI',
                                'BURUNGLOE'     => 'UPTD SMP NEGERI 38 SINJAI',
                                'KANALO'        => 'UPTD SMP NEGERI 35 SINJAI',
                                'KANALO I'      => 'UPTD SMP NEGERI 35 SINJAI',
                                'KANALO II'     => 'UPTD SMP NEGERI 35 SINJAI',
                                'BALAPPANGI'    => 'UPTD SMP NEGERI 36 SINJAI',
                                'PALANGKA'      => 'UPTD SMP NEGERI 37 SINJAI',
                                'BIKERU'        => 'UPTD SMP NEGERI 37 SINJAI',
                                'BARAMBANG'     => 'UPTD SMP NEGERI 39 SINJAI',
                                'TASOSSO'       => 'UPTD SMP NEGERI 39 SINJAI',
                                'TASSOSO'       => 'UPTD SMP NEGERI 39 SINJAI',
                                'TERASA'        => 'UPTD SMP NEGERI 40 SINJAI',
                                'PUNCAK'        => 'UPTD SMP NEGERI 40 SINJAI',
                            ];
                            foreach ($satapMap as $keyword => $targetName) {
                                if (strpos($normSearch, $keyword) !== false && $childName === $targetName) {
                                    $resolvedUnitId = $child['id'];
                                    $resolvedUnitName = $child['nama_unit_kerja'];
                                    break 2;
                                }
                            }
                        }

                        // Mapping khusus TK Negeri
                        if ($searchIsTk && $childIsTk) {
                            if (strpos($normSearch, 'MANGARABOMBANG') !== false && $childName === 'TK NEGERI V SINJAI TIMUR') {
                                $resolvedUnitId = $child['id'];
                                $resolvedUnitName = $child['nama_unit_kerja'];
                                break;
                            }
                        }
                        // Mapping khusus RSUD Pratama Bulupancing di bawah Dinas Kesehatan
                        if (strpos($childName, 'PRATAMA') !== false && (strpos($normSearch, 'BULUPANCING') !== false || strpos($normSearch, 'BULUPACCING') !== false || strpos($normSearch, 'PRATAMA') !== false || strpos($normSearch, 'KELAS D') !== false)) {
                            $resolvedUnitId = $child['id'];
                            $resolvedUnitName = $child['nama_unit_kerja'];
                            break;
                        }
                        // Mapping khusus UPTD Labkesda di bawah Dinas Kesehatan
                        if ((strpos($childName, 'LABKESDA') !== false || strpos($childName, 'LABORATORIUM') !== false) && 
                            (strpos($normSearch, 'LABORATORIUM') !== false || strpos($normSearch, 'LABKESDA') !== false)) {
                            $resolvedUnitId = $child['id'];
                            $resolvedUnitName = $child['nama_unit_kerja'];
                            break;
                        }
                    }
                }

                // Cek apakah jabatan atau jabatan_grup merupakan divisi internal OPD (Sub Bagian, Bidang, Seksi, Sekretariat)
                $isOpdInternalDivision = preg_match('/\b(SUB\s*BAGIAN|BIDANG|SEKSI|SEKRETARIAT|KEPALA\s*DINAS|KEPALA\s*BADAN|INSPEKTUR|KASUBAG|KABID|KASI|SEKRETARIS\s*DINAS|SEKRETARIS\s*BADAN|SEKRETARIS\s*INSPEKTORAT)\b/i', ($rawJabatan ?? '') . ' ' . $rawJabatanGrup);

                // Hanya pertahankan sub-unit yang ada jika BUKAN divisi internal OPD dan BUKAN Top Leader Setda
                if (!$isTopSetdaLeader && !$isOpdInternalDivision && $resolvedUnitId == $targetUnit['id'] && !empty($currentEmail['unit_kerja_id'])) {
                    $currentUnit = $this->unitKerjaModel->find($currentEmail['unit_kerja_id']);
                    if ($currentUnit && $currentUnit['parent_id'] == $targetUnit['id']) {
                        $resolvedUnitId = $currentUnit['id'];
                        $resolvedUnitName = $currentUnit['nama_unit_kerja'];
                    }
                }

                if ($resolvedUnitId != ($currentEmail['unit_kerja_id'] ?? null)) {
                    $updateData['unit_kerja_id'] = $resolvedUnitId;
                }
            }
        }

        // 3. Sync Jabatan & Normalisasi Berdasarkan Unit yang Sudah Ter-resolve
        if ($rawJabatan) {
            $cleanJab = $this->normalizeJabatanName($rawJabatan, $resolvedUnitName, $isPimpinan);
            if ($cleanJab) {
                $updateData['jabatan'] = $cleanJab;

                if (!empty($source['jabatan_jenis_eselon'])) {
                    $eselonStr   = str_replace(['.', ' ', '-'], '', $source['jabatan_jenis_eselon']);
                    $eselonModel = new \App\Shared\Models\EselonModel();
                    $eselon      = $eselonModel->where('nama_eselon', $eselonStr)->first();
                    $updateData['eselon_id'] = $eselon ? $eselon['id'] : null;
                } else {
                    $updateData['eselon_id'] = null;
                }
            }
        }

        if (!empty($updateData)) {
            $this->emailModel->update($currentEmail['id'], $updateData);

            \App\Shared\Services\CacheService::invalidateDashboard();

            $unitKerjaId = $updateData['unit_kerja_id'] ?? ($currentEmail['unit_kerja_id'] ?? null);
            $unitKerjaName = $currentEmail['nama_unit_kerja'] ?? ($currentEmail['unit_kerja_name'] ?? '');
            $unitKerjaId = array_key_exists('unit_kerja_id', $updateData) ? $updateData['unit_kerja_id'] : ($currentEmail['unit_kerja_id'] ?? null);
            $unitKerjaName = '';
            $parentUnitKerjaName = '';
            $parentUnitKerjaId = null;

            if (!empty($unitKerjaId)) {
                $u = $this->unitKerjaModel->find($unitKerjaId);
                if ($u) {
                    $unitKerjaName = $u['nama_unit_kerja'];
                    if (!empty($u['parent_id'])) {
                        $p = $this->unitKerjaModel->find($u['parent_id']);
                        $parentUnitKerjaName = $p['nama_unit_kerja'] ?? '';
                        $parentUnitKerjaId = $p['id'] ?? null;
                    }
                }
            }

            $eselonId = array_key_exists('eselon_id', $updateData) ? $updateData['eselon_id'] : ($currentEmail['eselon_id'] ?? null);
            $eselonName = null;
            if (!empty($eselonId)) {
                $eselonModel = new \App\Shared\Models\EselonModel();
                $es = $eselonModel->find($eselonId);
                $eselonName = $es ? $es['nama_eselon'] : null;
            }

            $updateData['unit_kerja_name'] = $unitKerjaName;
            $updateData['parent_unit_kerja_name'] = $parentUnitKerjaName;
            $updateData['parent_unit_kerja_id'] = $parentUnitKerjaId;
            $updateData['parent_id'] = $parentUnitKerjaId;
            $updateData['eselon_name'] = $eselonName;

            $responseData = array_merge($currentEmail ?: [], $updateData);

            return [
                'success' => true,
                'updated' => true,
                'message' => 'Data pegawai dan jabatan berhasil disinkronkan & dinormalkan',
                'data'    => $responseData,
            ];
        }

        $unitKerjaId = $currentEmail['unit_kerja_id'] ?? null;
        $unitKerjaName = '';
        $parentUnitKerjaName = '';
        $parentUnitKerjaId = null;

        if (!empty($unitKerjaId)) {
            $u = $this->unitKerjaModel->find($unitKerjaId);
            if ($u) {
                $unitKerjaName = $u['nama_unit_kerja'];
                if (!empty($u['parent_id'])) {
                    $p = $this->unitKerjaModel->find($u['parent_id']);
                    $parentUnitKerjaName = $p['nama_unit_kerja'] ?? '';
                    $parentUnitKerjaId = $p['id'] ?? null;
                }
            }
        }

        $eselonId = $currentEmail['eselon_id'] ?? null;
        $eselonName = null;
        if (!empty($eselonId)) {
            $eselonModel = new \App\Shared\Models\EselonModel();
            $es = $eselonModel->find($eselonId);
            $eselonName = $es ? $es['nama_eselon'] : null;
        }

        return [
            'success' => true,
            'updated' => false,
            'message' => $isPimpinan ? 'Akun Pimpinan - Data jabatan tetap dipertahankan' : 'Data sudah terbaru',
            'data'    => [
                'jabatan'               => $currentEmail['jabatan'] ?? '-',
                'pangkat_nama'          => $currentEmail['pangkat_nama'] ?? '-',
                'pangkat_golruang'      => $currentEmail['pangkat_golruang'] ?? '-',
                'unit_kerja_id'         => $unitKerjaId,
                'unit_kerja_name'       => $unitKerjaName,
                'parent_unit_kerja_name'=> $parentUnitKerjaName,
                'parent_unit_kerja_id'  => $parentUnitKerjaId,
                'parent_id'             => $parentUnitKerjaId,
                'eselon_name'           => $eselonName,
            ],
        ];
    }

    public function swapAccountData(string $email1, string $email2)
    {
        if ($email1 === $email2) {
            throw new Exception('Alamat email pertama dan kedua tidak boleh sama.');
        }

        $record1 = $this->emailModel->where('email', $email1)->first();
        $record2 = $this->emailModel->where('email', $email2)->first();

        if (!$record1) throw new Exception("Akun $email1 tidak ditemukan di database.");
        if (!$record2) throw new Exception("Akun $email2 tidak ditemukan di database.");

        // Define which fields to swap (excluding email, password, domain, user, bsre_status, etc.)
        $fields = [
            'nik', 'nip', 'name', 'gelar_depan', 'gelar_belakang', 'tempat_lahir', 'tanggal_lahir', 'pendidikan',
            'jabatan', 'golongan', 'pangkat_nama', 'pangkat_golruang',
            'unit_kerja_id', 'eselon_id', 'status_asn_id', 'pimpinan', 'pimpinan_desa', 'pensiun_at'
        ];

        $data1 = [];
        $data2 = [];

        foreach ($fields as $field) {
            $data1[$field] = array_key_exists($field, $record2) ? $record2[$field] : null;
            $data2[$field] = array_key_exists($field, $record1) ? $record1[$field] : null;
        }

        log_message('info', "Swap Data 1 (to be saved to {$email1}): " . json_encode($data1));
        log_message('info', "Swap Data 2 (to be saved to {$email2}): " . json_encode($data2));

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Kosongkan sementara field unik (nik/nip) di record 1 agar tidak bentrok (Duplicate Entry)
        $db->table('emails')->where('id', $record1['id'])->update(['nik' => 'temp_' . $record1['id'], 'nip' => 'temp_' . $record1['id']]);

        // 2. Sekarang record 2 bisa aman mengambil data record 1
        $status2 = $db->table('emails')->where('id', $record2['id'])->update($data2);

        // 3. Terakhir, record 1 bisa aman mengambil data record 2
        $status1 = $db->table('emails')->where('id', $record1['id'])->update($data1);

        $db->transComplete();

        if ($db->transStatus() === false || !$status1 || !$status2) {
            throw new Exception("Terjadi kesalahan pada database saat menukar data. Proses dibatalkan.");
        }

        // Clear dashboard caches
        \App\Shared\Services\CacheService::invalidateDashboard();
    }

    public function normalizeJabatanName(?string $jabatan, ?string $unitKerjaName = null, bool $isPimpinan = false): ?string
    {
        if (empty($jabatan)) {
            return null;
        }

        $jab = trim(str_replace(["\xc2\xa0", "\u{00a0}", "\u{200b}"], ' ', (string)$jabatan));
        $jab = mb_strtoupper($jab, 'UTF-8');
        // Hapus karakter liar di akhir (titik ganda, titik koma, titik satu di ujung)
        $jab = preg_replace('/[,\.]+\s*$/', '', $jab);
        // Padatkan spasi ganda atau tab liar sejak awal
        $jab = preg_replace('/\s+/', ' ', $jab);
        // Pisahkan kata kunci OPD/Instansi yang menempel tanpa spasi ke kata sebelumnya (misal: VETERINERDINAS -> VETERINER DINAS)
        $jab = preg_replace('/([A-Z])(DINAS|BADAN|INSPEKTORAT|SEKRETARIAT|KANTOR|KECAMATAN)\b/i', '$1 $2', $jab);

        // Hapus prefix PLT. / PLH. / PJ. / PJS. / JF. / JFT. / JFU.
        $jab = preg_replace('/^\s*(PLT|PLH|PJ|PJS|JF|JFT|JFU)\.?\s+/i', '', $jab);
        // Perbaiki typo penulisan umum seperti KEPALKEPALA, KEP., PENGENLOLA, GURUR, dsb.
        $jab = preg_replace('/^KEPALKEPALA\b/i', 'KEPALA', $jab);
        $jab = preg_replace('/^KEP\.\s*/i', 'KEPALA ', $jab);
        $jab = preg_replace('/\bPENGENLOLA\b/i', 'PENGELOLA', $jab);
        $jab = preg_replace('/\bPENGELOLAH\b/i', 'PENGOLAH', $jab);
        $jab = preg_replace('/\bGURUR\b/i', 'GURU', $jab);
        $jab = preg_replace('/\bFISIOTRAPI\b/i', 'FISIOTERAPIS', $jab);
        $jab = preg_replace('/\bBULUPACCING\b/i', 'BULUPANCING', $jab);
        $jab = preg_replace('/\b(PUSKSEWAN|PUSKSE\s*WAN|PUSKES\s+WAN)\b/i', 'PUSKESWAN', $jab);
        $jab = preg_replace('/\bBBI\b/i', 'BALAI BENIH IKAN', $jab);
        $jab = preg_replace('/^KEPALA\s+(TATA\s+USAHA|TU|KTU)\s+(UPTD\s+)?BALAI\s+BENIH\s+IKAN\b/i', 'KEPALA TATA USAHA UPTD BALAI BENIH IKAN', $jab);
        $jab = preg_replace('/^KTU\s+(UPTD\s+)?BALAI\s+BENIH\s+IKAN\b/i', 'KEPALA TATA USAHA UPTD BALAI BENIH IKAN', $jab);
        $jab = preg_replace('/^KEPALA\s+(UPTD\s+)?BALAI\s+BENIH\s+IKAN\b/i', 'KEPALA UPTD BALAI BENIH IKAN', $jab);

        // Standarisasi UPTD Laboratorium DLHK -> LABORATORIUM LINGKUNGAN
        if (!empty($unitKerjaName) && (stripos($unitKerjaName, 'LINGKUNGAN HIDUP') !== false || stripos($unitKerjaName, 'DLHK') !== false)) {
            if (preg_match('/^KEPALA\s+(TATA\s+USAHA|TU|KTU)\s+(UPTD\s+|UPT\s+)?LABORATORIUM(\s+LINGKUNGAN(\s+HIDUP)?)?$/i', $jab) || preg_match('/^KTU\s+(UPTD\s+|UPT\s+)?LABORATORIUM(\s+LINGKUNGAN(\s+HIDUP)?)?$/i', $jab)) {
                $jab = 'KEPALA TATA USAHA UPTD LABORATORIUM LINGKUNGAN';
            } elseif (preg_match('/^KEPALA\s+(UPTD\s+|UPT\s+)?LABORATORIUM(\s+LINGKUNGAN(\s+HIDUP)?)?$/i', $jab)) {
                $jab = 'KEPALA UPTD LABORATORIUM LINGKUNGAN';
            }
        }

        // Standarisasi BARANG/JASA -> BARANG DAN JASA sebelum resolusi garis miring
        $jab = preg_replace('/\bBARANG\s*\/\s*JASA\b/i', 'BARANG DAN JASA', $jab);

        // Hapus embel-embel /SUB KOORDINATOR ... atau SUB.KOORDINATOR ... pada Jabatan Fungsional hasil Penyetaraan
        $jab = preg_replace('/[\/\s]+(?:SUB[\s\.\-_]*KOORDINATOR)\b.*$/i', '', $jab);

        // Resolusi jabatan kombinasi garis miring (Utamakan Jabatan Struktural / Manajerial / Kepala)
        if (strpos($jab, '/') !== false && !preg_match('/\b[IVX]+\/[A-D]\b/i', $jab)) {
            $parts = explode('/', $jab);
            if (count($parts) > 1 && strlen(trim($parts[0])) > 2 && strlen(trim($parts[1])) > 2) {
                $managerialKeywords = ['KEPALA', 'DIREKTUR', 'KOORDINATOR', 'KETUA', 'WAKIL', 'SEKRETARIS', 'KASUBAG', 'KASI', 'KABID', 'PIMPINAN', 'INSPEKTUR'];
                $chosen = null;
                foreach ($parts as $p) {
                    $pUpper = strtoupper(trim($p));
                    foreach ($managerialKeywords as $kw) {
                        if (stripos($pUpper, $kw) !== false) {
                            $chosen = $p;
                            break 2;
                        }
                    }
                }
                $jab = trim($chosen ?: $parts[0]);
            }
        }

        // Format Baku Kepala Sekolah, Kepala Puskesmas, & Direktur
        if (preg_match('/^(KEPALA|KEPALKEPALA|KEP\.?)\s+(SEKOLAH|(UPTD\s+(SPF\s+)?)?(SDN?|SMPN?|SMAN?|TKN?|SKB|TK|SD|SMP|SMA|SLB))\b/i', $jab)) {
            return 'KEPALA SEKOLAH';
        }
        if (preg_match('/^KEPALA\s+(UPTD\s+)?PUSKESMAS\b/i', $jab)) {
            return 'KEPALA PUSKESMAS';
        }
        if (preg_match('/^DIREKTUR\b/i', $jab)) {
            return 'DIREKTUR';
        }

        // Standarisasi KEPALA/KTU UPT -> KEPALA/KTU UPTD (Format Baku Perangkat Daerah)
        $jab = preg_replace('/\b(KEPALA\s+TATA\s+USAHA|KEPALA\s+TU|KTU|KEPALA)\s+UPT\s+(?!D\b)/i', '$1 UPTD ', $jab);

        // 1. Cek judul definitif pimpinan dari teks jabatan terlebih dahulu
        if (preg_match('/^LURAH\b/i', $jab) || (preg_match('/\bLURAH\b/i', $jab) && !preg_match('/\b(SEKRETARIS|SEKLUR|KEPALA\s+SEKSI|KASI|STAF|BENDAHARA|PENGELOLA|KELURAHAN)\b/i', $jab))) {
            return 'LURAH';
        }
        if (preg_match('/^CAMAT\b/i', $jab) || (preg_match('/\bCAMAT\b/i', $jab) && !preg_match('/\b(SEKRETARIS|SEKCAM|KEPALA\s+SEKSI|KASI|STAF|BENDAHARA|PENGELOLA|KECAMATAN)\b/i', $jab))) {
            return 'CAMAT';
        }
        // KEPALA BAGIAN di Unit yang memiliki Child Unit Bagian (Setda Bagian Hukum/Umum) disederhanakan,
        // sedangkan di Sekretariat DPRD dan RSUD (non-child unit), nama Bagian tetap dipertahankan penuh
        if (stripos($jab, 'KEPALA BAGIAN') === 0 || stripos($jab, 'KABAG') === 0) {
            $jab = preg_replace('/^KABAG\b/i', 'KEPALA BAGIAN', $jab);
            if (!empty($unitKerjaName) && strpos(strtoupper($unitKerjaName), 'BAGIAN ') === 0) {
                return 'KEPALA BAGIAN';
            }
            $jab = preg_replace('/\s+(?:(?:PADA|DI)\s+)?(SEKRETARIAT\s+DPRD|SETWAN|KABUPATEN\s+SINJAI)\b.*$/i', '', $jab);
            return trim($jab);
        }
        if (stripos($jab, 'KEPALA DINAS') === 0 || stripos($jab, 'KADIS') === 0) {
            return 'KEPALA DINAS';
        }
        if (stripos($jab, 'KEPALA BADAN') === 0 || stripos($jab, 'KABAN') === 0) {
            return 'KEPALA BADAN';
        }
        if (stripos($jab, 'KEPALA SATUAN') === 0 || stripos($jab, 'KEPALA SATPOL') === 0 || stripos($jab, 'KASAT POL') === 0 || stripos($jab, 'KASATPOL') === 0) {
            return 'KEPALA SATUAN';
        }
        if (stripos($jab, 'INSPEKTUR') === 0 && stripos($jab, 'PEMBANTU') === false && stripos($jab, 'IRBAN') === false) {
            return 'INSPEKTUR';
        }

        // Normalisasi Khusus Asisten Sekda & Staf Ahli Bupati
        if (stripos($jab, 'ASISTEN') === 0 && stripos($jab, 'ASISTEN APOTEKER') === false) {
            if (stripos($jab, 'PEMERINTAHAN') !== false || stripos($jab, 'KESRA') !== false || stripos($jab, 'KESEJAHTERAAN') !== false || preg_match('/\bASISTEN\s*(I|1)\b/i', $jab)) {
                return 'ASISTEN PEMERINTAHAN DAN KESEJAHTERAAN RAKYAT';
            } elseif (stripos($jab, 'EKONOMI') !== false || stripos($jab, 'PEREKONOMIAN') !== false || stripos($jab, 'PEMBANGUNAN') !== false || preg_match('/\bASISTEN\s*(II|2)\b/i', $jab)) {
                return 'ASISTEN PEREKONOMIAN DAN PEMBANGUNAN';
            } elseif (stripos($jab, 'ADMINISTRASI') !== false || stripos($jab, 'UMUM') !== false || preg_match('/\bASISTEN\s*(III|3)\b/i', $jab)) {
                return 'ASISTEN ADMINISTRASI UMUM';
            }
            return $jab;
        }

        if (stripos($jab, 'STAF AHLI') === 0) {
            if (stripos($jab, 'SOSIAL') !== false || stripos($jab, 'SDM') !== false || stripos($jab, 'SUMBER DAYA MANUSIA') !== false) {
                return 'STAF AHLI BIDANG SOSIAL DAN SUMBER DAYA MANUSIA';
            } elseif (stripos($jab, 'EKONOMI') !== false || stripos($jab, 'KEUANGAN') !== false || stripos($jab, 'PEMBANGUNAN') !== false) {
                return 'STAF AHLI BIDANG EKONOMI, KEUANGAN DAN PEMBANGUNAN';
            } elseif (stripos($jab, 'HUKUM') !== false || stripos($jab, 'POLITIK') !== false || stripos($jab, 'PEMERINTAHAN') !== false) {
                return 'STAF AHLI BIDANG HUKUM, POLITIK DAN PEMERINTAHAN';
            }
            return $jab;
        }

        // Inferensi dari unit HANYA jika akun berstatus pimpinan kepala dinas/badan/camat/lurah utama
        $isSubordinateLeader = preg_match('/\b(SEKOLAH|PUSKESMAS|UPTD|UPT|BIDANG|SEKSI|SUB\s*BAGIAN|SUBBAG|SUB\s*BIDANG|SUBBID|RUANGAN|INSTALASI|LABORATORIUM)\b/i', $jab);
        if ($isPimpinan && !$isSubordinateLeader) {
            if (!empty($unitKerjaName)) {
                $unitUpper = strtoupper($unitKerjaName);
                if (strpos($unitUpper, 'KELURAHAN') !== false) {
                    return 'LURAH';
                } elseif (strpos($unitUpper, 'INSPEKTORAT') !== false) {
                    return 'INSPEKTUR';
                } elseif (strpos($unitUpper, 'SATUAN POLISI') !== false || strpos($unitUpper, 'SATPOL') !== false) {
                    return 'KEPALA SATUAN';
                } elseif (strpos($unitUpper, 'RUMAH SAKIT') !== false || strpos($unitUpper, 'RSUD') !== false) {
                    return 'DIREKTUR';
                } elseif (strpos($unitUpper, 'BAGIAN') !== false) {
                    return 'KEPALA BAGIAN';
                } elseif (strpos($unitUpper, 'DINAS') !== false) {
                    return 'KEPALA DINAS';
                } elseif (strpos($unitUpper, 'BADAN') !== false) {
                    return 'KEPALA BADAN';
                } elseif (strpos($unitUpper, 'KECAMATAN') !== false) {
                    return 'CAMAT';
                } elseif (strpos($unitUpper, 'UPTD') !== false || strpos($unitUpper, 'UPT ') !== false) {
                    if (strpos($unitUpper, 'PUSKESMAS') !== false) {
                        return 'KEPALA PUSKESMAS';
                    } elseif (strpos($unitUpper, 'RUMAH SAKIT') !== false || strpos($unitUpper, 'RSUD') !== false) {
                        return 'DIREKTUR';
                    } elseif (strpos($unitUpper, 'SD') !== false || strpos($unitUpper, 'SMP') !== false || strpos($unitUpper, 'TK') !== false) {
                        return 'KEPALA SEKOLAH';
                    }
                    return 'KEPALA UPTD';
                }
            }
        }

        // Format Ringkas Sekretaris OPD
        if (stripos($jab, 'SEKRETARIS DAERAH') === 0) {
            return 'SEKRETARIS DAERAH';
        } elseif (stripos($jab, 'SEKRETARIS DPRD') === 0) {
            return 'SEKRETARIS DPRD';
        } elseif (stripos($jab, 'SEKRETARIS INSPEKTORAT') === 0) {
            return 'SEKRETARIS INSPEKTORAT';
        } elseif (stripos($jab, 'SEKRETARIS BADAN') === 0 || stripos($jab, 'SEKRETARIS BPBD') === 0) {
            return 'SEKRETARIS BADAN';
        } elseif (stripos($jab, 'SEKRETARIS SATUAN') === 0 || stripos($jab, 'SEKRETARIS SATPOL') === 0) {
            return 'SEKRETARIS SATUAN';
        } elseif (stripos($jab, 'SEKRETARIS DINAS') === 0) {
            return 'SEKRETARIS DINAS';
        } elseif (stripos($jab, 'SEKRETARIS CAMAT') === 0 || stripos($jab, 'SEKRETARIS KECAMATAN') === 0) {
            return 'SEKRETARIS KECAMATAN';
        } elseif (stripos($jab, 'SEKRETARIS LURAH') === 0 || stripos($jab, 'SEKRETARIS KELURAHAN') === 0) {
            return 'SEKRETARIS KELURAHAN';
        } elseif ($jab === 'SEKRETARIS' && !empty($unitKerjaName)) {
            $unitUpper = strtoupper($unitKerjaName);
            if (strpos($unitUpper, 'INSPEKTORAT') !== false) {
                return 'SEKRETARIS INSPEKTORAT';
            } elseif (strpos($unitUpper, 'SATUAN POLISI') !== false || strpos($unitUpper, 'SATPOL') !== false) {
                return 'SEKRETARIS SATUAN';
            } elseif (strpos($unitUpper, 'DINAS') !== false) {
                return 'SEKRETARIS DINAS';
            } elseif (strpos($unitUpper, 'BADAN') !== false || strpos($unitUpper, 'BPBD') !== false) {
                return 'SEKRETARIS BADAN';
            } elseif (strpos($unitUpper, 'KECAMATAN') !== false) {
                return 'SEKRETARIS KECAMATAN';
            } elseif (strpos($unitUpper, 'KELURAHAN') !== false) {
                return 'SEKRETARIS KELURAHAN';
            }
        }

        // Bersihkan embel-embel OPD pada Inspektur Pembantu (Irban)
        if (stripos($jab, 'INSPEKTUR PEMBANTU') === 0) {
            $jab = preg_replace('/\s+(INSPEKTORAT|PADA|DAERAH)\s*.*$/i', '', $jab);
        }

        $isHospital = (!empty($unitKerjaName) && (stripos($unitKerjaName, 'RUMAH SAKIT') !== false || stripos($unitKerjaName, 'RSUD') !== false || stripos($unitKerjaName, 'PRATAMA') !== false))
                      || (stripos($jab, 'RSUD') !== false || stripos($jab, 'RUMAH SAKIT') !== false || stripos($jab, 'PRATAMA') !== false || stripos($jab, 'BULUPANCING') !== false);

        // Koreksi Kasubag Tata Usaha dan Kepegawaian di OPD/Sekretariat
        $jab = preg_replace('/^KEPALA\s+TATA\s+USAHA\s+DAN\s+KEPEGAWAIAN\b/i', 'KEPALA SUB BAGIAN TATA USAHA DAN KEPEGAWAIAN', $jab);

        // Standarisasi Singkatan Jabatan Struktural (KTU, Kepala TU, Kasubag, Kasubid, Kabid, Kasi, Kepala Tata Usaha)
        if ($isHospital && preg_match('/\b(KTU|KEPALA\s+TU|KASUBAG\s+TU|KASUBAG\s+TATA\s+USAHA|KEPALA\s+TATA\s+USAHA|KEPALA\s+SUB\s*BAGIAN\s+TATA\s+USAHA|SUB\s*BAGIAN\s+TATA\s+USAHA)\b/i', $jab)) {
            $jab = 'KEPALA SUB BAGIAN TATA USAHA';
        } else {
            $jab = preg_replace('/\b(KTU|KEPALA\s+TU|KASUBAG\s+TU|KASUBAG\s+TATA\s+USAHA(?!\s+(DAN|PIMPINAN))|KEPALA\s+SUB\s*BAGIAN\s+TATA\s+USAHA(?!\s+(DAN|PIMPINAN))|SUB\s*BAGIAN\s+TATA\s+USAHA(?!\s+(DAN|PIMPINAN)))\b/i', 'KEPALA TATA USAHA', $jab);
        }
        $jab = preg_replace('/\b(KEPALA\s+TATA\s+USAHA|KEPALA)\s+UPT\s+(?!D\b)/i', '$1 UPTD ', $jab);
        $jab = preg_replace('/\bKASUBAG\b/i', 'KEPALA SUB BAGIAN', $jab);
        $jab = preg_replace('/\bKASUBBID\b/i', 'KEPALA SUB BIDANG', $jab);
        $jab = preg_replace('/\bKABID\b/i', 'KEPALA BIDANG', $jab);
        $jab = preg_replace('/\bKASI\b/i', 'KEPALA SEKSI', $jab);

        // Tambahkan prefix KEPALA jika di SIMPEG hanya tertulis "BIDANG ...", "SUB BAGIAN ...", "SEKSI ...", "SUB BIDANG ..."
        if (preg_match('/^(BIDANG|SUB BAGIAN|SUB\. BAGIAN|SUB BIDANG|SEKSI)\s+/i', $jab) && stripos($jab, 'KEPALA') === false) {
            $jab = 'KEPALA ' . $jab;
        }

        // Koreksi SUB. BAGIAN / SUB. BIDANG menjadi SUB BAGIAN / SUB BIDANG
        $jab = preg_replace('/\bSUB\.\s*/i', 'SUB ', $jab);

        // 1. Bersihkan akhiran OPD/Lokasi pada Jabatan Struktural (Kepala Dinas, Kabid, Kasubag, Kasi, dsb)
        if (preg_match('/^(KEPALA SEKSI|KEPALA SUB BAGIAN|KEPALA SUB BIDANG)\s+/i', $jab)) {
            $jab = preg_replace('/^(KEPALA\s+(?:SEKSI|SUB\s*BAGIAN|SUB\s*BIDANG)\s+.+?)\s+(?:(?:PADA|DI)\s+)?(?:BIDANG|BAGIAN|SEKRETARIAT)\b.*$/i', '$1', $jab);
        }
        if (preg_match('/^(KEPALA BIDANG|KEPALA SUB BAGIAN|KEPALA SEKSI|KEPALA SUB BIDANG|SEKRETARIS|KEPALA DINAS|KEPALA BADAN|INSPEKTUR)\b/i', $jab)) {
            $jab = preg_replace('/\s+(?:(?:PADA|DI)\s+)?(DINAS|BADAN|INSPEKTORAT|SEKRETARIAT|KANTOR|KECAMATAN|KEC\.|UPTD|UPT|PUSKESMAS|RSUD|SATPOL\s*PP|SATUAN\s+POLISI|SATPOL|BPBD)\b.*$/i', '', $jab);
            $jab = preg_replace('/\s+(?:(?:PADA|DI)\s+)?(?:KABUPATEN|KAB\.)\s+SINJAI\s*.*$/i', '', $jab);
        }

        // Bersihkan nama puskesmas pada KTU Puskesmas (karena Puskesmas adalah child unit)
        if (preg_match('/^KEPALA TATA USAHA\b/i', $jab)) {
            $jab = preg_replace('/\s+(?:(?:PADA|DI)\s+)?(UPTD\s+|UPT\s+)?PUSKESMAS\b.*$/i', '', $jab);
        }

        // Standarisasi Nomenklatur Profesi Kesehatan (Perawat Gigi -> Terapis Gigi dan Mulut sesuai PermenPAN-RB No. 37/2019)
        $jab = preg_replace('/\bPERAWAT\s+GIGI\b/i', 'TERAPIS GIGI DAN MULUT', $jab);

        // Standarisasi Jenjang Keterampilan Format Lama: Pelaksana Lanjutan -> Mahir, Pelaksana Pemula -> Pemula
        $jab = preg_replace('/\bPELAKSANA\s+LANJUTAN\b/i', 'MAHIR', $jab);
        $jab = preg_replace('/\bPELAKSANA\s+PEMULA\b/i', 'PEMULA', $jab);

        // Standarisasi Format SSCASN (JENJANG - NAMA PROFESI) menjadi Format Baku PermenPAN-RB & BKN (NAMA PROFESI JENJANG)
        // Contoh: "AHLI PERTAMA - APOTEKER" -> "APOTEKER AHLI PERTAMA", "TERAMPIL - PERAWAT" -> "PERAWAT TERAMPIL", "MAHIR - FISIOTERAPIS" -> "FISIOTERAPIS MAHIR"
        if (preg_match('/^(AHLI PERTAMA|AHLI MUDA|AHLI MADYA|AHLI UTAMA|TERAMPIL|MAHIR|PENYELIA|PEMULA)\s*-\s*(.+)$/i', $jab, $matches)) {
            $jenjang = trim($matches[1]);
            $profesi = trim($matches[2]);
            $jab = $profesi . ' ' . $jenjang;
        }

        // 2. Bersihkan embel-embel lokasi/bagian pada Staf Pelaksana & Jabatan Fungsional (JF)
        // Hapus penyisipan nama OPD di tengah jabatan fungsional sebelum jenjang (misal: MEDIK VETERINER DINAS PETERNAKAN... AHLI PERTAMA)
        $jab = preg_replace('/\s+(DINAS|BADAN|INSPEKTORAT|SEKRETARIAT)\s+[A-Z\s]+(?=\s+(AHLI\s+(PERTAMA|MUDA|MADYA|UTAMA)|TERAMPIL|MAHIR|PENYELIA|PEMULA))/i', '', $jab);

        if (preg_match('/^(PENGADMINISTRASI|PENGELOLA|OPERATOR|PRANATA|BENDAHARA|PENGEMUDI|PRAMU|PETUGAS|STAF|TEKNISI|FASILITATOR|PENYUSUN|PEMERIKSA|ANALIS|GURU|DOKTER|PERAWAT|BIDAN|APOTEKER|EPIDEMIOLOG|SANITARIAN|NUTRISIONIS|ARSIPARIS|PUSTAKAWAN|AUDITOR|PERENCANA|PENYULUH|INSTRUKTUR|STATISTISI|PENELITI|PENGUJI|POLISI PAMONG PRAJA|MEDIK VETERINER|PARAMEDIK VETERINER|PENGAWAS BIBIT|PENGAWAS MUTU)\b/i', $jab)) {
            $jab = preg_replace('/\s+(PADA|DI)\s+(SEKRETARIAT|KEC\.|KECAMATAN|DINAS|BADAN|INSPEKTORAT|SATPOL|SATUAN POLISI|UPTD|RSUD|PUSKESMAS|KELURAHAN|KEL\.|BAGIAN|IFK|GFK|SKB|INSTALASI|LABKESDA|LABORATORIUM|SEKOLAH|SDN|SMPN|SMAN|TKN)\b.*$/i', '', $jab);
            $jab = preg_replace('/\s+(BAGIAN\s+(ORGANISASI|HUKUM|UMUM|PROTOKOL|KESRA|PEMERINTAHAN|PEMBANGUNAN|PEREKONOMIAN|KEUANGAN|PENGADAAN)|UPTD\s+RSUD|UPTD\s+PUSKESMAS|UPTD\s+LABKESDA|UPTD\s+SKB|IFK|GFK|SKB)\b.*$/i', '', $jab);
            $jab = preg_replace('/\s+(DINAS|BADAN|INSPEKTORAT|KECAMATAN|KELURAHAN)\s+[A-Z\s]+$/i', '', $jab);
        }

        // Bersihkan embel-embel nama sekolah pada jabatan Guru (karena sudah tertera di kolom Unit Kerja)
        if (preg_match('/^GURU\b/i', $jab)) {
            $jab = preg_replace('/\s+(PADA|DI|UPTD\s+SPF|UPTD\s+SMP|UPTD\s+SD|UPTD|SDN|SMPN|SMAN|TKN|TK\s+NEGERI|SD\s+NEG|SMP\s+NEG|TK\s+NEG|SD\s+NEGERI|SMP\s+NEGERI)\s+.*$/i', '', $jab);
        }

        // 1. Standarisasi Jenjang Fungsional Guru Format Lama (PermenPAN-RB & BKN)
        if (preg_match('/^GURU\b/i', $jab)) {
            $jab = preg_replace('/\b(TINGKAT|TK)[\.,\s]+[IVX\d]+\b/i', '', $jab);
            $jab = preg_replace('/\bGURU\s+PRATAMA\b/i', 'GURU AHLI PERTAMA', $jab);
            $jab = preg_replace('/\bGURU\s+DEWASA\b/i', 'GURU AHLI MUDA', $jab);
            $jab = preg_replace('/\bGURU\s+PEMBINA\s+UTAMA\b/i', 'GURU AHLI UTAMA', $jab);
            $jab = preg_replace('/\bGURU\s+PEMBINA\b/i', 'GURU AHLI MADYA', $jab);
        }

        // 2. Standarisasi [Profesi] [Pertama/Muda/Madya/Utama] -> [Profesi] AHLI [Jenjang]
        $profesiKeahlian = 'GURU|PERAWAT|BIDAN|DOKTER|AUDITOR|APOTEKER|ASISTEN APOTEKER|EPIDEMIOLOG|SANITARIAN|NUTRISIONIS|ARSIPARIS|PUSTAKAWAN|PRANATA KOMPUTER|PENYULUH|PENGUJI|INSTRUKTUR|PERENCANA|STATISTISI|PENELITI|ANALIS KEBIJAKAN|ADMINISTRATOR KESEHATAN|MEDIK VETERINER|PARAMEDIK VETERINER|PENGAWAS BIBIT TERNAK|PENGAWAS MUTU PAKAN|FISIOTERAPIS|PRANATA LABORATORIUM KESEHATAN|PRANATA LABORATORIUM|TERAPIS GIGI DAN MULUT|RADIOGRAFER|REFRAKSIONIS OPTISIEN|TEKNISI ELEKTROMEDIS|PEREKAM MEDIS|OKUPASI TERAPIS|TERAPIS WICARA|ORTOTIS PROSTETIS|TEKNISI GIGI|FISIKAWAN MEDIS|PEMBIMBING KESEHATAN KERJA|ENTOMOLOG KESEHATAN';
        $jab = preg_replace("/\b({$profesiKeahlian})\s+PERTAMA\b/i", '$1 AHLI PERTAMA', $jab);
        $jab = preg_replace("/\b({$profesiKeahlian})\s+MUDA\b/i", '$1 AHLI MUDA', $jab);
        $jab = preg_replace("/\b({$profesiKeahlian})\s+MADYA\b/i", '$1 AHLI MADYA', $jab);
        $jab = preg_replace("/\b({$profesiKeahlian})\s+UTAMA\b/i", '$1 AHLI UTAMA', $jab);

        // 3. Standarisasi Jenjang Keterampilan Format Lama: Pelaksana Lanjutan -> Mahir, Pelaksana -> Terampil
        $jab = preg_replace("/\b({$profesiKeahlian})\s+PELAKSANA\b/i", '$1 TERAMPIL', $jab);
        $jab = preg_replace("/\bPELAKSANA\s+({$profesiKeahlian})\b/i", '$1 TERAMPIL', $jab);
        $jab = preg_replace("/\bPELAKSANA\s*-\s*({$profesiKeahlian})\b/i", '$1 TERAMPIL', $jab);

        // 4. Koreksi Singkatan / Nomenklatur Mata Pelajaran & Teknis Pelaksana
        $jab = preg_replace('/\bPKN\b/i', 'PPKN', $jab);
        $jab = preg_replace('/\bPENJASKES\b/i', 'PENJASORKES', $jab);
        $jab = preg_replace('/\bTEHNIS\b/i', 'TEKNIS', $jab);
        $jab = preg_replace('/\bPENELAH\b/i', 'PENELAAH', $jab);
        $jab = preg_replace('/\bPENGOLA\b/i', 'PENGOLAH', $jab);
        $jab = preg_replace('/\bPENGADMINISTRASIAN\b/i', 'PENGADMINISTRASI', $jab);
        $jab = preg_replace('/\b(PELAKASANA|PELAKSAN)\b/i', 'PELAKSANA', $jab);

        // 6. Standarisasi Format Fungsional Tanpa Kata "AHLI" (Contoh: "GURU KELAS PERTAMA" -> "GURU KELAS AHLI PERTAMA", "PAMONG BELAJAR MADYA" -> "PAMONG BELAJAR AHLI MADYA")
        // Pengecualian: jangan tambahkan pada "SEKOLAH MENENGAH PERTAMA"
        $jab = preg_replace('/(?<!\bAHLI)(?<!\bMENENGAH)\s+(PERTAMA|MUDA|MADYA|UTAMA)\b/i', ' AHLI $1', $jab);

        // Spasi sebelum/setelah tanda baca titik dan koma
        $jab = preg_replace('/\s+([,\.])/', '$1', $jab);
        $jab = preg_replace('/([,\.])\s+/', '$1 ', $jab);
        // Standarisasi slash (III/a tanpa spasi)
        $jab = preg_replace('/\s*\/\s*/', '/', $jab);
        // Pastikan kata ulang tidak terpecah
        $jab = preg_replace('/\b(PERUNDANG|UNDANG|SARANA|ORGANISASI)\s*-\s*(UNDANGAN|UNDANG|PRASARANA)\b/i', '$1-$2', $jab);
        // Hapus spasi ganda atau tab liar
        $jab = preg_replace('/\s+/', ' ', trim($jab));

        // Koreksi typo umum di master data SIMPEG
        $jab = str_replace(
            ['TEHNOLOGI', 'KOMSUMSI', 'HOLTIKULTURA'],
            ['TEKNOLOGI', 'KONSUMSI', 'HORTIKULTURA'],
            $jab
        );

        return $jab;
    }
}
