<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Src\Http\Controllers\Admin\LoginController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;


Route::get('/login', "Admin\LoginController@showLoginForm")->middleware('web')->name('login');
Route::post('/login', "Admin\LoginController@login")->middleware('web')->name('login');
Route::post('/logout', "Admin\LoginController@logout")->middleware('web')->name('logout');

Route::get('/dang-nhap', 'Clients\LoginController@showLoginForm')->name('clients-login');
Route::post('/dang-nhap', 'Clients\LoginController@login')->name('clients-postlogin');
Route::get('/dang-ky', 'Clients\RegisterController@showRegistrationForm')->name('clients-register');
Route::post('/dang-ky', 'Clients\RegisterController@register')->name('clients-postregister');
Route::post('/dang-xuat', 'Clients\LoginController@logout')->name('clients-logout');

Route::get('/block', 'Clients\BlockController@index')->middleware('auth:students')->name('block-index');

Route::get('/email/verify', 'Clients\VerifyController@index')->middleware('auth:students')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('home');
})->middleware(['auth:students', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification','Clients\VerifyController@resend')->middleware(['auth:students', 'throttle:1,1'])->name('verification.send');
