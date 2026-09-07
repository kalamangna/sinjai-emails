<?php

namespace App\Shared\Services;

use App\Shared\Libraries\CpanelApi;
use App\Domains\Email\Models\EmailModel;
use App\Shared\Models\AppSettingModel;
use Exception;

class SyncService
{
    protected $cpanelApi;
    protected $emailModel;
    protected $appSettingModel;

    public function __construct()
    {
        $this->cpanelApi = new CpanelApi();
        $this->emailModel = new EmailModel();
        $this->appSettingModel = new AppSettingModel();
    }

    public function syncFromCpanel()
    {
        require_once APPPATH . 'Shared/Helpers/TanggalHelper.php';

        if ((defined('ENVIRONMENT') && ENVIRONMENT === 'development') || env('CI_ENVIRONMENT') === 'development') {
            log_message('info', 'SyncService: Sinkronisasi cPanel dilewati di environment lokal/development.');
            $this->saveSyncTimestamp('last_sync_time', untukDatabase('now'));
            $this->saveSyncTimestamp('last_sync_cpanel', untukDatabase('now'));
            \App\Shared\Services\CacheService::updateDashboardCache();

            return [
                'status'   => 'success',
                'success'  => true,
                'message'  => 'Sinkronisasi cPanel disimulasikan (Mode Lokal/Dev).',
                'synced'   => 0,
                'deleted'  => 0,
                'raw_data' => []
            ];
        }

        // 1. Fetch all accounts from cPanel
        $all_emails = $this->cpanelApi->get_email_accounts_detailed();

        // 2. Upsert (insert new / update existing) from cPanel data
        $this->emailModel->upsertBatch($all_emails);

        // 3. Soft-delete database records that no longer exist on cPanel
        //    Build a set of all email addresses returned by cPanel
        $cpanel_emails = array_column($all_emails, 'email');
        $cpanel_emails_map = array_flip($cpanel_emails);

        //    Fetch all non-deleted records from database (no filters on pensiun/soft delete)
        $db_emails = $this->emailModel->findAll();

        $to_delete = [];
        foreach ($db_emails as $record) {
            if (!isset($cpanel_emails_map[$record['email']])) {
                $to_delete[] = $record['id'];
            }
        }

        if (!empty($to_delete)) {
            // Soft-delete in batches to keep the query efficient
            foreach (array_chunk($to_delete, 500) as $chunk) {
                $this->emailModel->delete($chunk);
            }
            log_message('info', 'SyncService: Soft-deleted ' . count($to_delete) . ' email(s) no longer found on cPanel: ' . implode(', ', array_slice($to_delete, 0, 20)));
        }

        // 4. Update last sync timestamps
        $now = untukDatabase('now');
        $this->saveSyncTimestamp('last_sync_time', $now);
        $this->saveSyncTimestamp('last_sync_cpanel', $now);

        // 5. Update and warm up dashboard cache
        \App\Shared\Services\CacheService::updateDashboardCache();

        return [
            'status'   => 'success',
            'success'  => true,
            'message'  => 'Email data synchronization from cPanel was successful.',
            'synced'   => count($all_emails),
            'deleted'  => count($to_delete),
        ];
    }

    private function saveSyncTimestamp(string $key, string $value): void
    {
        $this->appSettingModel->where('key', $key)->set(['value' => $value])->update();
        if ($this->appSettingModel->affectedRows() == 0) {
            $this->appSettingModel->insert(['key' => $key, 'value' => $value]);
        }
    }
}
