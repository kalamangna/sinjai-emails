<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Konfigurasi Rute Aplikasi Sistem Identitas Digital & Layanan Terintegrasi
 *
 * @var RouteCollection $routes
 */

// ====================================================================
// 1. RUTE PUBLIK & AUTENTIKASI
// ====================================================================

// Halaman Muka / Beranda
$routes->get('/', '\App\Domains\Dashboard\Controllers\HomeController::index');

// Autentikasi Pengguna
$routes->get('login', '\App\Domains\Auth\Controllers\AuthController::login');
$routes->post('auth/attemptLogin', '\App\Domains\Auth\Controllers\AuthController::attemptLogin');
$routes->get('logout', '\App\Domains\Auth\Controllers\AuthController::logout');

// Verifikasi Identitas & Profil Publik
$routes->get('verifikasi/(:any)', '\App\Domains\Email\Controllers\EmailController::profile/$1');

// Layanan Helpdesk Publik
$routes->get('helpdesk', '\App\Domains\Helpdesk\Controllers\HelpdeskPublicController::index');
$routes->post('helpdesk/submit', '\App\Domains\Helpdesk\Controllers\HelpdeskPublicController::submit');
$routes->get('helpdesk/success/(:any)', '\App\Domains\Helpdesk\Controllers\HelpdeskPublicController::success/$1');

// Verifikasi Dokumen PDF Publik
$routes->get('verifikasi-pdf', '\App\Domains\Email\Controllers\BsreController::publicVerify');
$routes->post('verifikasi-pdf', '\App\Domains\Email\Controllers\BsreController::verifyPdf');

// Portal PPPK (Penandatanganan Perjanjian Kerja Mandiri)
$routes->group('portal-pk', function ($routes) {
    $routes->get('/', '\App\Domains\Email\Controllers\PortalPkController::index');
    $routes->post('auth', '\App\Domains\Email\Controllers\PortalPkController::auth');
    $routes->get('dashboard', '\App\Domains\Email\Controllers\PortalPkController::dashboard');
    $routes->get('preview', '\App\Domains\Email\Controllers\PortalPkController::previewPdf');
    $routes->post('sign', '\App\Domains\Email\Controllers\PortalPkController::sign');
    $routes->get('download', '\App\Domains\Email\Controllers\PortalPkController::download');
    $routes->get('logout', '\App\Domains\Email\Controllers\PortalPkController::logout');
});

// ====================================================================
// 2. RUTE INTEGRASI EKSTERNAL & API
// ====================================================================

// API Gateway (v1) - Akses Terproteksi Whitelist & Token
$routes->group('api/v1', ['filter' => ['api_gateway', 'throttle']], function ($routes) {
    $routes->get('emails', '\App\Domains\Api\Controllers\GatewayController::listEmails');
    $routes->get('pppk', '\App\Domains\Api\Controllers\GatewayController::listPppkPenuh');
    $routes->get('pppk-pw', '\App\Domains\Api\Controllers\GatewayController::listPppkParuh');
    $routes->get('pns', '\App\Domains\Api\Controllers\GatewayController::listPns');
    $routes->get('unit/(:num)', '\App\Domains\Api\Controllers\GatewayController::listByUnit/$1');
});

// Eksekusi Antrean Asinkron Internal (Khusus Admin & Super Admin)
$routes->get('api_trigger_queue', '\App\Domains\Email\Controllers\EmailApiController::apiTriggerQueue', ['filter' => 'role:admin,super_admin']);
$routes->get('apiTriggerQueue', '\App\Domains\Email\Controllers\EmailApiController::apiTriggerQueue', ['filter' => 'role:admin,super_admin']);

// Layanan Dokumentasi Panduan Bridge TTE
$routes->get('bridge/panduan', '\App\Domains\Bridge\Controllers\BridgeController::panduan');
$routes->get('bridge/docs', '\App\Domains\Bridge\Controllers\BridgeController::panduan');

// ====================================================================
// 3. RUTE APLIKASI UTAMA (TERPROTEKSI SESI LOGIN)
// ====================================================================

