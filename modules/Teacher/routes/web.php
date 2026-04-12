<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Teacher\src\Http\Controllers\Admin\TeacherApplicationController as AdminTeacherApplicationController;
use Modules\Teacher\src\Http\Controllers\Admin\TeacherAnnouncementController as AdminTeacherAnnouncementController;
use Modules\Teacher\src\Http\Controllers\Admin\TeacherFinanceController;
use Modules\Teacher\src\Http\Controllers\Admin\TeacherPackageController as AdminTeacherPackageController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherApplicationController as ClientTeacherApplicationController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherAffiliateLinkController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherAuthController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherCouponController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherCertificateController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherDashboardController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherLandingController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherPublicController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherProfileController;

Route::prefix('admin')->group(function () {
   Route::prefix('teacher')->name('teacher.')->group(function () {
      Route::get('/', 'TeacherController@index')->middleware('permission:teachers.view,teachers.create,teachers.edit,teachers.delete,teachers.soft_delete,teachers.force_delete,teachers.logs')->name('index');
      Route::get('/trash', 'TeacherController@trash')->middleware('permission:teachers.view,teachers.soft_delete,teachers.force_delete')->name('trash');
      Route::get('data', 'TeacherController@data')->middleware('permission:teachers.view,teachers.create,teachers.edit,teachers.delete,teachers.soft_delete,teachers.force_delete,teachers.logs')->name('data');
      Route::get('/trash/data', 'TeacherController@trashData')->middleware('permission:teachers.view,teachers.soft_delete,teachers.force_delete')->name('trash.data');
      Route::post('/bulk', 'TeacherController@bulkAction')->middleware('permission:teachers.delete,teachers.soft_delete')->name('bulk');
      Route::post('/trash/bulk', 'TeacherController@trashBulkAction')->middleware('permission:teachers.delete,teachers.soft_delete,teachers.force_delete')->name('trash.bulk');
      Route::get('/create', 'TeacherController@create')->middleware('permission:teachers.create')->name('add');
      Route::post('/create', 'TeacherController@store')->middleware('permission:teachers.create')->name('post-add');
      Route::post('/restore/{teacher}', 'TeacherController@restore')->middleware('permission:teachers.restore,teachers.delete,teachers.soft_delete')->name('restore');
      Route::delete('/force-delete/{teacher}', 'TeacherController@forceDelete')->middleware('permission:teachers.force_delete')->name('force-delete');
      Route::get('/edit/{teacher}', 'TeacherController@edit')->middleware('permission:teachers.edit')->name('edit');
      Route::post('/edit/{teacher}', 'TeacherController@update')->middleware('permission:teachers.edit')->name('post-edit');
      Route::delete('/delete/{teacher}', 'TeacherController@delete')->middleware('permission:teachers.delete,teachers.soft_delete')->name('delete');
      Route::get('logs/{teacher}', 'TeacherController@logs')->middleware('permission:teachers.logs')->name('logs');
   });

   Route::prefix('teacher-applications')->name('teacher-applications.')->group(function () {
      Route::get('/', [AdminTeacherApplicationController::class, 'index'])->middleware('permission:teachers.view')->name('index');
      Route::get('/{id}', [AdminTeacherApplicationController::class, 'show'])->middleware('permission:teachers.view')->name('show');
      Route::post('/{id}/approve', [AdminTeacherApplicationController::class, 'approve'])->middleware('permission:teachers.edit')->name('approve');
      Route::post('/{id}/reject', [AdminTeacherApplicationController::class, 'reject'])->middleware('permission:teachers.edit')->name('reject');
   });

   Route::prefix('teacher-packages')->name('teacher-packages.')->group(function () {
      Route::get('/', [AdminTeacherPackageController::class, 'index'])->middleware('permission:teachers.view')->name('index');
      Route::get('/create', [AdminTeacherPackageController::class, 'create'])->middleware('permission:teachers.create')->name('add');
      Route::post('/create', [AdminTeacherPackageController::class, 'store'])->middleware('permission:teachers.create')->name('post-add');
      Route::get('/edit/{id}', [AdminTeacherPackageController::class, 'edit'])->middleware('permission:teachers.edit')->name('edit');
      Route::post('/edit/{id}', [AdminTeacherPackageController::class, 'update'])->middleware('permission:teachers.edit')->name('post-edit');
      Route::post('/reorder', [AdminTeacherPackageController::class, 'reorder'])->middleware('permission:teachers.edit')->name('reorder');
      Route::delete('/delete/{id}', [AdminTeacherPackageController::class, 'delete'])->middleware('permission:teachers.delete')->name('delete');
   });

   Route::prefix('teacher-announcements')->name('teacher-announcements.')->group(function () {
      Route::get('/', [AdminTeacherAnnouncementController::class, 'index'])->middleware('permission:teachers.view')->name('index');
      Route::get('/create', [AdminTeacherAnnouncementController::class, 'create'])->middleware('permission:teachers.edit')->name('add');
      Route::post('/create', [AdminTeacherAnnouncementController::class, 'store'])->middleware('permission:teachers.edit')->name('post-add');
      Route::get('/edit/{id}', [AdminTeacherAnnouncementController::class, 'edit'])->middleware('permission:teachers.edit')->name('edit');
      Route::post('/edit/{id}', [AdminTeacherAnnouncementController::class, 'update'])->middleware('permission:teachers.edit')->name('post-edit');
      Route::delete('/delete/{id}', [AdminTeacherAnnouncementController::class, 'delete'])->middleware('permission:teachers.delete')->name('delete');
   });

   Route::prefix('teacher-finance')->name('teacher-finance.')->group(function () {
      Route::get('/earnings', [TeacherFinanceController::class, 'earnings'])->middleware('permission:teachers.view')->name('earnings');
      Route::get('/earnings/export/{format}', [TeacherFinanceController::class, 'exportEarnings'])->middleware('permission:teachers.view')->name('earnings.export');
      Route::get('/payouts', [TeacherFinanceController::class, 'payouts'])->middleware('permission:teachers.view')->name('payouts');
      Route::get('/payouts/export/{format}', [TeacherFinanceController::class, 'exportPayouts'])->middleware('permission:teachers.view')->name('payouts.export');
      Route::post('/payouts/{id}', [TeacherFinanceController::class, 'updatePayout'])->middleware('permission:teachers.edit')->name('payouts.update');
      Route::post('/payout-account-change-requests/{id}', [TeacherFinanceController::class, 'updatePayoutAccountChangeRequest'])->middleware('permission:teachers.edit')->name('payout-account-change-requests.update');
   });
});

