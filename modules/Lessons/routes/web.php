<?php

use Illuminate\Support\Facades\Route;
use Modules\Lessons\src\Http\Controllers\LessonController;

Route::prefix('admin')->group(function () {
   Route::prefix('lessons')->name('lessons.')->group(function () {
      Route::get('/{courseId}', 'LessonController@index')->middleware('permission:lessons.view,lessons.create,lessons.edit,lessons.delete,lessons.sort')->name('index');
      Route::get('/{courseId}/sort', 'LessonController@sort')->middleware('permission:lessons.sort')->name('sort');
      Route::post('/{courseId}/sort', 'LessonController@handleSort')->middleware('permission:lessons.sort')->name('post-sort');
      Route::get('/{courseId}/data', 'LessonController@data')->middleware('permission:lessons.view,lessons.create,lessons.edit,lessons.delete')->name('data');
      Route::get('/{courseId}/create', 'LessonController@create')->middleware('permission:lessons.create')->name('add');
      Route::post('/{courseId}/create', 'LessonController@store')->middleware('permission:lessons.create')->name('post-add');
      Route::get('/edit/{lessonId}', 'LessonController@edit')->middleware('permission:lessons.edit')->name('edit');
      Route::post('/edit/{lessonId}', 'LessonController@update')->middleware('permission:lessons.edit')->name('post-edit');
      Route::delete('/delete/{lessonId}', 'LessonController@delete')->middleware('permission:lessons.delete')->name('delete');
   });
});

Route::group(['as' => 'lessons.', 'prefix' => '{locale}', 'where' => ['locale' => 'vi|en|ko|ja|zh'], 'middleware' => ['setLocale','auth:students', 'verified', 'user.block']], function () {
   Route::get('/bai-hoc/{slug}', 'Clients\LessonController@index')->name('home');
   Route::post('/bai-hoc/{slug}/hoan-thanh', 'Clients\LessonController@toggleCompletion')->name('toggle-completion');
});
