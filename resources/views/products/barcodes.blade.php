<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Barcode Labels</title>
    <style>
        @page {
            margin: 10mm;
            size: A4 portrait;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 10px; }
        .label-grid {
            display: grid;
            grid-template-columns: repeat({{ $columns }}, 1fr);
            gap: 8px;
            page-break-after: always;
        }
        .label {
            border: 1px dashed #ccc;
            padding: 8px;
            text-align: center;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: {{ $format === '4x2' ? '120px' : '200px' }};
        }
        .label .name {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 4px;
            text-transform: uppercase;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }
        .label .sku {
            font-size: 8px;
            color: #666;
            margin-bottom: 4px;
        }
        .label .price {
            font-size: 14px;
            font-weight: bold;
            margin-top: 4px;
        }
        .label .unit {
            font-size: 9px;
            color: #666;
        }
        .label svg {
            max-width: 90%;
            height: auto;
        }
        .no-print {
            text-align: center;
            padding: 20px;
        }
        @media print {
            .no-print { display: none; }
            .label { border-color: #ddd; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 16px; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer;">
            Print Labels
        </button>
        <span style="margin-left: 10px; color: #666;">Format: {{ $format }} ({{ $columns }}x{{ $rows }})</span>
    </div>

    <div class="label-grid" style="margin-top: 20px;">
        @forelse ($labels as $label)
            <div class="label">
                <div class="name">{{ $label['product_name'] }}</div>
                <div class="sku">SKU: {{ $label['sku'] }}</div>
                {!! $label['barcode_svg'] !!}
                <div class="unit">{{ $label['unit'] }}</div>
                @if ($label['price'])
                    <div class="price">TSh {{ number_format($label['price'], 0) }}</div>
                @endif
            </div>
        @empty
            <p>No labels to display.</p>
        @endforelse
    </div>
</body>
</html>
