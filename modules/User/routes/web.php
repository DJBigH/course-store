<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('user')->name('user.')->group(function () {
      Route::get('/', 'UserController@index')->name('index');
      Route::get('data', 'UserController@data')->name('data');
      Route::get('/create', 'UserController@create')->name('add');
      Route::post('/create', 'UserController@store')->name('post-add');
      Route::get('/edit/{user}', 'UserController@edit')->name('edit');
      Route::post('/edit/{user}', 'UserController@update')->name('post-edit');
      Route::delete('/delete/{user}', 'UserController@delete')->name('delete');
   });
});
