<?php

use Illuminate\Support\Facades\Route;
use Modules\Packages\src\Http\Controllers\Admin\PackageController as AdminPackageController;
use Modules\Packages\src\Http\Controllers\Admin\PackageFeatureController;
use Modules\Packages\src\Http\Controllers\Admin\PackageGrantController;
use Modules\Packages\src\Http\Controllers\Teacher\UpgradeController;

Route::prefix('admin')->group(function () {
    Route::prefix('teacher-packages')->name('teacher-packages.')->group(function () {
        Route::get('/', [AdminPackageController::class, 'index'])->middleware('permission:teachers.view')->name('index');
        Route::get('/create', [AdminPackageController::class, 'create'])->middleware('permission:teachers.create')->name('add');
        Route::post('/create', [AdminPackageController::class, 'store'])->middleware('permission:teachers.create')->name('post-add');
        Route::get('/edit/{id}', [AdminPackageController::class, 'edit'])->middleware('permission:teachers.edit')->name('edit');
        Route::post('/edit/{id}', [AdminPackageController::class, 'update'])->middleware('permission:teachers.edit')->name('post-edit');
        Route::post('/reorder', [AdminPackageController::class, 'reorder'])->middleware('permission:teachers.edit')->name('reorder');
        Route::post('/copy-features', [AdminPackageController::class, 'copyFeatures'])->middleware('permission:teachers.edit')->name('copy-features');
        Route::delete('/delete/{id}', [AdminPackageController::class, 'delete'])->middleware('permission:teachers.delete')->name('delete');
        // ─── Grant Package ───────────────────────────────────────────────────────
        Route::get('/grant', [PackageGrantController::class, 'index'])->middleware('permission:teachers.edit')->name('grant');
        Route::get('/grant/search-teachers', [PackageGrantController::class, 'searchTeachers'])->middleware('permission:teachers.edit')->name('grant.search-teachers');
        Route::post('/grant', [PackageGrantController::class, 'grant'])->middleware('permission:teachers.edit')->name('grant.store');
    });

    Route::prefix('teacher-package-features')->name('teacher-package-features.')->group(function () {
        Route::get('/', [PackageFeatureController::class, 'index'])->middleware('permission:teachers.view')->name('index');
        Route::get('/edit/{id}', [PackageFeatureController::class, 'edit'])->middleware('permission:teachers.edit')->name('edit');
        Route::post('/edit/{id}', [PackageFeatureController::class, 'update'])->middleware('permission:teachers.edit')->name('post-edit');
        Route::post('/reorder', [PackageFeatureController::class, 'reorder'])->middleware('permission:teachers.edit')->name('reorder');
        Route::post('/bulk-update', [PackageFeatureController::class, 'bulkUpdate'])->middleware('permission:teachers.edit')->name('bulk-update');
        Route::post('/sync', [PackageFeatureController::class, 'syncFeatures'])->middleware('permission:teachers.edit')->name('sync');
    });
});

Route::group([
    'prefix' => 'teacher',
    'as' => 'teacher.dashboard.',
    'middleware' => ['setLocale', 'auth:students', 'verified', 'user.block', 'teacher.active', 'teacher.activity'],
], function () {
    Route::get('/goi/nang-cap', [UpgradeController::class, 'upgradePackage'])->name('package.upgrade');
    Route::post('/goi/nang-cap', [UpgradeController::class, 'storeUpgradePackage'])->name('package.upgrade.store');
    Route::get('/goi/nang-cap/trang-thai', [UpgradeController::class, 'upgradePackageStatus'])->name('package.upgrade.status');
    Route::post('/goi/nang-cap/xac-nhan-da-thanh-toan', [UpgradeController::class, 'markUpgradePaid'])->name('package.upgrade.mark-paid');
    Route::post('/goi/nang-cap/huy', [UpgradeController::class, 'cancelUpgradePackage'])->name('package.upgrade.cancel');
    // ─── Claim granted package ─────────────────────────────────────────────────
    Route::get('/goi/nhan-qua/{token}', [\Modules\Packages\src\Http\Controllers\Teacher\PackageClaimController::class, 'show'])->name('package.claim');
    Route::post('/goi/nhan-qua/{token}', [\Modules\Packages\src\Http\Controllers\Teacher\PackageClaimController::class, 'claim'])->name('package.claim.store');
    Route::post('/goi/tu-choi/{token}', [\Modules\Packages\src\Http\Controllers\Teacher\PackageClaimController::class, 'decline'])->name('package.claim.decline');
});