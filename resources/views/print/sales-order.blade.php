@extends('layouts.print')

@section('title', 'Sales Order ' . $salesOrder->so_number)

@section('content')
    <div class="doc-header">
        <div class="doc-title">Sales Order (Proforma)</div>
        <div class="doc-meta">
            <strong>{{ $salesOrder->so_number }}</strong><br>
            Date: {{ $salesOrder->order_date->format('d M Y') }}<br>
            @if($salesOrder->delivery_date)
                Delivery: {{ $salesOrder->delivery_date->format('d M Y') }}<br>
            @endif
            Status: <span class="badge badge-{{ $salesOrder->status }}">{{ str_replace('_', ' ', $salesOrder->status) }}</span>
        </div>
    </div>

    <div class="parties">
        <div class="party-box">
            <h4>Customer</h4>
            <p><strong>{{ $salesOrder->customer->name }}</strong></p>
            <p>{{ $salesOrder->customer->address }}</p>
            <p>{{ $salesOrder->customer->phone }}</p>
            @if($salesOrder->customer->email)<p>{{ $salesOrder->customer->email }}</p>@endif
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
            @foreach($salesOrder->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 0) }}</td>
                    <td class="text-right">{{ number_format($item->discount, 0) }}</td>
                    <td class="text-right">{{ number_format($item->total, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td class="label">Subtotal</td><td class="value">{{ number_format($salesOrder->subtotal, 0) }}</td></tr>
        @if($salesOrder->discount > 0)
        <tr><td class="label">Discount</td><td class="value" style="color:#ef4444">-{{ number_format($salesOrder->discount, 0) }}</td></tr>
        @endif
        @if($salesOrder->tax > 0)
        <tr><td class="label">Tax</td><td class="value">{{ number_format($salesOrder->tax, 0) }}</td></tr>
        @endif
        <tr class="grand-total"><td class="label">Total</td><td class="value">{{ number_format($salesOrder->total, 0) }} TZS</td></tr>
    </table>

    @if($salesOrder->notes)
        <div class="terms">
            <strong>Notes:</strong><br>
            {{ $salesOrder->notes }}
        </div>
    @endif
@endsection

@section('document-number', 'SO: ' . $salesOrder->so_number)

@section('signatures')
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Prepared By</div>
        <div class="signature-title">{{ $salesOrder->creator->name ?? '' }}</div>
    </div>
    @if($salesOrder->approver)
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Approved By</div>
        <div class="signature-title">{{ $salesOrder->approver->name }}</div>
    </div>
    @endif
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Authorized Signatory</div>
        <div class="signature-title">{{ $business['signatory_name'] }}<br>{{ $business['signatory_title'] }}</div>
    </div>
@endsection
