<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::get('user/show', 'UserController@show')->name('user.show');
   Route::post('user/show', 'UserController@showUpdate')->name('user.post-show');

   Route::prefix('user')->name('user.')->group(function () {
      Route::get('/', 'UserController@index')->middleware('permission:users.view,users.create,users.edit,users.delete,users.logs')->name('index');
      Route::get('data', 'UserController@data')->middleware('permission:users.view,users.create,users.edit,users.delete,users.logs')->name('data');
      Route::post('/bulk', 'UserController@bulkAction')->middleware('permission:users.delete')->name('bulk');
      Route::get('/create', 'UserController@create')->middleware('permission:users.create')->name('add');
      Route::post('/create', 'UserController@store')->middleware('permission:users.create')->name('post-add');
      Route::get('/edit/{user}', 'UserController@edit')->middleware('permission:users.edit')->name('edit');
      Route::post('/edit/{user}', 'UserController@update')->middleware('permission:users.edit')->name('post-edit');
      Route::delete('/delete/{user}', 'UserController@delete')->middleware('permission:users.delete')->name('delete');
      Route::get('logs/{user}', 'UserController@logs')->middleware('permission:users.logs')->name('logs');
   });

   Route::prefix('groups')->name('groups.')->middleware('permission:groups.manage')->group(function () {
      Route::get('/', 'GroupController@index')->name('index');
      Route::post('/sync-permissions', 'GroupController@syncPermissions')->middleware('permission:permissions.manage')->name('sync-permissions');
      Route::get('/create', 'GroupController@create')->name('create');
      Route::post('/create', 'GroupController@store')->name('store');
      Route::get('/edit/{group}', 'GroupController@edit')->name('edit');
      Route::post('/edit/{group}', 'GroupController@update')->name('update');
      Route::delete('/delete/{group}', 'GroupController@destroy')->name('delete');
   });

   Route::prefix('permissions')->name('permissions.')->middleware('permission:permissions.manage')->group(function () {
      Route::get('/', 'PermissionController@index')->name('index');
      Route::get('/create', 'PermissionController@create')->name('create');
      Route::post('/create', 'PermissionController@store')->name('store');
      Route::get('/edit/{permission}', 'PermissionController@edit')->name('edit');
      Route::post('/edit/{permission}', 'PermissionController@update')->name('update');
      Route::delete('/delete/{permission}', 'PermissionController@destroy')->name('delete');
   });
});

Route::get('admin/notifications/read/{id}', function ($id) {
   $notification = auth()->user()
      ->notifications()
      ->where('id', $id)
      ->firstOrFail();

   $notification->markAsRead();

   return redirect($notification->data['url'] ?? route('admin.index'));
})->middleware(['auth', 'permission:dashboard.view'])->name('admin.notifications.read');

Route::get('admin/notifications', function () {
   return view('admin.notifications.index', [
      'pageTitle' => 'Thông báo',
      'notifications' => auth()->user()
         ->notifications()
         ->latest()
         ->paginate(50)
   ]);
})->middleware(['auth', 'permission:dashboard.view'])->name('admin.notifications.index');
