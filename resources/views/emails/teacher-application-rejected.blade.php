<p>Xin chào {{ $application->full_name }},</p>
<p>Hồ sơ đăng ký giảng viên của bạn hiện chưa được duyệt.</p>
<p>Đừng nản, đây chưa phải dấu chấm hết. Admin đã để lại ghi chú để bạn chỉnh hồ sơ cho thuyết phục hơn:</p>
<p><strong>{{ $application->admin_note ?: 'Vui lòng bổ sung thêm thông tin hồ sơ trước khi gửi lại.' }}</strong></p>
<p>Sau khi cập nhật, bạn có thể gửi lại hồ sơ để đội ngũ xem xét tiếp.</p>
<p>Trân trọng,<br>BigK Udemy</p>
