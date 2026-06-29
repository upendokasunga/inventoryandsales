<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax Report</title>
    <style>
        body { font-family: 'Inter', sans-serif; color: #1e293b; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1E4A92; padding-bottom: 10px; }
        .header h1 { color: #1E4A92; font-size: 18px; margin: 0; }
        .header p { color: #64748b; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #1E4A92; color: white; padding: 8px 6px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 6px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .summary { display: flex; justify-content: space-between; margin: 15px 0; }
        .summary-box { padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center; flex: 1; margin: 0 5px; }
        .summary-box .value { font-size: 16px; font-weight: bold; color: #1E4A92; }
        .summary-box .label { font-size: 9px; color: #64748b; text-transform: uppercase; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 5px; }
        .gov-badge { display: inline-block; padding: 4px 12px; border: 1px solid #1E4A92; border-radius: 4px; font-size: 9px; color: #1E4A92; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>VAT / Tax Report</h1>
        <div class="gov-badge">Government-Ready Format</div>
        <p>Period: {{ $startDate ?? '' }} to {{ $endDate ?? '' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Line</th>
                <th>Description</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>1</td><td>Total Sales (Net)</td><td class="text-right">{{ number_format(($salesTax['total_net'] ?? $salesTax['total'] ?? 0), 2) }}</td></tr>
            <tr><td>2</td><td>Output VAT Collected</td><td class="text-right">{{ number_format(($vatSummary['output_vat'] ?? $salesTax['tax'] ?? 0), 2) }}</td></tr>
            <tr><td>3</td><td>Input VAT Paid</td><td class="text-right">{{ number_format(($vatSummary['input_vat'] ?? 0), 2) }}</td></tr>
            <tr style="font-weight: bold; background: #f8fafc;"><td>4</td><td>Net VAT Payable</td><td class="text-right">{{ number_format(max(0, ($vatSummary['output_vat'] ?? 0) - ($vatSummary['input_vat'] ?? 0)), 2) }}</td></tr>
        </tbody>
    </table>

    <div class="footer">
        <p>{{ config('app.name') }} — Tax Report — Confidential</p>
    </div>
</body>
</html>
