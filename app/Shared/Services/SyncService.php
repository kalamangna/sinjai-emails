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
            return [
                'status'   => 'success',
                'message'  => 'Sinkronisasi cPanel disimulasikan (Mode Lokal/Dev).',
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

        // 4. Update last sync timestamp
        $this->appSettingModel->where('key', 'last_sync_time')->set(['value' => untukDatabase('now')])->update();
        if ($this->appSettingModel->affectedRows() == 0) {
            $this->appSettingModel->insert(['key' => 'last_sync_time', 'value' => untukDatabase('now')]);
        }

        return [
            'success'  => true,
            'message'  => 'Email data synchronization from cPanel was successful.',
            'synced'   => count($all_emails),
            'deleted'  => count($to_delete),
        ];
    }
}
