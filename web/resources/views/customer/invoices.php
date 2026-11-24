<?php
/**
 * Customer Invoices Page View
 *
 * @var string $csrf
 * @var array<string, string> $assets
 * @var array{first_name?: string, last_name?: string, mobile?: string}|null $user
 * @var array{
 *     total_invoices?: int,
 *     total_paid?: int,
 *     total_outstanding?: int
 * }|null $stats
 * @var list<array{
 *     id: int|string,
 *     invoice_number: string,
 *     date: string,
 *     date_iso: string,
 *     vehicle_model: string,
 *     license_plate: string,
 *     description: string,
 *     amount: int,
 *     status: 'Paid'|'Unpaid'|'Partially Paid'|'Overdue'
 * }> $invoices
 * @var list<array{
 *     id: int|string,
 *     license_plate: string,
 *     model: string
 * }> $vehicles
 */

$escape = static fn (string|int|float|null $value): string => htmlspecialchars(
    (string) ($value ?? ''),
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8',
);

$stats = $stats ?? [];
$total_invoices      = $stats['total_invoices'] ?? count($invoices);
$total_paid          = $stats['total_paid'] ?? count(array_filter($invoices, static fn (array $inv): bool => $inv['status'] === 'Paid'));
$total_outstanding   = $stats['total_outstanding'] ?? count(array_filter(
    $invoices,
    static fn (array $inv): bool => in_array($inv['status'], ['Unpaid', 'Partially Paid', 'Overdue'], true)
));

$status_class_map = [
    'Paid'          => 'inv-badge-paid',
    'Unpaid'        => 'inv-badge-unpaid',
    'Partially Paid' => 'inv-badge-partial',
    'Overdue'       => 'inv-badge-overdue',
];
?>

