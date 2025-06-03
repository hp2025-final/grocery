<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $receipt->receipt_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: left;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 5px;
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
        .customer-details {
            margin-bottom: 10px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 12px;
            border: 1px solid #ddd;
        }
        .items th {
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
            vertical-align: top;
            line-height: 1.4;
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
        }
        .amount-cell {
            text-align: right;
        }
        .totals {
            width: 300px;
            margin-top: 20px;
            border: 2px solid #ddd;
            border-collapse: collapse;
            font-size: 12px;
            float: right;
        }
        .totals td {
            padding: 8px;
            border: 2px solid #ddd;
        }
        .totals strong {
            font-size: 12px;
            font-weight: bold;
        }
        .totals tr:last-child td {
            background: #000000;
            color: #ffffff;
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
            <div class="invoice-title">PAYMENT RECEIPT</div>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div style="border-bottom: 2px solid #ddd; margin: 20px 0;"></div>

    <!-- RECEIPT INFORMATION -->
    <table class="invoice-details">
        <tr>
            <td>
                <strong>Received From:</strong><br>
                <div class="customer-details">
                    {{ $receipt->customer->name }}<br>
                    @if($receipt->customer->address)
                        {{ $receipt->customer->address }}<br>
                    @endif
                    @if($receipt->customer->phone)
                        Phone: {{ $receipt->customer->phone }}<br>
                    @endif
                </div>
            </td>
            <td style="text-align: right;">
                <strong>Receipt Number:</strong> {{ $receipt->receipt_number }}<br>
                <strong>Date:</strong> {{ date('d-m-Y', strtotime($receipt->receipt_date)) }}<br>
                <strong>Status:</strong> <span style="color: #059669; font-size: 11px; font-weight: bold;">Completed</span>
            </td>
        </tr>
    </table>
    <div style="border-bottom: 1px solid #ddd; margin: 20px 0;"></div><!-- RECEIPT DETAILS TABLE -->
    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th>Account</th>
                <th>Reference</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Payment Received</td>
                <td>{{ optional(optional($receipt->paymentAccount)->bank)->name ?? optional($receipt->paymentAccount)->name }}</td>
                <td>{{ $receipt->receipt_number }}</td>
                <td class="amount-cell">Rs. {{ number_format($receipt->amount_received, 2) }}</td>
            </tr>
        </tbody>
    </table>    <!-- TOTAL SECTION -->
    <table class="totals" align="right" style="width: 300px;">
        <tr>
            <td><strong>Total Amount:</strong></td>
            <td class="amount-cell">Rs. {{ number_format($receipt->amount_received, 2) }}</td>
        </tr>
    </table>

    <div style="clear: both;"></div>

    <!-- NOTES SECTION -->
    @if($receipt->notes)
    <div style="margin-top: 30px; clear: both; padding-top: 20px;">
        <strong>Notes:</strong> {{ $receipt->notes }}
    </div>
    <div style="border-bottom: 1px solid #ddd; margin: 20px 0;"></div>
    @endif

    <div style="margin-top: 40px; text-align: center;">
        <div style="font-size: 11px; font-style: italic; color: #666; margin-bottom: 20px;">
            This document is computer generated and does not require any manual authentication.
        </div>
        <div style="border-bottom: 1px solid #ddd; margin: 20px 0;"></div>
    </div><!-- FOOTER SECTION -->
    <div class="footer">
        {{ config('company.address') }}<br>
        {{ config('company.powered_by') }}
    </div>

    <!-- PAGE NUMBERS -->
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
