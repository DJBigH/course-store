<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Students\src\Http\Controllers\Clients\GiftCourseController;
use Modules\Students\src\Http\Controllers\Clients\StudentCertificateController;
use Modules\Students\src\Http\Controllers\Clients\TwoFactorController;

Route::prefix('admin')->group(function () {
   Route::prefix('students')->name('students.')->group(function () {
      Route::get('/', 'StudentController@index')->middleware('permission:students.view,students.create,students.edit,students.delete,students.soft_delete,students.force_delete,students.logs')->name('index');
      Route::get('/trash', 'StudentController@trash')->middleware('permission:students.view,students.soft_delete,students.force_delete')->name('trash');
      Route::get('data', 'StudentController@data')->middleware('permission:students.view,students.create,students.edit,students.delete,students.soft_delete,students.force_delete,students.logs')->name('data');
      Route::get('/trash/data', 'StudentController@trashData')->middleware('permission:students.view,students.soft_delete,students.force_delete')->name('trash.data');
      Route::post('/bulk', 'StudentController@bulkAction')->middleware('permission:students.edit,students.delete,students.soft_delete')->name('bulk');
      Route::post('/trash/bulk', 'StudentController@trashBulkAction')->middleware('permission:students.delete,students.soft_delete,students.force_delete')->name('trash.bulk');
      Route::get('/create', 'StudentController@create')->middleware('permission:students.create')->name('add');
      Route::post('/create', 'StudentController@store')->middleware('permission:students.create')->name('post-add');
      Route::post('/restore/{student}', 'StudentController@restore')->middleware('permission:students.restore,students.delete,students.soft_delete')->name('restore');
      Route::delete('/force-delete/{student}', 'StudentController@forceDelete')->middleware('permission:students.force_delete')->name('force-delete');
      Route::get('/edit/{student}', 'StudentController@edit')->middleware('permission:students.edit')->name('edit');
      Route::post('/edit/{student}', 'StudentController@update')->middleware('permission:students.edit')->name('post-edit');
      Route::delete('/delete/{student}', 'StudentController@delete')->middleware('permission:students.delete,students.soft_delete')->name('delete');
      Route::get('/{id}/coupon-history', 'StudentController@CouponHistory')->middleware('permission:students.view')->name('coupon-history');
      Route::get('/{id}/purchased-courses', 'StudentController@purchasedCourses')->middleware('permission:students.view')->name('purchased-courses');
      Route::get('/search-courses', 'StudentController@searchCourses')->middleware('permission:students.view')->name('search-courses');
      Route::post('/{id}/grant-course', 'StudentController@grantCourse')->middleware('permission:students.grant_course')->name('grant-course');
      Route::post('/{id}/revoke-course', 'StudentController@revokeCourse')->middleware('permission:students.grant_course')->name('revoke-course');
      Route::get('logs/{student}', 'StudentController@logs')->middleware('permission:students.logs')->name('logs');
      Route::get('impersonate/{student}', 'StudentController@impersonate')->middleware('permission:students.impersonate')->name('impersonate');
   });
});

Route::get('stop-impersonate', 'Modules\Students\src\Http\Controllers\StudentController@stopImpersonating')->name('students.stop-impersonate');

