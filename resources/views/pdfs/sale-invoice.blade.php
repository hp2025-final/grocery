<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>    <title>Sale Invoice #{{ $sale->sale_number }}</title>
    @include('components.pdf-styles')
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24pt;
            color: #2563eb;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .invoice-title {
            font-size: 16pt;
            color: #4b5563;
            margin-bottom: 5px;
        }
        .invoice-number {
            color: #6b7280;
            font-size: 10pt;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .info-grid .col {
            display: table-cell;
            width: 33.33%;
            vertical-align: top;
        }
        .label {
            color: #6b7280;
            font-size: 9pt;
            margin-bottom: 3px;
        }
        .value {
            color: #111827;
            font-size: 10pt;
            margin-bottom: 10px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table.items th {
            background-color: #f3f4f6;
            padding: 8px;
            text-align: left;
            font-size: 9pt;
            color: #4b5563;
            border-bottom: 1px solid #e5e7eb;
        }
        table.items td {
            padding: 8px;
            font-size: 9pt;
            color: #111827;
            border-bottom: 1px solid #e5e7eb;
        }
        .totals {
            width: 300px;
            float: right;
            margin-bottom: 30px;
        }
        .totals table {
            width: 100%;
        }
        .totals table td {
            padding: 5px 8px;
            font-size: 9pt;
        }
        .totals table td.label {
            text-align: right;
            color: #6b7280;
        }
        .totals table td.value {
            text-align: right;
            font-weight: bold;
            color: #111827;
        }
        .totals table tr.grand-total td {
            font-size: 11pt;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            text-align: center;
            font-size: 9pt;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <x-company-info />
            <div class="invoice-title">SALES INVOICE</div>
            <div class="invoice-number">#{{ $sale->sale_number }}</div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="col">
                <div class="label">Bill To:</div>
                <div class="value">{{ $sale->customer->name }}</div>
                @if($sale->customer->phone)
                <div class="value">{{ $sale->customer->phone }}</div>
                @endif
            </div>
            <div class="col">
                <div class="label">Invoice Date:</div>
                <div class="value">{{ date('d M Y', strtotime($sale->sale_date)) }}</div>
                @if($sale->notes)
                <div class="label">Notes:</div>
                <div class="value">{{ $sale->notes }}</div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="items">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Unit</th>
                    <th style="text-align: right">Quantity</th>
                    <th style="text-align: right">Rate</th>
                    <th style="text-align: right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->unit->name ?? '' }}</td>
                    <td style="text-align: right">{{ number_format($item->quantity, 2) }}</td>
                    <td style="text-align: right">{{ number_format($item->rate, 2) }}</td>
                    <td style="text-align: right">{{ number_format($item->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <table>
                <tr>
                    <td class="label">Sub Total:</td>
                    <td class="value">{{ number_format($sale->total_amount, 2) }}</td>
                </tr>
                @if($sale->discount_amount > 0)
                <tr>
                    <td class="label">Discount:</td>
                    <td class="value">{{ number_format($sale->discount_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="grand-total">
                    <td class="label">Total:</td>
                    <td class="value">{{ number_format($sale->net_amount, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Thank you for your business!
    </div>
</body>
</html> 