<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\EmailModel;
use App\Models\UnitKerjaModel;
use App\Models\StatusAsnModel;

class EmailTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $refresh     = true;
    protected $seed        = 'EmailSeeder'; // We'll need to create this seeder
    protected $basePath    = APPPATH . 'Database';

    protected function setUp(): void
    {
        parent::setUp();
        // Explicitly run migrations to ensure tables are created
        \Config\Services::migrations()->latest();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Clear all database data after each test
        $this->db->query('DELETE FROM db_emails');
        $this->db->query('DELETE FROM db_unit_kerja');
        $this->db->query('DELETE FROM db_status_asn');
        // You might need to reset auto-increment counters if needed
    }

    public function testUpdateDetails()
    {
        // Create dummy data
        $emailModel = new EmailModel();
        $unitKerjaModel = new UnitKerjaModel();
        $statusAsnModel = new StatusAsnModel();

        // Insert a dummy unit kerja and status_asn
        $unitKerjaId = $unitKerjaModel->insert(['nama_unit_kerja' => 'Test Unit']);
        $statusAsnId = $statusAsnModel->insert(['nama_status_asn' => 'Test Status']);

        $emailData = [
            'email'           => 'test@example.com',
            'user'            => 'test',
            'domain'          => 'example.com',
            'password'        => 'old_password',
            'name'            => 'Old Name',
            'gelar_depan'     => 'Old GD',
            'gelar_belakang'  => 'Old GB',
            'nik'             => '12345',
            'nip'             => '67890',
            'tempat_lahir'    => 'Old City',
            'tanggal_lahir'   => '1990-01-01',
            'pendidikan'      => 'Old Degree',
            'jabatan'         => 'Old Job',
            'status_asn_id'   => $statusAsnId,
            'unit_kerja_id'   => $unitKerjaId,
        ];
        $emailId = $emailModel->insert($emailData);
        $this->assertNotNull($emailId);

        // Simulate POST request
        $_POST = [
            'name'            => 'New Name',
            'email'           => 'new@example.com',
            'password'        => 'new_password',
            'gelar_depan'     => 'New GD',
            'gelar_belakang'  => 'New GB',
            'nik'             => '54321',
            'nip'             => '09876',
            'tempat_lahir'    => 'New City',
            'tanggal_lahir'   => '1995-05-05',
            'pendidikan'      => 'New Degree',
            'jabatan'         => 'New Job',
            'status_asn_id'   => $statusAsnId, // Using the same for simplicity
            'unit_kerja_id'   => $unitKerjaId, // Using the same for simplicity
        ];

        // Mocking the CpanelApi for password change, if necessary
        // You would typically mock the CpanelApi in a more sophisticated way
        // to assert that change_password was called.
        // For now, we'll assume it works and focus on DB update.

        $controller = new \App\Controllers\Email();
        $result = $controller->update_details('test'); // 'test' is the username

        // Assert redirect and session flashdata
        $this->assertTrue($result->isRedirect());
        $this->assertStringContainsString('email/detail/test', $result->getRedirectUrl());
        $this->assertStringContainsString('Email details have been updated successfully.', session()->getFlashdata('success'));

        // Verify data in the database
        $updatedEmail = $emailModel->find($emailId);

        $this->assertEquals('New Name', $updatedEmail['name']);
        $this->assertEquals('new@example.com', $updatedEmail['email']);
        $this->assertEquals('new_password', $updatedEmail['password']);
        $this->assertEquals('New GD', $updatedEmail['gelar_depan']);
        $this->assertEquals('New GB', $updatedEmail['gelar_belakang']);
        $this->assertEquals('54321', $updatedEmail['nik']);
        $this->assertEquals('09876', $updatedEmail['nip']);
        $this->assertEquals('New City', $updatedEmail['tempat_lahir']);
        $this->assertEquals('1995-05-05', $updatedEmail['tanggal_lahir']);
        $this->assertEquals('New Degree', $updatedEmail['pendidikan']);
        $this->assertEquals('New Job', $updatedEmail['jabatan']);
        $this->assertEquals($statusAsnId, $updatedEmail['status_asn_id']);
        $this->assertEquals($unitKerjaId, $updatedEmail['unit_kerja_id']);
    }
}
