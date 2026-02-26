<?php

namespace App\Services\Features;

use App\Libraries\CpanelApi;
use App\Models\EmailModel;
use App\Models\AppSettingModel;
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
        helper('tanggal');
        $all_emails = $this->cpanelApi->get_email_accounts_detailed();
        $this->emailModel->upsertBatch($all_emails);

        $this->appSettingModel->where('key', 'last_sync_time')->set(['value' => untukDatabase('now')])->update();
        if ($this->appSettingModel->affectedRows() == 0) {
            $this->appSettingModel->insert(['key' => 'last_sync_time', 'value' => untukDatabase('now')]);
        }

        return ['success' => true, 'message' => 'Email data synchronization from cPanel was successful.'];
    }
}
