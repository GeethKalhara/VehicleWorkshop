<?php

declare(strict_types=1);

use Vwork\Web\Controllers\CustomerAuthController;
use Vwork\Web\Controllers\CustomerDashboardController;
use Vwork\Web\Controllers\IController;
use Vwork\Web\Http\HttpMethods;
use Vwork\Web\Middleware\IMiddleware;

/**
 * @var list<array{
 *  method: HttpMethods,
 *  path: string,
 *  controller: array{
 *    class: class-string<IController>,
 *    method: string
 *  },
 *  middleware: list<class-string<IMiddleware>>,
 *  context: array<string, mixed>
 * }>
 */
return [
    [
        'method'     => HttpMethods::GET,
        'path'       => '/customer/login',
        'controller' => ['class' => CustomerAuthController::class, 'method' => 'loginPage'],
        'middleware' => [],
        'context'    => [],
    ],

    [
        'method'     => HttpMethods::GET,
        'path'       => '/customer/register',
        'controller' => ['class' => CustomerAuthController::class, 'method' => 'registerPage'],
        'middleware' => [],
        'context'    => [],
    ],

    [
        'method'     => HttpMethods::GET,
        'path'       => '/customer/overview',
        'controller' => ['class' => CustomerDashboardController::class, 'method' => 'overviewPage'],
        'middleware' => [],
        'context'    => [],
    ],

    [
        'method'     => HttpMethods::GET,
        'path'       => '/customer/dashboard',
        'controller' => ['class' => CustomerDashboardController::class, 'method' => 'overviewPage'],
        'middleware' => [],
        'context'    => [],
    ],

    [
        'method'     => HttpMethods::GET,
        'path'       => '/customer/vehicles',
        'controller' => ['class' => CustomerDashboardController::class, 'method' => 'vehiclesPage'],
        'middleware' => [],
        'context'    => [],
    ],

    [
        'method'     => HttpMethods::GET,
        'path'       => '/customer/appointments',
        'controller' => ['class' => CustomerDashboardController::class, 'method' => 'appointmentsPage'],
        'middleware' => [],
        'context'    => [],
    ],

    [
        'method'     => HttpMethods::GET,
        'path'       => '/customer/appointments/book',
        'controller' => ['class' => CustomerDashboardController::class, 'method' => 'appointmentBookingPage'],
        'middleware' => [],
        'context'    => [],
    ],

    [
        'method'     => HttpMethods::GET,
        'path'       => '/customer/history',
        'controller' => ['class' => CustomerDashboardController::class, 'method' => 'historyPage'],
        'middleware' => [],
        'context'    => [],
    ],

    [
        'method'     => HttpMethods::GET,
        'path'       => '/customer/invoices',
        'controller' => ['class' => CustomerDashboardController::class, 'method' => 'invoicesPage'],
        'middleware' => [],
        'context'    => [],
    ],

    [
        'method'     => HttpMethods::GET,
        'path'       => '/customer/invoices/{id}/print',
        'controller' => ['class' => CustomerDashboardController::class, 'method' => 'invoicePrintPage'],
        'middleware' => [],
        'context'    => [],
    ],

    [
        'method'     => HttpMethods::GET,
        'path'       => '/customer/invoices/{id}',
        'controller' => ['class' => CustomerDashboardController::class, 'method' => 'invoiceDetailPage'],
        'middleware' => [],
        'context'    => [],
    ],

    [
        'method'     => HttpMethods::GET,
        'path'       => '/customer/profile',
        'controller' => ['class' => CustomerDashboardController::class, 'method' => 'profilePage'],
        'middleware' => [],
        'context'    => [],
    ],

    [
        'method' => HttpMethods::POST,
        'path' => '/customer/logout',
        'controller' => ['class' => CustomerAuthController::class, 'method' => 'logout'],
        'middleware' => [],
        'context'    => [],
    ]
];
