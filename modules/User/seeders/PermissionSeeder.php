<?php

namespace Modules\User\seeders;

use Illuminate\Database\Seeder;
use Modules\User\src\Models\Group;
use Modules\User\src\Models\Permission;

class PermissionSeeder extends Seeder
{
    public static function definitions(): array
    {
        return [
            ['slug' => 'dashboard.view', 'name' => 'Xem dashboard', 'module' => 'dashboard'],

            ['slug' => 'courses.view', 'name' => 'Xem khóa học', 'module' => 'courses'],
            ['slug' => 'courses.create', 'name' => 'Tạo khóa học', 'module' => 'courses'],
            ['slug' => 'courses.edit', 'name' => 'Sửa khóa học', 'module' => 'courses'],
            ['slug' => 'courses.publish', 'name' => 'Xuất bản khóa học', 'module' => 'courses'],
            ['slug' => 'courses.soft_delete', 'name' => 'Xoa mem khoa hoc', 'module' => 'courses'],
            ['slug' => 'courses.restore', 'name' => 'Khoi phuc khoa hoc', 'module' => 'courses'],
            ['slug' => 'courses.force_delete', 'name' => 'Xóa vĩnh viễn khóa học', 'module' => 'courses'],

            ['slug' => 'lessons.view', 'name' => 'Xem bài giảng', 'module' => 'lessons'],
            ['slug' => 'lessons.create', 'name' => 'Thêm bài giảng', 'module' => 'lessons'],
            ['slug' => 'lessons.edit', 'name' => 'Sửa bài giảng', 'module' => 'lessons'],
            ['slug' => 'lessons.delete', 'name' => 'Xóa bài giảng', 'module' => 'lessons'],
            ['slug' => 'lessons.sort', 'name' => 'Sắp xếp bài giảng', 'module' => 'lessons'],

            ['slug' => 'categories.view', 'name' => 'Xem danh mục', 'module' => 'categories'],
            ['slug' => 'categories.create', 'name' => 'Tạo danh mục', 'module' => 'categories'],
            ['slug' => 'categories.edit', 'name' => 'Sửa danh mục', 'module' => 'categories'],
            ['slug' => 'categories.delete', 'name' => 'Xóa danh mục', 'module' => 'categories'],
            ['slug' => 'categories.soft_delete', 'name' => 'Xoa mem danh muc', 'module' => 'categories'],
            ['slug' => 'categories.restore', 'name' => 'Khoi phuc danh muc', 'module' => 'categories'],
            ['slug' => 'categories.force_delete', 'name' => 'Xóa vĩnh viễn danh mục', 'module' => 'categories'],
            ['slug' => 'categories.logs', 'name' => 'Xem lịch sử danh mục', 'module' => 'categories'],

            ['slug' => 'teachers.view', 'name' => 'Xem giảng viên', 'module' => 'teachers'],
            ['slug' => 'teachers.create', 'name' => 'Tạo giảng viên', 'module' => 'teachers'],
            ['slug' => 'teachers.edit', 'name' => 'Sửa giảng viên', 'module' => 'teachers'],
            ['slug' => 'teachers.delete', 'name' => 'Xóa giảng viên', 'module' => 'teachers'],
            ['slug' => 'teachers.soft_delete', 'name' => 'Xoa mem giang vien', 'module' => 'teachers'],
            ['slug' => 'teachers.restore', 'name' => 'Khoi phuc giang vien', 'module' => 'teachers'],
            ['slug' => 'teachers.force_delete', 'name' => 'Xóa vĩnh viễn giảng viên', 'module' => 'teachers'],
            ['slug' => 'teachers.logs', 'name' => 'Xem lịch sử giảng viên', 'module' => 'teachers'],

            ['slug' => 'orders.view', 'name' => 'Xem đơn hàng', 'module' => 'orders'],
            ['slug' => 'orders.update', 'name' => 'Cập nhật đơn hàng', 'module' => 'orders'],
            ['slug' => 'orders.delete', 'name' => 'Xóa đơn hàng', 'module' => 'orders'],
            ['slug' => 'orders.soft_delete', 'name' => 'Xoa mem don hang', 'module' => 'orders'],
            ['slug' => 'orders.restore', 'name' => 'Khoi phuc don hang', 'module' => 'orders'],
            ['slug' => 'orders.force_delete', 'name' => 'Xóa vĩnh viễn đơn hàng', 'module' => 'orders'],

            ['slug' => 'students.view', 'name' => 'Xem học viên', 'module' => 'students'],
            ['slug' => 'students.create', 'name' => 'Tạo học viên', 'module' => 'students'],
            ['slug' => 'students.edit', 'name' => 'Sửa học viên', 'module' => 'students'],
            ['slug' => 'students.delete', 'name' => 'Xóa học viên', 'module' => 'students'],
            ['slug' => 'students.soft_delete', 'name' => 'Xoa mem hoc vien', 'module' => 'students'],
            ['slug' => 'students.restore', 'name' => 'Khoi phuc hoc vien', 'module' => 'students'],
            ['slug' => 'students.force_delete', 'name' => 'Xóa vĩnh viễn học viên', 'module' => 'students'],
            ['slug' => 'students.logs', 'name' => 'Xem lịch sử học viên', 'module' => 'students'],

            ['slug' => 'users.view', 'name' => 'Xem người dùng', 'module' => 'users'],
            ['slug' => 'users.create', 'name' => 'Tạo người dùng', 'module' => 'users'],
            ['slug' => 'users.edit', 'name' => 'Sửa người dùng', 'module' => 'users'],
            ['slug' => 'users.delete', 'name' => 'Xóa người dùng', 'module' => 'users'],
            ['slug' => 'users.soft_delete', 'name' => 'Xoa mem nguoi dung', 'module' => 'users'],
            ['slug' => 'users.restore', 'name' => 'Khoi phuc nguoi dung', 'module' => 'users'],
            ['slug' => 'users.force_delete', 'name' => 'Xóa vĩnh viễn người dùng', 'module' => 'users'],
            ['slug' => 'users.logs', 'name' => 'Xem lịch sử người dùng', 'module' => 'users'],

            ['slug' => 'groups.view', 'name' => 'Xem nhóm quyền', 'module' => 'groups'],
            ['slug' => 'groups.create', 'name' => 'Tạo nhóm quyền', 'module' => 'groups'],
            ['slug' => 'groups.edit', 'name' => 'Sửa nhóm quyền', 'module' => 'groups'],
            ['slug' => 'groups.delete', 'name' => 'Xóa nhóm quyền', 'module' => 'groups'],
            ['slug' => 'groups.soft_delete', 'name' => 'Xoa mem nhom quyen', 'module' => 'groups'],
            ['slug' => 'groups.restore', 'name' => 'Khoi phuc nhom quyen', 'module' => 'groups'],
            ['slug' => 'groups.force_delete', 'name' => 'Xoa vinh vien nhom quyen', 'module' => 'groups'],
            ['slug' => 'groups.manage', 'name' => 'Quản lý nhóm quyền', 'module' => 'groups'],
            ['slug' => 'permissions.view', 'name' => 'Xem quyền chi tiết', 'module' => 'permissions'],
            ['slug' => 'permissions.create', 'name' => 'Tạo quyền chi tiết', 'module' => 'permissions'],
            ['slug' => 'permissions.edit', 'name' => 'Sửa quyền chi tiết', 'module' => 'permissions'],
            ['slug' => 'permissions.delete', 'name' => 'Xóa quyền chi tiết', 'module' => 'permissions'],
            ['slug' => 'permissions.soft_delete', 'name' => 'Xoa mem quyen chi tiet', 'module' => 'permissions'],
            ['slug' => 'permissions.restore', 'name' => 'Khoi phuc quyen chi tiet', 'module' => 'permissions'],
            ['slug' => 'permissions.force_delete', 'name' => 'Xoa vinh vien quyen chi tiet', 'module' => 'permissions'],
            ['slug' => 'permissions.manage', 'name' => 'Quản lý quyền chi tiết', 'module' => 'permissions'],

            ['slug' => 'coupons.view', 'name' => 'Xem mã giảm giá', 'module' => 'coupons'],
            ['slug' => 'coupons.create', 'name' => 'Tạo mã giảm giá', 'module' => 'coupons'],
            ['slug' => 'coupons.edit', 'name' => 'Sửa mã giảm giá', 'module' => 'coupons'],
            ['slug' => 'coupons.delete', 'name' => 'Xóa mã giảm giá', 'module' => 'coupons'],
            ['slug' => 'coupons.soft_delete', 'name' => 'Xoa mem ma giam gia', 'module' => 'coupons'],
            ['slug' => 'coupons.restore', 'name' => 'Khoi phuc ma giam gia', 'module' => 'coupons'],
            ['slug' => 'coupons.force_delete', 'name' => 'Xóa vĩnh viễn mã giảm giá', 'module' => 'coupons'],
            ['slug' => 'coupons.assign', 'name' => 'Gán mã giảm giá', 'module' => 'coupons'],
            ['slug' => 'coupons.logs', 'name' => 'Xem lịch sử mã giảm giá', 'module' => 'coupons'],

            ['slug' => 'contacts.view', 'name' => 'Xem liên hệ', 'module' => 'contacts'],
            ['slug' => 'contacts.update', 'name' => 'Tiếp nhận liên hệ', 'module' => 'contacts'],
            ['slug' => 'contacts.delete', 'name' => 'Xóa liên hệ', 'module' => 'contacts'],
            ['slug' => 'contacts.soft_delete', 'name' => 'Xoa mem lien he', 'module' => 'contacts'],
            ['slug' => 'contacts.restore', 'name' => 'Khoi phuc lien he', 'module' => 'contacts'],
            ['slug' => 'contacts.force_delete', 'name' => 'Xóa vĩnh viễn liên hệ', 'module' => 'contacts'],
            ['slug' => 'contacts.logs', 'name' => 'Xem lịch sử liên hệ', 'module' => 'contacts'],

            ['slug' => 'comments.moderate', 'name' => 'Kiểm duyệt bình luận', 'module' => 'comments'],

            ['slug' => 'settings.view', 'name' => 'Xem cấu hình', 'module' => 'settings'],
            ['slug' => 'settings.update', 'name' => 'Cập nhật cấu hình', 'module' => 'settings'],
            ['slug' => 'settings.logs', 'name' => 'Xem lịch sử cấu hình', 'module' => 'settings'],

            ['slug' => 'chatbot.view', 'name' => 'Xem tri thuc chatbot', 'module' => 'chatbot'],
            ['slug' => 'chatbot.create', 'name' => 'Them tri thuc chatbot', 'module' => 'chatbot'],
            ['slug' => 'chatbot.edit', 'name' => 'Sua tri thuc chatbot', 'module' => 'chatbot'],
            ['slug' => 'chatbot.delete', 'name' => 'Xoa tri thuc chatbot', 'module' => 'chatbot'],
            ['slug' => 'chatbot.logs', 'name' => 'Xem log chatbot', 'module' => 'chatbot'],

            ['slug' => 'logs.view', 'name' => 'Xem nhật ký hệ thống', 'module' => 'logs'],
        ];
    }

    public function run(): void
    {
        foreach (self::definitions() as $permission) {
            Permission::query()->updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $allPermissionIds = Permission::query()->pluck('id')->all();

        $adminPermissionIds = Permission::query()
            ->whereNotIn('module', ['groups', 'permissions'])
            ->pluck('id')
            ->all();

        Group::query()->where('slug', 'super_admin')->first()?->permissions()->sync($allPermissionIds);
        Group::query()->where('slug', 'admin')->first()?->permissions()->sync($adminPermissionIds);
    }
}






