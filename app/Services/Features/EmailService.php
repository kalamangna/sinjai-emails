<?php

namespace App\Services\Features;

use App\Models\EmailModel;
use App\Models\UnitKerjaModel;
use App\Models\StatusAsnModel;
use App\Models\EselonModel;
use App\Models\PkModel;
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
        $parentUnitKerjaList = $this->unitKerjaModel->where('parent_id IS NULL')->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $unitKerjaList = [];
        foreach ($parentUnitKerjaList as $parentUnit) {
            $parentId = $parentUnit['id'];
            $childrenIds = $this->unitKerjaModel->where('parent_id', $parentId)->findColumn('id');
            $allUnitIds = array_merge([$parentId], $childrenIds ?: []);
            $emailCount = $this->emailModel->allowCallbacks(false)->whereIn('unit_kerja_id', $allUnitIds)->countAllResults();
            $parentUnit['email_count'] = $emailCount;
            $unitKerjaList[] = $parentUnit;
        }

        $allEselonOptions = $this->eselonModel->orderBy('nama_eselon', 'ASC')->findAll();
        $eselonCounts = [];
        foreach ($allEselonOptions as $option) {
            $count = $this->emailModel->allowCallbacks(false)
                ->where('eselon_id', $option['id'])
                ->countAllResults();
            $eselonCounts[] = [
                'id' => $option['id'],
                'name' => $option['nama_eselon'],
                'count' => $count
            ];
        }

        return [
            'unit_kerja_nav' => $unitKerjaList,
            'eselon_nav' => $eselonCounts
        ];
    }

    public function getEmailDashboardData($search = null, $bsre_status = null, $perPage = 100)
    {
        $builder = $this->emailModel;

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

        $counts = $this->emailModel->allowCallbacks(false)->select('COUNT(*) as total_emails, SUM(CASE WHEN suspended_login = 0 THEN 1 ELSE 0 END) as active_count, SUM(CASE WHEN suspended_login = 1 THEN 1 ELSE 0 END) as suspended_count')->first();

        $parentUnitKerjaList = $this->unitKerjaModel->where('parent_id IS NULL')->orderBy('nama_unit_kerja', 'ASC')->findAll();

        $unitKerjaList = [];
        foreach ($parentUnitKerjaList as $parentUnit) {
            $parentId = $parentUnit['id'];
            $childrenIds = $this->unitKerjaModel->where('parent_id', $parentId)->findColumn('id');
            $allUnitIds = array_merge([$parentId], $childrenIds ?: []);

            $emailCount = $this->emailModel->allowCallbacks(false)->whereIn('unit_kerja_id', $allUnitIds)->countAllResults();

            $parentUnit['email_count'] = $emailCount;
            $unitKerjaList[] = $parentUnit;
        }

        $allStatusAsnOptions = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
        $statusAsnCounts = [];
        foreach ($allStatusAsnOptions as $option) {
            $count = $this->emailModel->allowCallbacks(false)
                ->where('status_asn_id', $option['id'])
                ->countAllResults();
            $statusAsnCounts[] = [
                'id' => $option['id'],
                'name' => $option['nama_status_asn'],
                'count' => $count
            ];
        }

        $allEselonOptions = $this->eselonModel->orderBy('nama_eselon', 'ASC')->findAll();
        $eselonCounts = [];
        foreach ($allEselonOptions as $option) {
            $count = $this->emailModel->allowCallbacks(false)
                ->where('eselon_id', $option['id'])
                ->countAllResults();
            $eselonCounts[] = [
                'id' => $option['id'],
                'name' => $option['nama_eselon'],
                'count' => $count
            ];
        }

        $rawBsreCounts = $this->emailModel->allowCallbacks(false)
            ->select('bsre_status, COUNT(*) as count')
            ->groupBy('bsre_status')
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
            'not_synced' => 'NOT SYNCED'
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
                    'count' => $row['count']
                ];
            }
        }
        if ($notSyncedCount > 0) {
            $bsreStatusCounts[] = [
                'status' => 'not_synced',
                'label' => 'NOT SYNCED',
                'count' => $notSyncedCount
            ];
        }

        return [
            'emails' => $emails,
            'pager' => $pager,
            'total_emails' => $counts['total_emails'],
            'filtered_count' => $filtered_count,
            'active_count' => $counts['active_count'],
            'suspended_count' => $counts['suspended_count'],
            'unit_kerja_list' => $unitKerjaList,
            'status_asn_counts' => $statusAsnCounts,
            'eselon_counts' => $eselonCounts,
            'bsre_status_counts' => $bsreStatusCounts,
            'bsre_status_labels' => $bsre_status_labels
        ];
    }

    public function getEmailDetail($username)
    {
        $email_detail = $this->emailModel->where('user', $username)->first();
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
            'unit_kerja_options' => $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll(),
            'status_asn_options' => $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll(),
            'eselon_options' => $this->eselonModel->orderBy('nama_eselon', 'ASC')->findAll(),
        ];
    }

    public function getUnitKerjaDetail($unitKerjaId, $params = [])
    {
        $unitKerja = $this->unitKerjaModel->find($unitKerjaId);
        if (!$unitKerja) {
            throw new Exception('Unit Kerja not found.');
        }

        $children = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->findAll();
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
        $emailBuilder = $this->emailModel->whereIn('unit_kerja_id', $allUnitIds);
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
            'not_synced' => 'NOT SYNCED'
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

        uksort($bsre_status_counts, function ($a, $b) {
            if ($a === 'ISSUE') return -1;
            if ($b === 'ISSUE') return 1;
            return strcmp($a, $b);
        });

        $active_count = $bsre_status_counts['ISSUE']['count'] ?? 0;
        $total_emails_in_unit = array_sum(array_column($bsre_status_counts, 'count'));

        return [
            'unit_kerja' => $unitKerja,
            'parent_unit' => !empty($unitKerja['parent_id']) ? $this->unitKerjaModel->find($unitKerja['parent_id']) : null,
            'child_units' => $children,
            'emails' => $emails,
            'total_emails' => $total_emails_in_unit,
            'filtered_count' => $filtered_count,
            'active_count' => $active_count,
            'pagination' => $pager,
            'status_asn_options' => $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll(),
            'bsre_status_options' => $bsre_status_options,
            'bsre_status_counts' => $bsre_status_counts,
        ];
    }
}
