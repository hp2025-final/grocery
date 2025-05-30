<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Inventory Balances</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        h4 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h4 class="text-center">Inventory Balances Report ({{ $from }} to {{ $to }})</h4>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 30%">Item</th>
                <th style="width: 10%">Unit</th>
                <th style="width: 15%">Opening Balance</th>
                <th style="width: 15%">In</th>
                <th style="width: 15%">Out</th>
                <th style="width: 15%">Closing Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventory as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->unit }}</td>
                <td class="text-right">{{ number_format($item->opening_balance, 2) }}</td>
                <td class="text-right">{{ number_format($item->period_in, 2) }}</td>
                <td class="text-right">{{ number_format($item->period_out, 2) }}</td>
                <td class="text-right">{{ number_format($item->closing_balance, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
