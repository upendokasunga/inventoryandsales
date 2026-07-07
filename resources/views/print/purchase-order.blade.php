@extends('layouts.print')

@section('title', 'Purchase Order ' . $purchaseOrder->po_number)

@section('content')
    <div class="doc-header">
        <div class="doc-title">Purchase Order</div>
        <div class="doc-meta">
            <strong>{{ $purchaseOrder->po_number }}</strong><br>
            Date: {{ $purchaseOrder->order_date->format('d M Y') }}<br>
            @if($purchaseOrder->expected_date)
                Expected: {{ $purchaseOrder->expected_date->format('d M Y') }}<br>
            @endif
            Status: <span class="badge badge-{{ $purchaseOrder->status }}">{{ str_replace('_', ' ', $purchaseOrder->status) }}</span>
        </div>
    </div>

    <div class="parties">
        <div class="party-box">
            <h4>Supplier</h4>
            <p><strong>{{ $purchaseOrder->supplier->name }}</strong></p>
            <p>{{ $purchaseOrder->supplier->address }}</p>
            <p>{{ $purchaseOrder->supplier->phone1 }}</p>
            @if($purchaseOrder->supplier->email)<p>{{ $purchaseOrder->supplier->email }}</p>@endif
            @if($purchaseOrder->supplier->tax_id)<p>TIN: {{ $purchaseOrder->supplier->tax_id }}</p>@endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40px">#</th>
                <th>Product</th>
                <th class="text-center" style="width:60px">Qty</th>
                <th class="text-right" style="width:90px">Price</th>
                <th class="text-right" style="width:100px">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 0) }}</td>
                    <td class="text-right">{{ number_format($item->subtotal, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td class="label">Subtotal</td><td class="value">{{ number_format($purchaseOrder->subtotal, 0) }}</td></tr>
        @if($purchaseOrder->discount > 0)
        <tr><td class="label">Discount</td><td class="value" style="color:#ef4444">-{{ number_format($purchaseOrder->discount, 0) }}</td></tr>
        @endif
        @if($purchaseOrder->tax > 0)
        <tr><td class="label">Tax</td><td class="value">{{ number_format($purchaseOrder->tax, 0) }}</td></tr>
        @endif
        <tr class="grand-total"><td class="label">Total</td><td class="value">{{ number_format($purchaseOrder->total, 0) }} TZS</td></tr>
    </table>

    @if($purchaseOrder->notes)
        <div class="terms">
            <strong>Notes:</strong><br>
            {{ $purchaseOrder->notes }}
        </div>
    @endif
@endsection

@section('document-number', 'PO: ' . $purchaseOrder->po_number)

@section('signatures')
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Prepared By</div>
        <div class="signature-title">{{ $purchaseOrder->creator->name ?? '' }}</div>
    </div>
    @if($purchaseOrder->approver)
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Approved By</div>
        <div class="signature-title">{{ $purchaseOrder->approver->name }}</div>
    </div>
    @endif
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Authorized Signatory</div>
        <div class="signature-title">{{ $business['signatory_name'] }}<br>{{ $business['signatory_title'] }}</div>
    </div>
@endsection
