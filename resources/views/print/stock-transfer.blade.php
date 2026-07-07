@extends('layouts.print')

@section('title', 'Stock Transfer ' . $stockTransfer->transfer_number)

@section('content')
    <div class="doc-header">
        <div class="doc-title">Stock Transfer Note</div>
        <div class="doc-meta">
            <strong>{{ $stockTransfer->transfer_number }}</strong><br>
            Status: <span class="badge badge-{{ $stockTransfer->status }}">{{ ucfirst($stockTransfer->status) }}</span>
        </div>
    </div>

    <div class="parties">
        <div class="party-box">
            <h4>From (Source)</h4>
            <p><strong>{{ $stockTransfer->sourceWarehouse->name }}</strong></p>
            <p>{{ $stockTransfer->sourceWarehouse->location }}</p>
        </div>
        <div class="party-box">
            <h4>To (Destination)</h4>
            <p><strong>{{ $stockTransfer->destinationWarehouse->name }}</strong></p>
            <p>{{ $stockTransfer->destinationWarehouse->location }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40px">#</th>
                <th>Product</th>
                <th class="text-center" style="width:70px">Qty Transferred</th>
                <th class="text-right" style="width:90px">Unit Cost</th>
                <th class="text-right" style="width:100px">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockTransfer->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item->quantity_transferred }}</td>
                    <td class="text-right">{{ number_format($item->unit_cost, 0) }}</td>
                    <td class="text-right">{{ number_format($item->quantity_transferred * $item->unit_cost, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($stockTransfer->reason)
        <div class="terms">
            <strong>Reason:</strong><br>
            {{ $stockTransfer->reason }}
        </div>
    @endif
@endsection

@section('document-number', 'STN: ' . $stockTransfer->transfer_number)

@section('signatures')
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Issued By</div>
        <div class="signature-title">{{ $stockTransfer->issuer->name ?? '' }}</div>
    </div>
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Received By</div>
        <div class="signature-title">{{ $stockTransfer->receiver->name ?? '' }}</div>
    </div>
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Authorized Signatory</div>
        <div class="signature-title">{{ $business['signatory_name'] }}<br>{{ $business['signatory_title'] }}</div>
    </div>
@endsection
