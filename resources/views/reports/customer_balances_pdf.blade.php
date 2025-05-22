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
            <x-company-info />
            <div class="report-title">Customer Balances Report</div>
        </div>
    </div>

    <div class="filters">
        <table width="100%">
            <tr>
                <td><strong>Period:</strong> From: {{ $from ? date('F d, Y', strtotime($from)) : 'All time' }} To: {{ $to ? date('F d, Y', strtotime($to)) : 'Present' }}</td>
                <td style="text-align: right;">Generated: {{ date('M d, Y h:i A') }}</td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="25%">Customer Name</th>
                <th width="15%" class="text-right">Opening Balance</th>
                <th width="15%" class="text-right">Sales</th>
                <th width="15%" class="text-right">Receipts</th>
                <th width="15%" class="text-right">Closing Balance</th>
                <th width="15%" class="text-right">Ledger</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
            <tr>
                <td>{{ $customer->name }}</td>
                <td class="text-right">{{ number_format($customer->opening_balance, 2) }}</td>
                <td class="text-right">{{ number_format($customer->current_month_sales, 2) }}</td>
                <td class="text-right">{{ number_format($customer->current_month_receipts, 2) }}</td>
                <td class="text-right">{{ number_format($customer->closing_balance, 2) }}</td>
                <td class="text-right"><a href="{{ route('customers.ledger', $customer->id) }}">View Ledger</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <x-company-info :showPoweredBy="true" />
    </div>
    <div class="page-number">Page 1</div>
</body>
</html>
