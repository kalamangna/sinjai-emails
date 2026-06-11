<?php

namespace App\Domains\Dashboard\Controllers;

use App\Shared\BaseController;

class Home extends BaseController
{
    public function index(): string
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'dashboard_summary_data_v3';

        if (!$data = $cache->get($cacheKey)) {
            $emailModel = new \App\Domains\Email\Controllers\EmailModel();
            $webOpdModel = new \App\Domains\Website\Controllers\WebOpdModel();
            $webDesaModel = new \App\Domains\Website\Controllers\WebDesaKelurahanModel();
            $assistanceModel = new \App\Domains\Assistance\Controllers\AssistanceModel();
            $appSettingModel = new \App\Shared\Models\AppSettingModel();
            $statusAsnModel = new \App\Shared\Models\StatusAsnModel();

            // Email Stats (Raw Status from database/API)
            $raw_stats = $emailModel->select('bsre_status, pimpinan, pimpinan_desa, nip, unit_kerja_id, COUNT(id) as count')
                ->allowCallbacks(false)
                ->groupBy('bsre_status')
                ->groupBy('pimpinan')
                ->groupBy('pimpinan_desa')
                ->groupBy('nip')
                ->groupBy('unit_kerja_id')
                ->findAll();

            $email_stats_map = [];
            $total_emails = 0;
            $active_bsre = 0;
            $not_synced_count = 0;
            $non_tte_count = 0;

            foreach ($raw_stats as $row) {
                $count = (int)$row['count'];
                $total_emails += $count;

                $isNeedTte = !empty($row['nip']) || ($row['pimpinan'] == 1) || ($row['pimpinan_desa'] == 1) || !empty($row['unit_kerja_id']);

                if (!$isNeedTte) {
                    $non_tte_count += $count;
                } elseif (empty($row['bsre_status'])) {
                    $not_synced_count += $count;
                } else {
                    $status = strtoupper($row['bsre_status']);
                    if (!isset($email_stats_map[$status])) {
                        $email_stats_map[$status] = 0;
                    }
                    $email_stats_map[$status] += $count;
                    if ($status === 'ISSUE') {
                        $active_bsre += $count;
                    }
                }
            }

            $email_stats = [];
            foreach ($email_stats_map as $label => $count) {
                $email_stats[] = ['label' => $label, 'count' => $count];
            }
            if ($not_synced_count > 0) {
                $email_stats[] = ['label' => 'NOT_SYNCED', 'count' => $not_synced_count];
            }
            if ($non_tte_count > 0) {
                $email_stats[] = ['label' => 'NON_TTE', 'count' => $non_tte_count];
            }

            // Custom sort for Email/TTE Status
            $tteOrder = ['ISSUE', 'EXPIRED', 'NO_CERTIFICATE', 'NOT_REGISTERED', 'NOT_SYNCED', 'NON_TTE'];
            usort($email_stats, function ($a, $b) use ($tteOrder) {
                $posA = array_search(strtoupper($a['label']), $tteOrder);
                $posB = array_search(strtoupper($b['label']), $tteOrder);
                if ($posA === false) $posA = 999;
                if ($posB === false) $posB = 999;
                return $posA - $posB;
            });

            // Status ASN Stats - Optimized: Single Aggregated Query
            $asn_stats_raw = $emailModel->select('status_asn_id, COUNT(id) as count')
                ->allowCallbacks(false)
                ->groupBy('status_asn_id')
                ->findAll();

            // Get status names
            $statuses = $statusAsnModel->select('id, nama_status_asn')->asArray()->findAll();
            $status_map = [];
            foreach ($statuses as $s) {
                $status_map[$s['id']] = $s['nama_status_asn'];
            }

            $status_asn_stats = [];
            foreach ($asn_stats_raw as $row) {
                $count = (int)$row['count'];
                if ($count > 0) {
                    if ($row['status_asn_id'] === null) {
                        $status_asn_stats[] = ['label' => 'LAINNYA', 'count' => $count];
                    } else {
                        $label = $status_map[$row['status_asn_id']] ?? 'UNKNOWN';
                        $status_asn_stats[] = [
                            'label' => strtoupper($label),
                            'count' => $count
                        ];
                    }
                }
            }

            // Custom sort for Status ASN
            $asnOrder = ['PNS', 'PPPK', 'PPPK PARUH WAKTU'];
            usort($status_asn_stats, function ($a, $b) use ($asnOrder) {
                $posA = array_search(strtoupper($a['label']), $asnOrder);
                $posB = array_search(strtoupper($b['label']), $asnOrder);
                if ($posA === false) $posA = 999;
                if ($posB === false) $posB = 999;
                return $posA - $posB;
            });

            // Website Stats - Optimized: Single query with conditional aggregation
            $web_stats = [
                'opd_aktif' => $webOpdModel->where('status', 'AKTIF')->countAllResults(),
                'opd_total' => $webOpdModel->countAllResults(),
                'desa_aktif' => 0,
                'desa_total' => 0,
                'kelurahan_aktif' => 0,
                'kelurahan_total' => 0,
            ];

            $desa_stats_raw = $webDesaModel->select("
                SUM(CASE WHEN desa_kelurahan NOT LIKE '%Kelurahan%' AND status = 'AKTIF' THEN 1 ELSE 0 END) as desa_aktif_count,
                SUM(CASE WHEN desa_kelurahan NOT LIKE '%Kelurahan%' THEN 1 ELSE 0 END) as desa_total_count,
                SUM(CASE WHEN desa_kelurahan LIKE '%Kelurahan%' AND status = 'AKTIF' THEN 1 ELSE 0 END) as kel_aktif_count,
                SUM(CASE WHEN desa_kelurahan LIKE '%Kelurahan%' THEN 1 ELSE 0 END) as kel_total_count
            ")->first();
            
            $web_stats['desa_aktif'] = (int)($desa_stats_raw['desa_aktif_count'] ?? 0);
            $web_stats['desa_total'] = (int)($desa_stats_raw['desa_total_count'] ?? 0);
            $web_stats['kelurahan_aktif'] = (int)($desa_stats_raw['kel_aktif_count'] ?? 0);
            $web_stats['kelurahan_total'] = (int)($desa_stats_raw['kel_total_count'] ?? 0);

            // Assistance Stats
            $total_assistance = $assistanceModel->countAllResults();
            $bulan = \bulanSekarang();
            $tahun = \tahunSekarang();
            $total_assistance_monthly = $assistanceModel->where('MONTH(tanggal_kegiatan)', $bulan)->where('YEAR(tanggal_kegiatan)', $tahun)->countAllResults();

            // Synchronization Timestamps
            $syncSettings = $appSettingModel->whereIn('key', [
                'last_sync_time', 
                'last_sync_tte', 
                'last_sync_pegawai', 
                'last_sync_website'
            ])->asArray()->findAll();
            
            $sync_map = [];
            foreach ($syncSettings as $s) {
                $sync_map[$s['key']] = $s['value'];
            }

            $data = [
                'email_stats' => $email_stats,
                'total_emails' => $total_emails,
                'active_bsre' => $active_bsre,
                'status_asn_stats' => $status_asn_stats,
                'web_stats' => $web_stats,
                'total_assistance' => $total_assistance,
                'total_assistance_monthly' => $total_assistance_monthly,
                'last_sync_time' => $sync_map['last_sync_time'] ?? null,
                'last_sync_tte' => $sync_map['last_sync_tte'] ?? null,
                'last_sync_pegawai' => $sync_map['last_sync_pegawai'] ?? null,
                'last_sync_website' => $sync_map['last_sync_website'] ?? null,
                'title' => 'Dashboard',
                'meta_description' => 'Ringkasan Statistik Identitas Digital, Sertifikat Elektronik, dan Pemantauan Website Pemerintah Kabupaten Sinjai.',
            ];

            // Cache for 10 minutes (600 seconds)
            $cache->save($cacheKey, $data, 600);
        }

        return view('home/index', $data);
    }
}
