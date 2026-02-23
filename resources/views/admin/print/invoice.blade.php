<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            font-size: 13px;
        }

        .container {
            padding: 30px;
        }

        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
        }

        .flex {
            display: flex;
            justify-content: space-between;
        }

        .row::after {
            content: "";
            clear: both;
            display: table;
        }

        .col-6 {
            float: left;
            width: 50%;
        }

        .text-right {
            text-align: right;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }

        .meta-info {
            color: #64748b;
            margin-top: 5px;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            margin-bottom: 10px;
        }

        .details-card {
            margin-bottom: 30px;
        }

        .info-label {
            font-weight: bold;
            color: #1e293b;
        }

        .info-value {
            color: #475569;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background-color: #f8fafc;
            color: #64748b;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .totals-section {
            margin-top: 30px;
            float: right;
            width: 250px;
        }

        .total-row {
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .grand-total {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            color: #0f172a;
        }

        .grand-total-label {
            font-weight: 800;
            font-size: 12px;
            text-transform: uppercase;
        }

        .grand-total-value {
            font-weight: 800;
            font-size: 18px;
            color: #2563eb;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            color: #94a3b8;
            font-size: 11px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-paid {
            background-color: #ecfdf5;
            color: #059669;
        }

        .status-pending {
            background-color: #fffbeb;
            color: #d97706;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="row">
                <div class="col-6">
                    <h1 class="title">INVOICE</h1>
                    <div class="meta-info">#{{ $invoice->invoice_no }}</div>
                </div>
                <div class="col-6 text-right">
                    <div class="status-badge {{ $invoice->status === 'paid' ? 'status-paid' : 'status-pending' }}">
                        {{ $invoice->status }}
                    </div>
                    <div class="meta-info">Date: {{ $invoice->invoice_date->format('d M, Y') }}</div>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="details-card">
            <div class="row">
                <div class="col-6">
                    <div class="section-title">Bill To</div>
                    <div class="font-bold" style="font-size: 16px; color: #0f172a;">{{ $invoice->patient->name }}</div>
                    <div>Patient ID: {{ $invoice->patient->patient_id }}</div>
                    <div>{{ $invoice->patient->place }}</div>
                    @if($invoice->patient->mobile_no)
                        <div>Ph: {{ $invoice->patient->mobile_no }}</div>
                    @endif
                </div>
                <div class="col-6 text-right">
                    <div class="section-title">Branch Info</div>
                    <div class="font-bold">{{ $invoice->branch->name }}</div>
                    @if($invoice->branch->address)
                        <div>{{ $invoice->branch->address }}</div>
                    @endif
                    @if($invoice->branch->phone)
                        <div>Ph: {{ $invoice->branch->phone }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th class="text-center" style="width: 10%;">Qty</th>
                    <th class="text-right" style="width: 20%;">Price</th>
                    <th class="text-right" style="width: 20%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right font-bold">₹{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="row">
            <div class="col-6">
                @if($invoice->notes)
                    <div style="margin-top: 30px;">
                        <div class="section-title">Notes</div>
                        <div style="color: #64748b; font-style: italic;">{{ $invoice->notes }}</div>
                    </div>
                @endif
            </div>
            <div class="col-6">
                <div class="totals-section">
                    <div class="row total-row">
                        <div class="col-6" style="color: #64748b;">Subtotal</div>
                        <div class="col-6 text-right font-bold">₹{{ number_format($invoice->sub_total, 2) }}</div>
                    </div>
                    @if($invoice->discount_amount > 0)
                        <div class="row total-row">
                            <div class="col-6" style="color: #ef4444;">Discount</div>
                            <div class="col-6 text-right font-bold" style="color: #ef4444;">-
                                ₹{{ number_format($invoice->discount_amount, 2) }}</div>
                        </div>
                    @endif
                    @if($invoice->tax_amount > 0)
                        <div class="row total-row">
                            <div class="col-6" style="color: #64748b;">Tax</div>
                            <div class="col-6 text-right font-bold">+ ₹{{ number_format($invoice->tax_amount, 2) }}</div>
                        </div>
                    @endif
                    <div class="grand-total row">
                        <div class="col-6 grand-total-label">Grand Total</div>
                        <div class="col-6 text-right grand-total-value">₹{{ number_format($invoice->total_amount, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is a computer generated invoice. No signature required.</p>
            <p>Thank you for choosing {{ $invoice->branch->name }}.</p>
        </div>
    </div>
</body>

</html>