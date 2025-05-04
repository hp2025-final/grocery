<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Customer Ledger - {{ $customer->name }}</title>
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
        .customer-details {
            text-align: right;
            float: right;
            margin-top: -50px;
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
        /* Column widths */
        .col-date { width: 10%; }
        .col-type { width: 10%; }
        .col-desc { width: 30%; }
        .col-notes { width: 20%; }
        .col-amount { width: 10%; }
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
            <div class="report-title">Customer Ledger</div>
        </div>
        <div class="customer-details">
            {{ $customer->name }}<br>
            @if($customer->phone)
                Phone: {{ $customer->phone }}
            @endif
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
                <th class="col-date">Date</th>
                <th class="col-type">Type</th>
                <th class="col-desc">Description</th>
                <th class="col-notes">Notes</th>
                <th class="col-amount text-right">Debit</th>
                <th class="col-amount text-right">Credit</th>
                <th class="col-amount text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr @if($row['type'] == 'Opening balance') class="opening-row" @endif>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['type'] }}</td>
                    <td>
                        @if (!empty($row['sale_items']))
                            <div style="font-size: 7pt; color: #666;">
                                @foreach($row['sale_items'] as $item)
                                    <div>
                                        {{ $item['product'] }}
                                        @if($item['qty'] !== '') ({{ $item['qty'] }} {{ $item['unit'] }}) @endif
                                        @if($item['rate'] !== '') x {{ is_numeric($item['rate']) ? number_format($item['rate'],2) : $item['rate'] }} @endif
                                        = {{ is_numeric($item['total']) ? number_format($item['total'],2) : $item['total'] }}
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
                                @endif
                            </div>
                        @else
                            {{ $row['description'] }}
                        @endif
                    </td>
                    <td>{{ $row['notes'] ?? '' }}</td>
                    <td class="text-right">{{ number_format($row['debit'], 2) }}</td>
                    <td class="text-right">{{ number_format($row['credit'], 2) }}</td>
                    <td class="text-right">{{ number_format($row['balance'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
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