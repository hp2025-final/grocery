<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Invoice</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .header-table td {
            vertical-align: top;
            padding: 5px;
        }
        .company-info {
            text-align: left;
        }
        .invoice-title {
            text-align: right;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .company-address {
            margin-bottom: 5px;
            color: #666;
        }
        .contact-info {
            color: #666;
        }
        .contact-info span.icon {
            margin-right: 3px;
        }
        .invoice-heading {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .invoice-number {
            color: #666;
            margin-bottom: 5px;
        }
        .customer-grid {
            width: 100%;
            margin-bottom: 30px;
            border: 1px solid #ddd;
            padding: 10px;
        }
        .customer-grid td {
            padding: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .items-table .text-right {
            text-align: right;
        }
        .summary-table {
            width: 350px;
            float: right;
            margin-bottom: 30px;
        }
        .summary-table td {
            padding: 5px;
        }
        .summary-table .total-row {
            font-weight: bold;
            border-top: 2px solid #ddd;
        }
        .footer {
            clear: both;
            margin-top: 50px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            text-align: center;
            color: #666;
        }
        .signature-section {
            margin-top: 50px;
        }
        .signature-line {
            width: 200px;
            border-top: 1px solid #ddd;
            margin-top: 50px;
            text-align: center;
            float: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <table class="header-table">
            <tr>
                <td class="company-info" width="60%">
                    <div class="company-name">{{ config('company.name') }}</div>
                    <div class="company-address">{{ config('company.address') }}</div>
                    <div class="contact-info">
                        <span class="icon">&#128222;</span>{{ config('company.phone') }}<br>
                        <span class="icon">&#9993;</span>{{ config('company.email') }}
                    </div>
                </td>
                <td class="invoice-title">
                    <div class="invoice-heading">SALE INVOICE</div>
                    <div class="invoice-number">#{{ $sale->sale_number }}</div>
                    <div>Date: {{ date('d-M-Y', strtotime($sale->sale_date)) }}</div>
                </td>
            </tr>
        </table>

        <!-- Customer Information -->
        <table class="customer-grid">
            <tr>
                <td width="15%"><strong>Customer:</strong></td>
                <td width="35%">{{ $sale->customer->name }}</td>
                <td width="15%"><strong>Phone:</strong></td>
                <td width="35%">{{ $sale->customer->phone }}</td>
            </tr>
            <tr>
                <td><strong>Address:</strong></td>
                <td colspan="3">{{ $sale->customer->address }}</td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="35%">Product</th>
                    <th width="15%">Quantity</th>
                    <th width="15%">Unit</th>
                    <th width="15%" class="text-right">Rate</th>
                    <th width="15%" class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ $item->unit->abbreviation }}</td>
                    <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Section -->
        <table class="summary-table">
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td class="text-right">{{ number_format($sale->total_amount, 2) }}</td>
            </tr>
            @if($sale->discount_amount > 0)
            <tr>
                <td><strong>Discount:</strong></td>
                <td class="text-right">{{ number_format($sale->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td><strong>Net Total:</strong></td>
                <td class="text-right">{{ number_format($sale->net_amount, 2) }}</td>
            </tr>
        </table>

        <!-- Notes Section -->
        @if($sale->notes)
        <div style="clear: both; margin-bottom: 30px;">
            <strong>Notes:</strong><br>
            {{ $sale->notes }}
        </div>
        @endif

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-line">
                Authorized Signature
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            {{ config('company.powered_by') }}<br>
            Thank you for your business!
        </div>
    </div>
</body>
</html>