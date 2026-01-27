<?php

use Illuminate\Support\Facades\Route;


Route::prefix('admin')->group(function () {
   Route::prefix('categories')->name('categories.')->group(function () {
      
   });
});
Route::group(['as' => 'contacts.'], function () {
   Route::get('/lien-he', 'ContactController@index')->name('home');
   Route::post('/lien-he', 'ContactController@store')->name('post-contacts');
});
