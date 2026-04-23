<?php

use Illuminate\Support\Facades\Route;
use Modules\Finances\src\Http\Controllers\Admin\FinanceController as AdminFinanceController;
use Modules\Finances\src\Http\Controllers\Teacher\AffiliateLinkController;
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
    
    // Payouts - support BOTH teacher.dashboard.payouts AND teacher.dashboard.payouts.index
    Route::get('/rut-tien', [PayoutController::class, 'index'])->name('payouts');
    
    Route::prefix('rut-tien')->group(function () {
        Route::get('/home', [PayoutController::class, 'index'])->name('payouts.index'); // Alias for sidebar
        Route::get('/tai-khoan', [PayoutController::class, 'accounts'])->name('payouts.accounts');
        Route::get('/lich-su', [PayoutController::class, 'history'])->name('payouts.history');
        Route::post('/', [PayoutController::class, 'storePayout'])->name('payouts.store');
        Route::post('/tai-khoan', [PayoutController::class, 'storePayoutAccount'])->name('payouts.account.store');
        Route::post('/yeu-cau-doi-tai-khoan', [PayoutController::class, 'storePayoutAccountChangeRequest'])->name('payouts.account-change.store');
    });

    // Affiliate Links
    Route::prefix('link-gioi-thieu')->name('affiliate-links.')->group(function () {
        Route::get('/', [AffiliateLinkController::class, 'index'])->name('index');
        Route::get('/tao-moi', [AffiliateLinkController::class, 'create'])->name('create');
        Route::post('/tao-moi', [AffiliateLinkController::class, 'store'])->name('store');
        Route::get('/chinh-sua/{id}', [AffiliateLinkController::class, 'edit'])->name('edit');
        Route::post('/chinh-sua/{id}', [AffiliateLinkController::class, 'update'])->name('update');
        Route::delete('/xoa/{id}', [AffiliateLinkController::class, 'delete'])->name('delete');
    });
});