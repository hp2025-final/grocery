<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>    <title>Bank Ledger - {{ $bank->name }}</title>
    @include('components.pdf-styles')
    <style>
        body { 
            font-family: sans-serif; 
            font-size: 8pt; 
            margin: 5mm 5mm 15mm 5mm;  /* Reduced side margins from 10mm to 5mm */
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
            font-size: 10pt;  /* Changed from 14pt to 10pt */
            font-weight: bold;
            margin-top: 5px;
        }
        .bank-details {
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
        /* Column widths - adjusted for better space usage */
        .col-date { width: 8%; }
        .col-type { width: 10%; }
        .col-accounts { width: 30%; }
        .col-reference { width: 15%; }
        .col-notes { width: 17%; }
        .col-amount { width: 10%; }
        @page {
            margin: 5mm 5mm 15mm 5mm;  /* Reduced side margins to match body */
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
            <div class="report-title">Bank Ledger</div>
        </div>
        <div class="bank-details">
            {{ $bank->name }}<br>
            Account: {{ $bank->account_number }}
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
                <th class="col-accounts">Accounts</th>
                <th class="col-reference">Reference</th>
                <th class="col-notes">Notes</th>
                <th class="col-amount text-right">Debit</th>
                <th class="col-amount text-right">Credit</th>
                <th class="col-amount text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($openingBalance))
                <tr class="opening-row">
                    <td>{{ $from }}</td>
                    <td>Opening Balance</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">{{ number_format($openingBalance, 2) }}</td>
                </tr>
            @endif
            
            @foreach($entries as $entry)
                <tr>
                    <td>{{ $entry['date'] }}</td>
                    <td>{{ $entry['type'] }}</td>
                    <td>{{ $entry['description'] }}</td>
                    <td>{{ $entry['reference'] }}</td>
                    <td>{{ $entry['notes'] }}</td>
                    <td class="text-right">{{ $entry['debit'] }}</td>
                    <td class="text-right">{{ $entry['credit'] }}</td>
                    <td class="text-right">{{ $entry['balance'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>    <div class="footer">
        <x-company-info :showPoweredBy="true" />
    </div>

    <span class="page-number"></span>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("Helvetica");
            $size = 7;
            $y = $pdf->get_height() - 15;  // Moved up from -20 to -15
            $x = $pdf->get_width() - 40;   // Moved in from -60 to -40
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $pdf->page_text($x, $y, $text, $font, $size, array(0,0,0));
        }
    </script>
</body>
</html>