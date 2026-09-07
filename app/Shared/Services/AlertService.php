<?php

namespace App\Shared\Services;

use App\Domains\Email\Models\EmailModel;
use App\Domains\Website\Models\WebDesaKelurahanModel;
use App\Shared\Libraries\TelegramLibrary;
use CodeIgniter\CLI\CLI;

class AlertService
{
    protected $telegram;

    public function __construct()
    {
        $this->telegram = new TelegramLibrary();
    }

    public function getHighQuotaAccounts(float $threshold = 90.0)
    {
        $emailModel = new EmailModel();
        return $emailModel->select('emails.*, unit_kerja.nama_unit_kerja as unit_name')
                          ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                          ->where('emails.diskusedpercent_float >=', $threshold)
                          ->orderBy('emails.diskusedpercent_float', 'DESC')
                          ->findAll();
    }

    public function getExpiredTtePimpinan()
    {
        $emailModel = new EmailModel();
        return $emailModel->select('emails.email, emails.name, emails.nip, emails.nik, emails.jabatan, unit_kerja.nama_unit_kerja as unit_name')
                          ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'inner')
                          ->where('emails.bsre_status', 'EXPIRED')
                          ->where('emails.deleted_at IS NULL')
                          ->where('emails.unit_kerja_id IS NOT NULL')
                          ->groupStart()
                              ->where('emails.pimpinan', 1)
                              ->orWhere('emails.pimpinan_desa', 1)
                          ->groupEnd()
                          ->findAll();
    }

    public function getExpiredTtePegawaiGroupedByUnitKerja()
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                COALESCE(p.nama_unit_kerja, u.nama_unit_kerja) as unit_name,
                COUNT(e.id) as total
            FROM emails e
            INNER JOIN unit_kerja u ON u.id = e.unit_kerja_id
            LEFT JOIN unit_kerja p ON p.id = COALESCE(u.parent_id, u.id)
            WHERE e.bsre_status = 'EXPIRED'
              AND e.deleted_at IS NULL
              AND e.unit_kerja_id IS NOT NULL
              AND (e.pimpinan = 0 OR e.pimpinan IS NULL)
              AND (e.pimpinan_desa = 0 OR e.pimpinan_desa IS NULL)
              AND e.nip IS NOT NULL
              AND e.nip != ''
            GROUP BY unit_name
            ORDER BY total DESC, unit_name ASC
        ")->getResultArray();
    }

    public function getExpiringWebsites(int $days = 30)
    {
        $webDesaModel = new WebDesaKelurahanModel();
        return $webDesaModel->where('sisa_hari <=', $days)
                            ->orderBy('sisa_hari', 'ASC')
                            ->findAll();
    }

    public function appendTteReport(\App\Shared\Libraries\TelegramMessageBuilder $builder, string $mode = 'HARIAN', bool $isSummary = false)
    {
        $isHarian = strtoupper($mode) === 'HARIAN';

        if ($isHarian) {
            $expired = $this->getExpiredTtePimpinan();
            $count = count($expired);

            if ($count === 0) {
                if ($isSummary) {
                    $builder->addKeyValue('Status TTE', 'Semua Aktif', '✅');
                }
                return;
            }

            $builder->addSection("TTE Expired ($count Akun)", "⚠️");

            foreach (array_slice($expired, 0, 5) as $acc) {
                $name = htmlspecialchars(mb_strtoupper(trim($acc['name'] ?? $acc['email'])), ENT_NOQUOTES, 'UTF-8');
                $unit = trim($acc['unit_name'] ?? '');
                $jabatan = trim($acc['jabatan'] ?? '');

                if (!empty($unit) && !empty($jabatan)) {
                    if (mb_stripos($jabatan, $unit) !== false) {
                        $instansi = $jabatan;
                    } elseif (mb_stripos($unit, 'DESA') === 0 && mb_stripos($jabatan, 'KEPALA DESA') !== false) {
                        $instansi = $unit;
                    } else {
                        $instansi = $unit;
                    }
                } else {
                    $instansi = $unit ?: $jabatan;
                }

                $instansiText = !empty($instansi) ? htmlspecialchars(mb_strtoupper($instansi), ENT_NOQUOTES, 'UTF-8') : '';
                $email = htmlspecialchars($acc['email'] ?? '', ENT_NOQUOTES, 'UTF-8');

                $lines = ["👤 <b>{$name}</b>"];
                if ($instansiText !== '') {
                    $lines[] = "🏛️ {$instansiText}";
                }
                if ($email !== '') {
                    $lines[] = "📧 {$email}";
                }

                $builder->addText(implode("\n", $lines));
            }

            if ($count > 5) {
                $builder->addText("<i>...dan " . ($count - 5) . " akun lainnya.</i>");
            }
        } else {
            // Mode Bulanan / Penuh: Tampilkan rekap per unit kerja
            $grouped = $this->getExpiredTtePegawaiGroupedByUnitKerja();
            if (empty($grouped)) {
                if ($isSummary) {
                    $builder->addKeyValue('TTE Pegawai', 'Semua Aktif', '✅');
                }
                return;
            }

            $totalExpired = array_sum(array_column($grouped, 'total'));
            $totalUnits = count($grouped);

            $builder->addSection("TTE Pegawai Expired ($totalExpired Akun)", "⚠️");

            $unitLines = [];
            foreach (array_slice($grouped, 0, 6) as $row) {
                $unitName = htmlspecialchars(mb_strtoupper(trim($row['unit_name'])), ENT_NOQUOTES, 'UTF-8');
                $total = (int)$row['total'];
                $unitLines[] = "🏛️ <b>{$unitName}</b>: {$total} Akun";
            }

            $builder->addText(implode("\n", $unitLines));

            if ($totalUnits > 6) {
                $builder->addText("<i>...dan " . ($totalUnits - 6) . " unit kerja lainnya.</i>");
            }
        }
    }

    public function appendQuotaReport(\App\Shared\Libraries\TelegramMessageBuilder $builder, bool $isSummary = false)
    {
        $highUsage = $this->getHighQuotaAccounts(90.0);
        $count = count($highUsage);

        if ($count === 0) {
            if ($isSummary) {
                $builder->addKeyValue('Kuota Email', 'Semua Normal', '✅');
            }
            return;
        }

        $builder->addSection("Kuota Kritis ($count Akun)", "⚠️");

        foreach (array_slice($highUsage, 0, 5) as $acc) {
            $name = htmlspecialchars(mb_strtoupper(trim($acc['name'] ?? $acc['email'])), ENT_NOQUOTES, 'UTF-8');
            $percent = round($acc['diskusedpercent_float'], 1);
            $used = htmlspecialchars($acc['humandiskused'] ?? '', ENT_NOQUOTES, 'UTF-8');
            $email = htmlspecialchars($acc['email'] ?? '', ENT_NOQUOTES, 'UTF-8');

            $lines = ["👤 <b>{$name}</b>"];
            $lines[] = "📊 {$percent}% ({$used})";
            if ($email !== '') {
                $lines[] = "📧 {$email}";
            }

            $builder->addText(implode("\n", $lines));
        }

        if ($count > 5) {
            $builder->addText("<i>...dan " . ($count - 5) . " akun lainnya.</i>");
        }
    }

    public function appendWebExpirationReport(\App\Shared\Libraries\TelegramMessageBuilder $builder, bool $isSummary = false)
    {
        $expiring = $this->getExpiringWebsites(30);
        $count = count($expiring);

        if ($count === 0) {
            if ($isSummary) {
                $builder->addKeyValue('Domain Web', 'Semua Aktif', '✅');
            }
            return;
        }

        $builder->addSection("Domain Expired ($count Web)", "🌐");

        foreach (array_slice($expiring, 0, 5) as $web) {
            $domain = htmlspecialchars($web['domain'] ?? '', ENT_NOQUOTES, 'UTF-8');
            $sisa = (int)$web['sisa_hari'];

            $lines = ["🌐 <b>{$domain}</b>"];
            $lines[] = "⏳ Sisa {$sisa} hari";

            $builder->addText(implode("\n", $lines));
        }

        if ($count > 5) {
            $builder->addText("<i>...dan " . ($count - 5) . " website lainnya.</i>");
        }
    }

    public function checkQuotaAlerts()
    {
        if (is_cli()) CLI::write('Checking for High Quota Usage Alerts...', 'yellow');
        try {
            $highUsageAccounts = $this->getHighQuotaAccounts(90.0);
            if (!empty($highUsageAccounts)) {
                $count = count($highUsageAccounts);
                if (is_cli()) CLI::write("Found $count accounts with high usage (>90%)", 'red');
                
                $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
                $builder->setTitle('KUOTA EMAIL (>90%)', '⚠️')
                        ->addDivider();
                $this->appendQuotaReport($builder);
                $this->telegram->sendMessage($builder->build());
            } else {
                if (is_cli()) CLI::write('No high usage accounts found.', 'green');
            }
        } catch (\Throwable $e) {
            if (is_cli()) CLI::error('Error checking quota alerts: ' . $e->getMessage());
        }
    }

    public function checkTteExpiredAlerts($sendIfSafe = true)
    {
        if (is_cli()) CLI::write('Checking for Expired TTE Alerts...', 'yellow');
        try {
            $expiredPimpinan = $this->getExpiredTtePimpinan();
            $count = count($expiredPimpinan);

            if ($count === 0) {
                if (is_cli()) CLI::write('No expired TTE accounts found.', 'green');
                if ($sendIfSafe) {
                    $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
                    $builder->setTitle('TTE PIMPINAN EXPIRED', '🔏')
                            ->addDivider()
                            ->addText("✅ Seluruh TTE pimpinan dalam kondisi aman.");
                    $this->telegram->sendMessage($builder->build());
                }
                return;
            }

            if (is_cli()) CLI::write("Total Pimpinan Expired: $count", 'cyan');
            $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
            $builder->setTitle('TTE PIMPINAN EXPIRED', '🔏')
                    ->addDivider();
            $this->appendTteReport($builder);
            $this->telegram->sendMessage($builder->build());

        } catch (\Throwable $e) {
            if (is_cli()) CLI::error('Error checking TTE expired alerts: ' . $e->getMessage());
        }
    }

    public function checkWebExpirationAlerts()
    {
        if (is_cli()) CLI::write('Checking for Expiring Website Domains...', 'yellow');
        try {
            $expiringWebs = $this->getExpiringWebsites(30);
            if (!empty($expiringWebs)) {
                $count = count($expiringWebs);
                if (is_cli()) CLI::write("Found $count website domains expiring soon", 'red');
                
                $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
                $builder->setTitle('DOMAIN EXPIRED (<30 HARI)', '🌐')
                        ->addDivider();
                $this->appendWebExpirationReport($builder);
                $this->telegram->sendMessage($builder->build());
            } else {
                if (is_cli()) CLI::write('No expiring website domains found.', 'green');
            }
        } catch (\Throwable $e) {
            if (is_cli()) CLI::error('Error checking website expiration alerts: ' . $e->getMessage());
        }
    }
}
