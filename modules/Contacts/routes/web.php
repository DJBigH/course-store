<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('contacts')->name('contacts.')->group(function () {
      Route::get('/', 'ContactController@index')->middleware('permission:contacts.view,contacts.update,contacts.delete,contacts.soft_delete,contacts.force_delete,contacts.logs')->name('index');
      Route::get('/support', 'ContactController@supportIndex')->middleware('permission:contacts.view,contacts.update,contacts.delete,contacts.soft_delete,contacts.force_delete,contacts.logs')->name('support-index');
      Route::get('/trash', 'ContactController@trash')->middleware('permission:contacts.view,contacts.soft_delete,contacts.force_delete')->name('trash');
      Route::get('data', 'ContactController@data')->middleware('permission:contacts.view,contacts.update,contacts.delete,contacts.soft_delete,contacts.force_delete,contacts.logs')->name('data');
      Route::get('support/data', 'ContactController@supportData')->middleware('permission:contacts.view,contacts.update,contacts.delete,contacts.soft_delete,contacts.force_delete,contacts.logs')->name('support-data');
      Route::get('/trash/data', 'ContactController@trashData')->middleware('permission:contacts.view,contacts.soft_delete,contacts.force_delete')->name('trash.data');
      Route::post('/bulk', 'ContactController@bulkAction')->middleware('permission:contacts.update,contacts.delete,contacts.soft_delete')->name('bulk');
      Route::post('/trash/bulk', 'ContactController@trashBulkAction')->middleware('permission:contacts.delete,contacts.soft_delete,contacts.force_delete')->name('trash.bulk');
      Route::get('/{id}', 'ContactController@show')->middleware('permission:contacts.view')->name('show');
      Route::get('/support/{id}', 'ContactController@supportShow')->middleware('permission:contacts.view')->name('support-show');
      Route::post('accpect/{id}', 'ContactController@accept')->middleware('permission:contacts.update')->name('accept');
      Route::post('/{id}/status', 'ContactController@updateStatus')->middleware('permission:contacts.update')->name('update-status');
      Route::post('/restore/{id}', 'ContactController@restore')->middleware('permission:contacts.restore,contacts.delete,contacts.soft_delete')->name('restore');
      Route::delete('/force-delete/{id}', 'ContactController@forceDelete')->middleware('permission:contacts.force_delete')->name('force-delete');
      Route::delete('delete/{id}', 'ContactController@delete')->middleware('permission:contacts.delete,contacts.soft_delete')->name('delete');
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
