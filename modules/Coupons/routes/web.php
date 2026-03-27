<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('coupons')->name('coupons.')->group(function () {
      Route::get('/', 'CouponController@index')->middleware('permission:coupons.view,coupons.create,coupons.edit,coupons.delete,coupons.assign,coupons.logs')->name('index');
      Route::get('/data', 'CouponController@data')->middleware('permission:coupons.view,coupons.create,coupons.edit,coupons.delete,coupons.assign,coupons.logs')->name('data');
      Route::post('/bulk', 'CouponController@bulkAction')->middleware('permission:coupons.delete,coupons.edit')->name('bulk');
      Route::get('/create', 'CouponController@create')->middleware('permission:coupons.create')->name('add');
      Route::post('/create', 'CouponController@store')->middleware('permission:coupons.create')->name('store');
      Route::get('/edit/{id}', 'CouponController@edit')->middleware('permission:coupons.edit')->name('edit');
      Route::post('/edit/{id}', 'CouponController@update')->middleware('permission:coupons.edit')->name('update');
      Route::delete('/delete/{id}', 'CouponController@delete')->middleware('permission:coupons.delete')->name('delete');

      Route::get('/coupons-student/{id}', 'CouponController@CouponStudent')->middleware('permission:coupons.assign')->name('coupons-student');
      Route::post('/coupons-student/{id}', 'CouponController@AssignCouponStudent')->middleware('permission:coupons.assign')->name('postcoupons-student');
      Route::get('/coupons-course/{id}', 'CouponController@CouponCourse')->middleware('permission:coupons.assign')->name('coupons-course');
      Route::post('/coupons-course/{id}', 'CouponController@AssignCouponCourse')->middleware('permission:coupons.assign')->name('postcoupons-course');
      Route::get('/coupons-usages/{id}', 'CouponController@CouponHistory')->middleware('permission:coupons.logs')->name('coupons-history');

      Route::get('logs/{student}', 'CouponController@logs')->middleware('permission:coupons.logs')->name('logs');
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
