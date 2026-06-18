<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Statement - {{ $statement['customer']->name }}</title>
    <style>
        body { font-family: 'Inter', sans-serif; font-size: 12px; color: #1e293b; padding: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 20px; font-weight: 700; color: #1E4A92; margin: 0; }
        .header p { color: #64748b; margin: 4px 0; }
        .customer-info { margin-bottom: 20px; }
        .customer-info table { width: 100%; }
        .customer-info td { padding: 2px 8px; }
        .summary { display: flex; justify-content: space-between; margin-bottom: 20px; padding: 12px; background: #f8fafc; border-radius: 8px; }
        .summary div { text-align: center; flex: 1; }
        .summary .label { font-size: 10px; text-transform: uppercase; color: #64748b; }
        .summary .value { font-size: 16px; font-weight: 700; margin-top: 2px; }
        table.transactions { width: 100%; border-collapse: collapse; }
        table.transactions th { background: #f1f5f9; padding: 8px 12px; text-align: left; font-size: 10px; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #e2e8f0; }
        table.transactions td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; }
        table.transactions .amount { text-align: right; }
        .total-row { background: #f8fafc; font-weight: 600; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name', 'WholesaleTZ') }}</h1>
        <p>Customer Statement</p>
    </div>

    <div class="customer-info">
        <table>
            <tr><td><strong>Customer:</strong> {{ $statement['customer']->name }}</td>
                <td><strong>Code:</strong> {{ $statement['customer']->code }}</td></tr>
            <tr><td><strong>Group:</strong> {{ $statement['customer']->group?->name ?? 'N/A' }}</td>
                <td><strong>Period:</strong> {{ $statement['from'] ?? 'All time' }} to {{ $statement['to'] }}</td></tr>
        </table>
    </div>

    <div class="summary">
        <div><div class="label">Opening Balance</div><div class="value" style="color:#1e293b">{{ number_format($statement['opening_balance'], 0) }}</div></div>
        <div><div class="label">Total Debit</div><div class="value" style="color:#ef4444">{{ number_format($statement['total_debit'], 0) }}</div></div>
        <div><div class="label">Total Credit</div><div class="value" style="color:#18b87a">{{ number_format($statement['total_credit'], 0) }}</div></div>
        <div><div class="label">Closing Balance</div><div class="value" style="color:#1e293b">{{ number_format($statement['closing_balance'], 0) }}</div></div>
    </div>

    <table class="transactions">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Description</th>
                <th class="amount">Debit</th>
                <th class="amount">Credit</th>
                <th class="amount">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($statement['transactions'] as $tx)
                <tr>
                    <td>{{ $tx->created_at->format('Y-m-d') }}</td>
                    <td>{{ ucfirst($tx->type) }}</td>
                    <td>{{ $tx->description }}</td>
                    <td class="amount">{{ in_array($tx->type, ['order', 'allocation']) ? number_format($tx->amount, 0) : '-' }}</td>
                    <td class="amount">{{ in_array($tx->type, ['payment', 'refund', 'reversal']) ? number_format(abs($tx->amount), 0) : '-' }}</td>
                    <td class="amount">{{ number_format($tx->balance_after, 0) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:20px;">No transactions in this period.</td></tr>
            @endforelse
            <tr class="total-row">
                <td colspan="3"><strong>Totals</strong></td>
                <td class="amount"><strong>{{ number_format($statement['total_debit'], 0) }}</strong></td>
                <td class="amount"><strong>{{ number_format($statement['total_credit'], 0) }}</strong></td>
                <td class="amount"><strong>{{ number_format($statement['closing_balance'], 0) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ $statement['generated_at']->format('Y-m-d H:i:s') }} &middot; This is a computer-generated document.
    </div>
</body>
</html>
