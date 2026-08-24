<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order #{{ $order->order_number }} - Maya Sree Fashion</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #FAF8F5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1E293B;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            background-color: #FAF8F5;
            padding: 30px 15px;
            box-sizing: border-box;
        }
        .container {
            max-width: 620px;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #E8DDD3;
            box-shadow: 0 4px 16px rgba(91, 22, 58, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #5B163A 0%, #3D0E26 100%);
            color: #FFFFFF;
            padding: 28px 24px;
            text-align: center;
        }
        .brand-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 24px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0;
            color: #FAF8F5;
            font-weight: 700;
        }
        .brand-subtitle {
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #D4AF37;
            margin-top: 4px;
            font-weight: 600;
        }
        .alert-bar {
            background-color: #FAF0E6;
            border-bottom: 2px solid #D4AF37;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .alert-text {
            color: #5B163A;
            font-weight: 700;
            font-size: 15px;
            margin: 0;
        }
        .content {
            padding: 24px;
        }
        .section-title {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748B;
            font-weight: 700;
            margin: 20px 0 10px 0;
            border-bottom: 1px solid #F1F5F9;
            padding-bottom: 6px;
        }
        .grid-box {
            background: #FAF8F5;
            border: 1px solid #E8DDD3;
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 16px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 13px;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-label {
            color: #64748B;
            font-weight: 500;
        }
        .info-val {
            color: #1E293B;
            font-weight: 600;
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-placed {
            background: #FEF3C7;
            color: #92400E;
        }
        .badge-paid {
            background: #DCFCE7;
            color: #166534;
        }
        .badge-pending {
            background: #FEE2E2;
            color: #991B1B;
        }
        .badge-cod {
            background: #F1F5F9;
            color: #475569;
        }
        .badge-online {
            background: #E0E7FF;
            color: #3730A3;
        }
        /* Product Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 16px;
            font-size: 13px;
        }
        .items-table th {
            background-color: #FAF8F5;
            color: #64748B;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #E8DDD3;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: top;
        }
        .product-title {
            font-weight: 600;
            color: #1E293B;
            margin: 0 0 4px 0;
            font-size: 13px;
        }
        .variant-pill {
            display: inline-block;
            background: #F1F5F9;
            color: #475569;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }
        .sku-tag {
            color: #94A3B8;
            font-size: 11px;
            margin-top: 2px;
        }
        /* Price Summary */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-top: 12px;
        }
        .summary-table td {
            padding: 6px 12px;
        }
        .grand-total-row td {
            border-top: 2px solid #5B163A;
            padding-top: 10px;
            padding-bottom: 10px;
            font-size: 16px;
            font-weight: 700;
            color: #5B163A;
        }
        .cta-container {
            text-align: center;
            margin: 28px 0 16px 0;
        }
        .btn-admin {
            display: inline-block;
            background-color: #5B163A;
            color: #FFFFFF !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 10px rgba(91, 22, 58, 0.25);
        }
        .footer {
            background-color: #FAF8F5;
            border-top: 1px solid #E8DDD3;
            padding: 20px 24px;
            text-align: center;
            font-size: 12px;
            color: #94A3B8;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Luxury Header -->
            <div class="header">
                <h1 class="brand-title">Maya Sree Fashion</h1>
                <div class="brand-subtitle">Exclusive Designer Wear</div>
            </div>

            <!-- Alert banner -->
            <div class="alert-bar">
                <p class="alert-text">🛍️ New Customer Order Placed</p>
                <span class="badge badge-placed">{{ $order->status_label ?? 'Order Placed' }}</span>
            </div>

            <div class="content">
                <!-- Order Metadata Card -->
                <div class="section-title">Order Information</div>
                <div class="grid-box">
                    <div class="info-row">
                        <span class="info-label">Order Number:</span>
                        <span class="info-val" style="color: #5B163A; font-family: monospace; font-size: 14px;">#{{ $order->order_number }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Placement Date & Time:</span>
                        <span class="info-val">{{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : date('d M Y, h:i A') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-val">
                            @if(strtolower($order->payment_method) === 'cod')
                                <span class="badge badge-cod">Cash on Delivery (COD)</span>
                            @else
                                <span class="badge badge-online">Online Payment ({{ ucfirst($order->payment_gateway ?? 'Cashfree') }})</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Status:</span>
                        <span class="info-val">
                            @if($order->payment_status === 'paid')
                                <span class="badge badge-paid">✓ Paid</span>
                            @else
                                <span class="badge badge-pending">Pending</span>
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Customer Details & Shipping Address -->
                <div class="section-title">Customer & Delivery Details</div>
                <div class="grid-box">
                    <div class="info-row">
                        <span class="info-label">Customer Name:</span>
                        <span class="info-val">{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Contact Phone:</span>
                        <span class="info-val"><a href="tel:{{ $order->shipping_phone }}" style="color: #5B163A; text-decoration: none; font-weight: 600;">{{ $order->shipping_phone }}</a></span>
                    </div>
                    @if($order->user && $order->user->email)
                    <div class="info-row">
                        <span class="info-label">Customer Email:</span>
                        <span class="info-val">{{ $order->user->email }}</span>
                    </div>
                    @endif
                    <div class="info-row" style="margin-top: 8px; border-top: 1px dashed #E2E8F0; padding-top: 8px;">
                        <span class="info-label">Shipping Address:</span>
                        <span class="info-val" style="text-align: right; max-width: 65%;">
                            {{ $order->shipping_address_line_1 }}
                            @if($order->shipping_address_line_2), {{ $order->shipping_address_line_2 }}@endif<br>
                            {{ $order->shipping_city }}, {{ $order->shipping_state }} - {{ $order->shipping_postal_code }}<br>
                            {{ $order->shipping_country ?? 'India' }}
                        </span>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="section-title">Ordered Items ({{ $order->total_items }} {{ $order->total_items > 1 ? 'items' : 'item' }})</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Product & Details</th>
                            <th style="text-align: center; width: 15%;">Qty</th>
                            <th style="text-align: right; width: 15%;">Unit Price</th>
                            <th style="text-align: right; width: 20%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <div class="product-title">{{ $item->product_name ?? ($item->product->name ?? 'Product') }}</div>
                                @if($item->variant_name)
                                    <span class="variant-pill">{{ $item->variant_name }}</span>
                                @endif
                                @if($item->sku)
                                    <div class="sku-tag">SKU: {{ $item->sku }}</div>
                                @endif
                            </td>
                            <td style="text-align: center; font-weight: 600;">
                                {{ $item->quantity }}
                            </td>
                            <td style="text-align: right; color: #64748B;">
                                ₹{{ number_format($item->unit_price, 2) }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #1E293B;">
                                ₹{{ number_format($item->total_price ?? ($item->unit_price * $item->quantity), 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Financial Breakdown -->
                <div class="section-title">Payment Summary</div>
                <table class="summary-table">
                    <tr>
                        <td style="color: #64748B;">Items Subtotal:</td>
                        <td style="text-align: right; font-weight: 600;">₹{{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    @if($order->discount_amount > 0)
                    <tr>
                        <td style="color: #166534;">Coupon Discount:</td>
                        <td style="text-align: right; font-weight: 600; color: #166534;">-₹{{ number_format($order->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="color: #64748B;">Delivery / Shipping:</td>
                        <td style="text-align: right; font-weight: 600;">
                            @if($order->shipping_amount <= 0)
                                <span style="color: #166534;">FREE</span>
                            @else
                                ₹{{ number_format($order->shipping_amount, 2) }}
                            @endif
                        </td>
                    </tr>
                    <tr class="grand-total-row">
                        <td>Grand Total Amount:</td>
                        <td style="text-align: right;">₹{{ number_format($order->grand_total, 2) }}</td>
                    </tr>
                </table>

                <!-- Call To Action Button for Admin -->
                <div class="cta-container">
                    <a href="{{ url('/admin/orders/' . $order->id) }}" class="btn-admin" target="_blank">
                        View & Manage Order in Admin Panel →
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <strong>Maya Sree Fashion</strong> &bull; Automated Order Notification System<br>
                This is an automated operational alert generated on {{ date('d M Y, h:i A') }}.
            </div>
        </div>
    </div>
</body>
</html>
