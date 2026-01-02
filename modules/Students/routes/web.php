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
