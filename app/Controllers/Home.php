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

        $data = [
            'total_emails' => $emailModel->countAllResults(),
            'total_bsre' => $emailModel->where('bsre_status', 'ISSUE')->countAllResults(),
            'total_web_opd' => $webOpdModel->countAllResults(),
            'total_web_desa' => $webDesaModel->countAllResults(),
            'total_assistance' => $assistanceModel->countAllResults(),
            'last_sync_time' => $appSettingModel->where('key', 'last_sync_time')->first()['value'] ?? null,
            'title' => 'Dashboard',
        ];

        return view('home/index', $data);
    }

    public function website_hub(): string
    {
        return view('home/website_hub');
    }
}
