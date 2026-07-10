<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Authentication Routes
$routes->get('login', '\App\Domains\Auth\Controllers\AuthController::login');
$routes->post('auth/attemptLogin', '\App\Domains\Auth\Controllers\AuthController::attemptLogin');
$routes->get('logout', '\App\Domains\Auth\Controllers\AuthController::logout');

// Public Identity Verification
$routes->get('verifikasi/(:any)', '\App\Domains\Email\Controllers\EmailController::profile/$1');

// Helpdesk (Public Form)
$routes->get('helpdesk', '\App\Domains\Helpdesk\Controllers\HelpdeskPublicController::index');
$routes->post('helpdesk/submit', '\App\Domains\Helpdesk\Controllers\HelpdeskPublicController::submit');
$routes->get('helpdesk/success/(:any)', '\App\Domains\Helpdesk\Controllers\HelpdeskPublicController::success/$1');

// Public PDF Verification
$routes->get('verifikasi-pdf', '\App\Domains\Email\Controllers\BsreController::publicVerify');
$routes->post('verifikasi-pdf', '\App\Domains\Email\Controllers\BsreController::verifyPdf');

// API Gateway (v1) - External Integration
$routes->group('api/v1', ['filter' => 'api_gateway'], function ($routes) {
    $routes->get('emails', '\App\Domains\Api\Controllers\GatewayController::listEmails');
    $routes->get('pppk', '\App\Domains\Api\Controllers\GatewayController::listPppkPenuh');
    $routes->get('pppk-pw', '\App\Domains\Api\Controllers\GatewayController::listPppkParuh');
    $routes->get('pns', '\App\Domains\Api\Controllers\GatewayController::listPns');
    $routes->get('unit/(:num)', '\App\Domains\Api\Controllers\GatewayController::listByUnit/$1');
});

// Internal Async Queue Trigger (Protected: Admin & Super Admin only)
$routes->get('apiTriggerQueue', '\App\Domains\Email\Controllers\EmailApiController::apiTriggerQueue', ['filter' => 'role:admin,super_admin']);

// Public Route - Landing Page
$routes->get('/', '\App\Domains\Dashboard\Controllers\HomeController::index');

