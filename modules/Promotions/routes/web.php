<?php

use Illuminate\Support\Facades\Route;
use Modules\Promotions\src\Http\Controllers\Teacher\PromotionController;

Route::group([
    'prefix' => 'teacher',
    'as' => 'teacher.dashboard.',
    'middleware' => ['setLocale', 'auth:students', 'verified', 'user.block', 'teacher.active', 'teacher.activity'],
], function () {
    Route::group(['prefix' => 'khuyen-mai'], function () {
        Route::get('/', [PromotionController::class, 'index'])->name('promotions');
        Route::post('/', [PromotionController::class, 'store'])->name('promotions.store');
        Route::post('/gui-thu', [PromotionController::class, 'test'])->name('promotions.test');
    });
});