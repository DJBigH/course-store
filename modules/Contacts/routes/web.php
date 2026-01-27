<?php

use Illuminate\Support\Facades\Route;

Route::group(['as' => 'contacts.'], function () {
   Route::get('/lien-he', 'ContactController@index')->name('index');
   Route::post('/lien-he', 'ContactController@store')->name('post-contacts');
});
