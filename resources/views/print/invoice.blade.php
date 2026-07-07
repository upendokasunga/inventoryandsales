@extends('layouts.print')

@section('title', $invoice->status === 'proforma' ? 'Proforma Invoice ' . $invoice->invoice_number : 'Invoice ' . $invoice->invoice_number)

@section('content')
    <div class="doc-header">
        <div class="doc-title">{{ $invoice->status === 'proforma' ? 'Proforma Invoice' : 'Invoice' }}</div>
        <div class="doc-meta">
            <strong>{{ $invoice->invoice_number }}</strong><br>
            Date: {{ $invoice->invoice_date->format('d M Y') }}<br>
            Status: <span class="badge badge-{{ $invoice->payment_status }}">{{ ucfirst($invoice->payment_status) }}</span>
        </div>
    </div>

    <div class="parties">
        <div class="party-box">
            <h4>Bill To</h4>
            <p><strong>{{ $invoice->customer->name ?? 'Walk-in Customer' }}</strong></p>
            @if($invoice->customer)
                <p>{{ $invoice->customer->address }}</p>
                <p>{{ $invoice->customer->phone }}</p>
                @if($invoice->customer->email)<p>{{ $invoice->customer->email }}</p>@endif
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40px">#</th>
                <th>Product</th>
                <th class="text-center" style="width:60px">Qty</th>
                <th class="text-right" style="width:90px">Price</th>
                <th class="text-right" style="width:80px">Discount</th>
                <th class="text-right" style="width:100px">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 0) }}</td>
                    <td class="text-right">{{ number_format($item->discount, 0) }}</td>
                    <td class="text-right">{{ number_format($item->line_total, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td class="label">Subtotal</td><td class="value">{{ number_format($invoice->subtotal, 0) }}</td></tr>
        <tr><td class="label">Discount</td><td class="value" style="color:#ef4444">-{{ number_format($invoice->discount, 0) }}</td></tr>
        <tr><td class="label">Tax (VAT)</td><td class="value">{{ number_format($invoice->tax, 0) }}</td></tr>
        <tr class="grand-total"><td class="label">Total</td><td class="value">{{ number_format($invoice->total, 0) }} TZS</td></tr>
        <tr><td class="label">Paid</td><td class="value" style="color:#18B87A">{{ number_format($invoice->amount_paid, 0) }}</td></tr>
        <tr><td class="label">Balance Due</td><td class="value" style="color:#ef4444">{{ number_format($invoice->balance_due, 0) }}</td></tr>
    </table>

    @if($invoice->notes)
        <div class="terms">
            <strong>Notes:</strong><br>
            {{ $invoice->notes }}
        </div>
    @endif
@endsection

@section('document-number', 'Invoice: ' . $invoice->invoice_number)

@section('signatures')
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Prepared By</div>
        <div class="signature-title">{{ $invoice->creator->name ?? '' }}</div>
    </div>
    @if($invoice->approver)
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Approved By</div>
        <div class="signature-title">{{ $invoice->approver->name }}</div>
    </div>
    @endif
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Authorized Signatory</div>
        <div class="signature-title">{{ $business['signatory_name'] }}<br>{{ $business['signatory_title'] }}</div>
    </div>
@endsection