$routes->group('', ['filter' => 'auth'], function ($routes) {

    // ----------------------------------------------------------------
    // 3.1 Sesi & Dashboard
    // ----------------------------------------------------------------
    $routes->match(['GET', 'POST'], 'auth/keep-alive', '\App\Domains\Auth\Controllers\AuthController::keepAlive');
    $routes->get('dashboard', '\App\Domains\Dashboard\Controllers\HomeController::dashboard');
    $routes->post('dashboard/sync_cpanel', '\App\Domains\Dashboard\Controllers\HomeController::syncCpanel', ['filter' => 'role:admin,super_admin']);
    $routes->post('dashboard/sync-cpanel', '\App\Domains\Dashboard\Controllers\HomeController::syncCpanel', ['filter' => 'role:admin,super_admin']);

    // Dokumentasi API Gateway (Admin & Super Admin)
    $routes->group('api-gateway', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Api\Controllers\GatewayController::index');
    });

    // ----------------------------------------------------------------
    // 3.2 Manajemen Email & Direktori Pegawai
    // ----------------------------------------------------------------
    $routes->group('email', function ($routes) {
        // Tampilan Indeks & Detail Akun
        $routes->get('/', '\App\Domains\Email\Controllers\EmailController::index');
        $routes->get('detail/(:any)', '\App\Domains\Email\Controllers\EmailController::detail/$1');

        // Manajemen Kotak Sampah
        $routes->get('trash', '\App\Domains\Email\Controllers\TrashController::index');
        $routes->get('trash/restore/(:num)', '\App\Domains\Email\Controllers\TrashController::restore/$1');
        $routes->post('trash/force_delete/(:num)', '\App\Domains\Email\Controllers\TrashController::forceDelete/$1');

        // Direktori & Klasifikasi Pegawai
        $routes->get('unit_kerja', '\App\Domains\Email\Controllers\EmailListController::unitKerjaList');
        $routes->get('unit_kerja/(:num)', '\App\Domains\Email\Controllers\EmailListController::unitKerjaDetail/$1');
        $routes->get('eselon', '\App\Domains\Email\Controllers\EmailListController::eselonList');
        $routes->get('eselon_detail/(:num)', '\App\Domains\Email\Controllers\EmailListController::eselonDetail/$1');
        $routes->get('eselonDetail/(:num)', '\App\Domains\Email\Controllers\EmailListController::eselonDetail/$1');
        $routes->get('pns', '\App\Domains\Email\Controllers\EmailListController::pnsList');
        $routes->get('pppk', '\App\Domains\Email\Controllers\EmailListController::pppkList');
        $routes->get('pppk-pw', '\App\Domains\Email\Controllers\EmailListController::pppkPwList');

        // Pejabat Pimpinan & Kepala Desa
        $routes->get('pimpinan', '\App\Domains\Pimpinan\Controllers\PimpinanController::pimpinan');
        $routes->get('pimpinan_desa', '\App\Domains\Pimpinan\Controllers\PimpinanController::pimpinanDesa');
        $routes->get('export_pimpinan_pdf', '\App\Domains\Pimpinan\Controllers\PimpinanController::exportPimpinanPdf');
        $routes->get('exportPimpinanPdf', '\App\Domains\Pimpinan\Controllers\PimpinanController::exportPimpinanPdf');
        $routes->get('export_pimpinan_desa_pdf', '\App\Domains\Pimpinan\Controllers\PimpinanController::exportPimpinanDesaPdf');
        $routes->get('exportPimpinanDesaPdf', '\App\Domains\Pimpinan\Controllers\PimpinanController::exportPimpinanDesaPdf');

        // Ekspor Dokumen & Berkas Unit Kerja
        $routes->get('export_unit_kerja_csv/(:num)', '\App\Domains\Email\Controllers\EmailExportController::exportUnitKerjaCsv/$1');
        $routes->get('exportUnitKerjaCsv/(:num)', '\App\Domains\Email\Controllers\EmailExportController::exportUnitKerjaCsv/$1');
        $routes->get('export_unit_kerja_excel/(:num)', '\App\Domains\Email\Controllers\EmailExportController::exportUnitKerjaExcel/$1');
        $routes->get('exportUnitKerjaExcel/(:num)', '\App\Domains\Email\Controllers\EmailExportController::exportUnitKerjaExcel/$1');
        $routes->get('export_unit_kerja_pdf/(:num)', '\App\Domains\Email\Controllers\EmailExportController::exportUnitKerjaPdf/$1');
        $routes->get('exportUnitKerjaPdf/(:num)', '\App\Domains\Email\Controllers\EmailExportController::exportUnitKerjaPdf/$1');
        $routes->get('export_account_detail_pdf/(:num)', '\App\Domains\Email\Controllers\EmailExportController::exportAccountDetailPdf/$1');
        $routes->get('exportAccountDetailPdf/(:num)', '\App\Domains\Email\Controllers\EmailExportController::exportAccountDetailPdf/$1');

        // Ekspor Dokumen Perjanjian Kerja (PK)
        $routes->get('export_perjanjian_kerja_pdf/(:num)', '\App\Domains\Email\Controllers\EmailExportController::exportPerjanjianKerjaPdf/$1');
        $routes->get('exportPerjanjianKerjaPdf/(:num)', '\App\Domains\Email\Controllers\EmailExportController::exportPerjanjianKerjaPdf/$1');
        $routes->get('export_perjanjian_kerja_zip/(:num)', '\App\Domains\Email\Controllers\EmailExportController::exportPerjanjianKerjaZipQueue/$1');
        $routes->get('exportPerjanjianKerjaZip/(:num)', '\App\Domains\Email\Controllers\EmailExportController::exportPerjanjianKerjaZipQueue/$1');
        $routes->get('export_single_perjanjian_kerja_pdf/(:any)', '\App\Domains\Email\Controllers\EmailExportController::exportSinglePerjanjianKerjaPdf/$1');
        $routes->get('exportSinglePerjanjianKerjaPdf/(:any)', '\App\Domains\Email\Controllers\EmailExportController::exportSinglePerjanjianKerjaPdf/$1');
        $routes->get('download_zip_file/(:any)', '\App\Domains\Email\Controllers\EmailExportController::downloadZipFile/$1');
        $routes->get('downloadZipFile/(:any)', '\App\Domains\Email\Controllers\EmailExportController::downloadZipFile/$1');

        // Kontrol Pencarian
        $routes->get('search', '\App\Domains\Email\Controllers\EmailApiController::search');

        // Mutasi Data & Utilitas Email (Admin & Super Admin)
        $routes->group('', ['filter' => 'role:admin,super_admin'], function ($routes) {
            $routes->get('create', '\App\Domains\Email\Controllers\EmailController::create');
            $routes->post('create_single', '\App\Domains\Email\Controllers\EmailApiController::createSingleEmail');
            $routes->get('edit_profile/(:any)', '\App\Domains\Email\Controllers\EmailController::editProfile/$1');
            $routes->post('update_details/(:any)', '\App\Domains\Email\Controllers\EmailController::updateDetails/$1');
            $routes->post('mark_pensiun/(:any)', '\App\Domains\Email\Controllers\EmailController::markPensiun/$1');
            $routes->get('edit_password/(:any)', '\App\Domains\Email\Controllers\EmailController::editPassword/$1');
            $routes->post('update_password/(:any)', '\App\Domains\Email\Controllers\EmailController::updatePassword/$1');
            $routes->get('edit_pk/(:any)', '\App\Domains\Email\Controllers\EmailController::editPk/$1');
            $routes->post('update_pk/(:any)', '\App\Domains\Email\Controllers\EmailController::updatePk/$1');

            // Pertukaran Data (Swap Data)
            $routes->get('swap_data', '\App\Domains\Email\Controllers\EmailController::swapForm');
            $routes->post('swap_process', '\App\Domains\Email\Controllers\EmailController::swapProcess');

            // Sinkronisasi Pegawai & Utilitas API Internal
            $routes->post('sync_pegawai', '\App\Domains\Email\Controllers\EmailApiController::syncPegawai');
            $routes->post('api_generate_pdf', '\App\Domains\Email\Controllers\EmailApiController::apiGeneratePdf');
            $routes->get('api_unit_emails/(:num)', '\App\Domains\Email\Controllers\EmailApiController::apiUnitEmails/$1');
            $routes->get('api_download_zip/(:num)', '\App\Domains\Email\Controllers\EmailApiController::apiDownloadZip/$1');
        });

        // Operasi Sensitif Email (Khusus Super Admin)
        $routes->group('', ['filter' => 'role:super_admin'], function ($routes) {
            $routes->post('delete/(:num)', '\App\Domains\Email\Controllers\EmailController::delete/$1');
            $routes->post('sign_pk_bupati/(:any)', '\App\Domains\Email\Controllers\EmailController::signPkBupati/$1');
            $routes->post('sign-pk-bupati/(:any)', '\App\Domains\Email\Controllers\EmailController::signPkBupati/$1');
        });
    });

    // ----------------------------------------------------------------
    // 3.3 Riwayat Ekspor & Operasi Massal (Batch)
    // ----------------------------------------------------------------
    // Riwayat Laporan Unduhan (Admin & Super Admin)
    $routes->group('reports', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('history', '\App\Domains\Email\Controllers\EmailExportController::history');
        $routes->get('download/(:num)', '\App\Domains\Email\Controllers\EmailExportController::downloadHistory/$1');
        $routes->post('delete/(:num)', '\App\Domains\Email\Controllers\EmailExportController::deleteHistory/$1');
    });

    // Operasi Batch Pembuatan & Pembaruan Akun (Admin & Super Admin)
    $routes->group('batch', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Batch\Controllers\BatchController::index');
        $routes->get('update', '\App\Domains\Batch\Controllers\BatchController::update');
        $routes->get('pk', '\App\Domains\Batch\Controllers\BatchController::pk');
        $routes->post('import_generic_spreadsheet', '\App\Domains\Batch\Controllers\BatchController::importGenericSpreadsheet');
        $routes->get('download_template', '\App\Domains\Batch\Controllers\BatchController::downloadTemplate');
        $routes->get('download_update_template', '\App\Domains\Batch\Controllers\BatchController::downloadUpdateTemplate');
        $routes->get('download_pk_template', '\App\Domains\Batch\Controllers\BatchController::downloadPkTemplate');
        $routes->get('download_unit_kerja_template', '\App\Domains\Batch\Controllers\BatchController::downloadUnitKerjaTemplate');
        $routes->match(['GET', 'POST'], 'execute_update', '\App\Domains\Batch\Controllers\BatchController::saveBatchUpdate');
        $routes->match(['GET', 'POST'], 'execute_create', '\App\Domains\Batch\Controllers\BatchController::saveBatchCreate');
    });

    // ----------------------------------------------------------------
    // 3.4 Penandatanganan Elektronik (TTE PK Bupati)
    // ----------------------------------------------------------------
    // Modul Kanonikal TTE PK (Khusus Super Admin)
    $routes->group('tte-pk', ['filter' => 'role:super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Email\Controllers\TteBupatiController::pppk');
        $routes->get('pppk', '\App\Domains\Email\Controllers\TteBupatiController::pppk');
        $routes->get('pppk-pw', '\App\Domains\Email\Controllers\TteBupatiController::pppkPw');
        $routes->post('sign-single/(:num)', '\App\Domains\Email\Controllers\TteBupatiController::signSingle/$1');
        $routes->post('sign_single/(:num)', '\App\Domains\Email\Controllers\TteBupatiController::signSingle/$1');
        $routes->post('sign-batch', '\App\Domains\Email\Controllers\TteBupatiController::signBatch');
        $routes->post('sign_batch', '\App\Domains\Email\Controllers\TteBupatiController::signBatch');
    });

    // Alias Kompatibilitas TTE Bupati (Khusus Super Admin)
    $routes->group('tte-bupati', ['filter' => 'role:super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Email\Controllers\TteBupatiController::index');
        $routes->get('pppk', '\App\Domains\Email\Controllers\TteBupatiController::pppk');
        $routes->get('pppk-pw', '\App\Domains\Email\Controllers\TteBupatiController::pppkPw');
        $routes->post('sign-single/(:num)', '\App\Domains\Email\Controllers\TteBupatiController::signSingle/$1');
        $routes->post('sign_single/(:num)', '\App\Domains\Email\Controllers\TteBupatiController::signSingle/$1');
        $routes->post('sign-batch', '\App\Domains\Email\Controllers\TteBupatiController::signBatch');
        $routes->post('sign_batch', '\App\Domains\Email\Controllers\TteBupatiController::signBatch');
    });

    // ----------------------------------------------------------------
    // 3.5 Data Induk & Pemantauan Website
    // ----------------------------------------------------------------
    // Manajemen Unit Kerja (Khusus Super Admin)
    $routes->group('unit_kerja', ['filter' => 'role:super_admin'], function ($routes) {
        $routes->get('manage', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::manage');
        $routes->get('add', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::add');
        $routes->post('store', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::store');
        $routes->get('edit/(:num)', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::edit/$1');
        $routes->post('update/(:num)', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::update/$1');
        $routes->get('delete/(:num)', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::delete/$1');
        $routes->get('batch_create', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::batchCreate');
        $routes->post('batch_store', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::batchStore');
        $routes->post('process_batch_create', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::processBatchCreate');
    });

    // Pemantauan Website Desa dan Kelurahan
    $routes->group('web_desa_kelurahan', function ($routes) {
        $routes->get('/', '\App\Domains\Website\Controllers\WebDesaKelurahanController::index');
        $routes->get('export_pdf', '\App\Domains\Website\Controllers\WebDesaKelurahanController::exportPdf');

        // Mutasi Website Desa & Kelurahan (Admin & Super Admin)
        $routes->group('', ['filter' => 'role:admin,super_admin'], function ($routes) {
            $routes->get('edit/(:num)', '\App\Domains\Website\Controllers\WebDesaKelurahanController::edit/$1');
            $routes->post('update/(:num)', '\App\Domains\Website\Controllers\WebDesaKelurahanController::update/$1');
            $routes->get('sync_expiration/(:num)', '\App\Domains\Website\Controllers\WebDesaKelurahanController::syncExpiration/$1');
        });
    });

    // Pemantauan Website Organisasi Perangkat Daerah (OPD)
    $routes->group('web_opd', function ($routes) {
        $routes->get('/', '\App\Domains\Website\Controllers\WebOpdController::index');
        $routes->get('export_pdf', '\App\Domains\Website\Controllers\WebOpdController::exportPdf');

        // Mutasi Website OPD (Admin & Super Admin)
        $routes->group('', ['filter' => 'role:admin,super_admin'], function ($routes) {
            $routes->get('edit/(:num)', '\App\Domains\Website\Controllers\WebOpdController::edit/$1');
            $routes->post('update/(:num)', '\App\Domains\Website\Controllers\WebOpdController::update/$1');
        });
    });

    // Log Layanan Pendampingan Teknis (Khusus Super Admin)
    $routes->group('assistance', ['filter' => 'role:super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Assistance\Controllers\AssistanceController::index');
        $routes->get('export_pdf', '\App\Domains\Assistance\Controllers\AssistanceController::exportPdf');
        $routes->get('create', '\App\Domains\Assistance\Controllers\AssistanceController::create');
        $routes->post('store', '\App\Domains\Assistance\Controllers\AssistanceController::store');
        $routes->get('edit/(:num)', '\App\Domains\Assistance\Controllers\AssistanceController::edit/$1');
        $routes->post('update/(:num)', '\App\Domains\Assistance\Controllers\AssistanceController::update/$1');
        $routes->get('delete/(:num)', '\App\Domains\Assistance\Controllers\AssistanceController::delete/$1');
    });

    // ----------------------------------------------------------------
    // 3.6 Manajemen Pengguna, Audit Trail, & Utilitas Akun
    // ----------------------------------------------------------------
    // Utilitas Akun Mandiri
    $routes->get('user/change_password', '\App\Domains\Auth\Controllers\UserController::changePassword');
    $routes->post('user/update_password', '\App\Domains\Auth\Controllers\UserController::updatePassword');
    $routes->post('user/check_email', '\App\Domains\Auth\Controllers\UserController::checkEmailAvailability');
    $routes->post('user/check_niknip', '\App\Domains\Auth\Controllers\UserController::checkNiknip');
    $routes->post('user/batch_check_availability', '\App\Domains\Auth\Controllers\UserController::batchCheckAvailability');

    // Manajemen Pengguna Login (Khusus Super Admin)
    $routes->group('auth/users', ['filter' => 'role:super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Auth\Controllers\UserManagementController::index');
        $routes->get('add', '\App\Domains\Auth\Controllers\UserManagementController::add');
        $routes->post('store', '\App\Domains\Auth\Controllers\UserManagementController::store');
        $routes->get('edit/(:num)', '\App\Domains\Auth\Controllers\UserManagementController::edit/$1');
        $routes->post('update/(:num)', '\App\Domains\Auth\Controllers\UserManagementController::update/$1');
        $routes->post('delete/(:num)', '\App\Domains\Auth\Controllers\UserManagementController::delete/$1');
    });

    // Audit Trail Jejak Aktivitas (Khusus Super Admin)
    $routes->get('audit-trail', '\App\Domains\Auth\Controllers\AuditLogController::index', ['filter' => 'role:super_admin']);

    // Pemeriksaan Kesehatan Sistem (Admin & Super Admin)
    $routes->get('api/health-check', '\App\Domains\Api\Controllers\GatewayController::healthCheck', ['filter' => 'role:admin,super_admin']);

    // ----------------------------------------------------------------
    // 3.7 Helpdesk Admin (Manajemen Tiket Pengaduan)
    // ----------------------------------------------------------------
    // 3.7 Layanan: Helpdesk Internal
    // ----------------------------------------------------------------
    // Kanonikal: helpdesk/manage | Alias: helpdesk/admin & admin/helpdesk
    $routes->group('helpdesk/manage', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::index');
        $routes->get('detail/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::detail/$1');
        $routes->post('update_status/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::updateStatus/$1');
        $routes->post('update-status/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::updateStatus/$1');
        $routes->post('delete/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::delete/$1');
    });
    $routes->group('helpdesk/admin', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::index');
        $routes->get('detail/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::detail/$1');
        $routes->post('update_status/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::updateStatus/$1');
        $routes->post('update-status/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::updateStatus/$1');
        $routes->post('delete/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::delete/$1');
    });
    $routes->group('admin/helpdesk', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::index');
        $routes->get('detail/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::detail/$1');
        $routes->post('update_status/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::updateStatus/$1');
        $routes->post('update-status/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::updateStatus/$1');
        $routes->post('delete/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::delete/$1');
    });

    // ----------------------------------------------------------------
    // 3.8 Integrasi: API Gateway
    // ----------------------------------------------------------------
    // Kanonikal: api/docs & api/log
    $routes->group('api', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('docs', '\App\Domains\Api\Controllers\GatewayController::index');
        $routes->get('/', '\App\Domains\Api\Controllers\GatewayController::index');
        $routes->get('log', '\App\Domains\Api\Controllers\GatewayLogController::index');
        $routes->get('log/download', '\App\Domains\Api\Controllers\GatewayLogController::download');
        $routes->post('log/clear', '\App\Domains\Api\Controllers\GatewayLogController::clear');
    });

    // Alias Kompatibilitas API Gateway
    $routes->group('api-gateway', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Api\Controllers\GatewayController::index');
        $routes->get('docs', '\App\Domains\Api\Controllers\GatewayController::index');
        $routes->get('log', '\App\Domains\Api\Controllers\GatewayLogController::index');
        $routes->get('log/download', '\App\Domains\Api\Controllers\GatewayLogController::download');
        $routes->post('log/clear', '\App\Domains\Api\Controllers\GatewayLogController::clear');
    });
    $routes->group('api-gateway-log', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Api\Controllers\GatewayLogController::index');
        $routes->get('download', '\App\Domains\Api\Controllers\GatewayLogController::download');
        $routes->post('clear', '\App\Domains\Api\Controllers\GatewayLogController::clear');
    });
    $routes->group('admin/api-gateway-log', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Api\Controllers\GatewayLogController::index');
        $routes->get('download', '\App\Domains\Api\Controllers\GatewayLogController::download');
        $routes->post('clear', '\App\Domains\Api\Controllers\GatewayLogController::clear');
    });

    // ----------------------------------------------------------------
    // 3.9 Integrasi: Bridge TTE
    // ----------------------------------------------------------------
    // Kanonikal: bridge/log & bridge/whitelist
    $routes->group('bridge', ['filter' => 'role:admin,super_admin'], function ($routes) {
        // Log Akses Bridge TTE
        $routes->get('log', '\App\Domains\Bridge\Controllers\BridgeLogController::index');
        $routes->get('log/download', '\App\Domains\Bridge\Controllers\BridgeLogController::download');
        $routes->post('log/clear', '\App\Domains\Bridge\Controllers\BridgeLogController::clear');

        // Whitelist Domain & Alamat IP Bridge TTE
        $routes->get('whitelist', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::index');
        $routes->post('whitelist/store', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::store');
        $routes->post('whitelist/update/(:num)', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::update/$1');
        $routes->post('whitelist/toggle/(:num)', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::toggle/$1');
        $routes->post('whitelist/sync/(:num)', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::sync/$1');
        $routes->post('whitelist/sync-all', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::syncAll');
        $routes->post('whitelist/delete/(:num)', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::delete/$1');
    });
    // Alias Kompatibilitas Log Bridge
    $routes->group('bridge-log', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Bridge\Controllers\BridgeLogController::index');
        $routes->get('download', '\App\Domains\Bridge\Controllers\BridgeLogController::download');
        $routes->post('clear', '\App\Domains\Bridge\Controllers\BridgeLogController::clear');
    });
    $routes->group('admin/bridge-log', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Bridge\Controllers\BridgeLogController::index');
        $routes->get('download', '\App\Domains\Bridge\Controllers\BridgeLogController::download');
        $routes->post('clear', '\App\Domains\Bridge\Controllers\BridgeLogController::clear');
    });
    // Alias Kompatibilitas Whitelist Bridge
    $routes->group('bridge-whitelist', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::index');
        $routes->post('store', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::store');
        $routes->post('update/(:num)', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::update/$1');
        $routes->post('toggle/(:num)', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::toggle/$1');
        $routes->post('sync/(:num)', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::sync/$1');
        $routes->post('sync-all', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::syncAll');
        $routes->post('delete/(:num)', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::delete/$1');
    });
    $routes->group('admin/bridge-whitelist', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::index');
        $routes->post('store', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::store');
        $routes->post('update/(:num)', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::update/$1');
        $routes->post('toggle/(:num)', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::toggle/$1');
        $routes->post('sync/(:num)', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::sync/$1');
        $routes->post('sync-all', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::syncAll');
        $routes->post('delete/(:num)', '\App\Domains\Bridge\Controllers\BridgeWhitelistController::delete/$1');
    });

    // ----------------------------------------------------------------
    // 3.8 Integrasi Layanan Sertifikat Elektronik BSrE
    // ----------------------------------------------------------------
    $routes->group('bsre', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('check-status', '\App\Domains\Email\Controllers\BsreController::checkStatus');
        $routes->get('sync-all', '\App\Domains\Email\Controllers\BsreController::syncAllStatus');
        $routes->get('sync-status', '\App\Domains\Email\Controllers\BsreController::syncStatus');
        $routes->post('sync-status', '\App\Domains\Email\Controllers\BsreController::syncStatus');
        $routes->post('verify', '\App\Domains\Email\Controllers\BsreController::verifyPdf');
    });
});

// ====================================================================
// 4. ROUTER PROXY BRIDGE TTE (WILDCARD FALLBACK)
// ====================================================================
$routes->match(['get', 'post'], 'bridge/(:any)', '\App\Domains\Bridge\Controllers\BridgeController::proxy/$1', ['filter' => 'throttle']);
$routes->match(['get', 'post'], 'bridge', '\App\Domains\Bridge\Controllers\BridgeController::index', ['filter' => 'throttle']);
