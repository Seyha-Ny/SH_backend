<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #{{ $order->id }} Status Updated</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f9; font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding: 24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 640px; background:#fff; border-radius:16px; border:1px solid #e9ecef; box-shadow: 0 1px 2px rgba(0,0,0,.03);">
                    <tr>
                        <td style="padding:24px 28px; border-bottom:1px solid #e9ecef;">
                            <h1 style="margin:0; font-size:22px; font-weight:700; color:#111827;">Order Update</h1>
                            <p style="margin:6px 0 0; color:#6b7280;">Order #{{ $order->id }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 28px; color:#374151; font-size:15px;">
                            <p style="margin:0 0 12px;">
                                Hello <strong>{{ $order->user?->name ?? 'Customer' }}</strong>,
                            </p>
                            <p style="margin:0 0 16px;">
                                Your order status has been updated to <strong style="color:#111827;">{{ ucfirst($order->status) }}</strong>.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f9fafb; border-radius:12px; border:1px solid #e5e7eb;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <p style="margin:0 0 10px; font-weight:600;">Order Summary</p>
                                        <p style="margin:0; color:#4b5563; font-size:14px;">Order: #{{ $order->id }}</p>
                                        <p style="margin:6px 0; color:#4b5563; font-size:14px;">Date: {{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d H:i') }}</p>
                                        <p style="margin:6px 0; color:#4b5563; font-size:14px;">Total: {{ '$' . number_format($order->total, 2) }}</p>
                                        @if($order->tracking_number)
                                            <p style="margin:6px 0; color:#4b5563; font-size:14px;">Tracking: {{ $order->tracking_number }}</p>
                                        @endif
                                        @if($order->shippingMethod)
                                            <p style="margin:6px 0; color:#4b5563; font-size:14px;">Shipping: {{ $order->shippingMethod->name }} ({{ $order->shippingMethod->courier ?? 'Standard' }})</p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px; color:#6b7280; font-size:13px; border-top:1px solid #e9ecef;">
                            Thank you for shopping with us.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
