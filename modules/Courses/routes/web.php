<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('courses')->name('courses.')->group(function () {
      Route::get('/', 'CoursesController@index')->name('index');
      Route::get('data', 'CoursesController@data')->name('data');
      Route::get('/create', 'CoursesController@create')->name('add');
      Route::post('/create', 'CoursesController@store')->name('post-add');
      Route::get('/edit/{courses}', 'CoursesController@edit')->name('edit');
      Route::post('/edit/{courses}', 'CoursesController@update')->name('post-edit');
      Route::delete('/delete/{courses}', 'CoursesController@delete')->name('delete');
   });
});

Route::group(['prefix' => 'filemanager', 'middleware' => ['web']], function () {
   \UniSharp\LaravelFilemanager\Lfm::routes();
});

Route::group(['as' => 'courses.'], function () {
   Route::get('/khoa-hoc', 'Clients\CoursesController@index')->name('home');
   Route::get('/khoa-hoc/{slug}', 'Clients\CoursesController@detail')->name('detail');
   Route::prefix('data')->name('data.')->group(function () {
      Route::get('/trial/{lessonId?}', 'Clients\CoursesController@getTrialVideo')->name('trial');
      Route::get('/stream', 'Clients\CoursesController@streamVideo')->middleware('user.block')->name('stream');
   });
});
