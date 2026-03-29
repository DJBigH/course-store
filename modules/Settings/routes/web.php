<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('settings')->name('settings.')->group(function () {
      Route::get('/', 'SettingController@index')->middleware('permission:settings.view,settings.update,settings.logs')->name('index');
      Route::post('/', 'SettingController@update')->middleware('permission:settings.update')->name('post-setting');
      Route::post('/test-mail', 'SettingController@testMail')->middleware('permission:settings.update')->name('test-mail');
      Route::get('/logs', 'SettingController@logs')->middleware('permission:settings.logs')->name('logs');
   });
});


