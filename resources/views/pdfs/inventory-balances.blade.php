<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>    <title>Inventory Balances Report</title>
    @include('components.pdf-styles')    <style>
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
            font-size: 36px;
            font-weight: bold;
            color: #000000;
        }
        .company-details, .company-contacts {
            font-size: 16px;
        }        .report-title {
            font-size: 10pt;
            font-weight: bold;
            margin-top: 5px;
            text-align: right;
            float: right;
            margin-right: 0;
        }
        .inventory-info {
            margin-bottom: 15px;
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
            height: auto;
            text-align: center;
            font-size: 7pt;
            color: #666;
            padding: 5px;
            border-top: 0.5px solid #ddd;
        }
        .footer .footer-address {
            margin-bottom: 3px;
        }
        .footer .developer {
            font-style: italic;
            display: inline-block;
        }        /* Column widths */
        .col-name { width: 25%; }
        .col-opening { width: 12%; }
        .col-in { width: 12%; }
        .col-out { width: 12%; }
        .col-closing { width: 12%; }
        .col-buy { width: 12%; }
        .col-sale { width: 12%; }
        /* Center align cells */
        .table td { 
            text-align: center;
        }
        .table td:first-child { /* Name column */
            text-align: left;
        }
        .table th {
            text-align: center;
        }
        .table th:first-child { /* Name header */
            text-align: left;
        }
    </style>
</head>
<body>    <div class="header-section">
        <table width="100%" style="border-collapse: collapse; margin-bottom: 10px;">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <x-inventory-balances-company-info />
                </td>
                <td style="width: 40%; text-align: right; vertical-align: bottom; padding-bottom: 5px;">
                    <div style="font-size:24px; font-weight:bold; color:#222;"></div>
                </td>
            </tr>
        </table>
    </div>    <hr style="border: 0; height: 1px; background-color: #ddd; margin: 25px 0 25px 0;">

    <div class="inventory-info" style="margin-bottom: 15px; text-align: right;">
        <div style="font-size: 14px; font-weight: bold;">Inventory Status Report</div>
        <div style="font-size: 10px;">Stock Movement Summary</div>
    </div>

    <div class="filters">
        <table width="100%">
            <tr>
                <td><strong>Period:</strong> From: {{ $from ? date('F d, Y', strtotime($from)) : 'All time' }} To: {{ $to ? date('F d, Y', strtotime($to)) : 'Present' }}</td>
                <td class="text-right">Generated: {{ date('F d, Y h:i A') }}</td>
            </tr>
        </table>
    </div>    <table class="table">
        <thead>
            <tr>
                <th class="col-name">Product</th>
                <th class="col-opening text-right">Opening</th>
                <th class="col-in text-right">In</th>
                <th class="col-out text-right">Out</th>
                <th class="col-closing text-right">Closing</th>
                <th class="col-buy text-right">Buy Price</th>
                <th class="col-sale text-right">Sale Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventory as $item)
            <tr>                <td>{{ $item->name }}</td>
                <td class="text-right">{{ number_format($item->opening_balance, 0) }} {{ $item->unit }}</td>
                <td class="text-right">{{ number_format($item->period_in, 0) }} {{ $item->unit }}</td>
                <td class="text-right">{{ number_format($item->period_out, 0) }} {{ $item->unit }}</td>
                <td class="text-right">{{ number_format($item->closing_balance, 0) }} {{ $item->unit }}</td>
                <td class="text-right">{{ number_format($item->buy_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->sale_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>                <td><strong>Total</strong></td>
                <td class="text-right"><strong>{{ number_format($inventory->sum('opening_balance'), 0) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($inventory->sum('period_in'), 0) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($inventory->sum('period_out'), 0) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($inventory->sum('closing_balance'), 0) }}</strong></td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
            </tr>
        </tfoot>
    </table>    <div class="footer">
        <x-inventory-balances-footer-info :showPoweredBy="true" />
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