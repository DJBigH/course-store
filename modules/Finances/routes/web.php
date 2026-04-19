<?php

use Illuminate\Support\Facades\Route;
use Modules\Finances\src\Http\Controllers\Admin\FinanceController as AdminFinanceController;
use Modules\Finances\src\Http\Controllers\Teacher\EarningController;
use Modules\Finances\src\Http\Controllers\Teacher\PayoutController;

Route::prefix('admin')->group(function () {
    Route::prefix('teacher-finance')->name('teacher-finance.')->group(function () {
        Route::get('/earnings', [AdminFinanceController::class, 'earnings'])->middleware('permission:teachers.view')->name('earnings');
        Route::get('/earnings/export/{format}', [AdminFinanceController::class, 'exportEarnings'])->middleware('permission:teachers.view')->name('earnings.export');
        Route::get('/payouts', [AdminFinanceController::class, 'payouts'])->middleware('permission:teachers.view')->name('payouts');
        Route::get('/payouts/export/{format}', [AdminFinanceController::class, 'exportPayouts'])->middleware('permission:teachers.view')->name('payouts.export');
        Route::post('/payouts/{id}', [AdminFinanceController::class, 'updatePayout'])->middleware('permission:teachers.edit')->name('payouts.update');
        Route::post('/payout-account-change-requests/{id}', [AdminFinanceController::class, 'updatePayoutAccountChangeRequest'])->middleware('permission:teachers.edit')->name('payout-account-change-requests.update');
    });
});

Route::group([
    'prefix' => 'teacher',
    'as' => 'teacher.dashboard.',
    'middleware' => ['setLocale', 'auth:students', 'verified', 'user.block', 'teacher.active', 'teacher.activity'],
], function () {
    Route::get('/doanh-thu', [EarningController::class, 'index'])->name('earnings');
    
    Route::prefix('rut-tien')->name('payouts.')->group(function () {
        Route::get('/', [PayoutController::class, 'index'])->name('index');
        Route::get('/tai-khoan', [PayoutController::class, 'accounts'])->name('accounts');
        Route::get('/lich-su', [PayoutController::class, 'history'])->name('history');
        Route::post('/', [PayoutController::class, 'storePayout'])->name('store');
        Route::post('/tai-khoan', [PayoutController::class, 'storePayoutAccount'])->name('account.store');
        Route::post('/yeu-cau-doi-tai-khoan', [PayoutController::class, 'storePayoutAccountChangeRequest'])->name('account-change.store');
    });
});