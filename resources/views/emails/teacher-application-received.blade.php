<p>Xin chào {{ $application->full_name }},</p>
<p>BigK Udemy đã nhận được đơn đăng ký giảng viên của bạn. Cảm ơn bạn vì đã chọn đồng hành cùng tụi mình, nghe hơi nghiêm túc nhưng là cảm ơn thật.</p>
<p>Trạng thái hiện tại của hồ sơ: <strong>{{ $application->display_status }}</strong>.</p>
@if ($application->status === 'pending_payment')
    <p>Bạn đã chọn gói trả phí. Vui lòng hoàn tất bước thanh toán và xác nhận để hồ sơ được chuyển sang hàng chờ duyệt.</p>
@else
    <p>Hồ sơ của bạn đã vào hàng chờ duyệt. Đội ngũ admin sẽ xem kỹ rồi phản hồi sớm cho bạn.</p>
@endif
<p>Trong lúc chờ, cứ yên tâm chuẩn bị thêm ý tưởng khóa học. Biết đâu lúc được duyệt là có hàng ngon để đăng luôn.</p>
<p>Trân trọng,<br>BigK Udemy</p>