// Protected Routes
$routes->group('', ['filter' => 'auth'], function ($routes) {
    
    // Portal Utama
    $routes->get('dashboard', '\App\Domains\Dashboard\Controllers\HomeController::dashboard');

        // API Documentation (Admin & Super Admin)
        $routes->group('api-gateway', ['filter' => 'role:admin,super_admin'], function ($routes) {
            $routes->get('/', '\App\Domains\Api\Controllers\GatewayController::index');
        });

        // Manajemen Email
        $routes->group('email', function ($routes) {
        // View Routes (Admin & Super Admin)
        $routes->get('/', '\App\Domains\Email\Controllers\EmailController::index');
        $routes->get('detail/(:any)', '\App\Domains\Email\Controllers\EmailController::detail/$1');
        
        // Trash Routes (Manajemen Sampah)
        $routes->get('trash', '\App\Domains\Email\Controllers\TrashController::index');
        $routes->get('trash/restore/(:num)', '\App\Domains\Email\Controllers\TrashController::restore/$1');
        $routes->post('trash/force_delete/(:num)', '\App\Domains\Email\Controllers\TrashController::forceDelete/$1');
        
        // List Routes
        $routes->get('unit_kerja', '\App\Domains\Email\Controllers\EmailListController::unitKerjaList');
        $routes->get('unit_kerja/(:num)', '\App\Domains\Email\Controllers\EmailListController::unitKerjaDetail/$1');
        $routes->get('eselonDetail/(:num)', '\App\Domains\Email\Controllers\EmailListController::eselonDetail/$1');
        $routes->get('pns', '\App\Domains\Email\Controllers\EmailListController::pnsList');
        $routes->get('exportPnsExcel', '\App\Domains\Email\Controllers\EmailExportController::exportPnsExcel');
        $routes->get('pppk', '\App\Domains\Email\Controllers\EmailListController::pppkList');
        $routes->get('pppk-pw', '\App\Domains\Email\Controllers\EmailListController::pppkPwList');
        $routes->get('eselon', '\App\Domains\Email\Controllers\EmailListController::eselonList');

        // Pimpinan Routes
        $routes->get('pimpinan', '\App\Domains\Pimpinan\Controllers\PimpinanController::pimpinan');
        $routes->get('pimpinanDesa', '\App\Domains\Pimpinan\Controllers\PimpinanController::pimpinanDesa');
        $routes->get('exportPimpinanPdf', '\App\Domains\Pimpinan\Controllers\PimpinanController::exportPimpinanPdf');
        $routes->get('exportPimpinanDesaPdf', '\App\Domains\Pimpinan\Controllers\PimpinanController::exportPimpinanDesaPdf');

        // Export Routes
        $routes->get('exportUnitKerjaCsv/(:num)', '\App\Domains\Email\Controllers\EmailExportController::exportUnitKerjaCsv/$1');
        $routes->get('exportUnitKerjaExcel/(:num)', '\App\Domains\Email\Controllers\EmailExportController::exportUnitKerjaExcel/$1');
        $routes->get('exportUnitKerjaPdf/(:num)', '\App\Domains\Email\Controllers\EmailExportController::exportUnitKerjaPdf/$1');
        $routes->get('exportAccountDetailPdf/(:num)', '\App\Domains\Email\Controllers\EmailExportController::exportAccountDetailPdf/$1');
        $routes->get('exportPerjanjianKerjaPdf/(:num)', '\App\Domains\Email\Controllers\EmailExportController::exportPerjanjianKerjaPdf/$1');
        $routes->get('exportSinglePerjanjianKerjaPdf/(:any)', '\App\Domains\Email\Controllers\EmailExportController::exportSinglePerjanjianKerjaPdf/$1');
        $routes->get('downloadZipFile/(:any)', '\App\Domains\Email\Controllers\EmailExportController::downloadZipFile/$1');



        // API Routes
        $routes->get('search', '\App\Domains\Email\Controllers\EmailApiController::search');

        // Mutation Routes (Admin & Super Admin)
        $routes->group('', ['filter' => 'role:admin,super_admin'], function ($routes) {

            $routes->get('create', '\App\Domains\Email\Controllers\EmailController::create');
            $routes->get('editProfile/(:any)', '\App\Domains\Email\Controllers\EmailController::editProfile/$1');
            $routes->post('updateDetails/(:any)', '\App\Domains\Email\Controllers\EmailController::updateDetails/$1');
            $routes->post('markPensiun/(:any)', '\App\Domains\Email\Controllers\EmailController::markPensiun/$1');
            $routes->get('editPassword/(:any)', '\App\Domains\Email\Controllers\EmailController::editPassword/$1');
            $routes->post('updatePassword/(:any)', '\App\Domains\Email\Controllers\EmailController::updatePassword/$1');
            $routes->get('editPk/(:any)', '\App\Domains\Email\Controllers\EmailController::editPk/$1');
            $routes->post('updatePk/(:any)', '\App\Domains\Email\Controllers\EmailController::updatePk/$1');
            $routes->post('create_single', '\App\Domains\Email\Controllers\EmailApiController::createSingleEmail');
            
            // Swap Data
            $routes->get('swap_data', '\App\Domains\Email\Controllers\EmailController::swapForm');
            $routes->post('swapProcess', '\App\Domains\Email\Controllers\EmailController::swapProcess');
        });

        // Sync & Utility Routes (Admin & Super Admin)
        $routes->group('', ['filter' => 'role:admin,super_admin'], function ($routes) {
            $routes->post('syncPegawai', '\App\Domains\Email\Controllers\EmailApiController::syncPegawai');
            $routes->post('apiGeneratePdf', '\App\Domains\Email\Controllers\EmailApiController::apiGeneratePdf');
            $routes->get('apiUnitEmails/(:num)', '\App\Domains\Email\Controllers\EmailApiController::apiUnitEmails/$1');
            $routes->get('apiDownloadZip/(:num)', '\App\Domains\Email\Controllers\EmailApiController::apiDownloadZip/$1');
        });

        // Destructive Routes (Super Admin Only)
        $routes->group('', ['filter' => 'role:super_admin'], function ($routes) {
            $routes->post('delete/(:num)', '\App\Domains\Email\Controllers\EmailController::delete/$1');
        });
    });

    // Reports History (Admin & Super Admin)
    $routes->group('reports', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('history', '\App\Domains\Email\Controllers\EmailExportController::history');
        $routes->get('download/(:num)', '\App\Domains\Email\Controllers\EmailExportController::downloadHistory/$1');
        $routes->post('delete/(:num)', '\App\Domains\Email\Controllers\EmailExportController::deleteHistory/$1');
    });

    // Batch Operations (Admin & Super Admin)
    $routes->group('batch', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Batch\Controllers\BatchController::index');
        $routes->post('importGenericSpreadsheet', '\App\Domains\Batch\Controllers\BatchController::importGenericSpreadsheet');
        $routes->get('downloadTemplate', '\App\Domains\Batch\Controllers\BatchController::downloadTemplate');
        $routes->get('downloadUpdateTemplate', '\App\Domains\Batch\Controllers\BatchController::downloadUpdateTemplate');
        $routes->get('downloadPkTemplate', '\App\Domains\Batch\Controllers\BatchController::downloadPkTemplate');
        $routes->get('downloadUnitKerjaTemplate', '\App\Domains\Batch\Controllers\BatchController::downloadUnitKerjaTemplate');
        $routes->get('update', '\App\Domains\Batch\Controllers\BatchController::update');
        $routes->get('pk', '\App\Domains\Batch\Controllers\BatchController::pk');
        $routes->match(['GET', 'POST'], 'execute_update', '\App\Domains\Batch\Controllers\BatchController::saveBatchUpdate');
        $routes->match(['GET', 'POST'], 'execute_create', '\App\Domains\Batch\Controllers\BatchController::saveBatchCreate');
    });

    // Manajemen Data Induk (Unit Kerja - Super Admin Only)
    $routes->group('unit_kerja', ['filter' => 'role:super_admin'], function ($routes) {
        $routes->get('manage', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::manage');
        $routes->get('add', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::add');
        $routes->post('store', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::store');
        $routes->get('batchCreate', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::batchCreate');
        $routes->post('batchStore', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::batchStore');
        $routes->post('processBatchCreate', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::processBatchCreate');
        $routes->get('edit/(:num)', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::edit/$1');
        $routes->post('update/(:num)', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::update/$1');
        $routes->get('delete/(:num)', '\App\Domains\UnitKerja\Controllers\UnitKerjaController::delete/$1');
    });

    // Pemantauan Website
    $routes->group('web_desa_kelurahan', function ($routes) {
        $routes->get('/', '\App\Domains\Website\Controllers\WebDesaKelurahanController::index');
        $routes->get('exportPdf', '\App\Domains\Website\Controllers\WebDesaKelurahanController::exportPdf');
        
        // Mutation Routes (Admin & Super Admin)
        $routes->group('', ['filter' => 'role:admin,super_admin'], function ($routes) {
            $routes->get('edit/(:num)', '\App\Domains\Website\Controllers\WebDesaKelurahanController::edit/$1');
            $routes->post('update/(:num)', '\App\Domains\Website\Controllers\WebDesaKelurahanController::update/$1');
            $routes->get('syncExpiration/(:num)', '\App\Domains\Website\Controllers\WebDesaKelurahanController::syncExpiration/$1');
        });
    });

    $routes->group('web_opd', function ($routes) {
        $routes->get('/', '\App\Domains\Website\Controllers\WebOpdController::index');
        $routes->get('exportPdf', '\App\Domains\Website\Controllers\WebOpdController::exportPdf');
        
        // Mutation Routes (Admin & Super Admin)
        $routes->group('', ['filter' => 'role:admin,super_admin'], function ($routes) {
            $routes->get('edit/(:num)', '\App\Domains\Website\Controllers\WebOpdController::edit/$1');
            $routes->post('update/(:num)', '\App\Domains\Website\Controllers\WebOpdController::update/$1');
        });
    });

    // Log Pendampingan (Super Admin Only)
    $routes->group('assistance', ['filter' => 'role:super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Assistance\Controllers\AssistanceController::index');
        $routes->get('exportPdf', '\App\Domains\Assistance\Controllers\AssistanceController::exportPdf');
        $routes->get('create', '\App\Domains\Assistance\Controllers\AssistanceController::create');
        $routes->post('store', '\App\Domains\Assistance\Controllers\AssistanceController::store');
        $routes->get('edit/(:num)', '\App\Domains\Assistance\Controllers\AssistanceController::edit/$1');
        $routes->post('update/(:num)', '\App\Domains\Assistance\Controllers\AssistanceController::update/$1');
        $routes->get('delete/(:num)', '\App\Domains\Assistance\Controllers\AssistanceController::delete/$1');
    });

    // Utilitas User & BSrE
    $routes->get('user/change_password', '\App\Domains\Auth\Controllers\UserController::changePassword');
    $routes->post('user/updatePassword', '\App\Domains\Auth\Controllers\UserController::updatePassword');
    $routes->post('user/check_email', '\App\Domains\Auth\Controllers\UserController::checkEmailAvailability');
    $routes->post('user/checkNiknip', '\App\Domains\Auth\Controllers\UserController::checkNiknip');
    $routes->post('user/batchCheckAvailability', '\App\Domains\Auth\Controllers\UserController::batchCheckAvailability');
    
    // User Management (Super Admin Only)
    $routes->group('auth/users', ['filter' => 'role:super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Auth\Controllers\UserManagementController::index');
        $routes->get('add', '\App\Domains\Auth\Controllers\UserManagementController::add');
        $routes->post('store', '\App\Domains\Auth\Controllers\UserManagementController::store');
        $routes->post('checkNip', '\App\Domains\Auth\Controllers\UserManagementController::checkNip');
        $routes->get('edit/(:num)', '\App\Domains\Auth\Controllers\UserManagementController::edit/$1');
        $routes->post('update/(:num)', '\App\Domains\Auth\Controllers\UserManagementController::update/$1');
        $routes->post('delete/(:num)', '\App\Domains\Auth\Controllers\UserManagementController::delete/$1');
    });

    // Audit Logs (Super Admin Only)
    $routes->get('audit-trail', '\App\Domains\Auth\Controllers\AuditLogController::index', ['filter' => 'role:super_admin']);

    // Health Check (Admin & Super Admin)
    $routes->get('api/health-check', '\App\Domains\Api\Controllers\GatewayController::healthCheck', ['filter' => 'role:admin,super_admin']);

    // Helpdesk Admin (Admin & Super Admin)
    $routes->group('admin/helpdesk', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::index');
        $routes->get('detail/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::detail/$1');
        $routes->post('update_status/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::updateStatus/$1');
        $routes->post('delete/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::delete/$1');
    });    
    $routes->group('bsre', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('check-status', '\App\Domains\Email\Controllers\BsreController::checkStatus');
        $routes->get('sync-all', '\App\Domains\Email\Controllers\BsreController::syncAllStatus');
        $routes->get('sync-status', '\App\Domains\Email\Controllers\BsreController::syncStatus');
        $routes->post('sync-status', '\App\Domains\Email\Controllers\BsreController::syncStatus');
        $routes->post('register', '\App\Domains\Email\Controllers\BsreController::registerUser');
        $routes->post('verify', '\App\Domains\Email\Controllers\BsreController::verifyPdf');
    });
});