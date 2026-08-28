<?php

use Illuminate\Support\Facades\Route;
use Modules\Packages\src\Http\Controllers\Admin\PackageController as AdminPackageController;
use Modules\Packages\src\Http\Controllers\Admin\PackageCategoryController;
use Modules\Packages\src\Http\Controllers\Admin\PackageFeatureController;
use Modules\Packages\src\Http\Controllers\Admin\PackageGrantController;
use Modules\Packages\src\Http\Controllers\Teacher\UpgradeController;

Route::prefix('admin')->group(function () {
    Route::prefix('teacher-packages')->name('teacher-packages.')->group(function () {
        Route::get('/', [AdminPackageController::class, 'index'])->middleware('permission:packages.view')->name('index');
        Route::get('/create', [AdminPackageController::class, 'create'])->middleware('permission:packages.manage')->name('add');
        Route::post('/create', [AdminPackageController::class, 'store'])->middleware('permission:packages.manage')->name('post-add');
        Route::get('/edit/{id}', [AdminPackageController::class, 'edit'])->middleware('permission:packages.manage')->name('edit');
        Route::post('/edit/{id}', [AdminPackageController::class, 'update'])->middleware('permission:packages.manage')->name('post-edit');
        Route::post('/reorder', [AdminPackageController::class, 'reorder'])->middleware('permission:packages.manage')->name('reorder');
        Route::post('/copy-features', [AdminPackageController::class, 'copyFeatures'])->middleware('permission:packages.manage')->name('copy-features');
        Route::post('/toggle-status/{id}', [AdminPackageController::class, 'toggleStatus'])->middleware('permission:packages.manage')->name('toggle-status');
        Route::post('/toggle-featured/{id}', [AdminPackageController::class, 'toggleFeatured'])->middleware('permission:packages.manage')->name('toggle-featured');
        Route::delete('/delete/{id}', [AdminPackageController::class, 'delete'])->middleware('permission:packages.manage')->name('delete');
        // ─── Grant Package ───────────────────────────────────────────────────────
        Route::get('/grant', [PackageGrantController::class, 'index'])->middleware('permission:packages.manage')->name('grant');
        Route::get('/grant/search-teachers', [PackageGrantController::class, 'searchTeachers'])->middleware('permission:packages.manage')->name('grant.search-teachers');
        Route::post('/grant', [PackageGrantController::class, 'grant'])->middleware('permission:packages.manage')->name('grant.store');

        // ─── Categories ──────────────────────────────────────────────────────────
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [PackageCategoryController::class, 'index'])->middleware('permission:packages.view')->name('index');
            Route::get('/create', [PackageCategoryController::class, 'create'])->middleware('permission:packages.manage')->name('create');
            Route::post('/create', [PackageCategoryController::class, 'store'])->middleware('permission:packages.manage')->name('store');
            Route::get('/edit/{id}', [PackageCategoryController::class, 'edit'])->middleware('permission:packages.manage')->name('edit');
            Route::post('/edit/{id}', [PackageCategoryController::class, 'update'])->middleware('permission:packages.manage')->name('update');
            Route::delete('/delete/{id}', [PackageCategoryController::class, 'delete'])->middleware('permission:packages.manage')->name('delete');
        });
    });

    Route::prefix('teacher-package-features')->name('teacher-package-features.')->group(function () {
        Route::get('/', [PackageFeatureController::class, 'index'])->middleware('permission:packages.view')->name('index');
        Route::get('/edit/{id}', [PackageFeatureController::class, 'edit'])->middleware('permission:packages.manage')->name('edit');
        Route::post('/edit/{id}', [PackageFeatureController::class, 'update'])->middleware('permission:packages.manage')->name('post-edit');
        Route::post('/reorder', [PackageFeatureController::class, 'reorder'])->middleware('permission:packages.manage')->name('reorder');
        Route::post('/bulk-update', [PackageFeatureController::class, 'bulkUpdate'])->middleware('permission:packages.manage')->name('bulk-update');
        Route::post('/sync', [PackageFeatureController::class, 'syncFeatures'])->middleware('permission:packages.manage')->name('sync');
    });
});

Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => 'vi|en|ko|ja|zh'],
    'middleware' => ['setLocale'],
], function () {
    Route::group([
        'prefix' => 'teacher',
        'as' => 'teacher.dashboard.',
        'middleware' => ['auth:students', 'verified', 'user.block', 'teacher.active', 'teacher.activity'],
    ], function () {
        Route::get('/goi/nang-cap', [UpgradeController::class, 'upgradePackage'])->name('package.upgrade');
        Route::post('/goi/nang-cap', [UpgradeController::class, 'storeUpgradePackage'])->name('package.upgrade.store');
        Route::get('/goi/nang-cap/trang-thai', [UpgradeController::class, 'upgradePackageStatus'])->name('package.upgrade.status');
        Route::get('/goi/nang-cap/ket-qua', [UpgradeController::class, 'upgradeResult'])->name('package.upgrade.result');
        Route::post('/goi/nang-cap/thanh-toan-lai', [UpgradeController::class, 'repayPackage'])->name('package.upgrade.repay');
        Route::post('/goi/nang-cap/xac-nhan-da-thanh-toan', [UpgradeController::class, 'markUpgradePaid'])->name('package.upgrade.mark-paid');
        Route::post('/goi/nang-cap/huy', [UpgradeController::class, 'cancelUpgradePackage'])->name('package.upgrade.cancel');

        // Payment Return/IPN Routes
        Route::get('/goi/nang-cap/vnpay-return', [UpgradeController::class, 'vnpayReturn'])->name('package.upgrade.vnpay-return');
        Route::get('/goi/nang-cap/vnpay-ipn', [UpgradeController::class, 'vnpayIpn'])->name('package.upgrade.vnpay-ipn');
        Route::get('/goi/nang-cap/momo-return', [UpgradeController::class, 'momoReturn'])->name('package.upgrade.momo-return');
        Route::post('/goi/nang-cap/momo-ipn', [UpgradeController::class, 'momoIpn'])->name('package.upgrade.momo-ipn');
        // ─── Claim granted package ─────────────────────────────────────────────────
        Route::get('/goi/nhan-qua/{token}', [\Modules\Packages\src\Http\Controllers\Teacher\PackageClaimController::class, 'show'])->name('package.claim');
        Route::post('/goi/nhan-qua/{token}', [\Modules\Packages\src\Http\Controllers\Teacher\PackageClaimController::class, 'claim'])->name('package.claim.store');
        Route::post('/goi/tu-choi/{token}', [\Modules\Packages\src\Http\Controllers\Teacher\PackageClaimController::class, 'decline'])->name('package.claim.decline');
    });
});