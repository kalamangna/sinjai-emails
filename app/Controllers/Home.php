<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $emailModel = new \App\Models\EmailModel();
        $webOpdModel = new \App\Models\WebOpdModel();
        $webDesaModel = new \App\Models\WebDesaKelurahanModel();
        $assistanceModel = new \App\Models\AssistanceModel();
        $appSettingModel = new \App\Models\AppSettingModel();
        $statusAsnModel = new \App\Models\StatusAsnModel();

        // Email Stats (Raw Status from database/API)
        $raw_stats = $emailModel->select('bsre_status, COUNT(*) as count')
            ->allowCallbacks(false)
            ->groupBy('bsre_status')
            ->findAll();
        
        $email_stats = [];
        $total_emails = 0;
        $active_bsre = 0;
        foreach ($raw_stats as $row) {
            $status = $row['bsre_status'] ?: 'NOT SYNCED';
            $count = (int)$row['count'];
            if ($count > 0) {
                $email_stats[] = [
                    'label' => strtoupper($status),
                    'count' => $count
                ];
            }
            $total_emails += $count;
            if ($row['bsre_status'] === 'ISSUE') {
                $active_bsre = $count;
            }
        }

        // Status ASN Stats
        $statuses = $statusAsnModel->findAll();
        $status_asn_stats = [];
        foreach ($statuses as $s) {
            $count = $emailModel->where('status_asn_id', $s['id'])->countAllResults();
            if ($count > 0) {
                $status_asn_stats[] = [
                    'label' => strtoupper($s['nama_status_asn']),
                    'count' => $count
                ];
            }
        }
        $non_asn_count = $emailModel->where('status_asn_id', null)->countAllResults();
        if ($non_asn_count > 0) {
            $status_asn_stats[] = ['label' => 'NON ASN / LAINNYA', 'count' => $non_asn_count];
        }

        // Website Stats
        $web_stats = [
            'opd' => $webOpdModel->countAllResults(),
            'desa' => $webDesaModel->where('desa_kelurahan NOT LIKE', '%Kelurahan%')->countAllResults(),
            'kelurahan' => $webDesaModel->where('desa_kelurahan LIKE', '%Kelurahan%')->countAllResults(),
        ];

        $data = [
            'email_stats' => $email_stats,
            'total_emails' => $total_emails,
            'active_bsre' => $active_bsre,
            'status_asn_stats' => $status_asn_stats,
            'web_stats' => $web_stats,
            'total_assistance' => $assistanceModel->countAllResults(),
            'total_assistance_monthly' => $assistanceModel->where('MONTH(tanggal_kegiatan)', date('m'))->where('YEAR(tanggal_kegiatan)', date('Y'))->countAllResults(),
            'last_sync_time' => $appSettingModel->where('key', 'last_sync_time')->first()['value'] ?? null,
            'title' => 'Dashboard',
        ];

        return view('home/index', $data);
    }
}
