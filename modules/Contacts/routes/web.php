<?php

use Illuminate\Support\Facades\Route;


Route::prefix('admin')->group(function () {
   Route::prefix('contacts')->name('contacts.')->group(function () {
      Route::get('/', 'ContactController@index')->name('index');
      Route::get('data', 'ContactController@data')->name('data');
      Route::post('/bulk', 'ContactController@bulkAction')->name('bulk');
      Route::get('/{id}', 'ContactController@show')->name('show');
      Route::post('accpect/{id}', 'ContactController@accept')->name('accept');
      Route::delete('delete/{id}', 'ContactController@delete')->name('delete');
      Route::get('logs/{id}', 'ContactController@logs')->name('logs');
   });
});
Route::group([
   'as' => 'contacts.',
   'prefix' => '{locale}',
   'where' => ['locale' => 'vi|en|ko|ja|zh'],
   'middleware' => ['setLocale']
], function () {
   Route::get('/lien-he', 'Clients\ContactController@index')->name('home');
   Route::post('/lien-he', 'Clients\ContactController@store')->name('post-contacts');
});
