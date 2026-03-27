<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('contacts')->name('contacts.')->group(function () {
      Route::get('/', 'ContactController@index')->middleware('permission:contacts.view,contacts.update,contacts.delete,contacts.logs')->name('index');
      Route::get('data', 'ContactController@data')->middleware('permission:contacts.view,contacts.update,contacts.delete,contacts.logs')->name('data');
      Route::post('/bulk', 'ContactController@bulkAction')->middleware('permission:contacts.update,contacts.delete')->name('bulk');
      Route::get('/{id}', 'ContactController@show')->middleware('permission:contacts.view')->name('show');
      Route::post('accpect/{id}', 'ContactController@accept')->middleware('permission:contacts.update')->name('accept');
      Route::delete('delete/{id}', 'ContactController@delete')->middleware('permission:contacts.delete')->name('delete');
      Route::get('logs/{id}', 'ContactController@logs')->middleware('permission:contacts.logs')->name('logs');
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
