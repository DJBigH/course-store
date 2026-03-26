<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('coupons')->name('coupons.')->group(function () {
      Route::get('/', 'CouponController@index')->name('index');
      Route::get('/data', 'CouponController@data')->name('data');
      Route::post('/bulk', 'CouponController@bulkAction')->name('bulk');
      Route::get('/create', 'CouponController@create')->name('add');
      Route::post('/create', 'CouponController@store')->name('store');
      Route::get('/edit/{id}', 'CouponController@edit')->name('edit');
      Route::post('/edit/{id}', 'CouponController@update')->name('update');
      Route::delete('/delete/{id}', 'CouponController@delete')->name('delete');

      Route::get('/coupons-student/{id}', 'CouponController@CouponStudent')->name('coupons-student');
      Route::post('/coupons-student/{id}', 'CouponController@AssignCouponStudent')->name('postcoupons-student');
      Route::get('/coupons-course/{id}', 'CouponController@CouponCourse')->name('coupons-course');
      Route::post('/coupons-course/{id}', 'CouponController@AssignCouponCourse')->name('postcoupons-course');
      Route::get('/coupons-usages/{id}', 'CouponController@CouponHistory')->name('coupons-history');

      Route::get('logs/{student}', 'CouponController@logs')->name('logs');
   });
});

Route::group([
   'as' => 'coupons.',
   'prefix' => '{locale}',
   'where' => ['locale' => 'vi|en|ko|ja|zh'],
   'middleware' => ['setLocale','auth:students', 'verified', 'user.block']
], function () {
   Route::get('/ma-giam-gia', 'CouponController@CouponClient')->name('home');
});
