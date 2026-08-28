<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('settings')->name('settings.')->group(function () {
      Route::get('/', 'SettingController@index')->middleware('permission:settings.view,settings.update,settings.logs')->name('index');
      Route::post('/', 'SettingController@update')->middleware('permission:settings.update')->name('post-setting');
      Route::post('/test-mail', 'SettingController@testMail')->middleware('permission:settings.update')->name('test-mail');
      Route::post('/sync-exchange-rates', 'SettingController@syncExchangeRates')->middleware('permission:settings.update')->name('sync-exchange-rates');
      Route::post('/run-backup', 'SettingController@runBackup')->middleware('permission:settings.update')->name('run-backup');
      Route::post('/firewall', 'SettingController@storeFirewall')->middleware('permission:settings.update')->name('firewall.store');
      Route::post('/firewall/delete/{id}', 'SettingController@deleteFirewall')->middleware('permission:settings.update')->name('firewall.delete');
      Route::post('/test-telegram', 'SettingController@testTelegram')->middleware('permission:settings.update')->name('test-telegram');
      Route::post('/cleanup', 'SettingController@cleanup')->middleware('permission:settings.cleanup')->name('cleanup');
      Route::post('/clear-cache', 'SettingController@clearCache')->middleware('permission:settings.maintenance')->name('clear-cache');
      Route::get('/health', 'SettingController@healthCheck')->middleware('permission:settings.health')->name('health');
      
      Route::prefix('media')->name('media.')->group(function() {
          Route::get('/', 'MediaController@index')->middleware('permission:media.manage')->name('index');
          Route::post('/delete', 'MediaController@delete')->middleware('permission:media.manage')->name('delete');
      });

      Route::get('/logs', 'SettingController@logs')->middleware('permission:settings.logs')->name('logs');
   });
});
