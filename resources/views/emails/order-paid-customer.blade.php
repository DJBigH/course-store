<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng #{{ $order->code }}</title>
</head>

<body style="margin:0;padding:0;background:#f3f5f9;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
        Xác nhận thanh toán đơn hàng #{{ $order->code }}.
    </div>

    @php
        // Ngày hiển thị: ưu tiên ngày hoàn tất thanh toán nếu có
        $orderDate = $order->payment_complete_date ?? $order->created_at;
        $orderDateText = $orderDate ? \Carbon\Carbon::parse($orderDate)->format('d \T\há\n\g m, Y') : '';

        // Tính tạm tính từ detail (fallback nếu thiếu price/qty)
        $subTotal = 0;
        if (!empty($order->detail)) {
            foreach ($order->detail as $d) {
                $qty = (int) ($d->qty ?? 1);
                $price = (int) ($d->price ?? 0);
                $subTotal += $qty * $price;
            }
        }

        $discount = (int) ($order->discount ?? 0);
        $grandTotal = (int) ($order->total ?? 0);

        // Phương thức thanh toán (tuỳ hệ thống bạn)
        $paymentMethod =
            $order->payment_method ??
            ($order->payment_method_name ?? (null ?? ($order->payment_type ?? (null ?? '—'))));

        $billingName = $order->students->name ?? '—';
        $billingEmail = $order->students->email ?? '—';
    @endphp

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f5f9;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0"
                    style="width:600px;max-width:600px;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 10px 30px rgba(17,24,39,0.08);">

                    <!-- Header -->
                    <tr>
                        <td style="padding:22px 26px;background:linear-gradient(135deg,#16a34a,#22c55e);">
                            <div style="font-size:13px;color:#dcfce7;">
                                {{ config('app.name') }}
                            </div>
                            <div style="font-size:20px;font-weight:800;color:#ffffff;margin-top:4px;">
                                Xác nhận thanh toán
                            </div>
                            <div style="margin-top:10px;font-size:13px;color:#ecfdf5;">
                                <strong style="color:#ffffff;">[Đơn hàng #{{ $order->code }}]</strong>
                                @if ($orderDateText)
                                    <span style="opacity:.95;"> ({{ $orderDateText }})</span>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:26px;">
                            <div style="font-size:14px;line-height:1.7;color:#111827;">
                                Xin chào <strong>{{ $billingName }}</strong>,<br>
                                Cảm ơn bạn đã mua hàng. Dưới đây là thông tin đơn hàng của bạn:
                            </div>

                            <!-- Products table -->
                            <div style="height:14px;"></div>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="border:1px solid #eef2f7;border-radius:12px;overflow:hidden;">
                                <tr>
                                    <td
                                        style="background:#f8fafc;padding:12px 14px;font-size:12px;color:#6b7280;font-weight:700;">
                                        Sản phẩm
                                    </td>
                                    <td align="center"
                                        style="background:#f8fafc;padding:12px 10px;font-size:12px;color:#6b7280;font-weight:700;width:90px;">
                                        Số lượng
                                    </td>
                                    <td align="right"
                                        style="background:#f8fafc;padding:12px 14px;font-size:12px;color:#6b7280;font-weight:700;width:140px;">
                                        Giá
                                    </td>
                                </tr>

                                @forelse($order->detail as $d)
                                    @php
                                        $name = $d->courses->name ?? 'Sản phẩm';
                                        $qty = (int) ($d->qty ?? 1);
                                        $price = (int) ($d->price ?? 0);
                                    @endphp
                                    <tr>
                                        <td style="padding:12px 14px;border-top:1px solid #eef2f7;">
                                            <div style="font-size:14px;font-weight:600;color:#111827;">
                                                {{ $name }}
                                            </div>
                                        </td>
                                        <td align="center"
                                            style="padding:12px 10px;border-top:1px solid #eef2f7;font-size:14px;color:#111827;">
                                            {{ $qty }}
                                        </td>
                                        <td align="right"
                                            style="padding:12px 14px;border-top:1px solid #eef2f7;font-size:14px;color:#111827;">
                                            {{ money($price) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3"
                                            style="padding:12px 14px;border-top:1px solid #eef2f7;color:#6b7280;">
                                            Không có sản phẩm trong đơn hàng.
                                        </td>
                                    </tr>
                                @endforelse
                            </table>

                            <!-- Totals -->
                            <div style="height:14px;"></div>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="border:1px solid #eef2f7;border-radius:12px;overflow:hidden;">
                                <tr>
                                    <td style="padding:10px 14px;font-size:13px;color:#6b7280;background:#fbfdff;">
                                        Tổng số phụ:
                                    </td>
                                    <td align="right"
                                        style="padding:10px 14px;font-size:13px;color:#111827;background:#fbfdff;">
                                        {{ moneyLocale($subTotal > 0 ? $subTotal : $grandTotal + $discount) }}
                                    </td>
                                </tr>

                                @if ($discount > 0)
                                    <tr>
                                        <td
                                            style="padding:10px 14px;font-size:13px;color:#6b7280;border-top:1px solid #eef2f7;">
                                            Giảm giá:
                                        </td>
                                        <td align="right"
                                            style="padding:10px 14px;font-size:13px;color:#dc2626;border-top:1px solid #eef2f7;">
                                            -{{ moneyLocale($discount) }}
                                        </td>
                                    </tr>
                                @endif

                                {{-- <tr>
                                    <td
                                        style="padding:10px 14px;font-size:13px;color:#6b7280;border-top:1px solid #eef2f7;">
                                        Phương thức thanh toán:
                                    </td>
                                    <td align="right"
                                        style="padding:10px 14px;font-size:13px;color:#111827;border-top:1px solid #eef2f7;">
                                        {{ $paymentMethod }}
                                    </td>
                                </tr> --}}

                                <tr>
                                    <td
                                        style="padding:12px 14px;font-size:14px;color:#111827;font-weight:800;border-top:1px solid #eef2f7;background:#f8fafc;">
                                        Tổng cộng:
                                    </td>
                                    <td align="right"
                                        style="padding:12px 14px;font-size:14px;color:#16a34a;font-weight:900;border-top:1px solid #eef2f7;background:#f8fafc;">
                                        {{ money($grandTotal) }}
                                    </td>
                                </tr>
                            </table>

                            <!-- Billing address -->
                            <div style="height:14px;"></div>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="border:1px solid #eef2f7;border-radius:12px;overflow:hidden;">
                                <tr>
                                    <td
                                        style="padding:12px 14px;background:#f8fafc;font-size:12px;color:#6b7280;font-weight:800;">
                                        Địa chỉ thanh toán
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 14px;">
                                        <div style="font-size:14px;font-weight:700;color:#111827;">
                                            {{ $billingName }}
                                        </div>
                                        <div style="margin-top:4px;font-size:13px;color:#6b7280;">
                                            {{ $billingEmail }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA -->
                            <div style="height:18px;"></div>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('students.account.order-detail', ['locale' => app()->getLocale(), 'id' => $order->id]) }}"
                                            style="display:inline-block;background:#16a34a;color:#ffffff;text-decoration:none;
                                                  font-size:14px;font-weight:800;padding:12px 18px;border-radius:10px;">
                                            Xem chi tiết đơn hàng
                                        </a>

                                        <div style="margin-top:10px;font-size:12px;color:#6b7280;line-height:1.5;">
                                            Nếu nút không bấm được, copy link này và mở trên trình duyệt:
                                            <div style="margin-top:6px;word-break:break-all;color:#2563eb;">
                                                {{ route('students.account.order-detail', ['locale' => app()->getLocale(), 'id' => $order->id]) }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-top:18px;font-size:12px;color:#6b7280;line-height:1.6;">
                                Cảm ơn bạn đã tin tưởng <strong>{{ config('app.name') }}</strong>.
                                Nếu bạn cần hỗ trợ, vui lòng liên hệ bộ phận CSKH.
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:18px 26px;background:#f8fafc;border-top:1px solid #eef2f7;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="font-size:12px;color:#6b7280;line-height:1.5;">
                                        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                                    </td>
                                    <td align="right" style="font-size:12px;color:#9ca3af;">
                                        Email tự động
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>

                <div style="height:16px;"></div>
            </td>
        </tr>
    </table>
</body>

</html>
