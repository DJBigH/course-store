<?php

use Illuminate\Support\Facades\Route;
use Modules\Students\src\Http\Controllers\Clients\TwoFactorController;

Route::prefix('admin')->group(function () {
   Route::prefix('students')->name('students.')->group(function () {
      Route::get('/', 'StudentController@index')->name('index');
      Route::get('data', 'StudentController@data')->name('data');
      Route::get('/create', 'StudentController@create')->name('add');
      Route::post('/create', 'StudentController@store')->name('post-add');
      Route::get('/edit/{student}', 'StudentController@edit')->name('edit');
      Route::post('/edit/{student}', 'StudentController@update')->name('post-edit');
      Route::delete('/delete/{student}', 'StudentController@delete')->name('delete');
      Route::get('/{id}/coupon-history', 'StudentController@CouponHistory')->name('coupon-history');
      Route::get('/{id}/purchased-courses', 'StudentController@purchasedCourses')->name('purchased-courses');
      Route::get('logs/{student}', 'StudentController@logs')->name('logs');
   });
});

Route::group(['as' => 'students.'], function () {
   Route::group(['prefix' => '{locale}/tai-khoan', 'where' => ['locale' => 'vi|en|ko|ja|zh'], 'as' => 'account.', 'middleware' => ['setLocale','auth:students', 'verified', 'user.block']], function () {
      Route::get('/', 'Clients\AccountController@index')->name('index');
      Route::get('/thong-tin', 'Clients\AccountController@profile')->name('profile');
      Route::post('/thong-tin', 'Clients\AccountController@updateProfile')->name('client-updateprofile');
      Route::post('/bao-mat-2-lop/bat', [TwoFactorController::class, 'startEnable'])->name('two-factor.enable');
      Route::post('/bao-mat-2-lop/tat', [TwoFactorController::class, 'startDisable'])->name('two-factor.disable');
      Route::get('/vo-hieu-hoa', 'Clients\AccountController@deactivateConfirm')->name('deactivate');
      Route::post('/vo-hieu-hoa/xac-thuc', [TwoFactorController::class, 'startDeactivate'])->name('deactivate-start-2fa');
      Route::post('/vo-hieu-hoa', 'Clients\AccountController@deactivate')->name('deactivate-submit');
      Route::get('/khoa-hoc', 'Clients\AccountController@showMyCourse')->name('my-courses');
      Route::get('/ma-giam-gia', 'Clients\AccountController@myCoupon')->name('my-coupon');
      Route::get('/don-hang', 'Clients\AccountController@myOrder')->name('my-order');
      Route::get('/don-hang/{id}', 'Clients\AccountController@detailOrder')->name('order-detail');
      Route::get('/doi-mat-khau', 'Clients\AccountController@showChangePassword')->middleware('student.2fa:change-password-page')->name('change-password');
      Route::post('/doi-mat-khau', 'Clients\AccountController@updatePassword')->middleware('student.2fa')->name('change-postpassword');
      Route::get('/lich-su-hoat-dong', 'Clients\AccountController@activityHistory')->name('activity-history');
      Route::get('/thanh-toan/{id}', 'Clients\CheckoutController@index')->name('checkout');
      Route::post('/thanh-toan/{id}/hoan-tat', 'Clients\CheckoutController@complete')->name('checkout-payment');
      Route::post('/thanh-toan/{id}/huy', 'Clients\CheckoutController@cancel')->name('checkout-cancel');
      Route::post('/thanh-toan/{id}/vnpay', 'Clients\CheckoutController@vnpay')->name('checkout-vnpay');
      Route::post('/thanh-toan/{id}/momo', 'Clients\CheckoutController@momo')->name('checkout-momo');

      Route::prefix('coupons')->group(function () {
         Route::post('/verify', 'Clients\CouponsController@verify')->name('coupons');
         Route::post('/remove', 'Clients\CouponsController@remove')->name('coupons-remove');
         Route::post('/polling', 'Clients\CouponsController@pollingCoupon')->name('coupons-pollingCoupon');
      });

      Route::group([], function () {
         Route::prefix('checkout')->group(function () {
            Route::get('/cam-on/{id}', 'Clients\CheckoutController@thankyou')->name('checkout-thankyou');
         });
      });
   });
});

Route::group([
   'prefix' => '{locale}/tai-khoan/thanh-toan/vnpay',
   'where' => ['locale' => 'vi|en|ko|ja|zh'],
   'as' => 'students.account.',
   'middleware' => ['setLocale'],
], function () {
   Route::get('/return', 'Clients\CheckoutController@vnpayReturn')->name('checkout-vnpay-return');
   Route::match(['get', 'post'], '/ipn', 'Clients\CheckoutController@vnpayIpn')->name('checkout-vnpay-ipn');
});

Route::group([
   'prefix' => '{locale}/tai-khoan/thanh-toan/momo',
   'where' => ['locale' => 'vi|en|ko|ja|zh'],
   'as' => 'students.account.',
   'middleware' => ['setLocale'],
], function () {
   Route::get('/return', 'Clients\CheckoutController@momoReturn')->name('checkout-momo-return');
   Route::match(['get', 'post'], '/ipn', 'Clients\CheckoutController@momoIpn')->name('checkout-momo-ipn');
});

Route::group([
   'prefix' => '{locale}/tai-khoan',
   'where' => ['locale' => 'vi|en|ko|ja|zh'],
   'as' => 'students.account.',
   'middleware' => ['setLocale', 'auth:students', 'user.block'],
], function () {
   Route::get('/vo-hieu-hoa/thanh-cong', 'Clients\AccountController@deactivateSuccess')->name('deactivate-success');
});

Route::get('students/notifications/read/{id}', function ($id) {
   $notification = auth('students')->user()
      ->notifications()
      ->where('id', $id)
      ->firstOrFail();

   $notification->markAsRead();

   return redirect($notification->data['url'] ?? '/');
})->middleware(['auth:students', 'verified', 'user.block'])->name('students.notifications.read');

Route::get('students/notifications', function () {
   return view('students.notifications.index', [
      'pageTitle' => 'Thông báo',
      'pageName' => 'Thông báo',
      'notifications' => auth('students')->user()->notifications()->latest()->paginate(100),
   ]);
})->middleware(['auth:students', 'verified', 'user.block'])->name('students.notifications.index');
