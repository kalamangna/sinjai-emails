<?php

namespace Tests\Domains\Batch;

use App\Domains\Batch\EmailBatchService;
use App\Domains\Email\EmailModel;
use App\Domains\Email\PkModel;
use App\Domains\UnitKerja\UnitKerjaModel;
use App\Shared\Libraries\CpanelApi;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class EmailBatchServiceTest extends CIUnitTestCase
{
    /** @var EmailBatchService */
    private $service;

    /** @var CpanelApi|MockObject */
    private $cpanelApi;

    /** @var EmailModel|MockObject */
    private $emailModel;

    /** @var UnitKerjaModel|MockObject */
    private $unitKerjaModel;

    /** @var PkModel|MockObject */
    private $pkModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cpanelApi = $this->getMockBuilder(CpanelApi::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailModel = $this->getMockBuilder(EmailModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['first', 'update', 'insert', 'findColumn'])
            ->addMethods(['where', 'whereIn'])
            ->getMock();

        $this->unitKerjaModel = $this->getMockBuilder(UnitKerjaModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['first'])
            ->addMethods(['where'])
            ->getMock();

        $this->pkModel = $this->getMockBuilder(PkModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['first', 'update', 'insert'])
            ->addMethods(['where'])
            ->getMock();

        $this->service = new EmailBatchService(
            $this->cpanelApi,
            $this->emailModel,
            $this->unitKerjaModel,
            $this->pkModel
        );
    }

    public function testProcessBatchUpdateNoChanges()
    {
        $data = [
            'mode' => 'email',
            'identifiers' => ['test@example.com'],
            'names' => ['Existing Name']
        ];

        $emailRecord = [
            'id' => 1,
            'email' => 'test@example.com',
            'name' => 'Existing Name',
            'unit_kerja_id' => 1,
            'status_asn_id' => 1,
            'eselon_id' => 1,
            'bsre_status' => 'APPROVED',
            'pimpinan' => 0,
            'pimpinan_desa' => 0
        ];

        $this->emailModel->expects($this->once())
            ->method('where')
            ->with('email', 'test@example.com')
            ->willReturn($this->emailModel);

        $this->emailModel->expects($this->once())
            ->method('first')
            ->willReturn($emailRecord);

        $this->pkModel->expects($this->once())
            ->method('where')
            ->with('email', 'test@example.com')
            ->willReturn($this->pkModel);

        $this->pkModel->expects($this->once())
            ->method('first')
            ->willReturn(null);

        $results = $this->service->processBatchUpdate($data);

        $this->assertCount(1, $results);
        $this->assertEquals('test@example.com', $results[0]['identifier']);
        $this->assertTrue($results[0]['success']);
        $this->assertStringContainsString('Skipped (no changes detected)', $results[0]['message']);
    }

    public function testProcessBatchUpdateSuccess()
    {
        $data = [
            'mode' => 'email',
            'identifiers' => ['test@example.com'],
            'names' => ['New Name']
        ];

        $emailRecord = [
            'id' => 1,
            'email' => 'test@example.com',
            'name' => 'Old Name',
            'unit_kerja_id' => 1,
            'status_asn_id' => 1,
            'eselon_id' => 1,
            'bsre_status' => 'APPROVED',
            'pimpinan' => 0,
            'pimpinan_desa' => 0
        ];

        $this->emailModel->method('where')->willReturn($this->emailModel);
        $this->emailModel->method('first')->willReturn($emailRecord);
        $this->pkModel->method('where')->willReturn($this->pkModel);
        $this->pkModel->method('first')->willReturn(null);

        $this->emailModel->expects($this->once())
            ->method('update')
            ->with(1, ['name' => 'New Name'])
            ->willReturn(true);

        $results = $this->service->processBatchUpdate($data);

        $this->assertCount(1, $results);
        $this->assertEquals('test@example.com', $results[0]['identifier']);
        $this->assertTrue($results[0]['success']);
        $this->assertStringContainsString('Successfully updated', $results[0]['message']);
    }

    public function testProcessBatchUpdateNotFound()
    {
        $data = [
            'mode' => 'email',
            'identifiers' => ['nonexistent@example.com']
        ];

        $this->emailModel->method('where')->willReturn($this->emailModel);
        $this->emailModel->method('first')->willReturn(null);

        $results = $this->service->processBatchUpdate($data);

        $this->assertCount(1, $results);
        $this->assertFalse($results[0]['success']);
        $this->assertStringContainsString('Record not found', $results[0]['message']);
    }

    public function testProcessBatchCreateSuccess()
    {
        $item = (object)[
            'email' => 'new@example.com',
            'password' => 'secret123',
            'quota' => 500,
            'unitKerja' => 'Test Unit',
            'nik' => '1234567890123456',
            'nip' => '199001012015011001',
            'name' => 'New User',
            'jabatan' => 'Staff',
            'statusAsn' => 1
        ];
        $data = [$item];

        $this->emailModel->method('whereIn')->willReturn($this->emailModel);
        $this->emailModel->method('findColumn')->willReturn([]);

        $this->unitKerjaModel->method('where')->willReturn($this->unitKerjaModel);
        $this->unitKerjaModel->method('first')->willReturn(['id' => 10, 'nama_unit_kerja' => 'Test Unit']);

        $this->cpanelApi->expects($this->once())
            ->method('create_email_account')
            ->with('new@example.com', 'secret123', 500)
            ->willReturn(true);

        $this->emailModel->expects($this->once())
            ->method('insert')
            ->willReturn(true);

        $results = $this->service->processBatchCreate($data);

        $this->assertCount(1, $results);
        $this->assertTrue($results[0]['success']);
        $this->assertEquals('new@example.com', $results[0]['email']);
    }

    public function testProcessBatchUpdateNikModeSuccess()
    {
        $nik = '1234567890123456';
        $nikHash = hash('sha256', $nik);
        $data = [
            'mode' => 'nik',
            'identifiers' => [$nik],
            'names' => ['New Name']
        ];

        $emailRecord = [
            'id' => 1,
            'email' => 'test@example.com',
            'name' => 'Old Name',
            'nik_hash' => $nikHash
        ];

        $this->emailModel->method('where')
            ->with('nik_hash', $nikHash)
            ->willReturn($this->emailModel);
        $this->emailModel->method('first')->willReturn($emailRecord);
        $this->pkModel->method('where')->willReturn($this->pkModel);
        $this->pkModel->method('first')->willReturn(null);

        $this->emailModel->expects($this->once())
            ->method('update')
            ->with(1, ['name' => 'New Name'])
            ->willReturn(true);

        $results = $this->service->processBatchUpdate($data);

        $this->assertCount(1, $results);
        $this->assertTrue($results[0]['success']);
    }

    public function testProcessBatchCreateDuplicateEmail()
    {
        $item = (object)['email' => 'existing@example.com'];
        $data = [$item];

        $this->emailModel->method('whereIn')->willReturn($this->emailModel);
        $this->emailModel->method('findColumn')->willReturn(['existing@example.com']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Email(s) already exist: existing@example.com');

        $this->service->processBatchCreate($data);
    }
}
