<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>    <title>Customer Ledger - {{ $customer->name }}</title>
    @include('components.pdf-styles')
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
            font-size: 36px;
            font-weight: bold;
            color: #000000;
        }
        .company-details, .company-contacts {
            font-size: 16px;
        }
        .report-title {
            font-size: 10pt;
            font-weight: bold;
            margin-top: 5px;
            text-align: right;
            float: right;
            margin-right: 0;
        }
        .customer-details {
            text-align: right;
            float: right;
            clear: right;
            margin-top: 5px;
        }
        .filters { 
            margin: 15px 0 10px 0;
            padding: 6px;
            background: #f8f8f8;
            font-size: 8pt;
            clear: both;
        }
        .opening-row { 
            background: #fff9c2; 
        }        .footer {
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
        }
        .page-number {
            position: fixed;
            bottom: 5mm;
            right: 5mm;
            font-size: 7pt;
        }
        /* Column widths */
        .col-date { width: 10%; }
        .col-desc { width: 50%; }
        .col-debit { width: 13%; }
        .col-credit { width: 13%; }
        .col-balance { width: 14%; }
        /* Center align cells */
        .table td { 
            text-align: center;
        }
        .table td:nth-child(2) { /* Description column */
            text-align: left;
        }
        .table th {
            text-align: center;
        }
        .table th:nth-child(2) { /* Description header */
            text-align: left;
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
<body>    <div class="header-section">
        <table width="100%" style="border-collapse: collapse; margin-bottom: 10px;">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <x-customer-ledger-company-info />
                </td>
                <td style="width: 40%; text-align: right; vertical-align: bottom; padding-bottom: 5px;">
                    <div style="font-size:24px; font-weight:bold; color:#222;">Customer Ledger</div>
                </td>
            </tr>
        </table>
    </div>
      <hr style="border: 0; height: 1px; background-color: #ddd; margin: 25px 0 25px 0;">

    <div class="customer-info" style="margin-bottom: 15px;">
        <div style="font-size: 14px; font-weight: bold;">Customer Name: {{ $customer->name }}</div>
        <div style="font-size: 10px; font-style: bold;">Customer Phone: {{ $customer->phone }}</div>
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
        <thead>            <tr>
                <th class="col-date">Date</th>
                <th class="col-desc">Description</th>
                <th class="col-debit text-right">Debit</th>
                <th class="col-credit text-right">Credit</th>
                <th class="col-balance text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)                <tr @if($row['type'] == 'Opening balance') class="opening-row" @endif>
                    <td>{{ $row['date'] }}</td>
                    <td>
                        @if (!empty($row['sale_items']))
                            <div style="font-size: 7pt; color: #666;">
                                @foreach($row['sale_items'] as $item)                                    <div>
                                        {{ $item['product'] }}
                                        @if($item['qty'] !== '') ({{ $item['qty'] }} {{ $item['unit'] }}) @endif
                                        @if($item['rate'] !== '') x {{ is_numeric($item['rate']) ? number_format($item['rate'],0) : $item['rate'] }} @endif
                                        = {{ is_numeric($item['total']) ? number_format($item['total'],0) : $item['total'] }}
                                    </div>
                                @endforeach
                            </div>
                        @elseif (!empty($row['receipt_bank']))
                            <div style="font-size: 7pt; color: #666;">
                                Bank: {{ $row['receipt_bank'] }}<br>
                                @if(!empty($row['receipt_account_title']))
                                    Account Title: {{ $row['receipt_account_title'] }}<br>
                                @endif
                                @if(!empty($row['receipt_reference']))
                                    Reference: {{ $row['receipt_reference'] }}
                                @endif                            </div>
                        @else
                            {{ $row['description'] }}
                        @endif                    </td>
                    <td class="text-right">{{ number_format($row['debit'], 0) }}</td>
                    <td class="text-right">{{ number_format($row['credit'], 0) }}</td>
                    <td class="text-right">{{ number_format($row['balance'], 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>    <div class="footer">
        <x-customer-ledger-footer-info :showPoweredBy="true" />
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