<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Authentication Routes
$routes->get('login', '\App\Domains\Auth\Controllers\Auth::login');
$routes->post('auth/attemptLogin', '\App\Domains\Auth\Controllers\Auth::attemptLogin');
$routes->get('logout', '\App\Domains\Auth\Controllers\Auth::logout');

// Public Identity Verification
$routes->get('verifikasi/(:any)', '\App\Domains\Email\Controllers\Email::profile/$1');

// Helpdesk (Public Form)
$routes->get('helpdesk', '\App\Domains\Helpdesk\Controllers\HelpdeskPublicController::index');
$routes->post('helpdesk/submit', '\App\Domains\Helpdesk\Controllers\HelpdeskPublicController::submit');
$routes->get('helpdesk/success/(:any)', '\App\Domains\Helpdesk\Controllers\HelpdeskPublicController::success/$1');

// API Gateway (v1) - External Integration
$routes->group('api/v1', ['filter' => 'api_gateway'], function ($routes) {
    $routes->get('emails', '\App\Domains\Api\Controllers\GatewayController::listEmails');
    $routes->get('pppk', '\App\Domains\Api\Controllers\GatewayController::listPppkPenuh');
    $routes->get('pppk-pw', '\App\Domains\Api\Controllers\GatewayController::listPppkParuh');
    $routes->get('pns', '\App\Domains\Api\Controllers\GatewayController::listPns');
    $routes->get('unit/(:num)', '\App\Domains\Api\Controllers\GatewayController::listByUnit/$1');
});

