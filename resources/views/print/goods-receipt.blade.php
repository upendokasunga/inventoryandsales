@extends('layouts.print')

@section('title', 'Goods Receipt ' . $goodsReceipt->receipt_number)

@section('content')
    <div class="doc-header">
        <div class="doc-title">Goods Receipt Note</div>
        <div class="doc-meta">
            <strong>{{ $goodsReceipt->receipt_number }}</strong><br>
            Date: {{ $goodsReceipt->receipt_date->format('d M Y') }}<br>
            @if($goodsReceipt->received_date)
                Received: {{ $goodsReceipt->received_date->format('d M Y') }}<br>
            @endif
            Status: <span class="badge badge-{{ $goodsReceipt->status }}">{{ ucfirst($goodsReceipt->status) }}</span>
        </div>
    </div>

    <div class="parties">
        <div class="party-box">
            <h4>Supplier</h4>
            <p><strong>{{ $goodsReceipt->purchaseOrder->supplier->name }}</strong></p>
        </div>
        <div class="party-box">
            <h4>Purchase Order</h4>
            <p><strong>{{ $goodsReceipt->purchaseOrder->po_number }}</strong></p>
            <p>Date: {{ $goodsReceipt->purchaseOrder->order_date->format('d M Y') }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40px">#</th>
                <th>Product</th>
                <th class="text-center" style="width:60px">Expected</th>
                <th class="text-center" style="width:60px">Received</th>
                <th class="text-center" style="width:70px">Condition</th>
            </tr>
        </thead>
        <tbody>
            @foreach($goodsReceipt->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item->expected_quantity }}</td>
                    <td class="text-center">{{ $item->received_quantity }}</td>
                    <td class="text-center">{{ ucfirst($item->condition) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($goodsReceipt->notes)
        <div class="terms">
            <strong>Notes:</strong><br>
            {{ $goodsReceipt->notes }}
        </div>
    @endif
@endsection

@section('document-number', 'GRN: ' . $goodsReceipt->receipt_number)

@section('signatures')
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Received By</div>
        <div class="signature-title">{{ $goodsReceipt->creator->name ?? '' }}</div>
    </div>
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Checked By</div>
        <div class="signature-title"></div>
    </div>
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Authorized Signatory</div>
        <div class="signature-title">{{ $business['signatory_name'] }}<br>{{ $business['signatory_title'] }}</div>
    </div>
@endsection
