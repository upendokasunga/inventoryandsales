<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Document')</title>
    <style>
        @page { margin: 15mm 15mm 25mm 15mm; }
        body { font-family: 'Inter', sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; margin: 0; padding: 0; }
        .letterhead { text-align: center; border-bottom: 2px solid #1E4A92; padding-bottom: 10px; margin-bottom: 15px; }
        .letterhead .company-name { font-size: 20px; font-weight: 700; color: #1E4A92; }
        .letterhead .company-details { font-size: 10px; color: #475569; margin-top: 4px; }
        .letterhead .company-details span { margin: 0 8px; }
        .letterhead .divider { height: 1px; background: #e2e8f0; margin: 6px 0; }
        .doc-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px; }
        .doc-title { font-size: 16px; font-weight: 700; color: #1E4A92; text-transform: uppercase; }
        .doc-meta { text-align: right; font-size: 10px; color: #475569; }
        .doc-meta strong { color: #1e293b; }
        .parties { display: flex; justify-content: space-between; margin-bottom: 12px; }
        .party-box { width: 48%; }
        .party-box h4 { font-size: 10px; text-transform: uppercase; color: #64748b; margin: 0 0 4px 0; }
        .party-box p { margin: 1px 0; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #1E4A92; color: white; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals { width: 280px; margin-left: auto; }
        .totals td { padding: 3px 8px; border: none; font-size: 10px; }
        .totals .grand-total td { font-size: 13px; font-weight: 700; color: #1E4A92; border-top: 2px solid #1E4A92; padding-top: 4px; }
        .totals .label { text-align: left; }
        .totals .value { text-align: right; }
        .signatures { display: flex; justify-content: space-between; margin-top: 40px; padding-top: 20px; }
        .signature-box { width: 30%; text-align: center; }
        .signature-line { border-top: 1px solid #1e293b; width: 80%; margin: 0 auto 4px auto; padding-top: 4px; }
        .signature-name { font-weight: 600; font-size: 10px; }
        .signature-title { font-size: 9px; color: #64748b; }
        .terms { margin-top: 20px; padding: 10px; background: #f8fafc; border-radius: 4px; font-size: 9px; color: #475569; }
        .terms strong { color: #1e293b; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 4px; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: 600; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-partial { background: #fef3c7; color: #92400e; }
        .badge-pending { background: #f1f5f9; color: #475569; }
        .badge-draft { background: #f1f5f9; color: #475569; }
        .badge-approved { background: #dcfce7; color: #166534; }
        .badge-completed { background: #dbeafe; color: #1e40af; }
        .badge-cancelled { background: #fef2f2; color: #991b1b; }
        .badge-reversed { background: #fef2f2; color: #991b1b; }
        .clearfix { clear: both; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="letterhead">
        @if($business['logo'])
            <div style="margin-bottom:6px"><img src="{{ $business['logo'] }}" alt="Logo" style="max-height:60px; max-width:200px;"></div>
        @endif
        <div class="company-name">{{ $business['name'] }}</div>
        <div class="company-details">
            <div>{{ $business['address'] }}</div>
            <div>
                @if($business['phone'])<span>Tel: {{ $business['phone'] }}</span>@endif
                @if($business['email'])<span>Email: {{ $business['email'] }}</span>@endif
            </div>
            <div>
                @if($business['tin'])<span>TIN: {{ $business['tin'] }}</span>@endif
                @if($business['vat'])<span>VAT: {{ $business['vat'] }}</span>@endif
            </div>
        </div>
    </div>

    @yield('content')

    @hasSection('signatures')
    <div class="signatures">
        @yield('signatures')
    </div>
    @endif

    @if($business['terms'])
    <div class="terms">
        <strong>Terms & Conditions:</strong><br>
        {{ $business['terms'] }}
    </div>
    @endif

    <div class="footer">
        <span>@yield('document-number', '')</span> |
        <span>Printed: {{ now()->format('d M Y H:i') }}</span> |
        <span>{{ $business['name'] }}</span>
    </div>
</body>
</html>
