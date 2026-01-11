<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Src\Http\Controllers\Admin\LoginController;

// Auth::routes();

Route::get('/login',"Admin\LoginController@showLoginForm")->middleware('web')->name('login');
Route::post('/login',"Admin\LoginController@login")->middleware('web')->name('login');
Route::post('/logout',"Admin\LoginController@logout")->middleware('web')->name('logout');

Route::get('/dang-nhap','Clients\LoginController@showLoginForm')->name('clients-login');
Route::get('/dang-ky','Clients\RegisterController@showRegistrationForm')->name('clients-register');


