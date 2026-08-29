<?php

namespace App\Domains\Email\Controllers;

use App\Shared\BaseController;
use App\Domains\Email\Services\EmailExportService;
use Exception;

class EmailExportController extends BaseController
{
    private $emailExportService;

    public function __construct()
    {
        $this->emailExportService = new EmailExportService();
    }

    public function exportUnitKerjaCsv($unitKerjaId)
    {
        try {
            $params = [
                'search' => $this->request->getGet('search'),
                'status_asn' => $this->request->getGet('status_asn'),
                'bsre_status' => $this->request->getGet('bsre_status'),
            ];

            $result = $this->emailExportService->generateUnitKerjaCsv($unitKerjaId, $params);
            
            log_audit('EXPORT', 'Email', $unitKerjaId, 'Ekspor CSV Email Unit Kerja');

            if ($result['type'] === 'csv') {
                return $this->response->download($result['path'], null)->setFileName($result['filename']);
            } else {
                return $this->response->download($result['path'], null)->setFileName($result['filename']);
            }
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function exportPnsExcel()
    {
        try {
            $params = [
                'has_nip' => $this->request->getGet('has_nip'),
                'bsre_status' => $this->request->getGet('bsre_status'),
                'parent_unit_kerja_id' => $this->request->getGet('parent_unit_kerja_id'),
            ];

            $result = $this->emailExportService->generatePnsExcel($params);

            log_audit('EXPORT', 'Email', null, 'Ekspor Excel PNS');

            return $this->response->download($result['path'], null)->setFileName($result['filename']);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function exportPnsCsv()
    {
        try {
            $params = [
                'has_nip' => $this->request->getGet('has_nip'),
                'bsre_status' => $this->request->getGet('bsre_status'),
                'parent_unit_kerja_id' => $this->request->getGet('parent_unit_kerja_id'),
            ];

            $result = $this->emailExportService->generatePnsCsv($params);

            log_audit('EXPORT', 'Email', null, 'Ekspor CSV PNS');

            return $this->response->download($result['path'], null)->setFileName($result['filename']);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function exportUnitKerjaExcel($unitKerjaId)
    {
        try {
            $params = [
                'search' => $this->request->getGet('search'),
                'status_asn' => $this->request->getGet('status_asn'),
                'bsre_status' => $this->request->getGet('bsre_status'),
            ];

            $result = $this->emailExportService->generateUnitKerjaExcel($unitKerjaId, $params);

            log_audit('EXPORT', 'Email', $unitKerjaId, 'Ekspor Excel Email Unit Kerja');

            return $this->response->download($result['path'], null)->setFileName($result['filename']);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function exportSinglePerjanjianKerjaPdf($username)
    {
        try {
            $result = $this->emailExportService->generatePerjanjianKerjaPdf($username);
            log_audit('EXPORT', 'Email', null, 'Ekspor PDF Perjanjian Kerja user: ' . $username);
            $pdfContent = $result['dompdf']->output();
            return $this->response
                ->setContentType('application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $result['filename'] . '"')
                ->setBody($pdfContent);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function exportPerjanjianKerjaPdf($unitKerjaId)
    {
        try {
            $pkType = $this->request->getGet('pk_type');
            $result = $this->emailExportService->generatePerjanjianKerjaZip($unitKerjaId, $pkType);

            log_audit('EXPORT', 'Email', $unitKerjaId, 'Ekspor ZIP Perjanjian Kerja Unit Kerja');

            $response = $this->response->download($result['path'], null)->setFileName($result['filename']);
            unlink($result['path']);
            return $response;
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function exportUnitKerjaPdf($unitKerjaId)
    {
        try {
            $search = $this->request->getGet('search');
            $status_asn = $this->request->getGet('status_asn');
            $bsre_status = $this->request->getGet('bsre_status');
            $pimpinan_desa = $this->request->getGet('pimpinan_desa') ?? 1;

            $filters = [
                'unitKerjaId'   => $unitKerjaId,
                'search'        => $search,
                'status_asn'    => $status_asn,
                'bsre_status'   => $bsre_status,
                'pimpinan_desa' => $pimpinan_desa
            ];

            $historyModel = new \App\Shared\Models\ExportHistoryModel();
            $jobModel = new \App\Shared\Models\JobModel();

            $historyId = $historyModel->insert([
                'user_id' => session()->get('user_id'),
                'type' => 'PDF_UNIT_KERJA',
                'status' => 'PENDING',
                'filters' => json_encode($filters)
            ]);

            $jobModel->push('default', [
                'type' => 'export_pdf',
                'task' => 'export_unit_kerja_pdf',
                'history_id' => $historyId,
                'filters' => $filters
            ]);

            log_audit('EXPORT', 'Email', $unitKerjaId, 'Antrean Ekspor PDF Unit Kerja');

            // Instead of cURL, tell the history page to trigger the worker via AJAX
            session()->setFlashdata('trigger_worker', true);
            session()->setFlashdata('success', 'Permintaan Export PDF berhasil ditambahkan ke antrean. File akan segera tersedia di Riwayat Laporan.');
            return redirect()->to('reports/history');
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function exportAccountDetailPdf($unitKerjaId)
    {
        try {
            $search = $this->request->getGet('search');
            $status_asn = $this->request->getGet('status_asn');
            $bsre_status = $this->request->getGet('bsre_status');
            $pimpinan_desa = $this->request->getGet('pimpinan_desa') ?? 1;

            $filters = [
                'unitKerjaId'   => $unitKerjaId,
                'search'        => $search,
                'status_asn'    => $status_asn,
                'bsre_status'   => $bsre_status,
                'pimpinan_desa' => $pimpinan_desa
            ];

            $historyModel = new \App\Shared\Models\ExportHistoryModel();
            $jobModel = new \App\Shared\Models\JobModel();

            $historyId = $historyModel->insert([
                'user_id' => session()->get('user_id'),
                'type' => 'PDF_DETAIL_AKUN',
                'status' => 'PENDING',
                'filters' => json_encode($filters)
            ]);

            $jobModel->push('default', [
                'type' => 'export_pdf',
                'task' => 'export_account_detail_pdf',
                'history_id' => $historyId,
                'filters' => $filters
            ]);

            log_audit('EXPORT', 'Email', $unitKerjaId, 'Antrean Ekspor PDF Detail Akun Unit Kerja');

            session()->setFlashdata('trigger_worker', true);
            session()->setFlashdata('success', 'Permintaan Export Detail Akun PDF berhasil ditambahkan ke antrean. File akan segera tersedia di Riwayat Laporan.');
            return redirect()->to('reports/history');
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function downloadZipFile($filename)
    {
        if (empty($filename) || strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            throw new \Exception('Invalid filename');
        }

        $path = WRITEPATH . 'uploads/' . $filename;
        if (file_exists($path)) {
            return $this->response->download($path, null);
        } else {
            throw new \CodeIgniter\Exceptions\PageNotFoundException($filename . ' not found');
        }
    }

    public function history()
    {
        $historyModel = new \App\Shared\Models\ExportHistoryModel();
        
        $data['title'] = 'Riwayat Laporan';
        
        $role = session()->get('role');
        if ($role !== 'super_admin') {
            $historyModel->where('user_id', session()->get('user_id'));
        }

        $histories = $historyModel->orderBy('created_at', 'DESC')->limit(100)->find();
        
        $unitKerjaModel = new \App\Domains\UnitKerja\Models\UnitKerjaModel();
        $statusAsnModel = new \App\Shared\Models\StatusAsnModel();
        
        $asnList = $statusAsnModel->findAll();
        $asnMap = [];
        foreach ($asnList as $asn) {
            $asnMap[$asn['id']] = $asn['nama_status_asn'];
        }

        foreach ($histories as &$h) {
            $filters = json_decode($h['filters'], true);
            $readable = [];
            if (is_array($filters)) {
                if (!empty($filters['unitKerjaId'])) {
                    $unit = $unitKerjaModel->find($filters['unitKerjaId']);
                    if ($unit) $readable[] = "Unit: " . $unit['nama_unit_kerja'];
                }
                if (!empty($filters['search'])) $readable[] = "Cari: " . $filters['search'];
                if (!empty($filters['status_asn'])) {
                    $asnName = $asnMap[$filters['status_asn']] ?? $filters['status_asn'];
                    $readable[] = "ASN: " . $asnName;
                }
                if (!empty($filters['bsre_status'])) $readable[] = "BSrE: " . $filters['bsre_status'];
                if (isset($filters['pimpinan_desa']) && $filters['pimpinan_desa'] == 0) $readable[] = "Inc. Desa: Tidak";
            }
            $h['readable_filters'] = !empty($readable) ? implode(' | ', $readable) : 'Semua Data';
        }
        
        $data['histories'] = $histories;
        return view('email/exports/history', $data);
    }

    public function downloadHistory($id)
    {
        $historyModel = new \App\Shared\Models\ExportHistoryModel();
        $history = $historyModel->find($id);

        if (!$history || $history['status'] !== 'COMPLETED' || empty($history['file_path'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File belum siap atau sudah kadaluwarsa.');
        }

        $path = WRITEPATH . $history['file_path'];
        if (file_exists($path)) {
            return $this->response->download($path, null)->setFileName($history['file_name']);
        } else {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File fisik tidak ditemukan, mungkin sudah dihapus otomatis.');
        }
    }
    public function deleteHistory($id)
    {
        $historyModel = new \App\Shared\Models\ExportHistoryModel();
        $history = $historyModel->find($id);

        if (!$history) {
            session()->setFlashdata('error', 'Riwayat tidak ditemukan.');
            return redirect()->to('reports/history');
        }

        if (session()->get('role') !== 'super_admin' && $history['user_id'] !== session()->get('user_id')) {
            session()->setFlashdata('error', 'Akses ditolak.');
            return redirect()->to('reports/history');
        }

        if (!empty($history['file_path'])) {
            $path = WRITEPATH . $history['file_path'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $historyModel->delete($id);
        session()->setFlashdata('success', 'Riwayat laporan berhasil dihapus.');
        return redirect()->to('reports/history');
    }
}
