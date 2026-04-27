<?php

use Illuminate\Support\Facades\Route;
use Modules\Announcements\src\Http\Controllers\Admin\AnnouncementController;
use Modules\Announcements\src\Http\Controllers\Clients\InboxController;

// Admin routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index'])->name('index');
        Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
        Route::post('/create', [AnnouncementController::class, 'store'])->name('store');
        Route::get('/search-users', [AnnouncementController::class, 'searchUsers'])->name('search-users');
        Route::get('/{announcement}', [AnnouncementController::class, 'show'])->name('show');
        Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
    });
});

// Client routes (Students & Teachers)
Route::group([
    'prefix' => '{locale}/tai-khoan/hop-thu',
    'where' => ['locale' => 'vi|en|ko|ja|zh'],
    'as' => 'clients.inbox.',
    'middleware' => ['setLocale', 'auth:students', 'verified', 'user.block'],
], function () {
    Route::get('/', [InboxController::class, 'index'])->name('index');
    Route::get('/{announcement}', [InboxController::class, 'show'])->name('show');
});
