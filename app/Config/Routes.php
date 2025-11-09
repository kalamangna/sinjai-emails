<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Email::index');
$routes->get('email', 'Email::index');
$routes->get('email/sync', 'Email::sync');
$routes->get('email/detail/(:any)', 'Email::detail/$1');
$routes->get('email/unit_kerja/(:num)', 'Email::unit_kerja_detail/$1');
$routes->get('email/batch', 'Email::batch');
$routes->post('email/batch_create', 'Email::batch_create');
$routes->get('email/export_csv', 'Email::export_csv');
$routes->post('user/check_email', 'User::checkEmailAvailability');
$routes->post('email/update_unit_kerja/(:segment)', 'Email::update_unit_kerja/$1');
