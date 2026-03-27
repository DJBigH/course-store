<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('orders')->name('orders.')->group(function () {
      Route::get('/', 'OrderController@index')->middleware('permission:orders.view,orders.update,orders.delete')->name('index');
      Route::get('/data', 'OrderController@data')->middleware('permission:orders.view,orders.update,orders.delete')->name('data');
      Route::post('/bulk', 'OrderController@bulkAction')->middleware('permission:orders.update,orders.delete')->name('bulk');
      Route::get('/{orderId}', 'OrderController@show')->middleware('permission:orders.view')->name('show');
      Route::delete('/delete/{orderId}', 'OrderController@delete')->middleware('permission:orders.delete')->name('delete');
   });
});
