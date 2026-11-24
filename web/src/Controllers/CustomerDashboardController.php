<?php

declare(strict_types=1);

namespace Vwork\Web\Controllers;

use Vwork\Shared\Collections\IRegistry;
use Vwork\Web\Http\Request\Request;
use Vwork\Web\Http\Response\Response;

/**
 * Handles Customer Dashboard flows.
 *
 * @phpstan-import-type StringMap from IController
 * @phpstan-type InvoiceItem array{
 *     description: string,
 *     type: 'labor'|'part'|'other',
 *     quantity: int|float,
 *     unit_price: int,
 *     total: int
 * }
 * @phpstan-type Invoice array{
 *     id: int,
 *     invoice_number: string,
 *     date: string,
 *     date_iso: string,
 *     due_date: string,
 *     vehicle_model: string,
 *     license_plate: string,
 *     vin: string,
 *     trim_badge: string,
 *     description: string,
 *     amount: int,
 *     status: 'Paid'|'Unpaid'|'Partially Paid'|'Overdue',
 *     customer_name: string,
 *     customer_email: string,
 *     customer_phone: string,
 *     items: list<InvoiceItem>,
 *     subtotal: int,
 *     discount: int,
 *     tax_rate: float,
 *     tax_amount: int,
 *     grand_total: int,
 *     payment_method?: string,
 *     payment_reference?: string,
 *     notes?: string
 * }
 */
