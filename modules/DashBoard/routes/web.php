<?php

use Illuminate\Support\Facades\Route;
use Modules\DashBoard\src\Http\Controllers\DashboardController;

Route::prefix('admin')->group(function () {
      Route::get('/', [DashboardController::class, 'index'])->name('index');
});
