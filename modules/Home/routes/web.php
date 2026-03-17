<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
   // ưu tiên: session locale -> nếu chưa có thì lấy từ header trình duyệt
   $locale = session('locale');

   if (!in_array($locale, ['vi', 'en', 'ko', 'ja', 'zh'], true)) {
      $locale = request()->getPreferredLanguage(['vi', 'en', 'ko', 'ja', 'zh']) ?? 'vi';
   }

   return redirect()->route('home', ['locale' => $locale]);
});
Route::group([
   'prefix' => '{locale}',
   'where' => ['locale' => 'vi|en|ko|ja|zh'],
   'middleware' => 'setLocale',
], function () {
   Route::get('/', 'HomeController@index')->name('home');
});
