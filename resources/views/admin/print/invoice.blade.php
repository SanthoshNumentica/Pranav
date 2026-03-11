<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice->invoice_no }}</title>
    <style>
        /* Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

        body {
            font-family: 'Inter', 'Helvetica', 'Arial', sans-serif;
            color: #1e293b;
            line-height: 1.5;
            margin: 0;
            padding: 40px;
            font-size: 13px;
            background-color: white;
        }

        /* Header */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 50px;
        }

        .header-section > div {
            min-width: 45%;
        }

        .label-uppercase {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #94a3b8;
            margin-bottom: 4px;
        }

        .name-large {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 4px;
        }

        .meta-text {
            color: #64748b;
            font-weight: 500;
            margin-top: 2px;
        }

        .branch-section {
            text-align: right;
        }

        .branch-name {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
        }

        /* Status Badge */
        .status-header {
            margin-top: 10px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-fully_paid {
            background-color: #ecfdf5;
            color: #059669;
        }

        .status-due {
            background-color: #fffbeb;
            color: #d97706;
        }

        .status-unpaid {
            background-color: #fef2f2;
            color: #ef4444;
        }

        /* Table */
        .services-section {
            margin-top: 60px;
        }

        .table-container {
            margin-top: 15px;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 14px 20px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #94a3b8;
            border-bottom: 1px solid #f1f5f9;
        }

        td {
            padding: 14px 20px;
            font-size: 13px;
            color: #0f172a;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-right {
            text-align: right;
        }

        .amount-cell {
            font-weight: 700;
            text-align: right;
        }

        .scan-type-tag {
            font-size: 10px;
            font-weight: 800;
            color: #94a3b8;
            background-color: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 6px;
        }

        /* Summary */
        .summary-container {
            margin-top: 40px;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            width: 300px;
            font-weight: 700;
            padding: 6px 12px;
            color: #64748b;
        }

        .summary-row.discount {
            color: #f43f5e;
        }

        .total-box {
            margin-top: 16px;
            width: 300px;
            background-color: #f0f9ff;
            border-radius: 16px;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 16px;
            font-weight: 900;
        }

        .total-label {
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #0f172a;
        }

        .total-value {
            font-size: 24px;
            font-weight: 900;
            color: #2563eb;
        }

        /* Payment Info */
        .payment-info {
            margin-top: 12px;
            width: 300px;
            padding: 0 12px;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 0;
        }

        .paid-text {
            color: #059669;
        }

        .due-text {
            color: #d97706;
        }

        /* Notes */
        .notes {
            margin-top: 40px;
            border-left: 2px solid #f1f5f9;
            padding-left: 16px;
        }

        .notes .label-uppercase {
            margin-bottom: 6px;
        }

        .notes-text {
            color: #64748b;
            font-size: 12px;
            font-style: italic;
        }

        /* Footer */
        .footer {
            margin-top: 60px;
            text-align: center;
            color: #94a3b8;
            font-size: 11px;
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
        }

        @media print {
            body {
                padding: 20px;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="content">
        <!-- Header Info -->
        <div class="header-section">
            <div>
                <div class="label-uppercase">Bill To</div>
                <div class="name-large">{{ $invoice->patient->name }}</div>
                <div class="meta-text">Patient ID: {{ $invoice->patient->patient_id }}</div>
                <div class="meta-text">{{ $invoice->patient->place }}</div>
                @if($invoice->patient->mobile_no || $invoice->patient->whatsapp_no)
                <div class="meta-text">Ph: {{ $invoice->patient->mobile_no ?? $invoice->patient->whatsapp_no }}</div>
                @endif
            </div>
            <div class="branch-section">
                <div class="label-uppercase">Branch</div>
                <div class="branch-name">{{ $invoice->branch->name }}</div>
                <div class="status-header">
                    <span class="status-badge status-{{ $invoice->status }}">
                        {{ str_replace('_', ' ', $invoice->status) }}
                    </span>
                </div>
                <div class="meta-text">Invoice: #{{ $invoice->invoice_no }}</div>
                <div class="meta-text">Date: {{ $invoice->invoice_date->format('d M Y') }}</div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="services-section">
            <div class="label-uppercase">Services & Scans</div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 75%;">Description</th>
                            <th style="width: 25%;" class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td>
                                <span style="font-weight: 600;">{{ $item->description }}</span>
                                @if($item->scan_type_name)
                                <span class="scan-type-tag">({{ $item->scan_type_name }})</span>
                                @endif
                            </td>
                            <td class="text-right amount-cell">
                                ₹{{ number_format($item->amount, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totals & Payments -->
        <div class="summary-container">
            <div class="summary-row">
                <span>Subtotal</span>
                <span>₹{{ number_format($invoice->sub_total, 2) }}</span>
            </div>
            @if($invoice->discount_amount > 0)
            <div class="summary-row discount">
                <span>Discount</span>
                <span>- ₹{{ number_format($invoice->discount_amount, 2) }}</span>
            </div>
            @endif
            @if($invoice->tax_amount > 0)
            <div class="summary-row">
                <span>Tax</span>
                <span>+ ₹{{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
            @endif

            <div class="total-box">
                <span class="total-label">Total</span>
                <span class="total-value">₹{{ number_format($invoice->total_amount, 2) }}</span>
            </div>

            @if($invoice->paid_amount > 0)
            <div class="payment-info">
                <div class="payment-row paid-text">
                    <span>Paid Amount</span>
                    <span>₹{{ number_format($invoice->paid_amount, 2) }}</span>
                </div>
                @if($invoice->status === 'due')
                <div class="payment-row due-text">
                    <span>Due Amount</span>
                    <span>₹{{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</span>
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Notes -->
        @if($invoice->notes)
        <div class="notes">
            <div class="label-uppercase">Notes</div>
            <div class="notes-text">{{ $invoice->notes }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>This is a computer generated invoice. No signature required.</p>
            <p>Thank you for choosing {{ $invoice->branch->name }}.</p>
        </div>
    </div>
</body>

</html>