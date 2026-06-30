<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Invoice #{{ $order->id }}</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji"; color: #0f172a; margin: 0; padding: 32px; }
    .page { max-width: 800px; margin: 0 auto; }
    .header { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; }
    .brand { font-size: 22px; font-weight: 800; color: #4f46e5; letter-spacing: -0.02em; }
    .title { font-size: 28px; font-weight: 800; margin-top: 24px; }
    .meta { margin-top: 6px; color: #475569; font-size: 14px; }
    .panel { margin-top: 24px; padding: 18px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; }
    .panel-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; margin-bottom: 6px; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    table { width: 100%; border-collapse: collapse; margin-top: 18px; }
    th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
    th { color: #475569; font-weight: 600; }
    .right { text-align: right; }
    .amount { font-variant-numeric: tabular-nums; }
    .totals { display: grid; grid-template-columns: 1fr auto; gap: 8px 18px; margin-top: 18px; }
    .total-row { font-size: 18px; font-weight: 700; color: #0f172a; }
    .footer { margin-top: 40px; color: #64748b; font-size: 13px; }
    @media print {
      body { padding: 0; }
      .no-print { display: none; }
      .panel, table, th, td { border-color: #cbd5e1; }
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="header">
      <div>
        <div class="brand">ShopVue</div>
        <div class="meta">Invoice</div>
      </div>
      <div class="right">
        <div class="title">#{{ $order->id }}</div>
        <div class="meta">{{ \Carbon\Carbon::parse($order->created_at)->toDateTimeString() }}</div>
      </div>
    </div>

    <div class="grid">
      <div class="panel">
        <div class="panel-title">Bill To</div>
        <div>
          <div style="font-weight:600">{{ $order->user?->name ?? 'Guest' }}</div>
          <div style="color:#334155">{{ $order->shipping_address }}</div>
          <div style="color:#334155">{{ $order->shipping_city }}, {{ $order->shipping_postal_code }}</div>
          <div style="color:#334155">{{ $order->shipping_phone }}</div>
        </div>
      </div>
      <div class="panel">
        <div class="panel-title">Payment</div>
        <div>
          <div style="font-weight:600">{{ ucfirst($order->payment_method) }}</div>
          <div style="color:#334155">Status: {{ ucfirst($order->payment_status) }}</div>
          <div style="color:#334155">Order: {{ ucfirst($order->status) }}</div>
        </div>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th>Item</th>
          <th class="right">Qty</th>
          <th class="right">Price</th>
          <th class="right">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->items as $item)
          <tr>
            <td>{{ $item->product?->name ?? ('Product #' . $item->product_id) }}</td>
            <td class="right">{{ $item->quantity }}</td>
            <td class="right amount">{{ number_format($item->price, 2) }}</td>
            <td class="right amount">{{ number_format($item->price * $item->quantity, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="totals">
      <div></div>
      <div>
        <div class="amount">Subtotal: {{ number_format($order->subtotal ?? $order->total, 2) }}</div>
        <div class="amount">Tax: {{ number_format($order->tax_amount ?? 0, 2) }}</div>
        <div class="amount">Shipping: {{ ($order->shipping_amount ?? 0) ? number_format($order->shipping_amount, 2) : 'Free' }}</div>
        <div class="total-row amount">Total: {{ number_format($order->total, 2) }}</div>
      </div>
    </div>

    <div class="footer">
      Thank you for shopping with ShopVue. If you have questions about this invoice, contact support.
    </div>

    <div class="no-print" style="margin-top:20px;">
      <button onclick="window.print()" style="padding:10px 16px;border-radius:8px;border:1px solid #cbd5e1;background:#fff;cursor:pointer;">Print / Save PDF</button>
    </div>
  </div>
</body>
</html>
