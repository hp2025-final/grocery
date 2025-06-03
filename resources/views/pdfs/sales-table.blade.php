<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sales Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 14pt;
            margin-bottom: 5px;
        }
        .period {
            font-size: 10pt;
            color: #666;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 9pt;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ config('company.name') }}</div>
        <div class="report-title">Sales Report</div>
        <div class="period">
            Period: {{ $from ? date('F d, Y', strtotime($from)) : 'All time' }} 
            to {{ $to ? date('F d, Y', strtotime($to)) : 'Present' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%">Invoice No</th>
                <th style="width: 15%">Date</th>
                <th style="width: 30%">Customer</th>
                <th style="width: 20%" class="text-right">Amount</th>
                <th style="width: 20%">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($sales as $sale)
                @php $total += $sale->net_amount @endphp
                <tr>
                    <td>#{{ $sale->sale_number }}</td>
                    <td>{{ date('d-m-Y', strtotime($sale->sale_date)) }}</td>
                    <td>{{ $sale->customer->name }}</td>
                    <td class="text-right">{{ number_format($sale->net_amount, 2) }}</td>
                    <td>{{ $sale->payment_status }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" class="text-right">Total:</td>
                <td class="text-right">{{ number_format($total, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ date('F d, Y h:i A') }} | {{ config('company.powered_by') }}
    </div>
</body>
</html>
