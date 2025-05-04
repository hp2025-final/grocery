<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Inventory Values Report</title>
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
            <div class="report-title">Inventory Values Report</div>
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
                <th>Product</th>
                <th>Unit</th>
                <th class="text-right">Avg. Buy Price</th>
                <th class="text-right">Avg. Sale Price</th>
                <th class="text-right">Opening Value</th>
                <th class="text-right">IN Value</th>
                <th class="text-right">OUT Value</th>
                <th class="text-right">Closing Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventory as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->unit }}</td>
                <td class="text-right">{{ $item->unit_price }}</td>
                <td class="text-right">{{ $item->sale_price }}</td>
                <td class="text-right">{{ $item->opening_value }}</td>
                <td class="text-right">{{ $item->period_in_value }}</td>
                <td class="text-right">{{ $item->period_out_value }}</td>
                <td class="text-right">{{ $item->closing_value }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2"><strong>Total</strong></td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right"><strong>{{ number_format($inventory->sum(function($item) { return (float)str_replace(',', '', $item->opening_value); }), 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($inventory->sum(function($item) { return (float)str_replace(',', '', $item->period_in_value); }), 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($inventory->sum(function($item) { return (float)str_replace(',', '', $item->period_out_value); }), 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($inventory->sum(function($item) { return (float)str_replace(',', '', $item->closing_value); }), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <span class="developer">App Developed By: NFTech's Grocer +923162694747</span>
    </div>
</body>
</html> 