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

        $isKecamatan = stripos($unitKerja['nama_unit_kerja'], 'Kecamatan') !== false;

        // Start building the query for the emails list
        $emailBuilder = $this->emailModel->withDetails()->whereIn('emails.unit_kerja_id', $allUnitIds);
        if ($isKecamatan && $pimpinan_desa == 0) {
            $emailBuilder->where('emails.pimpinan_desa', 0);
        }

        $applyFilters = function($builder) use ($search, $status_asn, $bsre_status, $password_status) {
            if ($search) {
                $builder->groupStart();
                $cleanSearch = str_replace([' ', '.', '-', '\''], '', $search);
                $builder->like('emails.email', $search)
                             ->orLike('emails.name', $search);
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
        $showUnitKerjaColumn = !empty($childrenIds);

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
        $statsBuilder = $this->emailModel->whereIn('emails.unit_kerja_id', $allUnitIds);
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
        $asnStatsBuilder = $this->emailModel->whereIn('emails.unit_kerja_id', $allUnitIds);
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
        $activeStatsBuilder = $this->emailModel->whereIn('emails.unit_kerja_id', $allUnitIds);
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

    public function syncPegawaiFromApi(string $nip): array
    {
        $currentEmail = $this->emailModel
            ->select('emails.*, unit_kerja.nama_unit_kerja')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
            ->where('emails.nip', $nip)
            ->first();

        // Guard: Hanya abaikan akun PPPK dan PPPK Paruh Waktu
        if ($currentEmail && !empty($currentEmail['status_asn_id'])) {
            $statusPppk = $this->statusAsnModel->where('nama_status_asn', 'PPPK')->asArray()->first();
            $statusPppkPw = $this->statusAsnModel->where('nama_status_asn', 'PPPK PARUH WAKTU')->asArray()->first();
            $excludeIds = array_filter([
                $statusPppk['id'] ?? null,
                $statusPppkPw['id'] ?? null,
            ]);

            if (in_array($currentEmail['status_asn_id'], $excludeIds)) {
                return [
                    'success' => true,
                    'skipped' => true,
                    'reason'  => 'pppk',
                    'message' => 'Akun PPPK / PPPK Paruh Waktu - Data tidak disinkronkan',
                    'current' => $currentEmail,
                    'data'    => [
                        'jabatan'          => $currentEmail['jabatan'] ?? '-',
                        'pangkat_nama'     => $currentEmail['pangkat_nama'] ?? '-',
                        'pangkat_golruang' => $currentEmail['pangkat_golruang'] ?? '-',
                    ]
                ];
            }
        }

        $pegawaiApi = new \App\Shared\Libraries\PegawaiApi();
        $result     = $pegawaiApi->getPegawaiData($nip);

        if (!$result['success']) {
            return ['success' => false, 'message' => $result['message'] ?? 'Gagal menghubungi API pegawai'];
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

        // 1. Sync Jabatan & Normalisasi
        $rawJabatan = $source['jabatan_nama'] ?? $source['jabatan'] ?? ($currentEmail['jabatan'] ?? null);
        if ($rawJabatan) {
            $cleanJab = $this->normalizeJabatanName($rawJabatan, $currentEmail['nama_unit_kerja'] ?? '');
            if ($cleanJab) {
                $updateData['jabatan'] = $cleanJab;

                if (!empty($source['jabatan_jenis_eselon'])) {
                    $eselonStr   = str_replace(['.', ' '], '', $source['jabatan_jenis_eselon']);
                    $eselonModel = new \App\Shared\Models\EselonModel();
                    $eselon      = $eselonModel->where('nama_eselon', $eselonStr)->first();
                    if ($eselon) $updateData['eselon_id'] = $eselon['id'];
                }
            }
        }

        // 2. Sync Pangkat & Golongan
        if (isset($source['pangkat_nama']))    $updateData['pangkat_nama']    = trim($source['pangkat_nama']);
        if (isset($source['pangkat_golruang'])) $updateData['pangkat_golruang'] = trim($source['pangkat_golruang']);

        // 3. Sync Unit Kerja jika terjadi mutasi / pindah tugas di SIMPEG
        if (!empty($source['unit_id'])) {
            $targetUnit = $this->unitKerjaModel->where('api_unit_id', $source['unit_id'])->first();
            if ($targetUnit) {
                $resolvedUnitId = $targetUnit['id'];

                // Pimpinan Utama Setda (Sekda, Asisten, Staf Ahli, Bupati, Wabup) selalu berada langsung di SEKRETARIAT DAERAH (bukan sub-unit Bagian)
                $isTopSetdaLeader = false;
                $upperRawJab = strtoupper($rawJabatan ?? '');
                if (stripos($upperRawJab, 'SEKRETARIS DAERAH') !== false 
                    || (stripos($upperRawJab, 'ASISTEN') !== false && stripos($upperRawJab, 'ASISTEN APOTEKER') === false)
                    || stripos($upperRawJab, 'STAF AHLI') !== false
                    || stripos($upperRawJab, 'BUPATI') !== false
                    || stripos($upperRawJab, 'WAKIL BUPATI') !== false) {
                    $isTopSetdaLeader = true;
                }

                // Cek apakah jabatan menyebutkan sub-unit khusus (misal Bagian, Kelurahan, Sekolah, Puskesmas) di bawah targetUnit
                $childUnits = !$isTopSetdaLeader ? $this->unitKerjaModel->where('parent_id', $targetUnit['id'])->findAll() : [];
                if (!empty($childUnits) && !empty($rawJabatan)) {
                    $cleanRawJab = strtoupper(str_replace(['/', '-', '.', ','], ' ', $rawJabatan));
                    foreach ($childUnits as $child) {
                        $childName = strtoupper($child['nama_unit_kerja']);
                        $cleanChildName = trim(preg_replace('/^(BAGIAN|KELURAHAN|PUSKESMAS|UPTD|SMPN|SDN|TK)\s+/i', '', $childName));
                        $cleanChildNormalized = str_replace(['/', '-', '.', ','], ' ', $cleanChildName);

                        if (stripos($cleanRawJab, $cleanChildNormalized) !== false) {
                            $resolvedUnitId = $child['id'];
                            break;
                        }

                        // Mapping sinonim umum Bagian Setda
                        if (strpos($childName, 'KESRA') !== false && (strpos($cleanRawJab, 'KESRA') !== false || strpos($cleanRawJab, 'KESEJAHTERAAN RAKYAT') !== false)) {
                            $resolvedUnitId = $child['id'];
                            break;
                        }
                        if (strpos($childName, 'PERENCANAAN') !== false && strpos($cleanRawJab, 'PERENCANAAN') !== false) {
                            $resolvedUnitId = $child['id'];
                            break;
                        }
                        if (strpos($childName, 'PENGADAAN') !== false && strpos($cleanRawJab, 'PENGADAAN') !== false) {
                            $resolvedUnitId = $child['id'];
                            break;
                        }
                        if (strpos($childName, 'UMUM') !== false && strpos($cleanRawJab, 'UMUM') !== false && strpos($cleanRawJab, 'HUKUM') === false) {
                            $resolvedUnitId = $child['id'];
                            break;
                        }
                    }
                }

                // Jika akun saat ini sudah berada di sub-unit dari targetUnit dan tidak terdeteksi sub-unit baru (dan bukan Top Leader Setda), pertahankan sub-unit yang ada
                if (!$isTopSetdaLeader && $resolvedUnitId == $targetUnit['id'] && !empty($currentEmail['unit_kerja_id'])) {
                    $currentUnit = $this->unitKerjaModel->find($currentEmail['unit_kerja_id']);
                    if ($currentUnit && $currentUnit['parent_id'] == $targetUnit['id']) {
                        $resolvedUnitId = $currentUnit['id'];
                    }
                }

                if ($resolvedUnitId != $currentEmail['unit_kerja_id']) {
                    $updateData['unit_kerja_id'] = $resolvedUnitId;
                }
            }
        }

        if (!empty($updateData)) {
            $this->emailModel->where('nip', $nip)->set($updateData)->update();

            \App\Shared\Services\CacheService::invalidateDashboard();

            $responseData = array_merge($currentEmail ?: [], $updateData);

            return [
                'success' => true,
                'updated' => true,
                'message' => 'Data pegawai dan jabatan berhasil disinkronkan & dinormalkan',
                'data'    => $responseData,
            ];
        }

        return [
            'success' => true,
            'updated' => false,
            'message' => $isPimpinan ? 'Akun Pimpinan - Data jabatan tetap dipertahankan' : 'Data sudah terbaru',
            'data'    => [
                'jabatan'          => $currentEmail['jabatan'] ?? '-',
                'pangkat_nama'     => $currentEmail['pangkat_nama'] ?? '-',
                'pangkat_golruang' => $currentEmail['pangkat_golruang'] ?? '-',
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

        $jab = mb_strtoupper(trim($jabatan), 'UTF-8');
        // Hapus karakter liar di akhir (titik ganda, titik koma, titik satu di ujung)
        $jab = preg_replace('/[,\.]+\s*$/', '', $jab);
        // Padatkan spasi ganda atau tab liar sejak awal
        $jab = preg_replace('/\s+/', ' ', $jab);

        // Hapus prefix PLT. / PLH. / PJ. / PJS.
        $jab = preg_replace('/^\s*(PLT|PLH|PJ|PJS)\.?\s+/i', '', $jab);

        // Jika akun pimpinan atau nama jabatan kepala dinas / badan / bagian / satpol / rsud / inspektorat / camat / lurah, terapkan format ringkas pimpinan
        if ($isPimpinan 
            || stripos($jab, 'KEPALA DINAS') === 0 
            || stripos($jab, 'KEPALA BADAN') === 0 
            || stripos($jab, 'KEPALA BAGIAN') === 0 
            || stripos($jab, 'KEPALA SATUAN') === 0 
            || stripos($jab, 'KEPALA SATPOL') === 0 
            || stripos($jab, 'DIREKTUR') === 0 
            || stripos($jab, 'CAMAT') === 0 
            || stripos($jab, 'LURAH') === 0 
            || (stripos($jab, 'INSPEKTUR') === 0 && stripos($jab, 'PEMBANTU') === false)
        ) {
            if (!empty($unitKerjaName)) {
                $unitUpper = strtoupper($unitKerjaName);
                if (strpos($unitUpper, 'INSPEKTORAT') !== false) {
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
                } elseif (strpos($unitUpper, 'KELURAHAN') !== false) {
                    return 'LURAH';
                }
            }
            if (stripos($jab, 'INSPEKTUR') === 0 && stripos($jab, 'PEMBANTU') === false) return 'INSPEKTUR';
            if (stripos($jab, 'KEPALA SATUAN') === 0 || stripos($jab, 'KEPALA SATPOL') === 0) return 'KEPALA SATUAN';
            if (stripos($jab, 'KEPALA BAGIAN') === 0) return 'KEPALA BAGIAN';
            if (stripos($jab, 'DIREKTUR') === 0) return 'DIREKTUR';
            if (stripos($jab, 'KEPALA DINAS') === 0) return 'KEPALA DINAS';
            if (stripos($jab, 'KEPALA BADAN') === 0) return 'KEPALA BADAN';
            if (stripos($jab, 'CAMAT') === 0) return 'CAMAT';
            if (stripos($jab, 'LURAH') === 0) return 'LURAH';
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

        // Resolusi jabatan kombinasi garis miring (Opsi B: Prioritas Tugas Manajerial/Kepala/Pimpinan)
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
                if (!$chosen) {
                    $chosen = $parts[0];
                }
                $jab = $chosen;
            }
        }

        // Tambahkan prefix KEPALA jika di SIMPEG hanya tertulis "BIDANG ...", "SUB BAGIAN ...", "SEKSI ...", "SUB BIDANG ..."
        if (preg_match('/^(BIDANG|SUB BAGIAN|SUB\. BAGIAN|SUB BIDANG|SEKSI)\s+/i', $jab) && stripos($jab, 'KEPALA') === false) {
            $jab = 'KEPALA ' . $jab;
        }

        // Koreksi SUB. BAGIAN / SUB. BIDANG menjadi SUB BAGIAN / SUB BIDANG
        $jab = preg_replace('/\bSUB\.\s*/i', 'SUB ', $jab);

        // Bersihkan embel-embel nama instansi di ujung nama Kabid/Kasubag/Kasi/Kasubid
        if (preg_match('/^(KEPALA BIDANG|KEPALA SUB BAGIAN|KEPALA SEKSI|KEPALA SUB BIDANG)\s+/i', $jab)) {
            $jab = preg_replace('/\s+(SEKRETARIAT|PADA|KEC\.|KECAMATAN|DINAS|BADAN|INSPEKTORAT|SATPOL PP|SATUAN POLISI|UPTD RSUD|RSUD)\s+.*$/i', '', $jab);
        }

        // 1. Standarisasi Jenjang Fungsional Guru Format Lama (PermenPAN-RB & BKN)
        $jab = preg_replace('/\bGURU\s+PRATAMA(\s+TK\.?\s*I)?\b/i', 'GURU AHLI PERTAMA', $jab);
        $jab = preg_replace('/\bGURU\s+DEWASA(\s+TK\.?\s*I)?\b/i', 'GURU AHLI MUDA', $jab);
        $jab = preg_replace('/\bGURU\s+MUDA\s+TK\.?\s*I\b/i', 'GURU AHLI MUDA', $jab);
        $jab = preg_replace('/\bGURU\s+PEMBINA\s+UTAMA\b/i', 'GURU AHLI UTAMA', $jab);
        $jab = preg_replace('/\bGURU\s+PEMBINA(\s+TK\.?\s*I)?\b/i', 'GURU AHLI MADYA', $jab);

        // 2. Standarisasi [Profesi] [Pertama/Muda/Madya/Utama] -> [Profesi] AHLI [Jenjang]
        $profesiKeahlian = 'GURU|PERAWAT|BIDAN|DOKTER|AUDITOR|APOTEKER|EPIDEMIOLOG|SANITARIAN|NUTRISIONIS|ARSIPARIS|PUSTAKAWAN|PRANATA KOMPUTER|PENYULUH|PENGUJI|INSTRUKTUR|PERENCANA|STATISTISI|PENELITI|ANALIS KEBIJAKAN|ADMINISTRATOR KESEHATAN';
        $jab = preg_replace("/\b({$profesiKeahlian})\s+PERTAMA\b/i", '$1 AHLI PERTAMA', $jab);
        $jab = preg_replace("/\b({$profesiKeahlian})\s+MUDA\b/i", '$1 AHLI MUDA', $jab);
        $jab = preg_replace("/\b({$profesiKeahlian})\s+MADYA\b/i", '$1 AHLI MADYA', $jab);
        $jab = preg_replace("/\b({$profesiKeahlian})\s+UTAMA\b/i", '$1 AHLI UTAMA', $jab);

        // 3. Standarisasi Jenjang Keterampilan Format Lama: Pelaksana Lanjutan -> Mahir, Pelaksana -> Terampil
        $jab = preg_replace("/\b({$profesiKeahlian})\s+PELAKSANA\s+LANJUTAN\b/i", '$1 MAHIR', $jab);
        $jab = preg_replace("/\bPELAKSANA\s+LANJUTAN\s*-\s*({$profesiKeahlian})\b/i", 'MAHIR - $1', $jab);
        $jab = preg_replace("/\b({$profesiKeahlian})\s+PELAKSANA\b/i", '$1 TERAMPIL', $jab);
        $jab = preg_replace("/\bPELAKSANA\s+({$profesiKeahlian})\b/i", '$1 TERAMPIL', $jab);

        // 4. Koreksi Singkatan / Nomenklatur Mata Pelajaran & Teknis Pelaksana
        $jab = preg_replace('/\bPKN\b/i', 'PPKN', $jab);
        $jab = preg_replace('/\bTEHNIS\b/i', 'TEKNIS', $jab);
        $jab = preg_replace('/\bPENELAH\b/i', 'PENELAAH', $jab);
        $jab = preg_replace('/\bPENGOLA\b/i', 'PENGOLAH', $jab);
        $jab = preg_replace('/\bPENGADMINISTRASIAN\b/i', 'PENGADMINISTRASI', $jab);
        $jab = preg_replace('/\b(PELAKASANA|PELAKSAN)\b/i', 'PELAKSANA', $jab);

        // 5. Standarisasi Format SSCASN (JENJANG - NAMA PROFESI) menjadi Format Baku PermenPAN-RB & BKN (NAMA PROFESI JENJANG)
        // Contoh: "AHLI PERTAMA - APOTEKER" -> "APOTEKER AHLI PERTAMA", "TERAMPIL - PERAWAT" -> "PERAWAT TERAMPIL"
        if (preg_match('/^(AHLI PERTAMA|AHLI MUDA|AHLI MADYA|AHLI UTAMA|TERAMPIL|MAHIR|PENYELIA|PEMULA)\s*-\s*(.+)$/i', $jab, $matches)) {
            $jenjang = trim($matches[1]);
            $profesi = trim($matches[2]);
            $jab = $profesi . ' ' . $jenjang;
        }

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
