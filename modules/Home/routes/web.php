<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
   // ưu tiên: session locale -> nếu chưa có thì lấy từ header trình duyệt
   $locale = session('locale');

   if (!in_array($locale, ['vi', 'en'], true)) {
      $locale = request()->getPreferredLanguage(['vi', 'en']) ?? 'vi';
   }

   return redirect()->route('home', ['locale' => $locale]);
});
Route::group([
   'prefix' => '{locale}',
   'where' => ['locale' => 'vi|en'],
   'middleware' => 'setLocale',
], function () {
   Route::get('/', 'HomeController@index')->name('home');
});
