<?php

namespace App\Domains\Auth\Controllers;

use App\Shared\BaseController;
use App\Shared\Models\AuditLogModel;

class AuditLogController extends BaseController
{
    public function index()
    {
        $auditModel = new AuditLogModel();

        $filterAction = trim($this->request->getGet('action') ?? '');
        $filterEntity = trim($this->request->getGet('entity') ?? '');
        $search       = trim($this->request->getGet('search') ?? '');

        $perPage = 50;
        $logs = $auditModel
            ->applyFilters($filterAction, $filterEntity, $search)
            ->paginate($perPage);
        $pager = $auditModel->pager;

        // Fresh model instance for summaries (no leftover query state)
        $summaryModel = new AuditLogModel();

        $data = [
            'title'          => 'Audit Trail (Log Aktivitas)',
            'logs'           => $logs,
            'pager'          => $pager,
            'action_summary' => $summaryModel->getActionSummary(),
            'entity_summary' => (new AuditLogModel())->getEntitySummary(),
            'actions'        => (new AuditLogModel())->getDistinctActions(),
            'entities'       => (new AuditLogModel())->getDistinctEntities(),
            'filterAction'   => $filterAction,
            'filterEntity'   => $filterEntity,
            'search'         => $search,
        ];

        return view('audit_log/index', $data);
    }
}
