<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('students')->name('students.')->group(function () {
      Route::get('/', 'StudentController@index')->name('index');
      Route::get('data', 'StudentController@data')->name('data');
      Route::get('/create', 'StudentController@create')->name('add');
      Route::post('/create', 'StudentController@store')->name('post-add');
      Route::get('/edit/{student}', 'StudentController@edit')->name('edit');
      Route::post('/edit/{student}', 'StudentController@update')->name('post-edit');
      Route::delete('/delete/{student}', 'StudentController@delete')->name('delete');
   });
});

Route::group(['as' => 'students.'], function () {
   Route::group(['prefix' => 'tai-khoan', 'as' => 'account.', 'middleware' => ['auth:students', 'verified', 'user.block']], function () {
      Route::get('/', 'Clients\AccountController@index')->name('index');
      Route::get('/thong-tin', 'Clients\AccountController@profile')->name('profile');
      Route::post('/thong-tin', 'Clients\AccountController@updateProfile')->name('client-updateprofile');
      Route::get('/khoa-hoc', 'Clients\AccountController@myCourse')->name('my-courses');
      Route::get('/don-hang', 'Clients\AccountController@myOrder')->name('my-order');
      Route::get('/don-hang/{id}', 'Clients\AccountController@orderDetail')->name('order-detail');
      Route::get('/doi-mat-khau', 'Clients\AccountController@changePassword')->name('change-password');
      Route::post('/doi-mat-khau', 'Clients\AccountController@updatePassword')->name('change-postpassword');
      Route::get('/thanh-toan/{id}', 'Clients\CheckoutController@index')->name('checkout');
      Route::post('/thanh-toan/{id}/hoan-tat', 'Clients\CheckoutController@complete')->name('checkout-payment');
      Route::post('/thanh-toan/{id}/huy','Clients\CheckoutController@cancel')->name('checkout-cancel');

      Route::prefix('coupons')->group(function () {
         Route::post('/verify', 'Clients\CouponsController@verify')->name('coupons');
         Route::post('/remove', 'Clients\CouponsController@remove')->name('coupons-remove');
         Route::post('/polling', 'Clients\CouponsController@pollingCoupon')->name('coupons-pollingCoupon');
      });

      Route::prefix('checkout')->group(function () {
         Route::get('/cam-on/{id}', 'Clients\CheckoutController@thankyou')->name('checkout-thankyou');
      });
   });
});
