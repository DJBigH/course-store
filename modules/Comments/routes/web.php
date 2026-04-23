<?php 

use Illuminate\Support\Facades\Route;
use Modules\Comments\src\Http\Controllers\Teacher\CommentController;

Route::group([
    'prefix' => 'teacher',
    'as' => 'teacher.dashboard.',
    'middleware' => ['setLocale', 'auth:students', 'verified', 'user.block', 'teacher.active', 'teacher.activity'],
], function () {
    Route::get('/binh-luan', [CommentController::class, 'index'])->name('comments');
    Route::post('/binh-luan/{comment}/reply', [CommentController::class, 'reply'])->name('comments.reply');
    Route::post('/binh-luan/{comment}/toggle', [CommentController::class, 'toggleVisibility'])->name('comments.toggle');
});