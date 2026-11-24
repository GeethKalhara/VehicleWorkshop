<?php
/**
 * Customer Invoice Detail View
 *
 * @var string $csrf
 * @var array<string, string> $assets
 * @var array{first_name?: string, last_name?: string, mobile?: string}|null $user
 * @var array{
 *     id: int|string,
 *     invoice_number: string,
 *     date: string,
 *     due_date: string,
 *     status: 'Paid'|'Unpaid'|'Partially Paid'|'Overdue',
 *     vehicle_model: string,
 *     license_plate: string,
 *     vin: string,
 *     trim_badge: string,
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
 * } $invoice
 */

$escape = static fn (string|int|float|null $value): string => htmlspecialchars(
    (string) ($value ?? ''),
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8',
);

$status_class_map = [
    'Paid'           => 'inv-badge-paid',
    'Unpaid'         => 'inv-badge-unpaid',
    'Partially Paid' => 'inv-badge-partial',
    'Overdue'        => 'inv-badge-overdue',
];
$status_class = $status_class_map[$invoice['status']];
?>

<div class="dashboard-wrapper">

    <!-- ======================================================== -->
    <!-- 1. DETAIL PAGE HEADER                                    -->
    <!-- ======================================================== -->
    <section class="invoices-page-header">
        <div class="invoices-title-group">
            <div class="inv-breadcrumb">
                <a href="/customer/invoices" class="inv-breadcrumb-link">Invoices</a>
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
                <span><?= $escape($invoice['invoice_number']) ?></span>
            </div>
            <h1 class="dashboard-heading">Invoice <?= $escape($invoice['invoice_number']) ?></h1>
        </div>
        <div class="inv-detail-header-actions">
            <a href="/customer/invoices" class="btn-inv-back">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Invoices
            </a>
            <a
                href="/customer/invoices/<?= $escape((string) $invoice['id']) ?>/print"
                class="btn-inv-print-primary"
                target="_blank"
                rel="noopener"
            >
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print / Save PDF
            </a>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- 2. INVOICE DOCUMENT CARD                                 -->
    <!-- ======================================================== -->
    <div class="inv-document-card">

        <!-- Document Header: Workshop + Invoice Meta -->
        <div class="inv-doc-header">
            <div class="inv-doc-workshop">
                <div class="inv-doc-workshop-logo">
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#f97316" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <div class="inv-doc-workshop-name">AutoCare Workshop</div>
                    <div class="inv-doc-workshop-addr">123 Galle Road, Colombo 03, Sri Lanka</div>
                    <div class="inv-doc-workshop-addr">Tel: +94 11 234 5678 · autocare@example.lk</div>
                </div>
            </div>
            <div class="inv-doc-meta">
                <div class="inv-doc-label-block">
                    <span class="inv-doc-label">INVOICE</span>
                    <span class="inv-doc-number"><?= $escape($invoice['invoice_number']) ?></span>
                </div>
                <dl class="inv-doc-meta-list">
                    <div>
                        <dt>Issue Date</dt>
                        <dd><?= $escape($invoice['date']) ?></dd>
                    </div>
                    <div>
                        <dt>Due Date</dt>
                        <dd><?= $escape($invoice['due_date']) ?></dd>
                    </div>
                    <div>
                        <dt>Status</dt>
                        <dd>
                            <span class="inv-status-badge <?= $status_class ?>">
                                <span class="inv-status-dot"></span>
                                <?= $escape($invoice['status']) ?>
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Bill To + Vehicle Info Row -->
        <div class="inv-doc-parties">
            <div class="inv-doc-party">
                <span class="inv-doc-party-label">BILL TO</span>
                <div class="inv-doc-party-name"><?= $escape($invoice['customer_name']) ?></div>
                <div class="inv-doc-party-detail"><?= $escape($invoice['customer_email']) ?></div>
                <div class="inv-doc-party-detail"><?= $escape($invoice['customer_phone']) ?></div>
            </div>
            <div class="inv-doc-party">
                <span class="inv-doc-party-label">VEHICLE</span>
                <div class="inv-doc-party-name"><?= $escape($invoice['vehicle_model']) ?></div>
                <div class="inv-doc-party-detail">
                    <span class="license-plate-badge-soft"><?= $escape($invoice['license_plate']) ?></span>
                </div>
                <?php if ($invoice['trim_badge'] !== ''): ?>
                    <div class="inv-doc-party-detail"><?= $escape($invoice['trim_badge']) ?></div>
                <?php endif; ?>
                <?php if ($invoice['vin'] !== ''): ?>
                    <div class="inv-doc-party-detail inv-vin">VIN: <?= $escape($invoice['vin']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="inv-items-section">
            <table class="inv-items-table">
                <thead>
                    <tr>
                        <th class="inv-item-desc-col">DESCRIPTION</th>
                        <th class="inv-item-type-col">TYPE</th>
                        <th class="inv-item-qty-col">QTY</th>
                        <th class="inv-item-unit-col">UNIT PRICE</th>
                        <th class="inv-item-total-col">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoice['items'] as $item): ?>
                        <tr class="inv-item-row">
                            <td class="inv-item-desc-cell">
                                <?= $escape($item['description']) ?>
                            </td>
                            <td class="inv-item-type-cell">
                                <span class="inv-type-chip inv-type-<?= $escape($item['type']) ?>">
                                    <?= $escape(ucfirst($item['type'])) ?>
                                </span>
                            </td>
                            <td class="inv-item-qty-cell"><?= $escape((string) $item['quantity']) ?></td>
                            <td class="inv-item-unit-cell">Rs.&nbsp;<?= number_format($item['unit_price']) ?></td>
                            <td class="inv-item-total-cell">Rs.&nbsp;<?= number_format($item['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals Block -->
        <div class="inv-totals-section">
            <dl class="inv-totals-list">
                <div class="inv-total-row">
                    <dt>Subtotal</dt>
                    <dd>Rs.&nbsp;<?= number_format($invoice['subtotal']) ?></dd>
                </div>
                <?php if ($invoice['discount'] > 0): ?>
                    <div class="inv-total-row inv-discount-row">
                        <dt>Discount</dt>
                        <dd>− Rs.&nbsp;<?= number_format($invoice['discount']) ?></dd>
                    </div>
                <?php endif; ?>
                <div class="inv-total-row">
                    <dt>Tax (<?= number_format($invoice['tax_rate'] * 100, 0) ?>%)</dt>
                    <dd>Rs.&nbsp;<?= number_format($invoice['tax_amount']) ?></dd>
                </div>
                <div class="inv-total-row inv-grand-total-row">
                    <dt>Total</dt>
                    <dd>Rs.&nbsp;<?= number_format($invoice['grand_total']) ?></dd>
                </div>
            </dl>
        </div>

        <!-- Payment Info -->
        <?php if (!empty($invoice['payment_method'])): ?>
            <div class="inv-payment-info">
                <span class="inv-payment-label">Payment</span>
                <span class="inv-payment-method"><?= $escape($invoice['payment_method']) ?></span>
                <?php if (!empty($invoice['payment_reference'])): ?>
                    <span class="inv-payment-ref">Ref: <?= $escape($invoice['payment_reference']) ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Notes -->
        <?php if (!empty($invoice['notes'])): ?>
            <div class="inv-notes-block">
                <span class="inv-notes-label">Notes</span>
                <p class="inv-notes-text"><?= $escape($invoice['notes']) ?></p>
            </div>
        <?php endif; ?>

    </div><!-- /.inv-document-card -->

</div>
