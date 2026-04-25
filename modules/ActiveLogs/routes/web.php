<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('activelogs')->name('activelogs.')->middleware('permission:logs.view')->group(function () {
      Route::get('/', 'ActiveLogController@index')->name('index');
   });
});
Route::group([
    'prefix' => 'teacher',
    'as' => 'teacher.dashboard.',
    'middleware' => ['web', 'setLocale', 'auth:students', 'verified', 'user.block', 'teacher.active', 'teacher.activity'],
], function () {
    Route::get('/nhat-ky-hoat-dong', [Modules\ActiveLogs\src\Http\Controllers\Teacher\ActivityLogController::class, 'index'])->name('activity-logs');
});
