<?php

namespace App\Domains\Email\Controllers;

use App\Shared\BaseController;
use App\Domains\Email\Services\EmailExportService;
use Exception;

class EmailExport extends BaseController
{
    private $emailExportService;

    public function __construct()
    {
        $this->emailExportService = new EmailExportService();
    }

    public function export_unit_kerja_csv($unitKerjaId)
    {
        try {
            $params = [
                'search' => $this->request->getGet('search'),
                'status_asn' => $this->request->getGet('status_asn'),
                'bsre_status' => $this->request->getGet('bsre_status'),
            ];

            $result = $this->emailExportService->generateUnitKerjaCsv($unitKerjaId, $params);

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

    public function export_pns_excel()
    {
        try {
            $params = [
                'has_nip' => $this->request->getGet('has_nip'),
                'parent_unit_kerja_id' => $this->request->getGet('parent_unit_kerja_id'),
            ];

            $result = $this->emailExportService->generatePnsExcel($params);

            return $this->response->download($result['path'], null)->setFileName($result['filename']);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function export_unit_kerja_excel($unitKerjaId)
    {
        try {
            $params = [
                'search' => $this->request->getGet('search'),
                'status_asn' => $this->request->getGet('status_asn'),
                'bsre_status' => $this->request->getGet('bsre_status'),
            ];

            $result = $this->emailExportService->generateUnitKerjaExcel($unitKerjaId, $params);

            return $this->response->download($result['path'], null)->setFileName($result['filename']);
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function export_single_perjanjian_kerja_pdf($username)
    {
        try {
            $result = $this->emailExportService->generatePerjanjianKerjaPdf($username);
            $result['dompdf']->stream($result['filename'], ["Attachment" => true]);
            exit();
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function export_perjanjian_kerja_pdf($unitKerjaId)
    {
        try {
            $pkType = $this->request->getGet('pk_type');
            $result = $this->emailExportService->generatePerjanjianKerjaZip($unitKerjaId, $pkType);

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
            header('Content-Length: ' . filesize($result['path']));
            readfile($result['path']);
            unlink($result['path']);
            exit();
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function export_unit_kerja_pdf($unitKerjaId)
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

            // Trigger Queue Worker Asynchronously
            $phpPath = PHP_BINARY;
            $sparkPath = ROOTPATH . 'spark';
            exec("$phpPath $sparkPath queue:work --stop-when-empty > /dev/null 2>&1 &");

            session()->setFlashdata('success', 'Permintaan Export PDF berhasil ditambahkan ke antrean. File akan segera tersedia di Riwayat Laporan.');
            return redirect()->to('reports/history');
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function export_account_detail_pdf($unitKerjaId)
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

            // Trigger Queue Worker Asynchronously
            $phpPath = PHP_BINARY;
            $sparkPath = ROOTPATH . 'spark';
            exec("$phpPath $sparkPath queue:work --stop-when-empty > /dev/null 2>&1 &");

            session()->setFlashdata('success', 'Permintaan Export Detail Akun PDF berhasil ditambahkan ke antrean. File akan segera tersedia di Riwayat Laporan.');
            return redirect()->to('reports/history');
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function download_zip_file($filename)
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
        
        $data['title'] = 'Riwayat Export Laporan';
        // Only show latest 100 for performance
        $data['histories'] = $historyModel->orderBy('created_at', 'DESC')->limit(100)->find();
        
        return view('email/exports/history', $data);
    }

    public function download_history($id)
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
}
