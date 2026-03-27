<?php

use Illuminate\Support\Facades\Route;
use Modules\DashBoard\src\Http\Controllers\DashboardController;

Route::prefix('admin')->name('admin.')->group(function () {
      Route::get('/', [DashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('index');
});
