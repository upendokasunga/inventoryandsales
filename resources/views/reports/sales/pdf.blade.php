<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        body { font-family: 'Inter', sans-serif; color: #1e293b; font-size: 11px; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1E4A92; padding-bottom: 10px; }
        .header h1 { color: #1E4A92; font-size: 18px; margin: 0; }
        .header p { color: #64748b; font-size: 10px; margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #1E4A92; color: white; padding: 8px 6px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 6px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .summary { display: flex; justify-content: space-between; margin: 15px 0; }
        .summary-box { padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center; flex: 1; margin: 0 5px; }
        .summary-box .value { font-size: 16px; font-weight: bold; color: #1E4A92; }
        .summary-box .label { font-size: 9px; color: #64748b; text-transform: uppercase; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sales Report</h1>
        <p>{{ $startDate ?? '' }} to {{ $endDate ?? '' }}</p>
        <p>Generated: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-box">
            <div class="value">{{ number_format($summary['total_sales'] ?? 0) }}</div>
            <div class="label">Total Sales</div>
        </div>
        <div class="summary-box">
            <div class="value">{{ number_format($summary['total_revenue'] ?? 0, 2) }}</div>
            <div class="label">Revenue</div>
        </div>
        <div class="summary-box">
            <div class="value">{{ number_format($summary['gross_profit'] ?? 0, 2) }}</div>
            <div class="label">Gross Profit</div>
        </div>
        <div class="summary-box">
            <div class="value">{{ number_format($summary['average_order_value'] ?? 0, 2) }}</div>
            <div class="label">Avg Order Value</div>
        </div>
    </div>

    <div class="footer">
        <p>{{ config('app.name') }} — Confidential</p>
    </div>
</body>
</html>
