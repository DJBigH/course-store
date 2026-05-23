<?php

return array (
  'actions' => 
  array (
    'create' => 'Tạo combo',
    'update' => 'Cập nhật combo',
    'edit' => 'Chỉnh sửa',
    'delete' => 'Xóa',
    'view_public' => 'Xem công khai',
  ),
  'form' => 
  array (
    'name' => 'Tên combo',
    'price' => 'Giá gốc',
    'sale_price' => 'Giá khuyến mãi',
    'status' => 'Hiển thị công khai và cho phép mua',
    'thumbnail' => 'Ảnh đại diện',
    'description' => 'Mô tả Combo',
    'content_title' => 'Nội dung Combo',
    'pricing_title' => 'Giá & Trạng thái',
    'price_help' => 'Giá niêm yết của cả gói combo.',
    'sale_price_help' => 'Giá bán thực tế sau khi giảm giá. Để trống nếu không giảm giá.',
    'courses_title' => 'Chọn khóa học',
    'courses_help' => 'Chọn các khóa học muốn gộp vào combo (Tối thiểu 2).',
    'quantity' => 'Số lượng giới hạn',
    'quantity_help' => 'Số lượng suất mua tối đa. Để trống nếu không giới hạn.',
    'end_at' => 'Hạn đăng ký',
    'end_at_help' => 'Sau thời gian này khách hàng không thể mua combo.',
    'is_coming_soon' => 'Chế độ Sắp ra mắt',
    'is_coming_soon_help' => 'Hiển thị đếm ngược và chưa cho phép mua.',
    'coming_soon_start_at' => 'Ngày mở bán chính thức',
  ),
  'flash' => 
  array (
    'created' => 'Đã tạo combo khóa học thành công.',
    'updated' => 'Đã cập nhật combo khóa học thành công.',
    'deleted' => 'Đã xóa combo khóa học.',
  ),
  'labels' => 
  array (
    'course_count' => ':count khóa học',
    'slug' => 'Đường dẫn: :slug',
  ),
  'history' => 
  array (
    'bundle_created' => 'Tạo combo khóa học mới: :name',
    'bundle_updated' => 'Cập nhật combo khóa học: :name',
    'bundle_deleted' => 'Xóa combo khóa học: :name',
  ),
  'validation' => 
  array (
    'name_required' => 'Vui lòng nhập tên combo.',
    'price_required' => 'Vui lòng nhập giá gốc combo.',
    'course_ids_required' => 'Vui lòng chọn ít nhất 2 khóa học.',
    'course_ids_min' => 'Combo phải có tối thiểu 2 khóa học.',
    'sale_price_lt' => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',
  ),
);