// Protected Routes
$routes->group('', ['filter' => 'auth'], function ($routes) {
    
    // Portal Utama
    $routes->get('/', '\App\Domains\Dashboard\Controllers\Home::index');

        // API Documentation (Admin & Super Admin)
        $routes->group('api-docs', ['filter' => 'role:admin,super_admin'], function ($routes) {
            $routes->get('/', '\App\Domains\Api\Controllers\GatewayController::index');
        });

        // Manajemen Email
        $routes->group('email', function ($routes) {
        // View Routes (Admin & Super Admin)
        $routes->get('/', '\App\Domains\Email\Controllers\Email::index');
        $routes->get('detail/(:any)', '\App\Domains\Email\Controllers\Email::detail/$1');
        
        // Trash Routes (Manajemen Sampah)
        $routes->get('trash', '\App\Domains\Email\Controllers\TrashController::index');
        $routes->get('trash/restore/(:num)', '\App\Domains\Email\Controllers\TrashController::restore/$1');
        $routes->post('trash/force_delete/(:num)', '\App\Domains\Email\Controllers\TrashController::forceDelete/$1');
        
        // List Routes
        $routes->get('unit_kerja', '\App\Domains\Email\Controllers\EmailList::unit_kerja_list');
        $routes->get('unit_kerja/(:num)', '\App\Domains\Email\Controllers\EmailList::unit_kerja_detail/$1');
        $routes->get('eselon_detail/(:num)', '\App\Domains\Email\Controllers\EmailList::eselon_detail/$1');
        $routes->get('pns_list', '\App\Domains\Email\Controllers\EmailList::pns_list');
    $routes->get('export_pns_excel', '\App\Domains\Email\Controllers\EmailExport::export_pns_excel');
        $routes->get('pppk_list', '\App\Domains\Email\Controllers\EmailList::pppk_list');
        $routes->get('pppk_pw_list', '\App\Domains\Email\Controllers\EmailList::pppk_pw_list');
        $routes->get('eselon_list', '\App\Domains\Email\Controllers\EmailList::eselon_list');

        // Pimpinan Routes
        $routes->get('pimpinan', '\App\Domains\Pimpinan\Controllers\PimpinanController::pimpinan');
        $routes->get('pimpinan_desa', '\App\Domains\Pimpinan\Controllers\PimpinanController::pimpinan_desa');
        $routes->get('export_pimpinan_pdf', '\App\Domains\Pimpinan\Controllers\PimpinanController::export_pimpinan_pdf');
        $routes->get('export_pimpinan_desa_pdf', '\App\Domains\Pimpinan\Controllers\PimpinanController::export_pimpinan_desa_pdf');

        // Export Routes
        $routes->get('export_unit_kerja_csv/(:num)', '\App\Domains\Email\Controllers\EmailExport::export_unit_kerja_csv/$1');
        $routes->get('export_unit_kerja_excel/(:num)', '\App\Domains\Email\Controllers\EmailExport::export_unit_kerja_excel/$1');
        $routes->get('export_unit_kerja_pdf/(:num)', '\App\Domains\Email\Controllers\EmailExport::export_unit_kerja_pdf/$1');
        $routes->get('export_account_detail_pdf/(:num)', '\App\Domains\Email\Controllers\EmailExport::export_account_detail_pdf/$1');
        $routes->get('export_perjanjian_kerja_pdf/(:num)', '\App\Domains\Email\Controllers\EmailExport::export_perjanjian_kerja_pdf/$1');
        $routes->get('export_single_perjanjian_kerja_pdf/(:any)', '\App\Domains\Email\Controllers\EmailExport::export_single_perjanjian_kerja_pdf/$1');
        $routes->get('download_zip_file/(:any)', '\App\Domains\Email\Controllers\EmailExport::download_zip_file/$1');



        // API Routes
        $routes->get('search', '\App\Domains\Email\Controllers\EmailApi::search');

        // Mutation Routes (Admin & Super Admin)
        $routes->group('', ['filter' => 'role:admin,super_admin'], function ($routes) {

            $routes->get('create', '\App\Domains\Email\Controllers\Email::create');
            $routes->get('edit_profile/(:any)', '\App\Domains\Email\Controllers\Email::edit_profile/$1');
            $routes->post('update_details/(:any)', '\App\Domains\Email\Controllers\Email::update_details/$1');
            $routes->post('mark_pensiun/(:any)', '\App\Domains\Email\Controllers\Email::mark_pensiun/$1');
            $routes->get('edit_password/(:any)', '\App\Domains\Email\Controllers\Email::edit_password/$1');
            $routes->post('update_password/(:any)', '\App\Domains\Email\Controllers\Email::update_password/$1');
            $routes->get('edit_pk/(:any)', '\App\Domains\Email\Controllers\Email::edit_pk/$1');
            $routes->post('update_pk/(:any)', '\App\Domains\Email\Controllers\Email::update_pk/$1');
            $routes->post('create_single', '\App\Domains\Email\Controllers\EmailApi::create_single_email');
            
            // Swap Data
            $routes->get('swap_data', '\App\Domains\Email\Controllers\Email::swap_form');
            $routes->post('swap_process', '\App\Domains\Email\Controllers\Email::swap_process');
        });

        // Sync & Utility Routes (Admin & Super Admin)
        $routes->group('', ['filter' => 'role:admin,super_admin'], function ($routes) {
            $routes->post('sync_pegawai', '\App\Domains\Email\Controllers\EmailApi::sync_pegawai');
            $routes->post('api_generate_pdf', '\App\Domains\Email\Controllers\EmailApi::api_generate_pdf');
            $routes->get('api_unit_emails/(:num)', '\App\Domains\Email\Controllers\EmailApi::api_unit_emails/$1');
            $routes->get('api_download_zip/(:num)', '\App\Domains\Email\Controllers\EmailApi::api_download_zip/$1');
        });

        // Destructive Routes (Super Admin Only)
        $routes->group('', ['filter' => 'role:super_admin'], function ($routes) {
            $routes->post('delete/(:num)', '\App\Domains\Email\Controllers\Email::delete/$1');
        });
    });

    // Reports History
    $routes->get('reports/history', '\App\Domains\Email\Controllers\EmailExport::history');
    $routes->get('reports/download/(:num)', '\App\Domains\Email\Controllers\EmailExport::download_history/$1');

    // Batch Create & Update API
    $routes->match(['GET', 'POST'], 'batch_execute_update', '\App\Domains\Batch\Controllers\BatchController::save_batch_update');
    $routes->match(['GET', 'POST'], 'batch_execute_create', '\App\Domains\Batch\Controllers\BatchController::save_batch_create');

    // Batch Operations (Admin & Super Admin)
    $routes->group('batch', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Batch\Controllers\BatchController::index');
        $routes->post('import_generic_spreadsheet', '\App\Domains\Batch\Controllers\BatchController::import_generic_spreadsheet');
        $routes->get('download_template', '\App\Domains\Batch\Controllers\BatchController::download_template');
        $routes->get('download_update_template', '\App\Domains\Batch\Controllers\BatchController::download_update_template');
        $routes->get('download_pk_template', '\App\Domains\Batch\Controllers\BatchController::download_pk_template');
        $routes->get('download_unit_kerja_template', '\App\Domains\Batch\Controllers\BatchController::download_unit_kerja_template');
        $routes->get('update', '\App\Domains\Batch\Controllers\BatchController::update');
        $routes->get('pk', '\App\Domains\Batch\Controllers\BatchController::pk');
    });

    // Manajemen Data Induk (Unit Kerja - Super Admin Only)
    $routes->group('unit_kerja', ['filter' => 'role:super_admin'], function ($routes) {
        $routes->get('manage', '\App\Domains\UnitKerja\Controllers\UnitKerja::manage');
        $routes->get('add', '\App\Domains\UnitKerja\Controllers\UnitKerja::add');
        $routes->post('store', '\App\Domains\UnitKerja\Controllers\UnitKerja::store');
        $routes->get('batch_create', '\App\Domains\UnitKerja\Controllers\UnitKerja::batch_create');
        $routes->post('batch_store', '\App\Domains\UnitKerja\Controllers\UnitKerja::batch_store');
        $routes->post('process_batch_create', '\App\Domains\UnitKerja\Controllers\UnitKerja::process_batch_create');
        $routes->get('edit/(:num)', '\App\Domains\UnitKerja\Controllers\UnitKerja::edit/$1');
        $routes->post('update/(:num)', '\App\Domains\UnitKerja\Controllers\UnitKerja::update/$1');
        $routes->get('delete/(:num)', '\App\Domains\UnitKerja\Controllers\UnitKerja::delete/$1');
    });

    // Pemantauan Website
    $routes->group('web_desa_kelurahan', function ($routes) {
        $routes->get('/', '\App\Domains\Website\Controllers\WebDesaKelurahan::index');
        $routes->get('export_pdf', '\App\Domains\Website\Controllers\WebDesaKelurahan::export_pdf');
        
        // Mutation Routes (Admin & Super Admin)
        $routes->group('', ['filter' => 'role:admin,super_admin'], function ($routes) {
            $routes->get('edit/(:num)', '\App\Domains\Website\Controllers\WebDesaKelurahan::edit/$1');
            $routes->post('update/(:num)', '\App\Domains\Website\Controllers\WebDesaKelurahan::update/$1');
            $routes->get('sync_expiration/(:num)', '\App\Domains\Website\Controllers\WebDesaKelurahan::sync_expiration/$1');
        });
    });

    $routes->group('web_opd', function ($routes) {
        $routes->get('/', '\App\Domains\Website\Controllers\WebOpd::index');
        $routes->get('export_pdf', '\App\Domains\Website\Controllers\WebOpd::export_pdf');
        
        // Mutation Routes (Admin & Super Admin)
        $routes->group('', ['filter' => 'role:admin,super_admin'], function ($routes) {
            $routes->get('edit/(:num)', '\App\Domains\Website\Controllers\WebOpd::edit/$1');
            $routes->post('update/(:num)', '\App\Domains\Website\Controllers\WebOpd::update/$1');
        });
    });

    // Log Pendampingan (Super Admin Only)
    $routes->group('assistance', ['filter' => 'role:super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Assistance\Controllers\Assistance::index');
        $routes->get('export_pdf', '\App\Domains\Assistance\Controllers\Assistance::export_pdf');
        $routes->get('create', '\App\Domains\Assistance\Controllers\Assistance::create');
        $routes->post('store', '\App\Domains\Assistance\Controllers\Assistance::store');
        $routes->get('edit/(:num)', '\App\Domains\Assistance\Controllers\Assistance::edit/$1');
        $routes->post('update/(:num)', '\App\Domains\Assistance\Controllers\Assistance::update/$1');
        $routes->get('delete/(:num)', '\App\Domains\Assistance\Controllers\Assistance::delete/$1');
    });

    // Utilitas User & BSrE
    $routes->get('user/change_password', '\App\Domains\Auth\Controllers\User::changePassword');
    $routes->post('user/update_password', '\App\Domains\Auth\Controllers\User::updatePassword');
    $routes->post('user/check_email', '\App\Domains\Auth\Controllers\User::checkEmailAvailability');
    $routes->post('user/check_niknip', '\App\Domains\Auth\Controllers\User::check_niknip');
    $routes->post('user/batch_check_availability', '\App\Domains\Auth\Controllers\User::batch_check_availability');
    
    // User Management (Super Admin Only)
    $routes->group('auth/users', ['filter' => 'role:super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Auth\Controllers\UserManagement::index');
        $routes->get('add', '\App\Domains\Auth\Controllers\UserManagement::add');
        $routes->post('store', '\App\Domains\Auth\Controllers\UserManagement::store');
        $routes->post('check_nip', '\App\Domains\Auth\Controllers\UserManagement::check_nip');
        $routes->get('edit/(:num)', '\App\Domains\Auth\Controllers\UserManagement::edit/$1');
        $routes->post('update/(:num)', '\App\Domains\Auth\Controllers\UserManagement::update/$1');
        $routes->post('delete/(:num)', '\App\Domains\Auth\Controllers\UserManagement::delete/$1');
    });

    // Audit Logs (Super Admin Only)
    $routes->get('audit_logs', '\App\Domains\Auth\Controllers\AuditLogController::index', ['filter' => 'role:super_admin']);

    // Health Check (Admin & Super Admin)
    $routes->get('api/health-check', '\App\Domains\Api\Controllers\GatewayController::healthCheck', ['filter' => 'role:admin,super_admin']);

    // Helpdesk Admin (Admin & Super Admin)
    $routes->group('admin/helpdesk', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::index');
        $routes->get('detail/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::detail/$1');
        $routes->post('update_status/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::updateStatus/$1');
        $routes->post('delete/(:num)', '\App\Domains\Helpdesk\Controllers\HelpdeskAdminController::delete/$1');
    });    
    $routes->get('bsre/check-status', '\App\Domains\Email\Controllers\Bsre::checkStatus');
    
    $routes->group('bsre', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('sync-all', '\App\Domains\Email\Controllers\Bsre::syncAllStatus');
        $routes->get('sync-status', '\App\Domains\Email\Controllers\Bsre::syncStatus');
        $routes->post('sync-status', '\App\Domains\Email\Controllers\Bsre::syncStatus');
    });
});