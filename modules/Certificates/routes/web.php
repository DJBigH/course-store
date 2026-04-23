<?php

use Illuminate\Support\Facades\Route;
use Modules\Certificates\src\Http\Controllers\Teacher\CertificateController;
use Modules\Certificates\src\Http\Controllers\PublicCertificateController;

Route::group(['middleware' => ['web']], function () {
    // Public Verification Route
    Route::get('/certificates/verify/{locale}/{code}', [PublicCertificateController::class, 'verify'])
        ->name('certificates.verify');

    // Teacher Dashboard Routes
    Route::group([
        'prefix' => 'teacher',
        'as' => 'teacher.dashboard.',
        'middleware' => ['setLocale', 'auth:students', 'verified', 'user.block', 'teacher.active', 'teacher.activity'],
    ], function () {
        Route::prefix('chung-chi')->name('certificates.')->group(function () {
            Route::get('/', [CertificateController::class, 'index'])->name('index');
            Route::get('/export', [CertificateController::class, 'export'])->name('export');
            Route::post('/issue', [CertificateController::class, 'issue'])->name('issue');
            Route::get('/show/{id}', [CertificateController::class, 'show'])->name('show');
            Route::post('/revoke/{id}', [CertificateController::class, 'revoke'])->name('revoke');
        });
    });
});