<div class="dashboard-wrapper invoices-page">

    <!-- ======================================================== -->
    <!-- 1. PAGE HEADER                                           -->
    <!-- ======================================================== -->
    <section class="invoices-page-header">
        <div class="invoices-title-group">
            <h1 class="dashboard-heading">Invoices</h1>
            <p class="dashboard-subheading">
                View, download, and track payment status for all your workshop invoices.
            </p>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- 2. SUMMARY STAT CARDS                                    -->
    <!-- ======================================================== -->
    <section class="stats-grid invoices-stats-grid" aria-label="Invoice Summary">

        <!-- Total Invoices -->
        <article class="invoice-stat">
            <span class="invoice-stat-label">TOTAL INVOICES</span>
            <span class="invoice-stat-value"><?= $total_invoices ?></span>
        </article>

        <!-- Paid -->
        <article class="invoice-stat">
            <span class="invoice-stat-label">PAID</span>
            <span class="invoice-stat-value"><?= $total_paid ?></span>
        </article>

        <!-- Outstanding -->
        <article class="invoice-stat">
            <span class="invoice-stat-label">OUTSTANDING</span>
            <span class="invoice-stat-value"><?= $total_outstanding ?></span>
        </article>

    </section>

    <!-- ======================================================== -->
    <!-- 3. FILTER BAR                                            -->
    <!-- ======================================================== -->
    <section class="invoices-filter-bar" aria-label="Invoice Filters" id="invoice-filters">
        <div class="inv-filter-row">

            <!-- Vehicle Filter -->
            <div class="inv-filter-group">
                <label class="inv-filter-label" for="inv-vehicle-filter">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 17a2 2 0 100-4 2 2 0 000 4zm8 0a2 2 0 100-4 2 2 0 000 4z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l1.5-5.5a2 2 0 011.92-1.5h7.16a2 2 0 011.92 1.5L19 13M5 13v4a1 1 0 001 1h1m10 0h1a1 1 0 001-1v-4M5 13h14" />
                    </svg>
                    Vehicle
                </label>
                <div class="inv-select-wrapper">
                    <select id="inv-vehicle-filter" class="inv-select" data-filter="vehicle" aria-label="Filter by vehicle">
                        <option value="">All Vehicles</option>
                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?= $escape($v['license_plate']) ?>">
                                <?= $escape($v['model']) ?> — <?= $escape($v['license_plate']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <svg class="inv-select-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <!-- Date From -->
            <div class="inv-filter-group">
                <label class="inv-filter-label" for="inv-date-from">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    From
                </label>
                <input type="date" id="inv-date-from" class="inv-date-input" data-filter="date-from" aria-label="Filter from date">
            </div>

            <!-- Date To -->
            <div class="inv-filter-group">
                <label class="inv-filter-label" for="inv-date-to">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    To
                </label>
                <input type="date" id="inv-date-to" class="inv-date-input" data-filter="date-to" aria-label="Filter to date">
            </div>

            <!-- Reset -->
            <div class="inv-filter-actions">
                <button type="button" class="btn-inv-reset" id="inv-reset-btn" aria-label="Reset all filters">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Reset Filters
                </button>
            </div>

        </div>

        <!-- Active Filter Chips -->
        <div class="inv-active-filters" id="inv-active-filters" aria-live="polite" hidden></div>
    </section>

    <!-- ======================================================== -->
    <!-- 4. INVOICES TABLE CARD                                   -->
    <!-- ======================================================== -->
    <section class="invoices-table-card" aria-label="Invoice List">

        <h2 class="invoices-list-heading">Invoices</h2>
        <div class="table-responsive invoices-table-scroll" role="region" aria-label="Invoice records" tabindex="0">
            <table class="invoices-table" id="invoices-table">
                <thead>
                    <tr>
                        <th>INVOICE #</th>
                        <th>DATE</th>
                        <th>VEHICLE</th>
                        <th>DESCRIPTION</th>
                        <th class="th-amount">AMOUNT</th>
                        <th>STATUS</th>
                        <th class="th-action">ACTION</th>
                    </tr>
                </thead>
                <tbody id="invoices-tbody">
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="7" class="inv-empty-state-cell">
                                <div class="inv-empty-state">
                                    <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="#cbd5e1" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="inv-empty-state-text">No invoices found.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $inv):
                            $status_class = $status_class_map[$inv['status']];
                            ?>
                            <tr
                                class="invoice-row"
                                data-vehicle="<?= $escape($inv['license_plate']) ?>"
                                data-date="<?= $escape($inv['date_iso']) ?>"
                                data-search="<?= $escape(strtolower($inv['invoice_number'] . ' ' . $inv['vehicle_model'] . ' ' . $inv['license_plate'] . ' ' . $inv['description'])) ?>"
                                data-invoice-id="<?= $escape((string) $inv['id']) ?>"
                                data-invoice-number="<?= $escape($inv['invoice_number']) ?>"
                            >
                                <!-- INVOICE NUMBER -->
                                <td class="td-inv-number">
                                    <span class="inv-number-label"><?= $escape($inv['invoice_number']) ?></span>
                                </td>

                                <!-- DATE -->
                                <td class="td-inv-date">
                                    <span class="inv-date-text"><?= $escape($inv['date']) ?></span>
                                </td>

                                <!-- VEHICLE -->
                                <td class="td-inv-vehicle">
                                    <div class="vehicle-cell-group">
                                        <span class="vehicle-model-label"><?= $escape($inv['vehicle_model']) ?></span>
                                        <span class="license-plate-badge-soft"><?= $escape($inv['license_plate']) ?></span>
                                    </div>
                                </td>

                                <!-- DESCRIPTION -->
                                <td class="td-inv-desc">
                                    <span class="inv-desc-text"><?= $escape($inv['description']) ?></span>
                                </td>

                                <!-- AMOUNT -->
                                <td class="td-inv-amount">
                                    <span class="inv-amount-value">
                                        Rs.&nbsp;<?= number_format($inv['amount']) ?>
                                    </span>
                                </td>

                                <!-- STATUS -->
                                <td class="td-inv-status">
                                    <span class="inv-status-badge <?= $status_class ?>">
                                        <span class="inv-status-dot"></span>
                                        <?= $escape($inv['status']) ?>
                                    </span>
                                </td>

                                <!-- ACTION -->
                                <td class="td-inv-action">
                                    <div class="inv-action-group">
                                        <a
                                            href="/customer/invoices/<?= $escape((string) $inv['id']) ?>"
                                            class="btn-inv-view"
                                            aria-label="View invoice <?= $escape($inv['invoice_number']) ?>"
                                            title="View invoice"
                                        >
                                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <span>View</span>
                                        </a>
                                        <a
                                            href="/customer/invoices/<?= $escape((string) $inv['id']) ?>/print"
                                            class="btn-inv-print"
                                            target="_blank"
                                            rel="noopener"
                                            aria-label="Print invoice <?= $escape($inv['invoice_number']) ?>"
                                            title="Print / Save as PDF"
                                        >
                                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                            <span>Print</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div class="invoices-table-footer">
            <span class="footer-count-text" id="inv-count-label">
                Showing <strong id="inv-visible-count"><?= count($invoices) ?></strong>
                of <strong><?= count($invoices) ?></strong> invoices
            </span>
        </div>

    </section>

    <!-- ======================================================== -->
    <!-- 5. SUPPORT BANNER                                        -->
    <!-- ======================================================== -->
    <section class="roadside-assistance-card">
        <div class="roadside-left">
            <div class="roadside-icon-circle">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span class="roadside-message">Have a question about an invoice or payment?</span>
        </div>
        <a href="tel:+94112345678" class="btn-contact-workshop">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            <span>Contact Workshop</span>
        </a>
    </section>

</div>
