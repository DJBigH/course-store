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
            ['slug' => 'courses.soft_delete', 'name' => 'Xóa mềm khóa học', 'module' => 'courses'],
            ['slug' => 'courses.force_delete', 'name' => 'Xóa vĩnh viễn khóa học', 'module' => 'courses'],

            ['slug' => 'lessons.view', 'name' => 'Xem bài giảng', 'module' => 'lessons'],
            ['slug' => 'lessons.create', 'name' => 'Thêm bài giảng', 'module' => 'lessons'],
            ['slug' => 'lessons.edit', 'name' => 'Sửa bài giảng', 'module' => 'lessons'],
            ['slug' => 'lessons.delete', 'name' => 'Xóa bài giảng', 'module' => 'lessons'],
            ['slug' => 'lessons.sort', 'name' => 'Sắp xếp bài giảng', 'module' => 'lessons'],
            ['slug' => 'lessons.manage', 'name' => 'Quản lý bài giảng', 'module' => 'lessons'],

            ['slug' => 'categories.view', 'name' => 'Xem danh mục', 'module' => 'categories'],
            ['slug' => 'categories.create', 'name' => 'Tạo danh mục', 'module' => 'categories'],
            ['slug' => 'categories.edit', 'name' => 'Sửa danh mục', 'module' => 'categories'],
            ['slug' => 'categories.delete', 'name' => 'Xóa danh mục', 'module' => 'categories'],
            ['slug' => 'categories.soft_delete', 'name' => 'Xóa mềm danh mục', 'module' => 'categories'],
            ['slug' => 'categories.force_delete', 'name' => 'Xóa vĩnh viễn danh mục', 'module' => 'categories'],
            ['slug' => 'categories.logs', 'name' => 'Xem lịch sử danh mục', 'module' => 'categories'],
            ['slug' => 'categories.manage', 'name' => 'Quản lý danh mục', 'module' => 'categories'],

            ['slug' => 'teachers.view', 'name' => 'Xem giảng viên', 'module' => 'teachers'],
            ['slug' => 'teachers.create', 'name' => 'Tạo giảng viên', 'module' => 'teachers'],
            ['slug' => 'teachers.edit', 'name' => 'Sửa giảng viên', 'module' => 'teachers'],
            ['slug' => 'teachers.delete', 'name' => 'Xóa giảng viên', 'module' => 'teachers'],
            ['slug' => 'teachers.soft_delete', 'name' => 'Xóa mềm giảng viên', 'module' => 'teachers'],
            ['slug' => 'teachers.force_delete', 'name' => 'Xóa vĩnh viễn giảng viên', 'module' => 'teachers'],
            ['slug' => 'teachers.logs', 'name' => 'Xem lịch sử giảng viên', 'module' => 'teachers'],
            ['slug' => 'teachers.manage', 'name' => 'Quản lý giảng viên', 'module' => 'teachers'],

            ['slug' => 'orders.view', 'name' => 'Xem đơn hàng', 'module' => 'orders'],
            ['slug' => 'orders.update', 'name' => 'Cập nhật đơn hàng', 'module' => 'orders'],
            ['slug' => 'orders.delete', 'name' => 'Xóa đơn hàng', 'module' => 'orders'],
            ['slug' => 'orders.soft_delete', 'name' => 'Xóa mềm đơn hàng', 'module' => 'orders'],
            ['slug' => 'orders.force_delete', 'name' => 'Xóa vĩnh viễn đơn hàng', 'module' => 'orders'],

            ['slug' => 'students.view', 'name' => 'Xem học viên', 'module' => 'students'],
            ['slug' => 'students.create', 'name' => 'Tạo học viên', 'module' => 'students'],
            ['slug' => 'students.edit', 'name' => 'Sửa học viên', 'module' => 'students'],
            ['slug' => 'students.delete', 'name' => 'Xóa học viên', 'module' => 'students'],
            ['slug' => 'students.soft_delete', 'name' => 'Xóa mềm học viên', 'module' => 'students'],
            ['slug' => 'students.force_delete', 'name' => 'Xóa vĩnh viễn học viên', 'module' => 'students'],
            ['slug' => 'students.logs', 'name' => 'Xem lịch sử học viên', 'module' => 'students'],
            ['slug' => 'students.manage', 'name' => 'Quản lý học viên', 'module' => 'students'],

            ['slug' => 'users.view', 'name' => 'Xem người dùng', 'module' => 'users'],
            ['slug' => 'users.create', 'name' => 'Tạo người dùng', 'module' => 'users'],
            ['slug' => 'users.edit', 'name' => 'Sửa người dùng', 'module' => 'users'],
            ['slug' => 'users.delete', 'name' => 'Xóa người dùng', 'module' => 'users'],
            ['slug' => 'users.soft_delete', 'name' => 'Xóa mềm người dùng', 'module' => 'users'],
            ['slug' => 'users.force_delete', 'name' => 'Xóa vĩnh viễn người dùng', 'module' => 'users'],
            ['slug' => 'users.logs', 'name' => 'Xem lịch sử người dùng', 'module' => 'users'],
            ['slug' => 'users.manage', 'name' => 'Quản lý người dùng', 'module' => 'users'],

            ['slug' => 'groups.view', 'name' => 'Xem nhóm quyền', 'module' => 'groups'],
            ['slug' => 'groups.create', 'name' => 'Tạo nhóm quyền', 'module' => 'groups'],
            ['slug' => 'groups.edit', 'name' => 'Sửa nhóm quyền', 'module' => 'groups'],
            ['slug' => 'groups.delete', 'name' => 'Xóa nhóm quyền', 'module' => 'groups'],
            ['slug' => 'groups.manage', 'name' => 'Quản lý nhóm quyền', 'module' => 'groups'],
            ['slug' => 'permissions.view', 'name' => 'Xem quyền chi tiết', 'module' => 'permissions'],
            ['slug' => 'permissions.create', 'name' => 'Tạo quyền chi tiết', 'module' => 'permissions'],
            ['slug' => 'permissions.edit', 'name' => 'Sửa quyền chi tiết', 'module' => 'permissions'],
            ['slug' => 'permissions.delete', 'name' => 'Xóa quyền chi tiết', 'module' => 'permissions'],
            ['slug' => 'permissions.manage', 'name' => 'Quản lý quyền chi tiết', 'module' => 'permissions'],

            ['slug' => 'coupons.view', 'name' => 'Xem mã giảm giá', 'module' => 'coupons'],
            ['slug' => 'coupons.create', 'name' => 'Tạo mã giảm giá', 'module' => 'coupons'],
            ['slug' => 'coupons.edit', 'name' => 'Sửa mã giảm giá', 'module' => 'coupons'],
            ['slug' => 'coupons.delete', 'name' => 'Xóa mã giảm giá', 'module' => 'coupons'],
            ['slug' => 'coupons.soft_delete', 'name' => 'Xóa mềm mã giảm giá', 'module' => 'coupons'],
            ['slug' => 'coupons.force_delete', 'name' => 'Xóa vĩnh viễn mã giảm giá', 'module' => 'coupons'],
            ['slug' => 'coupons.assign', 'name' => 'Gán mã giảm giá', 'module' => 'coupons'],
            ['slug' => 'coupons.logs', 'name' => 'Xem lịch sử mã giảm giá', 'module' => 'coupons'],
            ['slug' => 'coupons.manage', 'name' => 'Quản lý mã giảm giá', 'module' => 'coupons'],

            ['slug' => 'contacts.view', 'name' => 'Xem liên hệ', 'module' => 'contacts'],
            ['slug' => 'contacts.update', 'name' => 'Tiếp nhận liên hệ', 'module' => 'contacts'],
            ['slug' => 'contacts.delete', 'name' => 'Xóa liên hệ', 'module' => 'contacts'],
            ['slug' => 'contacts.soft_delete', 'name' => 'Xóa mềm liên hệ', 'module' => 'contacts'],
            ['slug' => 'contacts.force_delete', 'name' => 'Xóa vĩnh viễn liên hệ', 'module' => 'contacts'],
            ['slug' => 'contacts.logs', 'name' => 'Xem lịch sử liên hệ', 'module' => 'contacts'],
            ['slug' => 'contacts.manage', 'name' => 'Quản lý liên hệ', 'module' => 'contacts'],

            ['slug' => 'comments.moderate', 'name' => 'Kiểm duyệt bình luận', 'module' => 'comments'],

            ['slug' => 'settings.view', 'name' => 'Xem cấu hình', 'module' => 'settings'],
            ['slug' => 'settings.update', 'name' => 'Cập nhật cấu hình', 'module' => 'settings'],
            ['slug' => 'settings.logs', 'name' => 'Xem lịch sử cấu hình', 'module' => 'settings'],
            ['slug' => 'settings.manage', 'name' => 'Quản lý cấu hình', 'module' => 'settings'],

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

        $contentPermissionIds = Permission::query()
            ->whereIn('slug', [
                'dashboard.view',
                'courses.view',
                'courses.create',
                'courses.edit',
                'courses.publish',
                'courses.soft_delete',
                'courses.force_delete',
                'lessons.view',
                'lessons.create',
                'lessons.edit',
                'lessons.delete',
                'lessons.sort',
                'categories.view',
                'categories.create',
                'categories.edit',
                'categories.delete',
                'categories.soft_delete',
                'categories.force_delete',
                'categories.logs',
                'teachers.view',
                'teachers.create',
                'teachers.edit',
                'teachers.delete',
                'teachers.soft_delete',
                'teachers.force_delete',
                'teachers.logs',
                'coupons.view',
                'coupons.create',
                'coupons.edit',
                'coupons.delete',
                'coupons.soft_delete',
                'coupons.force_delete',
                'coupons.assign',
                'coupons.logs',
                'comments.moderate',
            ])
            ->pluck('id')
            ->all();

        $salePermissionIds = Permission::query()
            ->whereIn('slug', [
                'dashboard.view',
                'orders.view',
                'orders.update',
                'orders.delete',
                'orders.soft_delete',
                'orders.force_delete',
                'students.view',
                'students.create',
                'students.edit',
                'students.delete',
                'students.soft_delete',
                'students.force_delete',
                'students.logs',
                'coupons.view',
                'coupons.logs',
                'contacts.view',
                'contacts.update',
                'contacts.delete',
                'contacts.soft_delete',
                'contacts.force_delete',
                'contacts.logs',
            ])
            ->pluck('id')
            ->all();

        $supportPermissionIds = Permission::query()
            ->whereIn('slug', [
                'dashboard.view',
                'students.view',
                'students.logs',
                'contacts.view',
                'contacts.update',
                'contacts.logs',
                'comments.moderate',
            ])
            ->pluck('id')
            ->all();

        $teacherManagerPermissionIds = Permission::query()
            ->whereIn('slug', [
                'dashboard.view',
                'teachers.view',
                'teachers.create',
                'teachers.edit',
                'teachers.delete',
                'teachers.soft_delete',
                'teachers.force_delete',
                'teachers.logs',
                'courses.view',
                'courses.soft_delete',
                'lessons.view',
                'lessons.create',
                'lessons.edit',
                'lessons.delete',
                'lessons.sort',
                'comments.moderate',
            ])
            ->pluck('id')
            ->all();

        Group::query()->where('slug', 'super_admin')->first()?->permissions()->sync($allPermissionIds);
        Group::query()->where('slug', 'content')->first()?->permissions()->sync($contentPermissionIds);
        Group::query()->where('slug', 'sale')->first()?->permissions()->sync($salePermissionIds);
        Group::query()->where('slug', 'support')->first()?->permissions()->sync($supportPermissionIds);
        Group::query()->where('slug', 'teacher_manager')->first()?->permissions()->sync($teacherManagerPermissionIds);
    }
}
