<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Authentication Routes
$routes->get('login', '\App\Domains\Auth\Auth::login');
$routes->post('auth/attemptLogin', '\App\Domains\Auth\Auth::attemptLogin');
$routes->get('logout', '\App\Domains\Auth\Auth::logout');

// Public Identity Verification
$routes->get('verifikasi/(:any)', '\App\Domains\Email\Email::profile/$1');

// Protected Routes
$routes->group('', ['filter' => 'auth'], function ($routes) {
    
    // Portal Utama
    $routes->get('/', '\App\Domains\Dashboard\Home::index');

    // Manajemen Email
    $routes->group('email', function ($routes) {
        // View Routes (Admin & Super Admin)
        $routes->get('/', '\App\Domains\Email\Email::index');
        $routes->get('detail/(:any)', '\App\Domains\Email\Email::detail/$1');
        
        // List Routes
        $routes->get('unit_kerja', '\App\Domains\Email\EmailList::unit_kerja_list');
        $routes->get('unit_kerja/(:num)', '\App\Domains\Email\EmailList::unit_kerja_detail/$1');
        $routes->get('eselon_detail/(:num)', '\App\Domains\Email\EmailList::eselon_detail/$1');
        $routes->get('pns_list', '\App\Domains\Email\EmailList::pns_list');
        $routes->get('pppk_list', '\App\Domains\Email\EmailList::pppk_list');
        $routes->get('pppk_pw_list', '\App\Domains\Email\EmailList::pppk_pw_list');
        $routes->get('eselon_list', '\App\Domains\Email\EmailList::eselon_list');

        // Pimpinan Routes
        $routes->get('pimpinan', '\App\Domains\Pimpinan\PimpinanController::pimpinan');
        $routes->get('pimpinan_desa', '\App\Domains\Pimpinan\PimpinanController::pimpinan_desa');
        $routes->get('export_pimpinan_pdf', '\App\Domains\Pimpinan\PimpinanController::export_pimpinan_pdf');
        $routes->get('export_pimpinan_desa_pdf', '\App\Domains\Pimpinan\PimpinanController::export_pimpinan_desa_pdf');

        // Export Routes
        $routes->get('export_unit_kerja_csv/(:num)', '\App\Domains\Email\EmailExport::export_unit_kerja_csv/$1');
        $routes->get('export_unit_kerja_excel/(:num)', '\App\Domains\Email\EmailExport::export_unit_kerja_excel/$1');
        $routes->get('export_unit_kerja_pdf/(:num)', '\App\Domains\Email\EmailExport::export_unit_kerja_pdf/$1');
        $routes->get('export_account_detail_pdf/(:num)', '\App\Domains\Email\EmailExport::export_account_detail_pdf/$1');
        $routes->get('export_perjanjian_kerja_pdf/(:num)', '\App\Domains\Email\EmailExport::export_perjanjian_kerja_pdf/$1');
        $routes->get('export_single_perjanjian_kerja_pdf/(:any)', '\App\Domains\Email\EmailExport::export_single_perjanjian_kerja_pdf/$1');
        $routes->get('download_zip_file/(:any)', '\App\Domains\Email\EmailExport::download_zip_file/$1');

        // API Routes
        $routes->get('search', '\App\Domains\Email\EmailApi::search');

        // Mutation Routes (Admin & Super Admin)
        $routes->group('', ['filter' => 'role:admin,super_admin'], function ($routes) {
            $routes->get('create', '\App\Domains\Email\Email::create');
            $routes->get('edit_profile/(:any)', '\App\Domains\Email\Email::edit_profile/$1');
            $routes->post('update_details/(:any)', '\App\Domains\Email\Email::update_details/$1');
            $routes->get('edit_password/(:any)', '\App\Domains\Email\Email::edit_password/$1');
            $routes->post('update_password/(:any)', '\App\Domains\Email\Email::update_password/$1');
            $routes->get('edit_pk/(:any)', '\App\Domains\Email\Email::edit_pk/$1');
            $routes->post('update_pk/(:any)', '\App\Domains\Email\Email::update_pk/$1');
            $routes->post('create_single', '\App\Domains\Email\EmailApi::create_single_email');
        });

        // Sync & Utility Routes (Admin & Super Admin)
        $routes->group('', ['filter' => 'role:admin,super_admin'], function ($routes) {
            $routes->get('sync', '\App\Domains\Email\Email::sync');
            $routes->post('sync_pegawai', '\App\Domains\Email\EmailApi::sync_pegawai');
            $routes->post('api_generate_pdf', '\App\Domains\Email\EmailApi::api_generate_pdf');
            $routes->get('api_unit_emails/(:num)', '\App\Domains\Email\EmailApi::api_unit_emails/$1');
            $routes->get('api_download_zip/(:num)', '\App\Domains\Email\EmailApi::api_download_zip/$1');
        });

        // Destructive Routes (Super Admin Only)
        $routes->group('', ['filter' => 'role:super_admin'], function ($routes) {
            $routes->post('delete/(:num)', '\App\Domains\Email\Email::delete/$1');
        });
    });

    // Batch Operations (Admin & Super Admin)
    $routes->group('batch', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Batch\BatchController::index');
        $routes->post('import_generic_spreadsheet', '\App\Domains\Batch\BatchController::import_generic_spreadsheet');
        $routes->get('download_template', '\App\Domains\Batch\BatchController::download_template');
        $routes->get('download_update_template', '\App\Domains\Batch\BatchController::download_update_template');
        $routes->get('download_pk_template', '\App\Domains\Batch\BatchController::download_pk_template');
        $routes->get('download_unit_kerja_template', '\App\Domains\Batch\BatchController::download_unit_kerja_template');
        $routes->get('update', '\App\Domains\Batch\BatchController::update');
        $routes->get('pk', '\App\Domains\Batch\BatchController::pk');
        $routes->post('process_update', '\App\Domains\Batch\BatchController::process_update');
        $routes->post('process_create', '\App\Domains\Batch\BatchController::process_create');
    });

    // Manajemen Data Induk (Unit Kerja - Super Admin Only)
    $routes->group('unit_kerja', ['filter' => 'role:super_admin'], function ($routes) {
        $routes->get('manage', '\App\Domains\UnitKerja\UnitKerja::manage');
        $routes->get('add', '\App\Domains\UnitKerja\UnitKerja::add');
        $routes->post('store', '\App\Domains\UnitKerja\UnitKerja::store');
        $routes->get('batch_create', '\App\Domains\UnitKerja\UnitKerja::batch_create');
        $routes->post('batch_store', '\App\Domains\UnitKerja\UnitKerja::batch_store');
        $routes->post('process_batch_create', '\App\Domains\UnitKerja\UnitKerja::process_batch_create');
        $routes->get('edit/(:num)', '\App\Domains\UnitKerja\UnitKerja::edit/$1');
        $routes->post('update/(:num)', '\App\Domains\UnitKerja\UnitKerja::update/$1');
        $routes->get('delete/(:num)', '\App\Domains\UnitKerja\UnitKerja::delete/$1');
    });

    // Pemantauan Website
    $routes->group('web_desa_kelurahan', function ($routes) {
        $routes->get('/', '\App\Domains\Website\WebDesaKelurahan::index');
        $routes->get('export_pdf', '\App\Domains\Website\WebDesaKelurahan::export_pdf');
        
        // Mutation Routes (Admin & Super Admin)
        $routes->group('', ['filter' => 'role:admin,super_admin'], function ($routes) {
            $routes->get('edit/(:num)', '\App\Domains\Website\WebDesaKelurahan::edit/$1');
            $routes->post('update/(:num)', '\App\Domains\Website\WebDesaKelurahan::update/$1');
            $routes->get('sync_expiration/(:num)', '\App\Domains\Website\WebDesaKelurahan::sync_expiration/$1');
        });
    });

    $routes->group('web_opd', function ($routes) {
        $routes->get('/', '\App\Domains\Website\WebOpd::index');
        $routes->get('export_pdf', '\App\Domains\Website\WebOpd::export_pdf');
        
        // Mutation Routes (Admin & Super Admin)
        $routes->group('', ['filter' => 'role:admin,super_admin'], function ($routes) {
            $routes->get('edit/(:num)', '\App\Domains\Website\WebOpd::edit/$1');
            $routes->post('update/(:num)', '\App\Domains\Website\WebOpd::update/$1');
        });
    });

    // Log Pendampingan (Super Admin Only)
    $routes->group('assistance', ['filter' => 'role:super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Assistance\Assistance::index');
        $routes->get('export_pdf', '\App\Domains\Assistance\Assistance::export_pdf');
        $routes->get('create', '\App\Domains\Assistance\Assistance::create');
        $routes->post('store', '\App\Domains\Assistance\Assistance::store');
        $routes->get('edit/(:num)', '\App\Domains\Assistance\Assistance::edit/$1');
        $routes->post('update/(:num)', '\App\Domains\Assistance\Assistance::update/$1');
        $routes->get('delete/(:num)', '\App\Domains\Assistance\Assistance::delete/$1');
    });

    // Utilitas User & BSrE
    $routes->get('user/change_password', '\App\Domains\Auth\User::changePassword');
    $routes->post('user/update_password', '\App\Domains\Auth\User::updatePassword');
    $routes->post('user/check_email', '\App\Domains\Auth\User::checkEmailAvailability');
    $routes->post('user/check_niknip', '\App\Domains\Auth\User::check_niknip');
    
    // User Management (Super Admin Only)
    $routes->group('auth/users', ['filter' => 'role:super_admin'], function ($routes) {
        $routes->get('/', '\App\Domains\Auth\UserManagement::index');
        $routes->get('add', '\App\Domains\Auth\UserManagement::add');
        $routes->post('store', '\App\Domains\Auth\UserManagement::store');
        $routes->get('edit/(:num)', '\App\Domains\Auth\UserManagement::edit/$1');
        $routes->post('update/(:num)', '\App\Domains\Auth\UserManagement::update/$1');
        $routes->post('delete/(:num)', '\App\Domains\Auth\UserManagement::delete/$1');
    });
    
    $routes->get('bsre/check-status', '\App\Domains\Email\Bsre::checkStatus');
    
    $routes->group('bsre', ['filter' => 'role:admin,super_admin'], function ($routes) {
        $routes->get('sync-all', '\App\Domains\Email\Bsre::syncAllStatus');
        $routes->get('sync-status', '\App\Domains\Email\Bsre::syncStatus');
        $routes->post('sync-status', '\App\Domains\Email\Bsre::syncStatus');
    });
});