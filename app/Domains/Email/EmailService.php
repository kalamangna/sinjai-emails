<?php

namespace App\Domains\Email;

use App\Domains\Email\EmailModel;
use App\Domains\Email\PkModel;
use App\Domains\UnitKerja\UnitKerjaModel;
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

    public function getEmailDashboardData($search = null, $bsre_status = null, $perPage = 100)
    {
        $builder = $this->emailModel->withDetails();

        if (!empty($search)) {
            $builder->groupStart()
                ->like('email', $search)
                ->orLike('name', $search)
                ->orLike('nik', $search)
                ->orLike('nip', $search)
                ->groupEnd();
        }

        if ($bsre_status) {
            if ($bsre_status === 'not_synced') {
                $builder->groupStart()
                    ->where('bsre_status', null)
                    ->orWhere('bsre_status', '')
                    ->groupEnd();
            } else {
                $builder->where('bsre_status', $bsre_status);
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
                ->select('bsre_status, COUNT(id) as count')
                ->groupBy('bsre_status')
                ->asArray()
                ->findAll();

            $bsre_status_labels = [
                'ISSUE' => 'ISSUE',
                'EXPIRED' => 'EXPIRED',
                'RENEW' => 'RENEW',
                'WAITING_FOR_VERIFICATION' => 'WAITING_FOR_VERIFICATION',
                'NEW' => 'NEW',
                'NO_CERTIFICATE' => 'NO_CERTIFICATE',
                'NOT_REGISTERED' => 'NOT_REGISTERED',
                'SUSPEND' => 'SUSPEND',
                'REVOKE' => 'REVOKE',
                'not_synced' => 'NOT_SYNCED'
            ];

            $bsreStatusCounts = [];
            $notSyncedCount = 0;
            foreach ($rawBsreCounts as $row) {
                if (empty($row['bsre_status'])) {
                    $notSyncedCount += $row['count'];
                } else {
                    $bsreStatusCounts[] = [
                        'status' => $row['bsre_status'],
                        'label' => $bsre_status_labels[$row['bsre_status']] ?? $row['bsre_status'],
                        'count' => (int)$row['count']
                    ];
                }
            }
            if ($notSyncedCount > 0) {
                $bsreStatusCounts[] = [
                    'status' => 'not_synced',
                    'label' => 'NOT_SYNCED',
                    'count' => $notSyncedCount
                ];
            }

            // Custom sort for BSrE Status
            $tteOrder = ['ISSUE', 'EXPIRED', 'NO_CERTIFICATE', 'NOT_REGISTERED', 'NOT_SYNCED'];
            usort($bsreStatusCounts, function ($a, $b) use ($tteOrder) {
                $posA = array_search($a['status'], $tteOrder);
                $posB = array_search($b['status'], $tteOrder);
                
                if ($posA === false) $posA = 999;
                if ($posB === false) $posB = 999;
                
                if ($posA === $posB) return strcmp($a['label'], $b['label']);
                return $posA - $posB;
            });

            $summaryData = [
                'unit_kerja_list' => $unitKerjaList,
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
            'suspended_count' => $counts['suspended_count'] ?? 0,
            'active_bsre_count' => $counts['active_bsre_count'] ?? 0,
            'unit_kerja_list' => $summaryData['unit_kerja_list'],
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

        $isKecamatan = stripos($unitKerja['nama_unit_kerja'], 'Kecamatan') !== false;

        // Start building the query for the emails list
        $emailBuilder = $this->emailModel->withDetails()->whereIn('unit_kerja_id', $allUnitIds);
        if ($isKecamatan && $pimpinan_desa == 0) {
            $emailBuilder->where('pimpinan_desa', 0);
        }

        // Apply filters to the list query
        if ($search) {
            $emailBuilder->groupStart()
                ->like('email', $search)
                ->orLike('name', $search)
                ->orLike('nik', $search)
                ->orLike('nip', $search)
                ->groupEnd();
        }

        if ($status_asn) {
            $emailBuilder->where('emails.status_asn_id', $status_asn);
        }

        if ($bsre_status) {
            if ($bsre_status === 'not_synced') {
                $emailBuilder->groupStart()
                    ->where('emails.bsre_status', null)
                    ->orWhere('emails.bsre_status', '')
                    ->groupEnd();
            } else {
                $emailBuilder->where('emails.bsre_status', $bsre_status);
            }
        }

        // Get filtered count BEFORE pagination
        $filtered_count = $emailBuilder->countAllResults(false);

        $emails = $emailBuilder
            ->orderBy('emails.eselon_id IS NULL', 'ASC', false)
            ->orderBy('emails.eselon_id', 'ASC')
            ->orderBy('emails.status_asn_id IS NULL', 'ASC', false)
            ->orderBy('emails.status_asn_id', 'ASC')
            ->orderBy('emails.jabatan IS NULL', 'ASC', false)
            ->orderBy('emails.jabatan', 'ASC')
            ->orderBy('emails.name', 'ASC')
            ->paginate($perPage);
        
        $pager = $this->emailModel->pager;

        $bsre_status_options = [
            'ISSUE' => 'ISSUE',
            'EXPIRED' => 'EXPIRED',
            'RENEW' => 'RENEW',
            'WAITING_FOR_VERIFICATION' => 'WAITING_FOR_VERIFICATION',
            'NEW' => 'NEW',
            'NO_CERTIFICATE' => 'NO_CERTIFICATE',
            'NOT_REGISTERED' => 'NOT_REGISTERED',
            'SUSPEND' => 'SUSPEND',
            'REVOKE' => 'REVOKE',
            'not_synced' => 'NOT_SYNCED'
        ];

        $bsre_status_counts = [];
        
        // Calculate overall stats for the unit (not affected by filters)
        $statsBuilder = $this->emailModel->whereIn('unit_kerja_id', $allUnitIds);
        if ($isKecamatan && $pimpinan_desa == 0) {
            $statsBuilder->where('pimpinan_desa', 0);
        }

        $rawCounts = $statsBuilder->allowCallbacks(false)
            ->select('bsre_status, COUNT(*) as count')
            ->groupBy('bsre_status')
            ->findAll();

        foreach ($rawCounts as $row) {
            $statusKey = $row['bsre_status'] ?: 'not_synced';
            if (!isset($bsre_status_counts[$statusKey])) {
                $bsre_status_counts[$statusKey] = [
                    'label' => $bsre_status_options[$statusKey] ?? $statusKey,
                    'count' => 0
                ];
            }
            $bsre_status_counts[$statusKey]['count'] += $row['count'];
        }

        $tteOrder = ['ISSUE', 'EXPIRED', 'NO_CERTIFICATE', 'NOT_REGISTERED', 'not_synced'];
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
        $asnStatsBuilder = $this->emailModel->whereIn('unit_kerja_id', $allUnitIds);
        if ($isKecamatan && $pimpinan_desa == 0) {
            $asnStatsBuilder->where('pimpinan_desa', 0);
        }
        $rawAsnStats = $asnStatsBuilder->allowCallbacks(false)
            ->select('status_asn.nama_status_asn as label, COUNT(emails.id) as count')
            ->join('status_asn', 'status_asn.id = emails.status_asn_id', 'left')
            ->groupBy('status_asn.nama_status_asn')
            ->findAll();
        
        $status_asn_stats = [];
        foreach ($rawAsnStats as $stat) {
            $status_asn_stats[] = [
                'label' => $stat['label'] ?: 'NON ASN',
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
        $activeStatsBuilder = $this->emailModel->whereIn('unit_kerja_id', $allUnitIds);
        if ($isKecamatan && $pimpinan_desa == 0) {
            $activeStatsBuilder->where('pimpinan_desa', 0);
        }
        $active_count = $activeStatsBuilder->where('suspended_login', 0)->countAllResults();

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
            'pagination' => $pager,
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

        try {
            $cpanelApi->create_email_account($data['email'], $data['password'], $data['quota'] ?? 1024);
        } catch (Exception $e) {
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
                'name'       => $data['name'] ?? null,
                'jabatan'    => $data['jabatan'] ?? null,
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
                } catch (Exception $e2) {
                    log_message('error', 'Cleanup failed after local insert failure for ' . $data['email'] . ': ' . $e2->getMessage());
                }
                throw new Exception('Gagal menyimpan data ke database lokal.');
            }

            return $insertId;
        } catch (Exception $e) {
            // If it's a DB error (like duplicate NIK), also try to cleanup cPanel
            if (strpos($e->getMessage(), 'Duplicate entry') !== false || strpos($e->getMessage(), 'database') !== false) {
                try {
                    $cpanelApi->delete_email_account($data['email']);
                } catch (Exception $e2) {
                    log_message('error', 'Cleanup failed after DB exception for ' . $data['email'] . ': ' . $e2->getMessage());
                }
            }
            throw $e;
        }
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
}
