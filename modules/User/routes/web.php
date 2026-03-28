<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::get('user/show', 'UserController@show')->name('user.show');
   Route::post('user/show', 'UserController@showUpdate')->name('user.post-show');
   Route::post('user/toggle-2fa', 'UserController@toggleTwoFactor')->name('user.toggle-2fa');
   Route::post('user/logout-all-sessions', 'UserController@logoutAllSessions')->name('user.logout-all-sessions');

   Route::prefix('user')->name('user.')->group(function () {
      Route::get('/', 'UserController@index')->middleware('permission:users.view,users.create,users.edit,users.delete,users.soft_delete,users.force_delete,users.logs')->name('index');
      Route::get('/trash', 'UserController@trash')->middleware('permission:users.view,users.soft_delete,users.force_delete')->name('trash');
      Route::get('data', 'UserController@data')->middleware('permission:users.view,users.create,users.edit,users.delete,users.soft_delete,users.force_delete,users.logs')->name('data');
      Route::get('/trash/data', 'UserController@trashData')->middleware('permission:users.view,users.soft_delete,users.force_delete')->name('trash.data');
      Route::post('/bulk', 'UserController@bulkAction')->middleware('permission:users.edit,users.delete,users.soft_delete')->name('bulk');
      Route::post('/trash/bulk', 'UserController@trashBulkAction')->middleware('permission:users.delete,users.soft_delete,users.force_delete')->name('trash.bulk');
      Route::get('/create', 'UserController@create')->middleware('permission:users.create')->name('add');
      Route::post('/create', 'UserController@store')->middleware('permission:users.create')->name('post-add');
      Route::post('/restore/{user}', 'UserController@restore')->middleware('permission:users.delete,users.soft_delete')->name('restore');
      Route::delete('/force-delete/{user}', 'UserController@forceDelete')->middleware('permission:users.force_delete')->name('force-delete');
      Route::get('/edit/{user}', 'UserController@edit')->middleware('permission:users.edit')->name('edit');
      Route::post('/edit/{user}', 'UserController@update')->middleware('permission:users.edit')->name('post-edit');
      Route::post('/toggle-lock/{user}', 'UserController@toggleLock')->middleware('permission:users.edit')->name('toggle-lock');
      Route::delete('/delete/{user}', 'UserController@delete')->middleware('permission:users.delete,users.soft_delete')->name('delete');
      Route::get('logs/{user}', 'UserController@logs')->middleware('permission:users.logs')->name('logs');
   });

   Route::prefix('groups')->name('groups.')->group(function () {
      Route::get('/', 'GroupController@index')->middleware('permission:groups.view,groups.create,groups.edit,groups.delete,groups.manage')->name('index');
      Route::post('/sync-permissions', 'GroupController@syncPermissions')->middleware('permission:permissions.manage')->name('sync-permissions');
      Route::get('/create', 'GroupController@create')->middleware('permission:groups.create,groups.manage')->name('create');
      Route::post('/create', 'GroupController@store')->middleware('permission:groups.create,groups.manage')->name('store');
      Route::get('/edit/{group}', 'GroupController@edit')->middleware('permission:groups.edit,groups.manage')->name('edit');
      Route::post('/edit/{group}', 'GroupController@update')->middleware('permission:groups.edit,groups.manage')->name('update');
      Route::delete('/delete/{group}', 'GroupController@destroy')->middleware('permission:groups.delete,groups.manage')->name('delete');
   });

   Route::prefix('permissions')->name('permissions.')->group(function () {
      Route::get('/', 'PermissionController@index')->middleware('permission:permissions.view,permissions.create,permissions.edit,permissions.delete,permissions.manage')->name('index');
      Route::get('/create', 'PermissionController@create')->middleware('permission:permissions.create,permissions.manage')->name('create');
      Route::post('/create', 'PermissionController@store')->middleware('permission:permissions.create,permissions.manage')->name('store');
      Route::get('/edit/{permission}', 'PermissionController@edit')->middleware('permission:permissions.edit,permissions.manage')->name('edit');
      Route::post('/edit/{permission}', 'PermissionController@update')->middleware('permission:permissions.edit,permissions.manage')->name('update');
      Route::delete('/delete/{permission}', 'PermissionController@destroy')->middleware('permission:permissions.delete,permissions.manage')->name('delete');
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
