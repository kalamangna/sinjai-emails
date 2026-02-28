<?php

namespace App\Shared\Services;

use App\Shared\Libraries\CpanelApi;
use App\Domains\Email\EmailModel;
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
        $all_emails = $this->cpanelApi->get_email_accounts_detailed();
        $this->emailModel->upsertBatch($all_emails);

        $this->appSettingModel->where('key', 'last_sync_time')->set(['value' => untukDatabase('now')])->update();
        if ($this->appSettingModel->affectedRows() == 0) {
            $this->appSettingModel->insert(['key' => 'last_sync_time', 'value' => untukDatabase('now')]);
        }

        return ['success' => true, 'message' => 'Email data synchronization from cPanel was successful.'];
    }
}
