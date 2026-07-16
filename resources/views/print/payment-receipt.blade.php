@extends('layouts.print')

@section('title', 'Payment Receipt')

@section('content')
    <div class="doc-header">
        <div class="doc-title">Payment Receipt</div>
        <div class="doc-meta">
            <strong>{{ $payment->account?->name ?? '-' }} - {{ $payment->reference_number ?? 'N/A' }}</strong><br>
            Date: {{ $payment->payment_date->format('d M Y') }}<br>
        </div>
    </div>

    <div class="parties">
        <div class="party-box">
            <h4>Customer</h4>
            <p><strong>{{ $payment->customer->name }}</strong></p>
            <p>{{ $payment->customer->phone }}</p>
        </div>
        <div class="party-box">
            <h4>Invoice</h4>
            <p><strong>{{ $payment->invoice->invoice_number }}</strong></p>
            <p>Invoice Total: {{ number_format($payment->invoice->total, 0) }} TZS</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40px">#</th>
                <th>Item</th>
                <th class="text-right" style="width:150px">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Payment for Invoice {{ $payment->invoice->invoice_number }} ({{ $payment->account?->name ?? '-' }})</td>
                <td class="text-right"><strong>{{ number_format($payment->amount, 0) }} TZS</strong></td>
            </tr>
        </tbody>
    </table>

    @if($payment->notes)
        <div class="terms">
            <strong>Notes:</strong><br>
            {{ $payment->notes }}
        </div>
    @endif
@endsection

@section('document-number', 'Receipt: ' . ($payment->reference_number ?? $payment->uuid))

@section('signatures')
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Received By</div>
        <div class="signature-title">{{ $payment->receiver->name ?? '' }}</div>
    </div>
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Authorized Signatory</div>
        <div class="signature-title">{{ $business['signatory_name'] }}<br>{{ $business['signatory_title'] }}</div>
    </div>
@endsection
