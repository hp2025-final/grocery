<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Product List by Category</title>
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
        }        .company-name {
            font-size: 24pt;
            font-weight: bold;
            color: #000000;
        }
        .report-title {
            font-size: 10pt;
            font-weight: bold;
            margin-top: 5px;
        }
        .category-header {
            background: #f0f0f0;
            padding: 5px;
            margin: 15px 0 5px 0;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        .category-count {
            font-size: 7pt;
            color: #666;
            font-weight: normal;
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
        .page-number {
            position: fixed;
            bottom: 5mm;
            right: 5mm;
            font-size: 7pt;
        }
        @page {
            margin: 5mm 5mm 15mm 5mm;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="company-section">
            <x-company-info />
            <div class="report-title">Product List by Category</div>
        </div>
    </div>

    <div class="filters">
        <table width="100%">
            <tr>
                <td>Categories: {{ $categories->count() }}</td>
                <td class="text-right">Generated: {{ date('F d, Y h:i A') }}</td>
            </tr>
        </table>
    </div>

    @php
        $totalProducts = 0;
    @endphp

    @foreach($categories as $category)
        @php
            $totalProducts += $category->inventories->count();
        @endphp
        <div class="category-header">
            {{ $category->name }}
            <span class="category-count">({{ $category->inventories->count() }} products)</span>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="65%">Product Name</th>
                    <th width="30%">Unit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($category->inventories as $index => $product)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->unit }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: #666;">No products in this category</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

    <div style="margin-top: 20px; font-weight: bold;">
        Total Products: {{ $totalProducts }}
    </div>    <div class="footer">
        {{ config('company.powered_by') }}
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
