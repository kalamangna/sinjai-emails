<?php

namespace App\Domains\Helpdesk\Controllers;

use App\Domains\Helpdesk\Models\HelpdeskModel;
use App\Domains\Helpdesk\Services\HelpdeskService;
use App\Shared\BaseController;

class HelpdeskAdminController extends BaseController
{
    protected $helpdeskModel;
    protected $helpdeskService;

    public function __construct()
    {
        $this->helpdeskModel   = new HelpdeskModel();
        $this->helpdeskService = new HelpdeskService();
    }

    public function index()
    {
        $statusFilter = $this->request->getGet('status');

        $builder = $this->helpdeskModel;
        if ($statusFilter) {
            $builder->where('status', $statusFilter);
        }

        $perPage = 50;
        $tickets = $builder->orderBy('created_at', 'DESC')->paginate($perPage);

        $data = [
            'title' => 'Helpdesk Layanan',
            'tickets' => $tickets,
            'pager' => $this->helpdeskModel->pager,
            'statusFilter' => $statusFilter,
            'statusCounts' => [
                'Menunggu' => $this->helpdeskModel->where('status', 'Menunggu')->countAllResults(),
                'Diproses' => $this->helpdeskModel->where('status', 'Diproses')->countAllResults(),
                'Selesai'  => $this->helpdeskModel->where('status', 'Selesai')->countAllResults(),
            ]
        ];

        return view('helpdesk/admin_index', $data);
    }

    public function detail($id)
    {
        $ticket = $this->helpdeskModel->find($id);
        if (!$ticket) return redirect()->to('admin/helpdesk')->with('error', 'Tiket tidak ditemukan');

        $data = [
            'title' => 'Detail Helpdesk Layanan',
            'ticket' => $ticket
        ];

        return view('helpdesk/admin_detail', $data);
    }

    public function updateStatus($id)
    {
        $newStatus  = $this->request->getPost('status');
        $adminNotes = $this->request->getPost('admin_notes');

        $result = $this->helpdeskService->updateTicketStatus((int)$id, $newStatus, $adminNotes);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        helper('audit');
        log_audit('UPDATE', 'Helpdesk', $id, 'Updated ticket status to ' . $newStatus . ': ' . ($result['ticket']['tiket_id'] ?? ''));

        return redirect()->to('admin/helpdesk/detail/' . $id)->with('success', 'Status tiket berhasil diperbarui.');
    }

    public function delete($id)
    {
        $ticket = $this->helpdeskModel->find($id);
        if (!$ticket) return redirect()->to('admin/helpdesk')->with('error', 'Tiket tidak ditemukan');

        $this->helpdeskModel->delete($id);

        helper('audit');
        log_audit('DELETE', 'Helpdesk', $id, 'Deleted ticket: ' . $ticket['tiket_id']);

        return redirect()->to('admin/helpdesk')->with('success', 'Tiket berhasil dihapus.');
    }
}
