<?php

namespace App\Domains\Pimpinan;

use App\Shared\BaseController;
use App\Domains\Email\EmailModel;
use App\Domains\Email\EmailExportService;

class PimpinanController extends BaseController
{
    private $emailModel;
    private $emailExportService;

    public function __construct()
    {
        $this->emailModel = new EmailModel();
        $this->emailExportService = new EmailExportService();
    }

    public function pimpinan()
    {
        try {
            $perPage = $this->request->getGet('per_page') ?? 100;
            $search = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');

            $emailBuilder = $this->emailModel->getPimpinanBuilder();

            if ($search) {
                $emailBuilder->groupStart()
                    ->like('emails.email', $search)
                    ->orLike('emails.name', $search)
                    ->orLike('emails.nik', $search)
                    ->orLike('emails.nip', $search)
                    ->groupEnd();
            }

            if ($bsre_status) {
                if ($bsre_status === 'not_synced') {
                    $emailBuilder->groupStart()
                        ->where('emails.bsre_status', null)
                        ->orWhere('emails.bsre_status', '')
                        ->groupEnd();
                } else {
                    $emailBuilder->where('emails.bsre_status', $bsre_status);
                }
            }

            $total_emails = $emailBuilder->countAllResults(false);

            $emails = $emailBuilder
                ->asArray()
                ->orderBy('emails.eselon_id', 'ASC')
                ->orderBy('COALESCE(parent_unit_kerja.nama_unit_kerja, unit_kerja.nama_unit_kerja)', 'ASC', false)
                ->orderBy('unit_kerja.parent_id IS NOT NULL', 'ASC', false)
                ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
                ->orderBy('emails.jabatan', 'ASC')
                ->orderBy('emails.name', 'ASC')
                ->paginate($perPage);

            $pager = $this->emailModel->pager;

            $bsre_status_options = $this->getBsreStatusOptions();

            $data = [
                'title' => 'Pimpinan',
                'emails' => $emails,
                'total_emails' => $total_emails,
                'pagination' => $pager,
                'per_page' => $perPage,
                'search' => $search,
                'bsre_status' => $bsre_status,
                'bsre_status_options' => $bsre_status_options,
                'back_url' => site_url('email'),
            ];

            return view('email/pimpinan', $data);
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function pimpinan_desa()
    {
        try {
            $perPage = $this->request->getGet('per_page') ?? 100;
            $search = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');

            $emailBuilder = $this->emailModel->getPimpinanDesaBuilder();

            if ($search) {
                $emailBuilder->groupStart()
                    ->like('emails.email', $search)
                    ->orLike('emails.name', $search)
                    ->orLike('emails.nik', $search)
                    ->orLike('emails.nip', $search)
                    ->groupEnd();
            }

            if ($bsre_status) {
                if ($bsre_status === 'not_synced') {
                    $emailBuilder->groupStart()
                        ->where('emails.bsre_status', null)
                        ->orWhere('emails.bsre_status', '')
                        ->groupEnd();
                } else {
                    $emailBuilder->where('emails.bsre_status', $bsre_status);
                }
            }

            $total_emails = $emailBuilder->countAllResults(false);

            $emails = $emailBuilder
                ->asArray()
                ->orderBy('emails.eselon_id', 'ASC')
                ->orderBy('COALESCE(parent_unit_kerja.nama_unit_kerja, unit_kerja.nama_unit_kerja)', 'ASC', false)
                ->orderBy('unit_kerja.parent_id IS NOT NULL', 'ASC', false)
                ->orderBy('unit_kerja.nama_unit_kerja', 'ASC')
                ->orderBy('emails.jabatan', 'ASC')
                ->orderBy('emails.name', 'ASC')
                ->paginate($perPage);

            $pager = $this->emailModel->pager;

            $bsre_status_options = $this->getBsreStatusOptions();

            $data = [
                'title' => 'Kepala Desa',
                'emails' => $emails,
                'total_emails' => $total_emails,
                'pagination' => $pager,
                'per_page' => $perPage,
                'search' => $search,
                'bsre_status' => $bsre_status,
                'bsre_status_options' => $bsre_status_options,
                'back_url' => site_url('email'),
            ];

            return view('email/pimpinan_desa', $data);
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function export_pimpinan_pdf()
    {
        try {
            $search = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');

            $dompdf = $this->emailExportService->generatePimpinanPdf($search, $bsre_status);

            require_once APPPATH . 'Shared/Helpers/TanggalHelper.php';
            $filename = 'Email & TTE Pimpinan - ' . formatTanggal('now') . '.pdf';
            $dompdf->stream($filename, ["Attachment" => true]);
            exit();
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function export_pimpinan_desa_pdf()
    {
        try {
            $search = $this->request->getGet('search');
            $bsre_status = $this->request->getGet('bsre_status');

            $dompdf = $this->emailExportService->generatePimpinanDesaPdf($search, $bsre_status);

            require_once APPPATH . 'Shared/Helpers/TanggalHelper.php';
            $filename = 'Email & TTE Kepala Desa - ' . formatTanggal('now') . '.pdf';
            $dompdf->stream($filename, ["Attachment" => true]);
            exit();
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    private function getBsreStatusOptions()
    {
        return [
            'ISSUE' => 'ISSUE',
            'EXPIRED' => 'EXPIRED',
            'NO_CERTIFICATE' => 'NO_CERTIFICATE',
            'NOT_REGISTERED' => 'NOT_REGISTERED',
            'not_synced' => 'NOT_SYNCED'
        ];
    }
}
