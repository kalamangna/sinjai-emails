<?php

namespace App\Domains\Helpdesk\Controllers;

use App\Domains\Helpdesk\Models\HelpdeskModel;

use App\Shared\BaseController;
use App\Domains\Assistance\Services\AssistanceExportService;
use App\Domains\Assistance\Controllers\Assistance;

class HelpdeskPublicController extends BaseController
{
    protected $helpdeskModel;
    protected $assistanceExportService;

    public function __construct()
    {
        $this->helpdeskModel = new HelpdeskModel();
        $this->assistanceExportService = new AssistanceExportService();
    }

    public function index()
    {
        $data = [
            'title' => 'Formulir Layanan Helpdesk',
            'agencies' => $this->assistanceExportService->getAgencyOptions(),
            'categoryMap' => Assistance::CATEGORY_MAP,
            'servicesMap' => Assistance::SERVICES_MAP,
            'keteranganByServiceMap' => Assistance::KETERANGAN_BY_SERVICE_MAP,
            'isPublic' => true
        ];

        return view('helpdesk/public_form', $data);
    }

    public function submit()
    {
        $rules = [
            'nama_pemohon' => 'required',
            'kontak_whatsapp' => 'required',
            'agency_info' => 'required',
            'category' => 'required',
            'service' => 'required',
            'kategori_layanan' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $agencyInfo = $this->request->getPost('agency_info');
        $agencyParts = explode('|', $agencyInfo);
        
        $agencyType = $agencyParts[0] ?? null;
        $agencyId = $agencyParts[1] ?? null;
        $agencyName = $agencyParts[2] ?? '';

        $tiketId = $this->helpdeskModel->generateTiketId();

        $data = [
            'tiket_id' => $tiketId,
            'nama_pemohon' => $this->request->getPost('nama_pemohon'),
            'nip_pemohon' => $this->request->getPost('nip_pemohon'),
            'kontak_whatsapp' => $this->request->getPost('kontak_whatsapp'),
            'agency_id' => $agencyId,
            'agency_type' => $agencyType,
            'agency_name' => $agencyName,
            'category' => $this->request->getPost('category'),
            'service' => $this->request->getPost('service'),
            'kategori_layanan' => $this->request->getPost('kategori_layanan'),
            'status' => 'Menunggu'
        ];

        $this->helpdeskModel->insert($data);

        return redirect()->to('helpdesk/success/' . $tiketId);
    }

    public function success($tiketId)
    {
        $ticket = $this->helpdeskModel->where('tiket_id', $tiketId)->first();
        if (!$ticket) {
            return redirect()->to('helpdesk');
        }

        $data = [
            'title' => 'Tiket Berhasil Dikirim',
            'ticket' => $ticket,
            'isPublic' => true
        ];

        return view('helpdesk/public_success', $data);
    }
}