final readonly class CustomerDashboardController extends ControllerBase
{
    public function __construct(IRegistry $registry)
    {
        parent::__construct($registry);
    }

    /**
     * Renders Customer Overview Dashboard (GET /customer/overview or GET /customer/dashboard)
     *
     * @param StringMap $attr
     */
    #[ControllerAction]
    public function overviewPage(Request $req, array $attr): Response
    {
        $vehicles = $this->getMockVehicles();
        $html = $this->view($req, 'customer/dashboard', [
            'title'           => 'Overview - AutoCare Customer Portal',
            'active_route'    => 'overview',
            'user'            => null,
            'stats'           => [
                'active_vehicles'  => count($vehicles),
                'vehicles_in_shop' => 1,
            ],
            'current_service' => [
                'status'             => 'In Progress',
                'vehicle_name'       => '2021 Land Cruiser Prado',
                'license_plate'      => 'WP CAB-7892',
                'completion_percent' => 65,
                'milestones'         => [
                    ['label' => 'Vehicle Received', 'status' => 'done'],
                    ['label' => 'Diagnostic Scan', 'status' => 'done'],
                    ['label' => 'Parts Replacement', 'status' => 'active'],
                    ['label' => 'Final QA & Wash', 'status' => 'pending'],
                ],
            ],
            'recent_activity' => [
                ['title' => 'Diagnostic report completed for WP CAB-7892', 'time_label' => '10:45 AM'],
                ['title' => 'Scheduled inspection booked for WP CBA-4321', 'time_label' => 'Yesterday'],
            ],
        ], 'layouts/customer');

        return Response::single()->html($html);
    }

    /**
     * Renders Customer Vehicles Page (GET /customer/vehicles)
     *
     * @param StringMap $attr
     */
    #[ControllerAction]
    public function vehiclesPage(Request $req, array $attr): Response
    {
        $vehicles = $this->getMockVehicles();
        $html = $this->view($req, 'customer/vehicles', [
            'title'        => 'My Vehicles - AutoCare Customer Portal',
            'active_route' => 'vehicles',
            'user'         => null,
            'vehicles'     => $vehicles,
        ], 'layouts/customer');

        return Response::single()->html($html);
    }

    /**
     * Renders Customer Appointments Page (GET /customer/appointments)
     *
     * @param StringMap $attr
     */
    #[ControllerAction]
    public function appointmentsPage(Request $req, array $attr): Response
    {
        $appointments = $this->getMockAppointments();
        $scheduledToday = 0;
        $upcomingCount = count(array_filter(
            $appointments,
            static fn (array $a): bool => in_array($a['status'], ['Upcoming', 'Confirmed'], true)
        ));
        $servicedThisYear = count(array_filter(
            $appointments,
            static fn (array $a): bool => $a['status'] === 'Completed'
        ));

        $html = $this->view($req, 'customer/appointments', [
            'title'        => 'Appointments - AutoCare Customer Portal',
            'active_route' => 'appointments',
            'user'         => null,
            'stats'        => [
                'scheduled_today'    => $scheduledToday,
                'upcoming_bookings'  => $upcomingCount,
                'serviced_this_year' => $servicedThisYear,
            ],
            'appointments' => $appointments,
        ], 'layouts/customer');

        return Response::single()->html($html);
    }

    /**
     * Renders the customer appointment booking page (GET /customer/appointments/book).
     *
     * @param StringMap $attr
     */
    #[ControllerAction]
    public function appointmentBookingPage(Request $req, array $attr): Response
    {
        $vehicles = array_values(array_filter(
            $this->getMockVehicles(),
            static fn (array $vehicle): bool => $vehicle['status'] === 'idle'
        ));
        $html = $this->view($req, 'customer/booking', [
            'title'        => 'Book Appointment - AutoCare Customer Portal',
            'active_route' => 'appointments',
            'user'         => null,
            'vehicles'     => $vehicles,
        ], 'layouts/customer');

        return Response::single()->html($html);
    }

    /**
     * Renders customer repair history (GET /customer/history).
     *
     * @param StringMap $attr
     */
    #[ControllerAction]
    public function historyPage(Request $req, array $attr): Response
    {
        $repairs = $this->getMockRepairs();
        $html = $this->view($req, 'customer/history', [
            'title'        => 'Repair History - AutoCare Customer Portal',
            'active_route' => 'history',
            'user'         => null,
            'repairs'      => $repairs,
        ], 'layouts/customer');

        return Response::single()->html($html);
    }

    /**
     * Renders customer profile details (GET /customer/profile).
     *
     * @param StringMap $attr
     */
    #[ControllerAction]
    public function profilePage(Request $req, array $attr): Response
    {
        $profile = $this->getMockProfile();
        $html = $this->view($req, 'customer/profile', [
            'title'        => 'Profile Details - AutoCare Customer Portal',
            'active_route' => 'profile',
            'user'         => $profile,
            'profile'      => $profile,
        ], 'layouts/customer');

        return Response::single()->html($html);
    }

    /**
     * @return array{
     *     first_name: string,
     *     last_name: string,
     *     full_name: string,
     *     email: string,
     *     phone: string,
     *     initials: string,
     *     badge: string
     * }
     */
    private function getMockProfile(): array
    {
        return [
            'first_name' => 'Kasun',
            'last_name'  => 'Perera',
            'full_name'  => 'Kasun Perera',
            'email'      => 'kasun.perera@example.com',
            'phone'      => '+94 771 123 4567',
            'initials'   => 'KP',
            'badge'      => 'Verified',
        ];
    }

    /**
     * @return list<array{
     *     id: int,
     *     date: string,
     *     time: string,
     *     vehicle_model: string,
     *     license_plate: string,
     *     service_type: string,
     *     service_color: string,
     *     status: 'Upcoming'|'Completed'|'Confirmed'|'Cancelled'
     * }>
     */
    private function getMockAppointments(): array
    {
        return [
            [
                'id'            => 1,
                'date'          => 'Oct 12, 2026',
                'time'          => '09:00 AM',
                'vehicle_model' => '2018 Toyota Axio',
                'license_plate' => 'WP CBA-4321',
                'service_type'  => 'Full Service',
                'service_color' => '#f97316',
                'status'        => 'Upcoming',
            ],
            [
                'id'            => 2,
                'date'          => 'Sep 01, 2026',
                'time'          => '10:30 AM',
                'vehicle_model' => '2015 Honda Fit',
                'license_plate' => 'NW WP-9876',
                'service_type'  => 'Brake Replacement',
                'service_color' => '#3b82f6',
                'status'        => 'Completed',
            ],
        ];
    }

    /**
     * @return list<array{
     *     id: int,
     *     date: string,
     *     vehicle_model: string,
     *     license_plate: string,
     *     service: string,
     *     amount: int,
     *     mechanic: string,
     *     details: string
     * }>
     */
    private function getMockRepairs(): array
    {
        return [
            [
                'id'            => 1,
                'date'          => 'Sep 01, 2026',
                'vehicle_model' => '2015 Honda Fit',
                'license_plate' => 'NW WP-9876',
                'service'       => 'Brake Replacement',
                'amount'        => 38500,
                'mechanic'      => 'Dishan Karunaratne',
                'details'       => 'Front brake pads and discs replaced; road test completed.',
            ],
            [
                'id'            => 2,
                'date'          => 'Jun 14, 2026',
                'vehicle_model' => '2018 Toyota Axio',
                'license_plate' => 'WP CBA-4321',
                'service'       => 'Oil and Filter Service',
                'amount'        => 24800,
                'mechanic'      => 'Sahan Nimesh',
                'details'       => 'Engine oil and oil filter replaced; multi-point inspection completed.',
            ],
            [
                'id'            => 3,
                'date'          => 'Feb 22, 2026',
                'vehicle_model' => '2021 Land Cruiser Prado',
                'license_plate' => 'WP CAB-7892',
                'service'       => 'Transmission Service',
                'amount'        => 96400,
                'mechanic'      => 'Manjula Kulatunga',
                'details'       => 'Transmission fluid and differential oil serviced.',
            ],
            [
                'id'            => 4,
                'date'          => 'Nov 08, 2025',
                'vehicle_model' => '2018 Toyota Axio',
                'license_plate' => 'WP CBA-4321',
                'service'       => 'Engine Air Filter Replacement',
                'amount'        => 12800,
                'mechanic'      => 'Sahan Nimesh',
                'details'       => 'Engine air filter replaced and intake system checked.',
            ],
            [
                'id'            => 5,
                'date'          => 'Aug 19, 2025',
                'vehicle_model' => '2020 BMW 330e M-Sport',
                'license_plate' => 'WP CAD-1029',
                'service'       => 'Hybrid System Inspection',
                'amount'        => 54200,
                'mechanic'      => 'Dilan Perera',
                'details'       => 'Hybrid battery health and high-voltage system inspected.',
            ],
            [
                'id'            => 6,
                'date'          => 'May 03, 2025',
                'vehicle_model' => '2015 Honda Fit',
                'license_plate' => 'NW WP-9876',
                'service'       => 'Tire Replacement',
                'amount'        => 78600,
                'mechanic'      => 'Dishan Karunaratne',
                'details'       => 'Four tires replaced and wheel alignment checked.',
            ],
            [
                'id'            => 7,
                'date'          => 'Jan 17, 2025',
                'vehicle_model' => '2021 Land Cruiser Prado',
                'license_plate' => 'WP CAB-7892',
                'service'       => 'Full Service',
                'amount'        => 112500,
                'mechanic'      => 'Manjula Kulatunga',
                'details'       => 'Scheduled full service including fluids, filters, and safety inspection.',
            ],
            [
                'id'            => 8,
                'date'          => 'Oct 11, 2024',
                'vehicle_model' => '2018 Toyota Axio',
                'license_plate' => 'WP CBA-4321',
                'service'       => 'Battery Replacement',
                'amount'        => 46800,
                'mechanic'      => 'Sahan Nimesh',
                'details'       => '12V battery replaced and charging system tested.',
            ],
        ];
    }

    /**
     * @return list<array{
     *     id: int,
     *     license_plate: string,
     *     model: string,
     *     mileage: string,
     *     vin: string,
     *     trim_badge: string,
     *     image: string,
     *     color_name: string,
     *     color_hex: string,
     *     status: 'active_service'|'idle',
     *     bay_status_label?: string
     * }>
     */
    private function getMockVehicles(): array
    {
        return [
            [
                'id'            => 1,
                'license_plate' => 'WP CBA-4321',
                'model'         => '2018 Toyota Axio',
                'mileage'       => '68,500 km',
                'vin'           => 'JTDBR32E720123456',
                'trim_badge'    => '1.5L Hybrid e-CVT',
                'image'         => 'https://images.unsplash.com/photo-1590362891988-3932824e438e?auto=format&fit=crop&w=800&q=80',
                'color_name'    => 'Pearl White',
                'color_hex'     => '#ffffff',
                'status'        => 'idle',
            ],
            [
                'id'               => 2,
                'license_plate'    => 'WP CAB-7892',
                'model'            => '2021 Land Cruiser Prado',
                'mileage'          => '42,300 km',
                'vin'              => 'JTEBR3FJ10K123456',
                'trim_badge'       => '2.8L D-4D 4WD',
                'image'            => 'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=800&q=80',
                'color_name'       => 'Attitude Black',
                'color_hex'        => '#0f172a',
                'status'           => 'active_service',
                'bay_status_label' => 'View Progress',
            ],
            [
                'id'            => 3,
                'license_plate' => 'WP CAD-1029',
                'model'         => '2020 BMW 330e M-Sport',
                'mileage'       => '31,200 km',
                'vin'           => 'WBA5P9C05LF123456',
                'trim_badge'    => '2.0L PHEV 292hp',
                'image'         => 'https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=800&q=80',
                'color_name'    => 'Mineral Grey',
                'color_hex'     => '#64748b',
                'status'        => 'idle',
            ],
        ];
    }

    /**
     * Renders Customer Invoices Listing Page (GET /customer/invoices)
     *
     * @param StringMap $attr
     */
    #[ControllerAction]
    public function invoicesPage(Request $req, array $attr): Response
    {
        $invoices = $this->getMockInvoices();
        $vehicles = $this->getMockVehicles();

        $vehicleList = array_map(
            static fn (array $v): array => [
                'id'            => $v['id'],
                'license_plate' => $v['license_plate'],
                'model'         => $v['model'],
            ],
            $vehicles
        );

        $paidCount = count(array_filter($invoices, static fn (array $i): bool => $i['status'] === 'Paid'));
        $outstandingCount = count(array_filter(
            $invoices,
            static fn (array $i): bool => in_array($i['status'], ['Unpaid', 'Partially Paid', 'Overdue'], true)
        ));

        $html = $this->view($req, 'customer/invoices', [
            'title'        => 'Invoices - AutoCare Customer Portal',
            'active_route' => 'invoices',
            'user'         => null,
            'stats'        => [
                'total_invoices'    => count($invoices),
                'total_paid'        => $paidCount,
                'total_outstanding' => $outstandingCount,
            ],
            'invoices' => $invoices,
            'vehicles' => $vehicleList,
        ], 'layouts/customer');

        return Response::single()->html($html);
    }

    /**
     * Renders an individual Invoice Detail page (GET /customer/invoices/{id})
     *
     * @param StringMap $attr
     */
    #[ControllerAction]
    public function invoiceDetailPage(Request $req, array $attr): Response
    {
        $idValue = $attr['id'] ?? null;
        $id      = is_string($idValue) || is_int($idValue) ? (int) $idValue : 0;
        $invoice = $this->getMockInvoiceById($id);

        if ($invoice === null) {
            $html = $this->view($req, 'customer/invoices', [
                'title'        => 'Invoice Not Found - AutoCare Customer Portal',
                'active_route' => 'invoices',
                'user'         => null,
                'stats'        => ['total_invoices' => 0, 'total_paid' => 0, 'total_outstanding' => 0],
                'invoices'     => [],
                'vehicles'     => [],
            ], 'layouts/customer');

            return Response::single()->html($html);
        }

        $html = $this->view($req, 'customer/invoice-detail', [
            'title'        => 'Invoice ' . $invoice['invoice_number'] . ' - AutoCare Customer Portal',
            'active_route' => 'invoices',
            'user'         => null,
            'invoice'      => $invoice,
        ], 'layouts/customer');

        return Response::single()->html($html);
    }

    /**
     * Renders a standalone printable Invoice page (GET /customer/invoices/{id}/print)
     * Uses the blank layout — no portal sidebar/nav.
     *
     * @param StringMap $attr
     */
    #[ControllerAction]
    public function invoicePrintPage(Request $req, array $attr): Response
    {
        $rawId   = $attr['id'] ?? null;
        $id      = is_int($rawId) ? $rawId : (is_string($rawId) && ctype_digit($rawId) ? (int) $rawId : 0);
        $invoice = $this->getMockInvoiceById($id);

        if ($invoice === null) {
            return Response::single()->html('<p>Invoice not found.</p>');
        }

        $html = $this->view($req, 'customer/invoice-print', [
            'title'   => 'Invoice ' . $invoice['invoice_number'],
            'invoice' => $invoice,
        ], 'layouts/blank');

        return Response::single()->html($html);
    }

    /**
     * @return list<array{
     *     id: int,
     *     invoice_number: string,
     *     date: string,
     *     date_iso: string,
     *     due_date: string,
     *     vehicle_model: string,
     *     license_plate: string,
     *     vin: string,
     *     trim_badge: string,
     *     description: string,
     *     amount: int,
     *     status: 'Paid'|'Unpaid'|'Partially Paid'|'Overdue',
     *     customer_name: string,
     *     customer_email: string,
     *     customer_phone: string,
     *     items: list<array{
     *         description: string,
     *         type: 'labor'|'part'|'other',
     *         quantity: int|float,
     *         unit_price: int,
     *         total: int
     *     }>,
     *     subtotal: int,
     *     discount: int,
     *     tax_rate: float,
     *     tax_amount: int,
     *     grand_total: int,
     *     payment_method?: string,
     *     payment_reference?: string,
     *     notes?: string
     * }>
     */
    private function getMockInvoices(): array
    {
        return [
            [
                'id'             => 1,
                'invoice_number' => 'INV-2026-0041',
                'date'           => 'Oct 12, 2026',
                'date_iso'       => '2026-10-12',
                'due_date'       => 'Oct 26, 2026',
                'vehicle_model'  => '2021 Land Cruiser Prado',
                'license_plate'  => 'WP CAB-7892',
                'vin'            => 'JTEBR3FJ10K123456',
                'trim_badge'     => '2.8L D-4D 4WD',
                'description'    => 'Full service — engine oil, filters, brakes',
                'amount'         => 112500,
                'status'         => 'Unpaid',
                'customer_name'  => 'Sachin Perera',
                'customer_email' => 'sachin@example.lk',
                'customer_phone' => '+94 71 234 5678',
                'items'          => [
                    ['description' => 'Synthetic Engine Oil (6L)', 'type' => 'part',  'quantity' => 1, 'unit_price' => 18500, 'total' => 18500],
                    ['description' => 'Engine Oil Filter',         'type' => 'part',  'quantity' => 1, 'unit_price' => 2800,  'total' => 2800],
                    ['description' => 'Air Filter Replacement',    'type' => 'part',  'quantity' => 1, 'unit_price' => 4200,  'total' => 4200],
                    ['description' => 'Brake Pad Set — Front',     'type' => 'part',  'quantity' => 1, 'unit_price' => 22400, 'total' => 22400],
                    ['description' => 'Brake Inspection & Fit',    'type' => 'labor', 'quantity' => 2, 'unit_price' => 8500,  'total' => 17000],
                    ['description' => 'Full Service Labour',        'type' => 'labor', 'quantity' => 3, 'unit_price' => 9500,  'total' => 28500],
                    ['description' => 'Vehicle Wash & Valet',      'type' => 'other', 'quantity' => 1, 'unit_price' => 3200,  'total' => 3200],
                ],
                'subtotal'    => 96600,
                'discount'    => 0,
                'tax_rate'    => 0.165,
                'tax_amount'  => 15939,
                'grand_total' => 112539,
                'notes'       => 'Next service due at 55,000 km or 6 months, whichever comes first.',
            ],
            [
                'id'             => 2,
                'invoice_number' => 'INV-2026-0038',
                'date'           => 'Sep 01, 2026',
                'date_iso'       => '2026-09-01',
                'due_date'       => 'Sep 15, 2026',
                'vehicle_model'  => '2018 Toyota Axio',
                'license_plate'  => 'WP CBA-4321',
                'vin'            => 'JTDBR32E720123456',
                'trim_badge'     => '1.5L Hybrid e-CVT',
                'description'    => 'Brake replacement — full set',
                'amount'         => 67200,
                'status'         => 'Paid',
                'customer_name'  => 'Sachin Perera',
                'customer_email' => 'sachin@example.lk',
                'customer_phone' => '+94 71 234 5678',
                'items'          => [
                    ['description' => 'Brake Pad Set — Front',   'type' => 'part',  'quantity' => 1, 'unit_price' => 16800, 'total' => 16800],
                    ['description' => 'Brake Pad Set — Rear',    'type' => 'part',  'quantity' => 1, 'unit_price' => 14200, 'total' => 14200],
                    ['description' => 'Brake Disc — Front (×2)', 'type' => 'part',  'quantity' => 2, 'unit_price' => 9500,  'total' => 19000],
                    ['description' => 'Brake Replacement Labour', 'type' => 'labor', 'quantity' => 2, 'unit_price' => 5700,  'total' => 11400],
                ],
                'subtotal'          => 61400,
                'discount'          => 2000,
                'tax_rate'          => 0.165,
                'tax_amount'        => 9801,
                'grand_total'       => 69201,
                'payment_method'    => 'Bank Transfer',
                'payment_reference' => 'TXN-20260901-8821',
                'notes'             => 'Loyalty discount applied — valued customer.',
            ],
            [
                'id'             => 3,
                'invoice_number' => 'INV-2026-0031',
                'date'           => 'Jul 14, 2026',
                'date_iso'       => '2026-07-14',
                'due_date'       => 'Jul 28, 2026',
                'vehicle_model'  => '2020 BMW 330e M-Sport',
                'license_plate'  => 'WP CAD-1029',
                'vin'            => 'WBA5P9C05LF123456',
                'trim_badge'     => '2.0L PHEV 292hp',
                'description'    => 'Diagnostic scan and suspension check',
                'amount'         => 38400,
                'status'         => 'Paid',
                'customer_name'  => 'Sachin Perera',
                'customer_email' => 'sachin@example.lk',
                'customer_phone' => '+94 71 234 5678',
                'items'          => [
                    ['description' => 'Full Vehicle Diagnostic Scan',    'type' => 'labor', 'quantity' => 1, 'unit_price' => 9500,  'total' => 9500],
                    ['description' => 'Suspension & Steering Inspection', 'type' => 'labor', 'quantity' => 2, 'unit_price' => 7500,  'total' => 15000],
                    ['description' => 'Wheel Alignment (4-wheel)',        'type' => 'labor', 'quantity' => 1, 'unit_price' => 6500,  'total' => 6500],
                    ['description' => 'Diagnostic Report Preparation',    'type' => 'other', 'quantity' => 1, 'unit_price' => 2000,  'total' => 2000],
                ],
                'subtotal'          => 33000,
                'discount'          => 0,
                'tax_rate'          => 0.165,
                'tax_amount'        => 5445,
                'grand_total'       => 38445,
                'payment_method'    => 'Cash',
                'payment_reference' => '',
            ],
            [
                'id'             => 4,
                'invoice_number' => 'INV-2025-0092',
                'date'           => 'Nov 08, 2025',
                'date_iso'       => '2025-11-08',
                'due_date'       => 'Nov 22, 2025',
                'vehicle_model'  => '2018 Toyota Axio',
                'license_plate'  => 'WP CBA-4321',
                'vin'            => 'JTDBR32E720123456',
                'trim_badge'     => '1.5L Hybrid e-CVT',
                'description'    => 'Engine air filter and fuel system service',
                'amount'         => 24600,
                'status'         => 'Paid',
                'customer_name'  => 'Sachin Perera',
                'customer_email' => 'sachin@example.lk',
                'customer_phone' => '+94 71 234 5678',
                'items'          => [
                    ['description' => 'Engine Air Filter',         'type' => 'part',  'quantity' => 1, 'unit_price' => 4200,  'total' => 4200],
                    ['description' => 'Cabin Air Filter',          'type' => 'part',  'quantity' => 1, 'unit_price' => 3100,  'total' => 3100],
                    ['description' => 'Fuel System Cleaner',       'type' => 'part',  'quantity' => 1, 'unit_price' => 2800,  'total' => 2800],
                    ['description' => 'Filter Replacement Labour',  'type' => 'labor', 'quantity' => 1, 'unit_price' => 5500,  'total' => 5500],
                    ['description' => 'Fuel Injector Service',     'type' => 'labor', 'quantity' => 1, 'unit_price' => 5500,  'total' => 5500],
                ],
                'subtotal'          => 21100,
                'discount'          => 0,
                'tax_rate'          => 0.165,
                'tax_amount'        => 3482,
                'grand_total'       => 24582,
                'payment_method'    => 'Cash',
                'payment_reference' => '',
            ],
            [
                'id'             => 5,
                'invoice_number' => 'INV-2025-0074',
                'date'           => 'Aug 19, 2025',
                'date_iso'       => '2025-08-19',
                'due_date'       => 'Sep 02, 2025',
                'vehicle_model'  => '2020 BMW 330e M-Sport',
                'license_plate'  => 'WP CAD-1029',
                'vin'            => 'WBA5P9C05LF123456',
                'trim_badge'     => '2.0L PHEV 292hp',
                'description'    => 'Hybrid system inspection and high-voltage check',
                'amount'         => 54200,
                'status'         => 'Partially Paid',
                'customer_name'  => 'Sachin Perera',
                'customer_email' => 'sachin@example.lk',
                'customer_phone' => '+94 71 234 5678',
                'items'          => [
                    ['description' => 'Hybrid Battery Health Scan',     'type' => 'labor', 'quantity' => 1, 'unit_price' => 12500, 'total' => 12500],
                    ['description' => 'High-Voltage System Inspection', 'type' => 'labor', 'quantity' => 2, 'unit_price' => 11000, 'total' => 22000],
                    ['description' => 'Coolant Flush — Hybrid Circuit', 'type' => 'labor', 'quantity' => 1, 'unit_price' => 7500,  'total' => 7500],
                    ['description' => 'Hybrid Coolant Fluid',           'type' => 'part',  'quantity' => 2, 'unit_price' => 2800,  'total' => 5600],
                ],
                'subtotal'    => 47600,
                'discount'    => 0,
                'tax_rate'    => 0.165,
                'tax_amount'  => 7854,
                'grand_total' => 55454,
                'notes'       => 'Partial payment of Rs. 30,000 received. Balance of Rs. 25,454 outstanding.',
            ],
            [
                'id'             => 6,
                'invoice_number' => 'INV-2025-0051',
                'date'           => 'May 03, 2025',
                'date_iso'       => '2025-05-03',
                'due_date'       => 'May 17, 2025',
                'vehicle_model'  => '2018 Toyota Axio',
                'license_plate'  => 'WP CBA-4321',
                'vin'            => 'JTDBR32E720123456',
                'trim_badge'     => '1.5L Hybrid e-CVT',
                'description'    => 'Tyre replacement — all four + alignment',
                'amount'         => 78600,
                'status'         => 'Overdue',
                'customer_name'  => 'Sachin Perera',
                'customer_email' => 'sachin@example.lk',
                'customer_phone' => '+94 71 234 5678',
                'items'          => [
                    ['description' => 'Bridgestone Ecopia 185/65R15 (×4)', 'type' => 'part',  'quantity' => 4, 'unit_price' => 14200, 'total' => 56800],
                    ['description' => 'Tyre Fitting & Balancing (×4)',      'type' => 'labor', 'quantity' => 4, 'unit_price' => 2200,  'total' => 8800],
                    ['description' => '4-Wheel Alignment',                  'type' => 'labor', 'quantity' => 1, 'unit_price' => 6500,  'total' => 6500],
                ],
                'subtotal'    => 72100,
                'discount'    => 5000,
                'tax_rate'    => 0.165,
                'tax_amount'  => 11067,
                'grand_total' => 78167,
                'notes'       => 'Invoice is overdue. Please contact us at +94 11 234 5678 to arrange payment.',
            ],
        ];
    }

    /**
     * @return array{
     *     id: int,
     *     invoice_number: string,
     *     date: string,
     *     date_iso: string,
     *     due_date: string,
     *     vehicle_model: string,
     *     license_plate: string,
     *     vin: string,
     *     trim_badge: string,
     *     description: string,
     *     amount: int,
     *     status: 'Paid'|'Unpaid'|'Partially Paid'|'Overdue',
     *     customer_name: string,
     *     customer_email: string,
     *     customer_phone: string,
     *     items: list<array{
     *         description: string,
     *         type: 'labor'|'part'|'other',
     *         quantity: int|float,
     *         unit_price: int,
     *         total: int
     *     }>,
     *     subtotal: int,
     *     discount: int,
     *     tax_rate: float,
     *     tax_amount: int,
     *     grand_total: int,
     *     payment_method?: string,
     *     payment_reference?: string,
     *     notes?: string
     * }|null
     */
    private function getMockInvoiceById(int $id): array|null
    {
        $invoices = $this->getMockInvoices();
        foreach ($invoices as $invoice) {
            if ($invoice['id'] === $id) {
                return $invoice;
            }
        }

        return null;
    }
}
