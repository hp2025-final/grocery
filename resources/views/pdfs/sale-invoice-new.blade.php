<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sale Invoice #{{ $sale->sale_number }}</title>    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: left;
            margin-bottom: 30px;
        }        .company-name {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            color: #333;
            text-align: right;
        }        .invoice-details {
            margin-bottom: 20px;
            width: 100%;
            font-size: 13px;
        }
        .invoice-details td {
            width: 50%;
            vertical-align: top;
            padding: 5px;
            line-height: 1.5;
        }
        .invoice-details strong {
            font-size: 14px;
        }
        .customer-details {
            margin-bottom: 10px;
        }        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 12px;
            border: 1px solid #ddd;
        }
        .items th:nth-child(1),
        .items td:nth-child(1) {
            width: 5%;
        }
        .items th:nth-child(2),
        .items td:nth-child(2) {
            width: 50%;
        }
        .items th:nth-child(3),
        .items td:nth-child(3) {
            width: 15%;
        }
        .items th:nth-child(4),
        .items td:nth-child(4) {
            width: 15%;
        }
        .items th:nth-child(5),
        .items td:nth-child(5) {
            width: 15%;
        }.items th {
            background: #000000;
            color: #ffffff;
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 13px;
            font-weight: bold;
        }
        .items td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .items th:first-child,
        .items td:first-child,
        .items th:nth-child(3),
        .items td:nth-child(3),
        .items th:nth-child(4),
        .items td:nth-child(4),
        .items th:nth-child(5),
        .items td:nth-child(5) {
            text-align: center;
        }        
        .items th:nth-child(1),
        .items td:nth-child(1) {
            width: 5%;
        }
        .items th:nth-child(2),
        .items td:nth-child(2) {
            width: 50%;
        }
        .items th:nth-child(3),
        .items td:nth-child(3) {
            width: 15%;
        }
        .items th:nth-child(4),
        .items td:nth-child(4) {
            width: 15%;
        }
        .items th:nth-child(5),
        .items td:nth-child(5) {
            width: 15%;
        }        .totals {
            width: 100%;
            margin-top: 20px;
            border: 2px solid #ddd;
            border-collapse: collapse;
            font-size: 12px;
        }
        .totals td {
            padding: 8px;
            border: 2px solid #ddd;
        }
        .totals strong {
            font-size: 12px;
            font-weight: bold;
        }
        .amount-cell {
            text-align: right;
        }
        .totals tr:last-child td {
            background: #000000;
            color: #ffffff;
        }        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 12px;
            color: #666;
            padding: 0 20px;
        }
        .signature-area {
            margin-top: 50px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>    <!-- HEADER SECTION -->
    <div class="header">
        <div style="float: left;">
            <div class="company-name">{{ config('company.name') }}</div>
            <div class="company-details">Phone: {{ config('company.phone') }}</div>
            <div class="company-details">Email: {{ config('company.email') }}</div>
        </div>
        <div style="float: right; margin-top: 10px;">
            <div class="invoice-title">SALES INVOICE</div>
        </div>
        <div style="clear: both;"></div>    </div>
    <div style="border-bottom: 2px solid #ddd; margin: 20px 0;"></div>

    <!-- BILLING INFORMATION SECTION -->
    <table class="invoice-details">
        <tr>
            <td>
                <strong>Invoice To:</strong><br>
                {{ $sale->customer->name }}<br>
                @if($sale->customer->address)
                {{ $sale->customer->address }}<br>
                @endif
                @if($sale->customer->phone)
                Phone: {{ $sale->customer->phone }}<br>
                @endif
            </td>
            <td style="text-align: right;">
                <strong>Invoice Number:</strong> {{ $sale->sale_number }}<br>
                <strong>Date:</strong> {{ date('d-m-Y', strtotime($sale->sale_date)) }}<br>            </td>        </tr>    </table>
    <div style="border-bottom: 1px solid #ddd; margin: 20px 0;"></div>

    <!-- ITEMS TABLE SECTION -->
    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>Item Description</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }} {{ $item->product->unit }}</td>
                <td class="amount-cell">Rs. {{ number_format($item->unit_price, 2) }}</td>
                <td class="amount-cell">Rs. {{ number_format($item->quantity * $item->unit_price, 2) }}</td>
            </tr>
            @endforeach
    </tbody>
    </table>

    <!-- SUMMARY SECTION -->
    <table class="totals" align="right" style="width: 300px;">
        <tr>
            <td><strong>Subtotal:</strong></td>
            <td class="amount-cell">Rs. {{ number_format($sale->total_amount, 2) }}</td>
        </tr>
        @if($sale->discount_amount > 0)
        <tr>
            <td><strong>Discount:</strong></td>
            <td class="amount-cell">Rs. {{ number_format($sale->discount_amount, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td><strong>Net Amount:</strong></td>
            <td class="amount-cell"><strong>Rs. {{ number_format($sale->net_amount, 2) }}</strong></td>
        </tr>    </table>    <!-- NOTES SECTION -->
    @if($sale->notes)
    <div style="margin-top: 30px;">
        <strong>Notes:</strong> {{ $sale->notes }}
    </div>
    <div style="border-bottom: 1px solid #ddd; margin: 20px 0;"></div>
    @endif    <!-- SIGNATURES SECTION -->
    <div style="margin-top: 40px; text-align: center;">
        <div style="font-size: 11px; font-style: italic; color: #666; margin-bottom: 20px;">
            {{ config('company.authentication_notice') }}
        </div>
        <div style="border-bottom: 1px solid #ddd; margin: 20px 0;"></div>
    </div>    <!-- FOOTER SECTION -->
    <div class="footer">
        {{ config('company.address') }}<br>
        {{ config('company.powered_by') }}
    </div>
</body>
</html>