<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('orders')->name('orders.')->group(function () {
      Route::get('/', 'OrderController@index')->name('index');
      Route::get('/data', 'OrderController@data')->name('data');
      Route::post('/bulk', 'OrderController@bulkAction')->name('bulk');
      Route::get('/{orderId}', 'OrderController@show')->name('show');
      Route::delete('/delete/{orderId}', 'OrderController@delete')->name('delete');
   });
});
