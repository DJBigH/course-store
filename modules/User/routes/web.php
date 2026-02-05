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
      Route::get('/show', 'UserController@show')->name('show');
      Route::post('/show', 'UserController@showUpdate')->name('post-show');
   });
});
Route::get('admin/notifications/read/{id}', function ($id) {
   $notification = auth()->user()
      ->notifications()
      ->where('id', $id)
      ->firstOrFail();

   $notification->markAsRead();

   return redirect($notification->data['url'] ?? route('admin.index'));
})->name('admin.notifications.read');


Route::get('admin/notifications', function () {
   return view('admin.notifications.index', [
      'notifications' => auth()->user()
         ->notifications()
         ->latest()
         ->paginate(50)
   ]);
})->name('admin.notifications.index');
