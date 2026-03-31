<?php

namespace App\Domains\Batch;

use App\Shared\BaseController;
use App\Domains\UnitKerja\UnitKerjaModel;
use App\Shared\Models\StatusAsnModel;
use App\Domains\Batch\EmailBatchService;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class BatchController extends BaseController
{
    private $unitKerjaModel;
    private $statusAsnModel;
    private $emailBatchService;

    public function __construct()
    {
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->statusAsnModel = new StatusAsnModel();
        $this->emailBatchService = new EmailBatchService();
    }

    public function download_template()
    {
        $header = ['nama', 'nip', 'nik'];
        $this->generate_template($header, 'batch-create.xlsx');
    }

    public function download_update_template()
    {
        $header = ['identifier', 'name', 'nik', 'nip', 'jabatan', 'golongan', 'pendidikan', 'gelar_depan', 'gelar_belakang', 'tempat_lahir', 'tanggal_lahir', 'unit_kerja_id'];
        $this->generate_template($header, 'batch-update.xlsx');
    }

    public function download_pk_template()
    {
        $header = ['identifier', 'nomor', 'gaji_nominal', 'gaji_terbilang', 'tanggal_kontrak_awal', 'tanggal_kontrak_akhir'];
        $this->generate_template($header, 'batch-pk.xlsx');
    }

    public function download_unit_kerja_template()
    {
        $header = ['nama_unit_kerja', 'parent_id'];
        $this->generate_template($header, 'batch-unit-kerja.xlsx');
    }

    private function generate_template(array $header, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($header, NULL, 'A1');

        $writer = new XlsxWriter($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. $filename .'"');
        $writer->save('php://output');
        exit();
    }

    public function import_generic_spreadsheet()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $file = $this->request->getFile('spreadsheet_file');
        $expectedHeaderString = $this->request->getPost('expected_headers');

        if (!$file || !$file->isValid() || $file->getClientMimeType() !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'File tidak valid. Harap unggah file XLSX.']);
        }

        if (empty($expectedHeaderString)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Tipe template tidak dispesifikasikan.']);
        }
        
        $expectedHeader = explode(',', $expectedHeaderString);

        try {
            $reader = new Xlsx();
            $spreadsheet = $reader->load($file->getTempName());
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            
            $sheetData = array_filter($sheetData, fn($row) => !empty(implode('', array_map('trim', $row))));
            
            $header = array_values(array_shift($sheetData));

            // Trim headers from file
            $header = array_map('trim', $header);

            if ($header !== $expectedHeader) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Header file tidak cocok. Harap gunakan template yang sesuai.']);
            }

            $records = [];
            foreach ($sheetData as $row) {
                $rowData = array_values($row);
                $records[] = array_combine($expectedHeader, $rowData);
            }

            return $this->response->setJSON(['success' => true, 'data' => $records]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Gagal memproses file: ' . $e->getMessage()]);
        }
    }

    public function index()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
        $data['title'] = 'Buat Akun Massal';
        return view('batch/create', $data);
    }

    public function update()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
        $data['eselon_options'] = (new \App\Shared\Models\EselonModel())->orderBy('nama_eselon', 'ASC')->findAll();
        $data['title'] = 'Edit Akun Massal';
        return view('batch/update', $data);
    }

    public function pk()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->orderBy('nama_unit_kerja', 'ASC')->findAll();
        $data['status_asn_options'] = $this->statusAsnModel->orderBy('nama_status_asn', 'ASC')->findAll();
        $data['eselon_options'] = (new \App\Shared\Models\EselonModel())->orderBy('nama_eselon', 'ASC')->findAll();
        $data['title'] = 'Edit PK Massal';
        return view('batch/pk', $data);
    }

    public function process_update()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $data = $this->request->getJSON(true);
        if (empty($data) || !isset($data['identifiers']) || !is_array($data['identifiers'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No identifiers provided.']);
        }

        $results = $this->emailBatchService->processBatchUpdate($data);
        return $this->response->setJSON(['success' => true, 'results' => $results]);
    }

    public function process_create()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/email');
        }

        $data = $this->request->getJSON();
        if (empty($data)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No data provided.']);
        }

        try {
            $results = $this->emailBatchService->processBatchCreate($data);
            return $this->response->setJSON(['success' => true, 'results' => $results]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
