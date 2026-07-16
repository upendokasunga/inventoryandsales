<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 0; size: 80mm auto; }
        body { font-family: 'Courier New', monospace; font-size: 11px; width: 72mm; margin: 0 auto; padding: 5mm; }
        .center { text-align: center; }
        .header { font-size: 14px; font-weight: bold; margin-bottom: 5px; }
        .line { border-top: 1px dashed #333; margin: 5px 0; }
        table { width: 100%; }
        td { padding: 2px 0; }
        .right { text-align: right; }
        .total { font-size: 14px; font-weight: bold; }
        .footer { margin-top: 10px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="center header">{{ $business['name'] }}</div>
    <div class="center">{{ $business['address'] }}</div>
    <div class="center">{{ $business['phone'] }}</div>
    <div class="line"></div>
    <div class="center">RECEIPT</div>
    <div>#: {{ $invoice->invoice_number }}</div>
    <div>Date: {{ $invoice->invoice_date->format('d M Y H:i') }}</div>
    <div>Cashier: {{ $invoice->creator?->name ?? 'System' }}</div>
    <div>Customer: {{ $invoice->customer->name ?? 'Walk-in' }}</div>
    <div class="line"></div>
    <table>
        @foreach($invoice->items as $item)
            <tr>
                <td colspan="2">{{ $item->product->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>x{{ $item->quantity }} @ {{ number_format($item->unit_price, 2) }}</td>
                <td class="right">{{ number_format($item->line_total, 2) }}</td>
            </tr>
        @endforeach
    </table>
    <div class="line"></div>
    <table>
        <tr><td>Subtotal</td><td class="right">{{ number_format($invoice->subtotal, 2) }}</td></tr>
        <tr><td>Discount</td><td class="right">-{{ number_format($invoice->discount, 2) }}</td></tr>
        <tr><td>Tax</td><td class="right">{{ number_format($invoice->tax, 2) }}</td></tr>
        <tr class="total"><td>TOTAL</td><td class="right">{{ number_format($invoice->total, 2) }}</td></tr>
        <tr><td>Paid</td><td class="right">{{ number_format($invoice->amount_paid, 2) }}</td></tr>
        <tr><td>Balance</td><td class="right">{{ number_format($invoice->balance_due, 2) }}</td></tr>
        @if($invoice->payments->count() > 0)
            <tr><td colspan="2"><div class="line"></div></td></tr>
            @foreach($invoice->payments as $payment)
                <tr><td>{{ $payment->account?->name ?? '-' }}</td><td class="right">{{ number_format($payment->amount, 2) }}</td></tr>
            @endforeach
        @endif
    </table>
    <div class="line"></div>
    @if(isset($barcodeSvg))
        <div class="center" style="margin:5px 0">{{ $barcodeSvg }}</div>
    @endif
    <div class="center">{{ $invoice->invoice_number }}</div>
    <div class="line"></div>
    <div class="center footer">Thank you for your purchase!</div>
</body>
</html>
