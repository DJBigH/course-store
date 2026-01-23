<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('coupons')->name('coupons.')->group(function () {
      Route::get('/', 'CouponController@index')->name('index');
      Route::get('/data', 'CouponController@data')->name('data');
      Route::get('/create', 'CouponController@create')->name('add');
      Route::post('/create', 'CouponController@store')->name('store');
      Route::get('/edit/{id}', 'CouponController@edit')->name('edit');
      Route::post('/edit/{id}', 'CouponController@update')->name('update');
      Route::delete('/delete/{id}', 'CouponController@delete')->name('delete');
   });
});
