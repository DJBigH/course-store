<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('activelogs')->name('activelogs.')->middleware('permission:logs.view')->group(function () {
      Route::get('/', 'ActiveLogController@index')->name('index');
   });
});
