<?php

use Illuminate\Support\Facades\Route;

// Route::prefix('home')->name('home.')->group(function () {
// });
Route::group([
   'prefix' => '{locale}',
   'where' => ['locale' => 'vi|en'],
   'middleware' => 'setLocale',
], function () {

   Route::get('/', 'HomeController@index')->name('home');
});
