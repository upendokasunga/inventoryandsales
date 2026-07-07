@extends('layouts.print')

@section('title', 'Journal Entry ' . $journalEntry->entry_number)

@section('content')
    <div class="doc-header">
        <div class="doc-title">Journal Entry Voucher</div>
        <div class="doc-meta">
            <strong>{{ $journalEntry->entry_number }}</strong><br>
            Date: {{ $journalEntry->entry_date->format('d M Y') }}<br>
            Type: <strong>{{ ucfirst($journalEntry->type) }}</strong><br>
            Status: <span class="badge badge-{{ $journalEntry->status }}">{{ ucfirst($journalEntry->status) }}</span>
        </div>
    </div>

    <p><strong>Description:</strong> {{ $journalEntry->description }}</p>

    <table>
        <thead>
            <tr>
                <th style="width:40px">#</th>
                <th>Account</th>
                <th>Description</th>
                <th class="text-right" style="width:110px">Debit (TZS)</th>
                <th class="text-right" style="width:110px">Credit (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($journalEntry->lines as $i => $line)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $line->account->name }} ({{ $line->account->code }})</td>
                    <td>{{ $line->description }}</td>
                    <td class="text-right">{{ $line->debit > 0 ? number_format($line->debit, 0) : '-' }}</td>
                    <td class="text-right">{{ $line->credit > 0 ? number_format($line->credit, 0) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr class="grand-total"><td class="label">Total Debit</td><td class="value">{{ number_format($journalEntry->total_debit, 0) }}</td></tr>
        <tr class="grand-total"><td class="label">Total Credit</td><td class="value">{{ number_format($journalEntry->total_credit, 0) }}</td></tr>
    </table>
@endsection

@section('document-number', 'JV: ' . $journalEntry->entry_number)

@section('signatures')
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Prepared By</div>
        <div class="signature-title">{{ $journalEntry->creator->name ?? '' }}</div>
    </div>
    @if($journalEntry->approver)
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Approved By</div>
        <div class="signature-title">{{ $journalEntry->approver->name }}</div>
    </div>
    @endif
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">Authorized Signatory</div>
        <div class="signature-title">{{ $business['signatory_name'] }}<br>{{ $business['signatory_title'] }}</div>
    </div>
@endsection
