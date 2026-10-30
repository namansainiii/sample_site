<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice {{ $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #0f172a; margin: 0; background: #f1f5f9; font-size: 13px; }
        .sheet { max-width: 860px; margin: 24px auto; background: #fff; padding: 36px; border: 1px solid #e2e8f0; }
        .top { display: flex; justify-content: space-between; gap: 20px; border-bottom: 3px solid #0f172a; padding-bottom: 16px; }
        h1 { font-size: 22px; margin: 0 0 4px; }
        h2 { font-size: 18px; margin: 0; letter-spacing: 2px; color: #b45309; }
        .muted { color: #64748b; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 18px 0; }
        .box { border: 1px solid #e2e8f0; padding: 12px; border-radius: 6px; }
        .box h4 { margin: 0 0 6px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e2e8f0; padding: 7px 8px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
        .num { text-align: right; white-space: nowrap; }
        .totals { width: 340px; margin-left: auto; margin-top: 12px; }
        .totals td { border: 0; padding: 4px 8px; }
        .totals tr.grand td { border-top: 2px solid #0f172a; font-size: 16px; font-weight: 700; padding-top: 8px; }
        .foot { margin-top: 30px; display: flex; justify-content: space-between; align-items: end; gap: 20px; }
        .actions { text-align: center; margin: 16px; }
        .actions button { background: #f59e0b; border: 0; padding: 10px 22px; font-weight: 700; border-radius: 6px; cursor: pointer; font-size: 14px; }
        @media print { body { background: #fff; } .sheet { margin: 0; border: 0; padding: 0; } .actions { display: none; } }
        @media (max-width: 640px) { .sheet { padding: 16px; margin: 0; } .grid, .top { grid-template-columns: 1fr; flex-direction: column; } .totals { width: 100%; } }
    </style>
</head>
<body>
@php
    $intra = $order->isIntraState();
    $byRate = $order->items->groupBy(fn ($item) => (string) (float) $item->gst_rate);
@endphp
<div class="actions"><button onclick="window.print()">Print / Save as PDF</button></div>
<div class="sheet">
    <div class="top">
        <div>
            <h1>{{ config('store.name') }}</h1>
            <div class="muted">{{ config('store.address') }}</div>
            <div class="muted">{{ config('store.phone') }} · {{ config('store.email') }}</div>
            <div><strong>GSTIN:</strong> {{ config('store.gstin') }}</div>
        </div>
        <div style="text-align:right;">
            <h2>TAX INVOICE</h2>
            <div><strong>Invoice #</strong> {{ $order->order_number }}</div>
            <div><strong>Date</strong> {{ $order->created_at->format('d M Y') }}</div>
            <div><strong>Payment</strong> {{ $order->payment_method->label() }} ({{ $order->payment_status->label() }})</div>
        </div>
    </div>

    <div class="grid">
        <div class="box">
            <h4>Bill to</h4>
            <strong>{{ $order->company_name ?: $order->shipping_name }}</strong><br>
            @if($order->gstin)GSTIN: {{ $order->gstin }}<br>@endif
            {{ $order->user->email }}
        </div>
        <div class="box">
            <h4>Ship to</h4>
            <strong>{{ $order->shipping_name }}</strong><br>
            {{ $order->shipping_address }}, {{ $order->shipping_city }}<br>
            {{ $order->shipping_state }} – {{ $order->shipping_pincode }} · {{ $order->shipping_phone }}<br>
            <span class="muted">Place of supply: {{ $order->shipping_state }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr><th>#</th><th>Description</th><th>HSN</th><th class="num">Qty</th><th class="num">Rate</th><th class="num">GST %</th><th class="num">Taxable value</th></tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->product_name }}<br><span class="muted">{{ $item->variant_label }} · {{ $item->sku }}</span></td>
                    <td>{{ $item->hsn_code }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">@inr($item->unit_price)</td>
                    <td class="num">{{ (float) $item->gst_rate }}%</td>
                    <td class="num">@inr($item->line_total)</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Taxable value</td><td class="num">@inr($order->subtotal)</td></tr>
        @foreach($byRate as $rate => $items)
            @php($tax = $items->sum(fn ($item) => $item->gstAmount()))
            @if($intra)
                <tr><td>CGST @ {{ $rate / 2 }}%</td><td class="num">@inr($tax / 2)</td></tr>
                <tr><td>SGST @ {{ $rate / 2 }}%</td><td class="num">@inr($tax / 2)</td></tr>
            @else
                <tr><td>IGST @ {{ $rate }}%</td><td class="num">@inr($tax)</td></tr>
            @endif
        @endforeach
        <tr><td>Shipping</td><td class="num">@inr($order->shipping_fee)</td></tr>
        <tr class="grand"><td>Grand total</td><td class="num">@inr($order->total)</td></tr>
    </table>

    <div class="foot">
        <div class="muted" style="max-width:420px;">
            Goods once sold will be exchanged only if unused and in original packing within 7 days.
            This is a computer-generated invoice.
        </div>
        <div style="text-align:center;">
            <div style="height:40px;"></div>
            <strong>For {{ config('store.name') }}</strong><br><span class="muted">Authorised signatory</span>
        </div>
    </div>
</div>
</body>
</html>
