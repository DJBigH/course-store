<?php

use Illuminate\Support\Facades\Route;
use Modules\Teacher\src\Http\Controllers\Admin\TeacherApplicationController as AdminTeacherApplicationController;
use Modules\Teacher\src\Http\Controllers\Admin\TeacherFinanceController;
use Modules\Teacher\src\Http\Controllers\Admin\TeacherPackageController as AdminTeacherPackageController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherApplicationController as ClientTeacherApplicationController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherDashboardController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherLandingController;

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
      Route::delete('/delete/{id}', [AdminTeacherPackageController::class, 'delete'])->middleware('permission:teachers.delete')->name('delete');
   });

   Route::prefix('teacher-finance')->name('teacher-finance.')->group(function () {
      Route::get('/earnings', [TeacherFinanceController::class, 'earnings'])->middleware('permission:teachers.view')->name('earnings');
      Route::get('/earnings/export/{format}', [TeacherFinanceController::class, 'exportEarnings'])->middleware('permission:teachers.view')->name('earnings.export');
      Route::get('/payouts', [TeacherFinanceController::class, 'payouts'])->middleware('permission:teachers.view')->name('payouts');
      Route::get('/payouts/export/{format}', [TeacherFinanceController::class, 'exportPayouts'])->middleware('permission:teachers.view')->name('payouts.export');
      Route::post('/payouts/{id}', [TeacherFinanceController::class, 'updatePayout'])->middleware('permission:teachers.edit')->name('payouts.update');
   });
});

Route::group([
   'prefix' => '{locale}',
   'where' => ['locale' => 'vi|en|ko|ja|zh'],
   'middleware' => ['setLocale'],
], function () {
   Route::get('/tro-thanh-giang-vien', [TeacherLandingController::class, 'index'])->name('teacher.portal.index');
   Route::get('/tro-thanh-giang-vien/bat-dau', [ClientTeacherApplicationController::class, 'begin'])->name('teacher.account.begin');
   Route::get('/tro-thanh-giang-vien/dang-ky', [ClientTeacherApplicationController::class, 'create'])->name('teacher.account.apply');
   Route::post('/tro-thanh-giang-vien/dang-ky', [ClientTeacherApplicationController::class, 'store'])->name('teacher.account.submit');
   Route::post('/tro-thanh-giang-vien/dang-ky/preview-coupon', [ClientTeacherApplicationController::class, 'previewCoupon'])->name('teacher.account.preview-coupon');
   Route::post('/tro-thanh-giang-vien/dang-ky/clear-coupon', [ClientTeacherApplicationController::class, 'clearCouponPreview'])->name('teacher.account.clear-coupon');
   Route::get('/tro-thanh-giang-vien/trang-thai', [ClientTeacherApplicationController::class, 'status'])->name('teacher.account.status');
   Route::get('/tro-thanh-giang-vien/chinh-sua', [ClientTeacherApplicationController::class, 'edit'])->name('teacher.account.edit');
   Route::post('/tro-thanh-giang-vien/chinh-sua', [ClientTeacherApplicationController::class, 'update'])->name('teacher.account.update');
   Route::post('/tro-thanh-giang-vien/xac-nhan-da-thanh-toan', [ClientTeacherApplicationController::class, 'markPaid'])->name('teacher.account.mark-paid');
});

Route::group([
   'prefix' => 'teacher',
   'as' => 'teacher.dashboard.',
   'middleware' => ['setLocale', 'auth:students', 'verified', 'user.block'],
], function () {
   Route::get('/', [TeacherDashboardController::class, 'index'])->name('index');
   Route::get('/khoa-hoc', [TeacherDashboardController::class, 'courses'])->name('courses');
   Route::get('/doanh-thu', [TeacherDashboardController::class, 'earnings'])->name('earnings');
   Route::get('/rut-tien', [TeacherDashboardController::class, 'payouts'])->name('payouts');
   Route::post('/rut-tien', [TeacherDashboardController::class, 'storePayout'])->name('payouts.store');
});
