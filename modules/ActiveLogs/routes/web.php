<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('activelogs')->name('activelogs.')->group(function () {
      Route::get('/', 'ActiveLogController@index')->name('index');
   });
});
