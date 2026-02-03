<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('settings')->name('settings.')->group(function () {
      Route::get('/', 'SettingController@index')->name('index');
      Route::post('/', 'SettingController@update')->name('post-setting');
   });
});