Route::group(['as' => 'students.'], function () {
   Route::group(['prefix' => '{locale}/tai-khoan', 'where' => ['locale' => 'vi|en|ko|ja|zh'], 'as' => 'account.', 'middleware' => ['setLocale', 'auth:students', 'verified', 'user.block']], function () {
      Route::get('/', 'Clients\AccountController@index')->name('index');
      Route::get('/thong-tin', 'Clients\AccountController@profile')->name('profile');
      Route::post('/thong-tin', 'Clients\AccountController@updateProfile')->name('client-updateprofile');
      Route::post('/bao-mat-2-lop/bat', [TwoFactorController::class, 'startEnable'])->name('two-factor.enable');
      Route::post('/bao-mat-2-lop/tat', [TwoFactorController::class, 'startDisable'])->name('two-factor.disable');
      Route::get('/xoa-tai-khoan', 'Clients\AccountController@deleteConfirm')->name('delete');
      Route::post('/xoa-tai-khoan/xac-thuc', [TwoFactorController::class, 'startDelete'])->name('delete-start-2fa');
      Route::get('/vo-hieu-hoa', 'Clients\AccountController@deactivateConfirm')->name('deactivate');
      Route::post('/vo-hieu-hoa/xac-thuc', [TwoFactorController::class, 'startDeactivate'])->name('deactivate-start-2fa');
      Route::post('/vo-hieu-hoa', 'Clients\AccountController@deactivate')->name('deactivate-submit');
      Route::get('/khoa-hoc', 'Clients\AccountController@showMyCourse')->name('my-courses');
      Route::get('/chung-chi', [StudentCertificateController::class, 'index'])->name('certificates.index');
      Route::get('/chung-chi/{id}', [StudentCertificateController::class, 'show'])->name('certificates.show');
      Route::get('/ma-giam-gia', 'Clients\AccountController@myCoupon')->name('my-coupon');
      Route::get('/don-hang', 'Clients\AccountController@myOrder')->name('my-order');
      Route::get('/don-hang/{id}', 'Clients\AccountController@detailOrder')->name('order-detail');
      Route::get('/doi-mat-khau', 'Clients\AccountController@showChangePassword')->middleware('student.2fa:change-password-page')->name('change-password');
      Route::post('/doi-mat-khau', 'Clients\AccountController@updatePassword')->middleware('student.2fa:change-password-page')->name('change-postpassword');
      Route::get('/lich-su-hoat-dong', 'Clients\AccountController@activityHistory')->name('activity-history');
      Route::get('/thanh-toan/{id}', 'Clients\CheckoutController@index')->name('checkout');
      Route::post('/thanh-toan/{id}/hoan-tat', 'Clients\CheckoutController@complete')->name('checkout-payment');
      Route::post('/thanh-toan/{id}/huy', 'Clients\CheckoutController@cancel')->name('checkout-cancel');
      Route::post('/thanh-toan/{id}/vnpay', 'Clients\CheckoutController@vnpay')->name('checkout-vnpay');
      Route::post('/thanh-toan/{id}/momo', 'Clients\CheckoutController@momo')->name('checkout-momo');

      Route::get('/tin-nhan/khuyen-mai/{promotion}', 'Clients\AccountController@showPromotion')->name('promotions.show');

      Route::prefix('coupons')->group(function () {
         Route::post('/verify', 'Clients\CouponsController@verify')->name('coupons');
         Route::post('/remove', 'Clients\CouponsController@remove')->name('coupons-remove');
         Route::post('/polling', 'Clients\CouponsController@pollingCoupon')->name('coupons-pollingCoupon');
      });

      Route::prefix('checkout')->group(function () {
         Route::get('/cam-on/{id}', 'Clients\CheckoutController@thankyou')->name('checkout-thankyou');
      });

      Route::prefix('qua-tang')->name('gifts.')->group(function () {
         Route::get('/', [GiftCourseController::class, 'index'])->name('index');
         Route::get('/{token}', [GiftCourseController::class, 'show'])->name('show');
         Route::post('/{token}/nhan', [GiftCourseController::class, 'accept'])->name('accept');
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
   'middleware' => ['setLocale'],
], function () {
   Route::get('/xoa-tai-khoan/thanh-cong', 'Clients\AccountController@deleteSuccess')->name('delete-success');
   Route::get('/vo-hieu-hoa/thanh-cong', 'Clients\AccountController@deactivateSuccess')->name('deactivate-success');
});

Route::group([
   'prefix' => '{locale}/tai-khoan/thong-bao',
   'where' => ['locale' => 'vi|en|ko|ja|zh'],
   'as' => 'students.notifications.',
   'middleware' => ['setLocale', 'auth:students', 'verified', 'user.block'],
], function () {
   Route::get('/', function (Request $request, string $locale) {
      $student = auth('students')->user();
      $query = $student->notifications()->latest();

      if ($request->input('status') === 'unread') {
         $query->whereNull('read_at');
      }

      if ($request->filled('type')) {
         $query->where('type', $request->string('type'));
      }

      $notifications = $query->paginate(20)->withQueryString();
      $types = $student->notifications()
         ->select('type')
         ->whereNotNull('type')
         ->distinct()
         ->orderBy('type')
         ->pluck('type');

      return view('students.notifications.index', [
         'pageTitle' => 'Thông báo',
         'pageName' => 'Thông báo',
         'notifications' => $notifications,
         'types' => $types,
      ]);
   })->name('index');

   Route::post('/danh-dau-da-doc-tat-ca', function (Request $request) {
      $student = auth('students')->user();
      $student?->unreadNotifications->markAsRead();

      if ($request->expectsJson() || $request->ajax()) {
         return response()->json([
            'success' => true,
            'unread_count' => 0,
            'message' => 'Đã đánh dấu tất cả thông báo là đã đọc.',
         ]);
      }

      return back()->with('msg_success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
   })->name('mark-all-read');

   Route::get('/doc/{id}', function (string $locale, $id) {
      $notification = auth('students')->user()
         ->notifications()
         ->where('id', $id)
         ->firstOrFail();

      $notification->markAsRead();

      $targetUrl = trim((string) ($notification->data['url'] ?? ''));
      $fallback = route('students.notifications.index', ['locale' => $locale]);

      if ($targetUrl === '') {
         return redirect($fallback);
      }

      $supportedLocales = ['vi', 'en', 'ko', 'ja', 'zh'];
      $parts = parse_url($targetUrl);

      if ($parts === false) {
         return redirect($fallback);
      }

      $host = $parts['host'] ?? null;
      $currentHost = request()->getHost();
      $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

      if ($host && !in_array($host, array_filter([$currentHost, $appHost]), true)) {
         return redirect($targetUrl);
      }

      $path = trim((string) ($parts['path'] ?? ''), '/');

      if ($path === '') {
         return redirect($fallback);
      }

      $segments = explode('/', $path);

      if (!empty($segments) && in_array($segments[0], $supportedLocales, true)) {
         $segments[0] = $locale;
      } else {
         array_unshift($segments, $locale);
      }

      $rebuiltPath = '/' . implode('/', array_filter($segments, static fn($segment) => $segment !== ''));
      $redirectUrl = $rebuiltPath;

      if (!empty($parts['scheme']) && !empty($parts['host'])) {
         $redirectUrl = $parts['scheme'] . '://' . $parts['host'];

         if (!empty($parts['port'])) {
            $redirectUrl .= ':' . $parts['port'];
         }

         $redirectUrl .= $rebuiltPath;
      }

      if (!empty($parts['query'])) {
         $redirectUrl .= '?' . $parts['query'];
      }

      if (!empty($parts['fragment'])) {
         $redirectUrl .= '#' . $parts['fragment'];
      }

      return redirect($redirectUrl);
   })->name('read');
});


Route::group(['as' => 'teacher.dashboard.'], function () {
    Route::group(['prefix' => 'teacher', 'middleware' => ['setLocale', 'auth:students', 'verified', 'user.block', 'teacher.active', 'teacher.activity']], function () {

        Route::prefix('hoc-vien')->group(function () {
            Route::get('/', 'Teacher\StudentController@students')->name('students');
            
            Route::name('students.')->group(function () {
                Route::get('/show/{student}', 'Teacher\StudentController@showStudent')->name('show');
                Route::post('/save-note/{student}', 'Teacher\StudentController@saveStudentNote')->name('note');
                Route::get('/export/{format}', 'Teacher\StudentController@exportStudents')->name('export');

                Route::prefix('cap-quyen')->name('grants.')->group(function () {
                    Route::get('/', 'Teacher\StudentController@createStudentGrant')->name('create');
                    Route::post('/', 'Teacher\StudentController@storeStudentGrant')->name('store');
                    Route::post('/revoke/{student}/{grant}', 'Teacher\StudentController@revokeStudentGrant')->name('revoke');
                });
            });
        });


    });
});
