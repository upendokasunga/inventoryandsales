@extends('layouts.print')

@section('title', 'Store Request ' . $storeRequest->request_number)

@section('content')
    <div class="doc-header">
        <div class="doc-title">Store Requisition Note</div>
        <div class="doc-meta">
            <strong>{{ $storeRequest->request_number }}</strong><br>
            Status: <span class="badge badge-{{ $storeRequest->status }}">{{ ucfirst($storeRequest->status) }}</span>
        </div>
    </div>

    <div class="parties">
        <div class="party-box">
            <h4>From (Source)</h4>
            <p><strong>{{ $storeRequest->sourceWarehouse->name }}</strong></p>
            <p>{{ $storeRequest->sourceWarehouse->location }}</p>
        </div>
        <div class="party-box">
            <h4>To (Destination)</h4>
            <p><strong>{{ $storeRequest->destinationWarehouse->name }}</strong></p>
            <p>{{ $storeRequest->destinationWarehouse->location }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40px">#</th>
                <th>Product</th>
                <th class="text-center" style="width:70px">Requested</th>
                <th class="text-center" style="width:70px">Issued</th>
                <th class="text-center" style="width:70px">Received</th>
            </tr>
        </thead>
        <tbody>
            @foreach($storeRequest->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item->quantity_requested }}</td>
                    <td class="text-center">{{ $item->quantity_issued ?? '-' }}</td>
                    <td class="text-center">{{ $item->quantity_received ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($storeRequest->reason)
        <div class="terms">
            <strong>Reason:</strong><br>
            {{ $storeRequest->reason }}
        </div>
    @endif
@endsection

@section('document-number', 'SRN: ' . $storeRequest->request_number)

@section('signatures')
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Requested By</div>
        <div class="signature-title">{{ $storeRequest->creator->name ?? '' }}</div>
    </div>
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Issued By</div>
        <div class="signature-title">{{ $storeRequest->issuer->name ?? '' }}</div>
    </div>
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Received By</div>
        <div class="signature-title">{{ $storeRequest->receiver->name ?? '' }}</div>
    </div>
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Authorized Signatory</div>
        <div class="signature-title">{{ $business['signatory_name'] }}<br>{{ $business['signatory_title'] }}</div>
    </div>
@endsection
