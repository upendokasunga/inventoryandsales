@extends('layouts.print')

@section('title', 'Stock Adjustment ' . $stockAdjustment->adjustment_number)

@section('content')
    <div class="doc-header">
        <div class="doc-title">Stock Adjustment Note</div>
        <div class="doc-meta">
            <strong>{{ $stockAdjustment->adjustment_number }}</strong><br>
            Type: <strong>{{ ucfirst($stockAdjustment->type) }}</strong><br>
            Status: <span class="badge badge-{{ $stockAdjustment->status }}">{{ str_replace('_', ' ', $stockAdjustment->status) }}</span>
        </div>
    </div>

    <p><strong>Reason:</strong> {{ $stockAdjustment->reason }}</p>
    @if($stockAdjustment->description)
        <p><strong>Description:</strong> {{ $stockAdjustment->description }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width:40px">#</th>
                <th>Product</th>
                <th class="text-center" style="width:70px">Expected</th>
                <th class="text-center" style="width:70px">Actual</th>
                <th class="text-center" style="width:70px">Difference</th>
                <th class="text-right" style="width:90px">Unit Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockAdjustment->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item->expected_quantity }}</td>
                    <td class="text-center">{{ $item->actual_quantity }}</td>
                    <td class="text-center" style="color:{{ $item->difference < 0 ? '#ef4444' : '#18B87A' }}">{{ $item->difference > 0 ? '+' : '' }}{{ $item->difference }}</td>
                    <td class="text-right">{{ number_format($item->unit_cost, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @foreach($stockAdjustment->items as $item)
        @if($item->notes)
            <div class="terms">
                <strong>Notes ({{ $item->product->name }}):</strong><br>
                {{ $item->notes }}
            </div>
        @endif
    @endforeach
@endsection

@section('document-number', 'ADJ: ' . $stockAdjustment->adjustment_number)

@section('signatures')
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Prepared By</div>
        <div class="signature-title">{{ $stockAdjustment->creator->name ?? '' }}</div>
    </div>
    @if($stockAdjustment->approver)
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Approved By</div>
        <div class="signature-title">{{ $stockAdjustment->approver->name }}</div>
    </div>
    @endif
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Authorized Signatory</div>
        <div class="signature-title">{{ $business['signatory_name'] }}<br>{{ $business['signatory_title'] }}</div>
    </div>
@endsection
