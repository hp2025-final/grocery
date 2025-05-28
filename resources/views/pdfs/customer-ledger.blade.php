<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Ledger - {{ $customer->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }        /* Column widths */
        .col-date { width: 10%; }
        .col-desc { width: 50%; }
        .col-debit { width: 13%; }
        .col-credit { width: 13%; }
        .col-balance { width: 14%; }
        th {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .date-range {
            margin-bottom: 15px;
            text-align: center;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .customer-info {
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Customer Ledger Report</h2>
    </div>

    <div class="customer-info">
        <h3>{{ $customer->name }}</h3>
        @if($from && $to)
        <div class="date-range">
            Period: {{ $from }} to {{ $to }}
        </div>
        @endif
    </div>

    <table>
        <thead>            <tr>
                <th class="col-date">Date</th>
                <th class="col-desc">Description</th>
                <th class="col-debit text-right">Debit</th>
                <th class="col-credit text-right">Credit</th>
                <th class="col-balance text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>                <td>{{ $transaction['date'] }}</td>
                <td>{{ $transaction['description'] }}</td>
                <td class="text-right">{{ $transaction['debit'] }}</td>
                <td class="text-right">{{ $transaction['credit'] }}</td>
                <td class="text-right">{{ $transaction['balance'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4">Total</td>
                <td class="text-right">{{ number_format(collect($transactions)->sum(function($t) { return floatval(str_replace(',', '', $t['debit'])); }), 2) }}</td>
                <td class="text-right">{{ number_format(collect($transactions)->sum(function($t) { return floatval(str_replace(',', '', $t['credit'])); }), 2) }}</td>
                <td class="text-right">{{ end($transactions)['balance'] }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html> 