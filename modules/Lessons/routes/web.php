<?php

use Illuminate\Support\Facades\Route;
use Modules\Lessons\src\Http\Controllers\LessonController;

Route::prefix('admin')->group(function () {
   Route::prefix('lessons')->name('lessons.')->group(function () {
      Route::get('/{courseId}', 'LessonController@index')->name('index');
      Route::get('/{courseId}/sort', 'LessonController@sort')->name('sort');
      Route::post('/{courseId}/sort', 'LessonController@handleSort')->name('post-sort');
      Route::get('/{courseId}/data', 'LessonController@data')->name('data');
      Route::get('/{courseId}/create', 'LessonController@create')->name('add');
      Route::post('/{courseId}/create', 'LessonController@store')->name('post-add');
      Route::get('/edit/{lessonId}', 'LessonController@edit')->name('edit');
      Route::post('/edit/{lessonId}', 'LessonController@update')->name('post-edit');
      Route::delete('/delete/{lessonId}', 'LessonController@delete')->name('delete');
   });
});
