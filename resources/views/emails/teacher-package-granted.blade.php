<p>Xin chào <strong>{{ $teacher->name_locale }}</strong>,</p>

<p>
    🎁 Admin vừa tặng bạn gói dịch vụ đặc quyền: <strong>{{ $package->name_locale }}</strong>.
</p>

<p>Bạn cần <strong>bấm vào nút bên dưới</strong> để nhận và kích hoạt gói tặng. Đừng bỏ lỡ!</p>

<p style="text-align:center;margin:1.5rem 0;">
    <a href="{{ $claimUrl }}"
       style="display:inline-block;background:#f59e0b;color:#fff;padding:0.75rem 2rem;border-radius:8px;text-decoration:none;font-weight:700;font-size:1rem;">
        🎁 Nhận gói ngay
    </a>
</p>

<p style="color:#888;font-size:0.9rem;">
    Hoặc copy link này vào trình duyệt:<br>
    <a href="{{ $claimUrl }}" style="color:#f59e0b;">{{ $claimUrl }}</a>
</p>

<p style="color:#888;font-size:0.85rem;">
    <em>Lưu ý: Link nhận gói có thời hạn 30 ngày. Sau thời hạn, gói tặng sẽ bị hủy tự động.</em>
</p>

<p>Cảm ơn bạn đã đồng hành cùng chúng tôi.</p>
<p>Trân trọng,<br>Đội ngũ quản trị</p>
