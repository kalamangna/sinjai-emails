<?php

namespace App\Domains\Helpdesk\Controllers;

use App\Domains\Helpdesk\Models\HelpdeskModel;

use App\Shared\BaseController;
use App\Domains\Assistance\Models\AssistanceModel;

class HelpdeskAdminController extends BaseController
{
    protected $helpdeskModel;

    public function __construct()
    {
        $this->helpdeskModel = new HelpdeskModel();
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
        $ticket = $this->helpdeskModel->find($id);
        if (!$ticket) return redirect()->back()->with('error', 'Tiket tidak ditemukan');

        $newStatus = $this->request->getPost('status');
        $adminNotes = $this->request->getPost('admin_notes');

        // Update ticket
        $this->helpdeskModel->update($id, [
            'status' => $newStatus,
            'admin_notes' => $adminNotes
        ]);

        // If marked as Selesai, push to Assistance log
        if ($newStatus === 'Selesai' && $ticket['status'] !== 'Selesai') {
            $assistanceModel = new AssistanceModel();
            $assistanceModel->insert([
                'tanggal_kegiatan' => date('Y-m-d H:i:s'),
                'agency_id' => $ticket['agency_id'],
                'agency_type' => $ticket['agency_type'],
                'agency_name' => $ticket['agency_name'],
                'category' => $ticket['category'],
                'method' => 'Online', // Default to online since it came via helpdesk
                'services' => json_encode([$ticket['service']]),
                'keterangan' => $ticket['kategori_layanan'] . ' (Via Helpdesk: ' . $ticket['tiket_id'] . ')'
            ]);
            
            helper('audit');
            log_audit('UPDATE', 'Helpdesk', $id, 'Completed ticket and logged to Assistance: ' . $ticket['tiket_id']);
        }

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
