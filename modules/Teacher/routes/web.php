<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('teacher')->name('teacher.')->group(function () {
      Route::get('/', 'TeacherController@index')->name('index');
      Route::get('data', 'TeacherController@data')->name('data');
      Route::get('/create', 'TeacherController@create')->name('add');
      Route::post('/create', 'TeacherController@store')->name('post-add');
      Route::get('/edit/{teacher}', 'TeacherController@edit')->name('edit');
      Route::post('/edit/{teacher}', 'TeacherController@update')->name('post-edit');
      Route::delete('/delete/{teacher}', 'TeacherController@delete')->name('delete');
   });
});
