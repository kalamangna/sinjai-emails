<?php

namespace App\Domains\Auth;

use App\Shared\BaseController;
use App\Shared\Models\AuditLogModel;

class AuditLogController extends BaseController
{
    public function index()
    {
        $auditModel = new AuditLogModel();
        
        $data = [
            'title' => 'Audit Trail (Log Aktivitas)',
            'logs'  => $auditModel->getLogsWithUser(200), // Get last 200 logs
        ];

        return view('audit_log/index', $data);
    }
}
