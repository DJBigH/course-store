<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('students')->name('students.')->group(function () {
      Route::get('/', 'StudentController@index')->name('index');
      Route::get('data', 'StudentController@data')->name('data');
      Route::get('/create', 'StudentController@create')->name('add');
      Route::post('/create', 'StudentController@store')->name('post-add');
      Route::get('/edit/{student}', 'StudentController@edit')->name('edit');
      Route::post('/edit/{student}', 'StudentController@update')->name('post-edit');
      Route::delete('/delete/{student}', 'StudentController@delete')->name('delete');
   });
});

Route::group(['as' => 'students.'], function () {
   Route::group(['prefix' => 'tai-khoan', 'as' => 'account.', 'middleware' => ['auth:students','verified','user.block']], function () {
      Route::get('/tai-khoan', 'Clients\AccountController@index')->name('index');
      Route::get('/tai-khoan/thong-tin', 'Clients\AccountController@profile')->name('profile');
      Route::get('/tai-khoan/khoa-hoc', 'Clients\AccountController@myCourse')->name('my-courses');
      Route::get('/tai-khoan/don-hang', 'Clients\AccountController@myOrder')->name('my-order');
      Route::get('/tai-khoan/doi-mat-khau', 'Clients\AccountController@changePassword')->name('change-password');
   });
});
