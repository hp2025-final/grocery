<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Customer Balances Report</title>
    <style>
        body { 
            font-family: sans-serif; 
            font-size: 8pt; 
            margin: 5mm 5mm 15mm 5mm;
            position: relative;
            min-height: 100%;
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 15px auto;
            table-layout: fixed;
        }
        .table th, .table td { 
            border: 1px solid #ddd; 
            padding: 3px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .table th { 
            background: #f5f5f5; 
            font-weight: bold;
            text-align: left;
            font-size: 8pt;
        }
        .text-right { 
            text-align: right; 
        }
        .header-section {
            margin-bottom: 15px;
        }
        .company-section {
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 16pt;
            font-weight: bold;
        }
        .report-title {
            font-size: 10pt;
            font-weight: bold;
            margin-top: 5px;
        }
        .filters { 
            margin: 15px 0 10px 0;
            padding: 6px;
            background: #f8f8f8;
            font-size: 8pt;
            clear: both;
        }
        .footer {
            position: fixed;
            bottom: 5mm;
            left: 0;
            right: 0;
            height: 20px;
            text-align: center;
            font-size: 7pt;
            color: #666;
            padding: 5px;
            border-top: 0.5px solid #ddd;
        }
        .footer .developer {
            font-style: italic;
            display: inline-block;
        }
        .page-number {
            position: fixed;
            bottom: 5mm;
            right: 5mm;
            font-size: 7pt;
        }
        @page {
            margin: 5mm 5mm 15mm 5mm;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="company-section">
            <div class="company-name">Steh Enterprise</div>
            <div class="report-title">Customer Balances Report</div>
        </div>
    </div>

    <div class="filters">
        <table width="100%">
            <tr>
                <td><strong>Period:</strong> From: {{ $from ? date('F d, Y', strtotime($from)) : 'All time' }} To: {{ $to ? date('F d, Y', strtotime($to)) : 'Present' }}</td>
                <td class="text-right">Generated: {{ date('F d, Y h:i A') }}</td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Customer Name</th>
                <th class="text-right">Opening Balance</th>
                <th class="text-right">Period Debit</th>
                <th class="text-right">Period Credit</th>
                <th class="text-right">Closing Balance</th>
                <th class="text-center">Ledger</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
            <tr>
                <td>{{ $customer->name }}</td>
                <td class="text-right">{{ number_format($customer->opening_balance, 2) }}</td>
                <td class="text-right">{{ number_format($customer->current_month_sales, 2) }}</td>
                <td class="text-right">{{ number_format($customer->current_month_payments, 2) }}</td>
                <td class="text-right">{{ number_format($customer->closing_balance, 2) }}</td>
                <td class="text-center">
                    <a href="{{ url('/customers/' . $customer->id . '/ledger') }}" style="color: #2563eb; text-decoration: underline;">View Ledger</a>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td><strong>Total</strong></td>
                <td class="text-right"><strong>{{ number_format($customers->sum('opening_balance'), 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($customers->sum('current_month_sales'), 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($customers->sum('current_month_payments'), 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($customers->sum('closing_balance'), 2) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <span class="developer">App Developed By: NFTech's Grocer +923162694747</span>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("Helvetica");
            $size = 7;
            $y = $pdf->get_height() - 15;
            $x = $pdf->get_width() - 40;
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $pdf->page_text($x, $y, $text, $font, $size, array(0,0,0));
        }
    </script>
</body>
</html> 