Route::group([
   'prefix' => '{locale}',
   'where' => ['locale' => 'vi|en|ko|ja|zh'],
   'middleware' => ['setLocale'],
], function () {
   Route::get('/tro-thanh-giang-vien', [TeacherLandingController::class, 'index'])->name('teacher.portal.index');
   Route::get('/teacher/login', [TeacherAuthController::class, 'showLoginForm'])->name('teacher.auth.login');
   Route::post('/teacher/login', [TeacherAuthController::class, 'login'])->name('teacher.auth.post-login');
   Route::get('/teacher/forgot-password', [TeacherAuthController::class, 'showForgotForm'])->name('teacher.auth.forgot');
   Route::post('/teacher/forgot-password', [TeacherAuthController::class, 'sendResetLink'])->name('teacher.auth.post-forgot');
   Route::get('/teacher/reset-password/{token}', [TeacherAuthController::class, 'showResetForm'])->name('teacher.password.reset');
   Route::post('/teacher/reset-password', [TeacherAuthController::class, 'updatePassword'])->name('teacher.auth.password.update');
   Route::get('/tro-thanh-giang-vien/bat-dau', [ClientTeacherApplicationController::class, 'begin'])->name('teacher.account.begin');
   Route::get('/tro-thanh-giang-vien/dang-ky', [ClientTeacherApplicationController::class, 'create'])->name('teacher.account.apply');
   Route::post('/tro-thanh-giang-vien/dang-ky', [ClientTeacherApplicationController::class, 'store'])->name('teacher.account.submit');
   Route::post('/tro-thanh-giang-vien/dang-ky/preview-coupon', [ClientTeacherApplicationController::class, 'previewCoupon'])->name('teacher.account.preview-coupon');
   Route::post('/tro-thanh-giang-vien/dang-ky/clear-coupon', [ClientTeacherApplicationController::class, 'clearCouponPreview'])->name('teacher.account.clear-coupon');
   Route::get('/tro-thanh-giang-vien/trang-thai', [ClientTeacherApplicationController::class, 'status'])->name('teacher.account.status');
   Route::get('/tro-thanh-giang-vien/chinh-sua', [ClientTeacherApplicationController::class, 'edit'])->name('teacher.account.edit');
   Route::post('/tro-thanh-giang-vien/chinh-sua', [ClientTeacherApplicationController::class, 'update'])->name('teacher.account.update');
   Route::post('/tro-thanh-giang-vien/xac-nhan-da-thanh-toan', [ClientTeacherApplicationController::class, 'markPaid'])->name('teacher.account.mark-paid');
   Route::get('/giang-vien/{slug}', [TeacherPublicController::class, 'show'])->name('teacher.public.show');
   Route::post('/giang-vien/{slug}/rating', [TeacherPublicController::class, 'rate'])->middleware(['auth:students', 'verified', 'user.block'])->name('teacher.public.rate');
});

