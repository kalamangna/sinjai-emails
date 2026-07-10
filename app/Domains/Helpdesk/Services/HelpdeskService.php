<?php

namespace App\Domains\Helpdesk\Services;

use App\Domains\Helpdesk\Models\HelpdeskModel;
use App\Domains\Assistance\Models\AssistanceModel;

class HelpdeskService
{
    protected $helpdeskModel;
    protected $assistanceModel;

    public function __construct()
    {
        $this->helpdeskModel   = new HelpdeskModel();
        $this->assistanceModel = new AssistanceModel();
    }

    /**
     * Update status tiket helpdesk.
     * Jika status baru adalah 'Selesai' dan sebelumnya bukan, catat ke log Asistensi.
     */
    public function updateTicketStatus(int $id, string $newStatus, ?string $adminNotes): array
    {
        $ticket = $this->helpdeskModel->find($id);
        if (!$ticket) {
            return ['success' => false, 'message' => 'Tiket tidak ditemukan'];
        }

        // Update tiket
        $this->helpdeskModel->update($id, [
            'status'      => $newStatus,
            'admin_notes' => $adminNotes,
        ]);

        // Jika selesai, push otomatis ke log Asistensi
        if ($newStatus === 'Selesai' && $ticket['status'] !== 'Selesai') {
            $this->assistanceModel->insert([
                'tanggal_kegiatan' => date('Y-m-d H:i:s'),
                'agency_id'        => $ticket['agency_id'],
                'agency_type'      => $ticket['agency_type'],
                'agency_name'      => $ticket['agency_name'],
                'category'         => $ticket['category'],
                'method'           => 'Online',
                'services'         => json_encode([$ticket['service']]),
                'keterangan'       => $ticket['kategori_layanan'] . ' (Via Helpdesk: ' . $ticket['tiket_id'] . ')',
            ]);
        }

        return ['success' => true, 'ticket' => $ticket];
    }
}
