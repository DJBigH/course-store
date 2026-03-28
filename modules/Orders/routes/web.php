<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('orders')->name('orders.')->group(function () {
      Route::get('/', 'OrderController@index')->middleware('permission:orders.view,orders.update,orders.delete,orders.soft_delete,orders.force_delete')->name('index');
      Route::get('/trash', 'OrderController@trash')->middleware('permission:orders.view,orders.soft_delete,orders.force_delete')->name('trash');
      Route::get('/data', 'OrderController@data')->middleware('permission:orders.view,orders.update,orders.delete,orders.soft_delete,orders.force_delete')->name('data');
      Route::get('/trash/data', 'OrderController@trashData')->middleware('permission:orders.view,orders.soft_delete,orders.force_delete')->name('trash.data');
      Route::post('/bulk', 'OrderController@bulkAction')->middleware('permission:orders.update,orders.delete,orders.soft_delete')->name('bulk');
      Route::post('/trash/bulk', 'OrderController@trashBulkAction')->middleware('permission:orders.delete,orders.soft_delete,orders.force_delete')->name('trash.bulk');
      Route::get('/{orderId}', 'OrderController@show')->middleware('permission:orders.view')->name('show');
      Route::post('/restore/{orderId}', 'OrderController@restore')->middleware('permission:orders.delete,orders.soft_delete')->name('restore');
      Route::delete('/force-delete/{orderId}', 'OrderController@forceDelete')->middleware('permission:orders.force_delete')->name('force-delete');
      Route::delete('/delete/{orderId}', 'OrderController@delete')->middleware('permission:orders.delete,orders.soft_delete')->name('delete');
   });
});