Route::group([
   'prefix' => 'teacher',
   'as' => 'teacher.dashboard.',
   'middleware' => ['setLocale', 'auth:students', 'verified', 'user.block', 'teacher.active', 'teacher.activity'],
], function () {
   Route::get('/locale/{locale}', function (Request $request, string $locale) {
      if (!in_array($locale, ['vi', 'en', 'ko', 'ja', 'zh'], true)) {
         $locale = 'vi';
      }

      session(['locale' => $locale]);
      app()->setLocale($locale);

      $redirect = (string) $request->query('redirect', route('teacher.dashboard.index'));
      $fallback = route('teacher.dashboard.index');

      if (!str_starts_with($redirect, url('/')) && !str_starts_with($redirect, '/')) {
         $redirect = $fallback;
      }

      return redirect()->to($redirect);
   })->name('locale');

   Route::get('/', [TeacherDashboardController::class, 'index'])->name('index');
   Route::get('/ho-so', [TeacherProfileController::class, 'show'])->name('profile');
   Route::post('/ho-so', [TeacherProfileController::class, 'update'])->name('profile.update');
   Route::get('/goi/nang-cap', [TeacherDashboardController::class, 'upgradePackage'])->name('package.upgrade');
   Route::post('/goi/nang-cap', [TeacherDashboardController::class, 'storeUpgradePackage'])->name('package.upgrade.store');
   Route::get('/goi/nang-cap/trang-thai', [TeacherDashboardController::class, 'upgradePackageStatus'])->name('package.upgrade.status');
   Route::post('/goi/nang-cap/xac-nhan-da-thanh-toan', [TeacherDashboardController::class, 'markUpgradePaid'])->name('package.upgrade.mark-paid');
   Route::post('/goi/nang-cap/huy', [TeacherDashboardController::class, 'cancelUpgradePackage'])->name('package.upgrade.cancel');
   Route::get('/thong-bao', [TeacherDashboardController::class, 'notifications'])->name('notifications');
   Route::get('/thong-bao/doc', [TeacherDashboardController::class, 'readNotification'])->name('notifications.read');
   Route::get('/thong-bao/announcement/{announcement}/doc', [TeacherDashboardController::class, 'readAnnouncement'])->name('notifications.announcements.read');
   Route::get('/khuyen-mai', [TeacherDashboardController::class, 'promotions'])->name('promotions');
   Route::post('/khuyen-mai', [TeacherDashboardController::class, 'storePromotion'])->name('promotions.store');
   Route::get('/link-gioi-thieu', [TeacherAffiliateLinkController::class, 'index'])->name('affiliate-links.index');
   Route::get('/link-gioi-thieu/tao-moi', [TeacherAffiliateLinkController::class, 'create'])->name('affiliate-links.create');
   Route::post('/link-gioi-thieu/tao-moi', [TeacherAffiliateLinkController::class, 'store'])->name('affiliate-links.store');
   Route::get('/link-gioi-thieu/{id}/chinh-sua', [TeacherAffiliateLinkController::class, 'edit'])->name('affiliate-links.edit');
   Route::post('/link-gioi-thieu/{id}/chinh-sua', [TeacherAffiliateLinkController::class, 'update'])->name('affiliate-links.update');
   Route::delete('/link-gioi-thieu/{id}', [TeacherAffiliateLinkController::class, 'delete'])->name('affiliate-links.delete');
   Route::get('/combo-khoa-hoc', [TeacherDashboardController::class, 'bundles'])->name('bundles');
   Route::get('/combo-khoa-hoc/tao-moi', [TeacherDashboardController::class, 'createBundle'])->name('bundles.create');
   Route::post('/combo-khoa-hoc/tao-moi', [TeacherDashboardController::class, 'storeBundle'])->name('bundles.store');
   Route::get('/combo-khoa-hoc/{bundle}/chinh-sua', [TeacherDashboardController::class, 'editBundle'])->name('bundles.edit');
   Route::post('/combo-khoa-hoc/{bundle}/chinh-sua', [TeacherDashboardController::class, 'updateBundle'])->name('bundles.update');
   Route::delete('/combo-khoa-hoc/{bundle}', [TeacherDashboardController::class, 'deleteBundle'])->name('bundles.delete');
   Route::get('/khoa-hoc', [TeacherDashboardController::class, 'courses'])->name('courses');
   Route::get('/ma-giam-gia', [TeacherCouponController::class, 'index'])->name('coupons.index');
   Route::get('/ma-giam-gia/tao-moi', [TeacherCouponController::class, 'create'])->name('coupons.create');
   Route::post('/ma-giam-gia/tao-moi', [TeacherCouponController::class, 'store'])->name('coupons.store');
   Route::get('/ma-giam-gia/{id}/chinh-sua', [TeacherCouponController::class, 'edit'])->name('coupons.edit');
   Route::post('/ma-giam-gia/{id}/chinh-sua', [TeacherCouponController::class, 'update'])->name('coupons.update');
   Route::post('/ma-giam-gia/{id}/uu-tien', [TeacherCouponController::class, 'togglePriority'])->name('coupons.priority');
   Route::delete('/ma-giam-gia/{id}', [TeacherCouponController::class, 'delete'])->name('coupons.delete');
   Route::get('/ma-giam-gia/{id}/hoc-vien', [TeacherCouponController::class, 'students'])->name('coupons.students');
   Route::post('/ma-giam-gia/{id}/hoc-vien', [TeacherCouponController::class, 'updateStudents'])->name('coupons.students.update');
   Route::get('/ma-giam-gia/{id}/khoa-hoc', [TeacherCouponController::class, 'courses'])->name('coupons.courses');
   Route::post('/ma-giam-gia/{id}/khoa-hoc', [TeacherCouponController::class, 'updateCourses'])->name('coupons.courses.update');
   Route::get('/binh-luan', [TeacherDashboardController::class, 'comments'])->name('comments');
   Route::post('/binh-luan/{comment}/reply', [TeacherDashboardController::class, 'replyComment'])->name('comments.reply');
   Route::post('/binh-luan/{comment}/toggle', [TeacherDashboardController::class, 'toggleCommentVisibility'])->name('comments.toggle');
   Route::get('/hoc-vien', [TeacherDashboardController::class, 'students'])->name('students');
   Route::get('/chung-chi', [TeacherCertificateController::class, 'index'])->name('certificates.index');
   Route::post('/chung-chi/cap', [TeacherCertificateController::class, 'issue'])->name('certificates.issue');
   Route::get('/chung-chi/{id}', [TeacherCertificateController::class, 'show'])->name('certificates.show');
   Route::post('/chung-chi/{id}/thu-hoi', [TeacherCertificateController::class, 'revoke'])->name('certificates.revoke');
   Route::get('/nhat-ky-hoat-dong', [TeacherDashboardController::class, 'activityLogs'])->name('activity-logs');
   Route::get('/don-hang', [TeacherDashboardController::class, 'orders'])->name('orders');
   Route::get('/don-hang/export/{format}', [TeacherDashboardController::class, 'exportOrders'])->name('orders.export');
   Route::get('/don-hang/{order}', [TeacherDashboardController::class, 'showOrder'])->name('orders.show');
   Route::get('/hoc-vien/cap-quyen', [TeacherDashboardController::class, 'createStudentGrant'])->name('students.grants.create');
   Route::post('/hoc-vien/cap-quyen', [TeacherDashboardController::class, 'storeStudentGrant'])->name('students.grants.store');
   Route::get('/hoc-vien/export/{format}', [TeacherDashboardController::class, 'exportStudents'])->name('students.export');
   Route::post('/hoc-vien/{student}/grant/{grant}/thu-hoi', [TeacherDashboardController::class, 'revokeStudentGrant'])->name('students.grants.revoke');
   Route::get('/hoc-vien/{student}', [TeacherDashboardController::class, 'showStudent'])->name('students.show');
   Route::post('/hoc-vien/{student}/ghi-chu', [TeacherDashboardController::class, 'saveStudentNote'])->name('students.note');
   Route::get('/khoa-hoc/thung-rac', [TeacherDashboardController::class, 'coursesTrash'])->name('courses.trash');
   Route::get('/khoa-hoc/tao-moi', [TeacherDashboardController::class, 'createCourse'])->name('courses.create');
   Route::post('/khoa-hoc/tao-moi', [TeacherDashboardController::class, 'storeCourse'])->name('courses.store');
   Route::get('/khoa-hoc/{course}/chinh-sua', [TeacherDashboardController::class, 'editCourse'])->name('courses.edit');
   Route::post('/khoa-hoc/{course}/chinh-sua', [TeacherDashboardController::class, 'updateCourse'])->name('courses.update');
   Route::post('/khoa-hoc/{course}/uu-tien', [TeacherDashboardController::class, 'toggleCoursePriority'])->name('courses.priority');
   Route::post('/khoa-hoc/{course}/trang-thai', [TeacherDashboardController::class, 'updateCourseVisibility'])->name('courses.visibility');
   Route::post('/khoa-hoc/{course}/nhan-ban', [TeacherDashboardController::class, 'duplicateCourse'])->name('courses.duplicate');
   Route::delete('/khoa-hoc/{course}', [TeacherDashboardController::class, 'deleteCourse'])->name('courses.delete');
   Route::post('/khoa-hoc/{course}/khoi-phuc', [TeacherDashboardController::class, 'restoreCourse'])->name('courses.restore');
   Route::delete('/khoa-hoc/{course}/xoa-vinh-vien', [TeacherDashboardController::class, 'forceDeleteCourse'])->name('courses.force-delete');
   Route::get('/khoa-hoc/{course}/bai-hoc', [TeacherDashboardController::class, 'lessons'])->name('lessons.index');
   Route::get('/khoa-hoc/{course}/bai-hoc/thung-rac', [TeacherDashboardController::class, 'lessonsTrash'])->name('lessons.trash');
   Route::get('/khoa-hoc/{course}/bai-hoc/tao-moi', [TeacherDashboardController::class, 'createLesson'])->name('lessons.create');
   Route::post('/khoa-hoc/{course}/bai-hoc/tao-moi', [TeacherDashboardController::class, 'storeLesson'])->name('lessons.store');
   Route::get('/khoa-hoc/{course}/bai-hoc/{lesson}/chinh-sua', [TeacherDashboardController::class, 'editLesson'])->name('lessons.edit');
   Route::post('/khoa-hoc/{course}/bai-hoc/{lesson}/chinh-sua', [TeacherDashboardController::class, 'updateLesson'])->name('lessons.update');
   Route::delete('/khoa-hoc/{course}/bai-hoc/{lesson}', [TeacherDashboardController::class, 'deleteLesson'])->name('lessons.delete');
   Route::post('/khoa-hoc/{course}/bai-hoc/{lesson}/khoi-phuc', [TeacherDashboardController::class, 'restoreLesson'])->name('lessons.restore');
   Route::delete('/khoa-hoc/{course}/bai-hoc/{lesson}/xoa-vinh-vien', [TeacherDashboardController::class, 'forceDeleteLesson'])->name('lessons.force-delete');
   Route::get('/doanh-thu', [TeacherDashboardController::class, 'earnings'])->name('earnings');
   Route::get('/rut-tien', [TeacherDashboardController::class, 'payouts'])->name('payouts');
   Route::post('/rut-tien', [TeacherDashboardController::class, 'storePayout'])->name('payouts.store');
   Route::post('/rut-tien/yeu-cau-doi-tai-khoan', [TeacherDashboardController::class, 'storePayoutAccountChangeRequest'])->name('payouts.account-change.store');
   Route::get('/gop-y-bao-cao', [TeacherDashboardController::class, 'support'])->name('support');
   Route::post('/gop-y-bao-cao', [TeacherDashboardController::class, 'storeSupport'])->name('support.store');
});
