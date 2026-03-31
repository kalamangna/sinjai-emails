<?php

namespace App\Domains\Email;

use App\Shared\BaseController;
use App\Domains\Email\EmailExportService;
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

            $result = $this->emailExportService->generateUnitKerjaPdf(
                $unitKerjaId,
                $search,
                $status_asn,
                $bsre_status,
                $pimpinan_desa
            );

            $result['dompdf']->stream($result['filename'], ["Attachment" => true]);
            exit();
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

            $result = $this->emailExportService->generateAccountDetailPdf(
                $unitKerjaId,
                $search,
                $status_asn,
                $bsre_status,
                $pimpinan_desa
            );

            $result['dompdf']->stream($result['filename'], ["Attachment" => true]);
            exit();
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
}
