<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Receipts List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: left;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        .company-details {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            color: #333;
            text-align: right;
        }
        .invoice-details {
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
        .meta-info {
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 12px;
            border: 1px solid #ddd;
        }
        .items th {
            background: #f8f9fa;
            color: #4b5563;
            padding: 10px 8px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 13px;
            font-weight: bold;
        }
        .items td {
            padding: 8px;
            border: 1px solid #ddd;
            color: #333;
        }
        .items th:first-child,
        .items td:first-child {
            width: 5%;
            text-align: center;
        }
        .items th:nth-child(2),
        .items td:nth-child(2) {
            width: 15%;
        }
        .items th:nth-child(3),
        .items td:nth-child(3) {
            width: 15%;
        }
        .items th:nth-child(4),
        .items td:nth-child(4) {
            width: 35%;
        }
        .items th:nth-child(5),
        .items td:nth-child(5) {
            width: 15%;
        }
        .items th:last-child,
        .items td:last-child {
            width: 15%;
        }
        .amount-cell {
            text-align: right;
            font-weight: 500;
        }
        .totals {
            width: 300px;
            margin: 20px 0 20px auto;
            border: 1px solid #ddd;
            border-collapse: collapse;
            font-size: 12px;
        }
        .totals td {
            padding: 8px;
            border: 1px solid #ddd;
            background: #f8f9fa;
        }
        .totals tr:last-child td {
            font-weight: bold;
        }
        .separator {
            border-bottom: 1px solid #ddd;
            margin: 20px 0;
            clear: both;
        }
        .authentication-notice {
            font-size: 11px;
            font-style: italic;
            color: #666;
            margin: 30px 0 20px;
            text-align: center;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 12px;
            color: #666;
            padding: 0 20px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <!-- HEADER SECTION -->
    <div class="header">
        <div style="float: left;">
            <div class="company-name">{{ config('company.name') }}</div>
            <div class="company-details">Phone: {{ config('company.phone') }}</div>
            <div class="company-details">Email: {{ config('company.email') }}</div>
        </div>
        <div style="float: right; margin-top: 10px;">
            <div class="invoice-title">CUSTOMER RECEIPTS LIST</div>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div class="separator"></div>

    <!-- META INFORMATION -->
    <div class="meta-info">
        <table width="100%">
            <tr>
                <td width="60%">
                    <strong>Total Receipts:</strong> {{ $receipts->count() }}<br>
                    <strong>Total Amount:</strong> Rs. {{ number_format($receipts->sum('amount_received'), 2) }}
                </td>
                <td width="40%" style="text-align: right;">
                    <strong>Generated:</strong> {{ date('d-m-Y h:i A') }}
                </td>
            </tr>
        </table>
    </div>

    <!-- RECEIPTS TABLE -->
    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>Receipt No</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Bank</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($receipts as $index => $receipt)
                @php $total += $receipt->amount_received; @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $receipt->receipt_number }}</td>
                    <td>{{ date('d-m-Y', strtotime($receipt->receipt_date)) }}</td>
                    <td>{{ $receipt->customer->name ?? '-' }}</td>
                    <td>{{ optional(optional($receipt->paymentAccount)->bank)->name ?? optional($receipt->paymentAccount)->name }}</td>
                    <td class="amount-cell">Rs. {{ number_format($receipt->amount_received, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- SUMMARY SECTION -->
    <table class="totals">
        <tr>
            <td><strong>Total Amount:</strong></td>
            <td class="amount-cell">Rs. {{ number_format($total, 2) }}</td>
        </tr>
    </table>

    <div class="separator"></div>
    
    <div class="authentication-notice">
        {{ config('company.authentication_notice') }}
    </div>

    <div class="separator"></div>

    <!-- FOOTER SECTION -->
    <div class="footer">
        {{ config('company.address') }}<br>
        {{ config('company.powered_by') }}
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("Helvetica");
            $size = 8;
            $y = $pdf->get_height() - 30;
            $x = $pdf->get_width() - 60;
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $pdf->page_text($x, $y, $text, $font, $size, array(0,0,0));
        }
    </script>
</body>
</html>
