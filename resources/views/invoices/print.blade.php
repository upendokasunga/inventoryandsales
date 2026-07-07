<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->status === 'proforma' ? 'Proforma Invoice' : 'Invoice' }} {{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 20mm; }
        body { font-family: 'Inter', sans-serif; font-size: 12px; color: #1e293b; }
        .header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 30px; }
        .business-name { font-size: 24px; font-weight: 700; color: #1E4A92; }
        .invoice-title { font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #f8fafc; text-align: left; padding: 8px 12px; font-size: 11px; text-transform: uppercase; color: #64748b; }
        td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; }
        .totals { width: 300px; margin-left: auto; }
        .totals td { text-align: right; padding: 4px 12px; border: none; }
        .totals .grand-total { font-size: 16px; font-weight: 700; color: #1E4A92; }
        .footer { margin-top: 40px; text-align: center; color: #94a3b8; font-size: 10px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-partial { background: #fef3c7; color: #92400e; }
        .badge-pending { background: #f1f5f9; color: #475569; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="business-name">{{ $business['name'] }}</div>
            <p>{{ $business['address'] }}</p>
            <p>{{ $business['phone'] }} | {{ $business['email'] }}</p>
        </div>
        <div style="text-align:right">
            <div class="invoice-title">{{ $invoice->status === 'proforma' ? 'PROFORMA INVOICE' : 'INVOICE' }}</div>
            <p><strong>{{ $invoice->invoice_number }}</strong></p>
            <p>{{ $invoice->invoice_date->format('d M Y') }}</p>
            <span class="badge badge-{{ $invoice->payment_status }}">{{ ucfirst($invoice->payment_status) }}</span>
        </div>
    </div>

    <div style="margin-bottom:20px">
        <p><strong>Bill To:</strong></p>
        <p>{{ $invoice->customer->name ?? 'Walk-in Customer' }}</p>
        @if($invoice->customer)<p>{{ $invoice->customer->phone }}</p>@endif
    </div>

    <table>
        <thead>
            <tr><th>#</th><th>Product</th><th style="text-align:center">Qty</th><th style="text-align:right">Price</th><th style="text-align:right">Discount</th><th style="text-align:right">Total</th></tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                    <td style="text-align:center">{{ $item->quantity }}</td>
                    <td style="text-align:right">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align:right">{{ number_format($item->discount, 2) }}</td>
                    <td style="text-align:right">{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td style="width:60%">Subtotal</td><td>{{ number_format($invoice->subtotal, 2) }}</td></tr>
        <tr><td>Discount</td><td style="color:#ef4444">-{{ number_format($invoice->discount, 2) }}</td></tr>
        <tr><td>Tax</td><td>{{ number_format($invoice->tax, 2) }}</td></tr>
        <tr class="grand-total"><td>Total</td><td>{{ number_format($invoice->total, 2) }}</td></tr>
        <tr><td>Paid</td><td style="color:#18B87A">{{ number_format($invoice->amount_paid, 2) }}</td></tr>
        <tr><td>Balance Due</td><td style="color:#ef4444">{{ number_format($invoice->balance_due, 2) }}</td></tr>
    </table>

    @if($invoice->notes)
        <div style="margin-top:20px;padding:12px;background:#f8fafc;border-radius:8px">
            <p style="font-size:10px;color:#64748b;text-transform:uppercase">Notes</p>
            <p>{{ $invoice->notes }}</p>
        </div>
    @endif

    <div class="footer">
        @if(isset($barcodeSvg))
            <div style="margin-bottom:10px">{{ $barcodeSvg }}</div>
        @endif
        <p>Invoice: {{ $invoice->invoice_number }}</p>
        <p>Thank you for your business!</p>
        <p>{{ $business['name'] }} | {{ $business['phone'] }}</p>
    </div>
</body>
</html>
