<?php
/**
 * Customer Invoice Print/PDF View — renders a standalone printable document
 * No sidebar, no portal layout. Uses layouts/blank.
 *
 * @var string $csrf
 * @var array<string, string> $assets
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

$status_label = $invoice['status'];
$is_paid      = $invoice['status'] === 'Paid';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= $escape($invoice['invoice_number']) ?> — AutoCare Workshop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ================================================================
           INVOICE PRINT STYLESHEET
           Standalone — no portal chrome. Auto-triggers print on load.
           ================================================================ */

        /* ---------- Reset & Base ---------- */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --orange:    #f97316;
            --orange-dk: #ea580c;
            --slate-900: #0f172a;
            --slate-700: #334155;
            --slate-500: #64748b;
            --slate-300: #cbd5e1;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50:  #f8fafc;
            --green:     #16a34a;
            --red:       #dc2626;
            --amber:     #d97706;
            --font: 'Inter', system-ui, -apple-system, sans-serif;
        }

        html, body {
            font-family: var(--font);
            font-size: 13px;
            line-height: 1.5;
            color: var(--slate-700);
            background: #ffffff;
        }

        /* ---------- Page Layout ---------- */
        .print-page {
            max-width: 794px; /* A4 width at 96dpi */
            margin: 0 auto;
            padding: 2.5rem 2.75rem;
        }

        /* ---------- Screen-only: print button bar ---------- */
        .print-toolbar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            justify-content: flex-end;
            margin-bottom: 1.5rem;
        }

        .print-toolbar a {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 0.875rem;
            border: 1px solid var(--slate-200);
            border-radius: 6px;
            background: #fff;
            color: var(--slate-700);
            font-size: 0.8125rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.1s;
        }

        .print-toolbar a:hover { background: var(--slate-50); }

        .btn-print-trigger {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            background: var(--orange);
            color: #fff;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            font-family: var(--font);
            transition: background 0.1s;
        }

        .btn-print-trigger:hover { background: var(--orange-dk); }

        /* ---------- Document ---------- */
        .inv-print-doc {
            border: 1px solid var(--slate-200);
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
        }

        /* Header strip */
        .inv-print-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1.5rem;
            padding: 2rem 2rem 1.5rem;
            border-bottom: 1px solid var(--slate-200);
            background: var(--slate-50);
        }

        .inv-print-workshop-brand {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .inv-print-workshop-logo {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #fff7ed;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        .inv-print-workshop-name {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--slate-900);
            letter-spacing: -0.02em;
        }

        .inv-print-workshop-addr {
            font-size: 0.75rem;
            color: var(--slate-500);
            margin-top: 0.1rem;
        }

        .inv-print-meta {
            text-align: right;
            flex: 0 0 auto;
        }

        .inv-print-label {
            display: block;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: var(--slate-500);
            margin-bottom: 0.2rem;
        }

        .inv-print-number {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--slate-900);
            letter-spacing: -0.02em;
        }

        .inv-print-meta-dl {
            margin-top: 0.75rem;
        }

        .inv-print-meta-dl div {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
            font-size: 0.75rem;
        }

        .inv-print-meta-dl dt {
            color: var(--slate-500);
        }

        .inv-print-meta-dl dd {
            color: var(--slate-700);
            font-weight: 500;
        }

        /* Status stamp */
        .inv-print-status {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            margin-top: 0.5rem;
            padding: 0.25rem 0.625rem;
            border-radius: 999px;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .inv-print-status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .inv-print-status-paid    { background: #dcfce7; color: var(--green); }
        .inv-print-status-paid .inv-print-status-dot { background: var(--green); }
        .inv-print-status-unpaid  { background: #fff7ed; color: var(--amber); }
        .inv-print-status-unpaid .inv-print-status-dot { background: var(--amber); }
        .inv-print-status-partial { background: #eff6ff; color: #2563eb; }
        .inv-print-status-partial .inv-print-status-dot { background: #2563eb; }
        .inv-print-status-overdue { background: #fef2f2; color: var(--red); }
        .inv-print-status-overdue .inv-print-status-dot { background: var(--red); }

        /* Parties (Bill To / Vehicle) */
        .inv-print-parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--slate-200);
        }

        .inv-print-party-label {
            display: block;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: var(--orange);
            margin-bottom: 0.5rem;
        }

        .inv-print-party-name {
            font-weight: 700;
            color: var(--slate-900);
            font-size: 0.875rem;
        }

        .inv-print-party-detail {
            font-size: 0.75rem;
            color: var(--slate-500);
            margin-top: 0.1rem;
        }

        .inv-print-plate {
            display: inline-block;
            margin-top: 0.25rem;
            padding: 0.1rem 0.4rem;
            border: 1px solid var(--slate-300);
            border-radius: 4px;
            background: var(--slate-100);
            font-size: 0.6875rem;
            font-family: 'SFMono-Regular', Consolas, monospace;
            color: var(--slate-500);
        }

        /* Items table */
        .inv-print-items {
            padding: 0 2rem;
        }

        .inv-print-items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8125rem;
        }

        .inv-print-items-table th {
            padding: 0.75rem 0.5rem;
            border-bottom: 2px solid var(--slate-200);
            color: var(--slate-500);
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-align: left;
        }

        .inv-print-items-table th.r { text-align: right; }

        .inv-print-items-table td {
            padding: 0.75rem 0.5rem;
            border-bottom: 1px solid var(--slate-100);
            color: var(--slate-700);
            vertical-align: middle;
        }

        .inv-print-items-table td.r { text-align: right; }
        .inv-print-items-table tbody tr:last-child td { border-bottom: none; }

        .inv-type-chip-sm {
            display: inline-block;
            padding: 0.1rem 0.4rem;
            border-radius: 999px;
            font-size: 0.6rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            vertical-align: middle;
        }

        .inv-type-labor  { background: #fff7ed; color: #c2410c; }
        .inv-type-part   { background: #eff6ff; color: #1d4ed8; }
        .inv-type-other  { background: var(--slate-100); color: var(--slate-500); }

        /* Totals */
        .inv-print-totals {
            display: flex;
            justify-content: flex-end;
            padding: 1rem 2rem 1.5rem;
            border-top: 1px solid var(--slate-200);
        }

        .inv-print-totals-dl {
            width: 18rem;
            font-size: 0.8125rem;
        }

        .inv-print-totals-dl > div {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.3rem 0;
        }

        .inv-print-totals-dl dt { color: var(--slate-500); }
        .inv-print-totals-dl dd { font-weight: 500; color: var(--slate-700); text-align: right; }

        .inv-print-grand-total-row {
            border-top: 2px solid var(--slate-900);
            margin-top: 0.25rem;
            padding-top: 0.5rem !important;
        }

        .inv-print-grand-total-row dt {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--slate-900);
        }

        .inv-print-grand-total-row dd {
            font-size: 0.875rem;
            font-weight: 800;
            color: var(--slate-900);
        }

        .inv-discount-dd { color: var(--green) !important; }

        /* Payment & Notes */
        .inv-print-footer-info {
            display: flex;
            gap: 2rem;
            padding: 1.25rem 2rem;
            border-top: 1px solid var(--slate-200);
            background: var(--slate-50);
            font-size: 0.75rem;
        }

        .inv-print-footer-block dt {
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: var(--slate-500);
            margin-bottom: 0.25rem;
        }

        .inv-print-footer-block dd {
            color: var(--slate-700);
        }

        /* Paid watermark */
        .inv-print-paid-stamp {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 5rem;
            font-weight: 900;
            color: rgba(22, 163, 74, 0.08);
            letter-spacing: 0.1em;
            pointer-events: none;
            user-select: none;
            white-space: nowrap;
        }

        .inv-print-doc-inner { position: relative; }

        /* ================================================================
           @MEDIA PRINT
           ================================================================ */
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 18mm 15mm;
        }

        @media print {
            html, body { font-size: 12px; }

            .print-toolbar { display: none !important; }

            .print-page {
                max-width: 100%;
                padding: 0;
                margin: 0;
            }

            .inv-print-doc {
                border: none;
                border-radius: 0;
            }

            .inv-print-header {
                background: #f8fafc;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .inv-print-footer-info {
                background: #f8fafc;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .inv-print-items-table th,
            .inv-print-items-table td { page-break-inside: avoid; }

            .inv-print-totals { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="print-page">

    <!-- Screen-only toolbar -->
    <div class="print-toolbar">
        <a href="/customer/invoices/<?= $escape((string) $invoice['id']) ?>">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Invoice
        </a>
        <button type="button" class="btn-print-trigger" onclick="window.print()">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print / Save as PDF
        </button>
    </div>

    <!-- Invoice Document -->
    <div class="inv-print-doc">
        <div class="inv-print-doc-inner">

            <?php if ($is_paid): ?>
                <div class="inv-print-paid-stamp" aria-hidden="true">PAID</div>
            <?php endif; ?>

            <!-- Header -->
            <div class="inv-print-header">
                <div class="inv-print-workshop-brand">
                    <div class="inv-print-workshop-logo">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#f97316" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="inv-print-workshop-name">AutoCare Workshop</div>
                        <div class="inv-print-workshop-addr">123 Galle Road, Colombo 03, Sri Lanka</div>
                        <div class="inv-print-workshop-addr">Tel: +94 11 234 5678 · autocare@example.lk</div>
                        <div class="inv-print-workshop-addr">BRN: PV 00123456</div>
                    </div>
                </div>
                <div class="inv-print-meta">
                    <span class="inv-print-label">INVOICE</span>
                    <div class="inv-print-number"><?= $escape($invoice['invoice_number']) ?></div>
                    <dl class="inv-print-meta-dl">
                        <div><dt>Issue Date</dt><dd><?= $escape($invoice['date']) ?></dd></div>
                        <div><dt>Due Date</dt><dd><?= $escape($invoice['due_date']) ?></dd></div>
                    </dl>
                    <?php
                        $status_lc = strtolower(str_replace(' ', '-', $invoice['status']));
$status_lc = $status_lc === 'partially-paid' ? 'partial' : $status_lc;
?>
                    <div>
                        <span class="inv-print-status inv-print-status-<?= $escape($status_lc) ?>">
                            <span class="inv-print-status-dot"></span>
                            <?= $escape($status_label) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Parties -->
            <div class="inv-print-parties">
                <div>
                    <span class="inv-print-party-label">BILL TO</span>
                    <div class="inv-print-party-name"><?= $escape($invoice['customer_name']) ?></div>
                    <div class="inv-print-party-detail"><?= $escape($invoice['customer_email']) ?></div>
                    <div class="inv-print-party-detail"><?= $escape($invoice['customer_phone']) ?></div>
                </div>
                <div>
                    <span class="inv-print-party-label">VEHICLE</span>
                    <div class="inv-print-party-name"><?= $escape($invoice['vehicle_model']) ?></div>
                    <div class="inv-print-party-detail">
                        <span class="inv-print-plate"><?= $escape($invoice['license_plate']) ?></span>
                    </div>
                    <?php if ($invoice['trim_badge'] !== ''): ?>
                        <div class="inv-print-party-detail"><?= $escape($invoice['trim_badge']) ?></div>
                    <?php endif; ?>
                    <?php if ($invoice['vin'] !== ''): ?>
                        <div class="inv-print-party-detail">VIN: <?= $escape($invoice['vin']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Line Items -->
            <div class="inv-print-items">
                <table class="inv-print-items-table">
                    <thead>
                        <tr>
                            <th style="width:40%">DESCRIPTION</th>
                            <th>TYPE</th>
                            <th class="r">QTY</th>
                            <th class="r">UNIT PRICE</th>
                            <th class="r">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoice['items'] as $item): ?>
                            <tr>
                                <td><?= $escape($item['description']) ?></td>
                                <td>
                                    <span class="inv-type-chip-sm inv-type-<?= $escape($item['type']) ?>">
                                        <?= $escape(ucfirst($item['type'])) ?>
                                    </span>
                                </td>
                                <td class="r"><?= $escape((string) $item['quantity']) ?></td>
                                <td class="r">Rs.&nbsp;<?= number_format($item['unit_price']) ?></td>
                                <td class="r">Rs.&nbsp;<?= number_format($item['total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="inv-print-totals">
                <dl class="inv-print-totals-dl">
                    <div>
                        <dt>Subtotal</dt>
                        <dd>Rs.&nbsp;<?= number_format($invoice['subtotal']) ?></dd>
                    </div>
                    <?php if ($invoice['discount'] > 0): ?>
                        <div>
                            <dt>Discount</dt>
                            <dd class="inv-discount-dd">− Rs.&nbsp;<?= number_format($invoice['discount']) ?></dd>
                        </div>
                    <?php endif; ?>
                    <div>
                        <dt>Tax (<?= number_format($invoice['tax_rate'] * 100, 0) ?>%)</dt>
                        <dd>Rs.&nbsp;<?= number_format($invoice['tax_amount']) ?></dd>
                    </div>
                    <div class="inv-print-grand-total-row">
                        <dt>Total</dt>
                        <dd>Rs.&nbsp;<?= number_format($invoice['grand_total']) ?></dd>
                    </div>
                </dl>
            </div>

            <!-- Payment Info & Notes -->
            <?php if (!empty($invoice['payment_method']) || !empty($invoice['notes'])): ?>
                <div class="inv-print-footer-info">
                    <?php if (!empty($invoice['payment_method'])): ?>
                        <dl class="inv-print-footer-block">
                            <dt>PAYMENT METHOD</dt>
                            <dd><?= $escape($invoice['payment_method']) ?></dd>
                            <?php if (!empty($invoice['payment_reference'])): ?>
                                <dd>Ref: <?= $escape($invoice['payment_reference']) ?></dd>
                            <?php endif; ?>
                        </dl>
                    <?php endif; ?>
                    <?php if (!empty($invoice['notes'])): ?>
                        <dl class="inv-print-footer-block">
                            <dt>NOTES</dt>
                            <dd><?= $escape($invoice['notes']) ?></dd>
                        </dl>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<script>
    // Auto-trigger print dialog when page loads
    window.addEventListener('load', function () {
        window.print();
    });
</script>
</body>
</html>
