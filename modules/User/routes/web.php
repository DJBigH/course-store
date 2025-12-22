<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('user')->name('user.')->group(function () {
      Route::get('/', 'UserController@index')->name('index');
      Route::get('/create','UserController@create')->name('add');
      Route::post('/create','UserController@store')->name('post-add');
   });
});
