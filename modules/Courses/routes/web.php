<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('courses')->name('courses.')->group(function () {
      Route::get('/', 'CoursesController@index')->name('index');
      Route::get('/trash', 'CoursesController@trash')->name('trash');
      Route::get('/comments', 'CourseCommentController@index')->name('comments.admin');
      Route::post('/comments/bulk', 'CourseCommentController@bulkAction')->name('comments.admin-bulk');
      Route::post('/comments/{commentId}/toggle-visibility', 'CourseCommentController@toggleVisibility')->name('comments.admin-toggle');
      Route::get('data', 'CoursesController@data')->name('data');
      Route::get('/trash/data', 'CoursesController@trashData')->name('trash.data');
      Route::get('/create', 'CoursesController@create')->name('add');
      Route::post('/create', 'CoursesController@store')->name('post-add');
      Route::post('/bulk', 'CoursesController@bulkAction')->name('bulk');
      Route::post('/trash/bulk', 'CoursesController@trashBulkAction')->name('trash.bulk');
      Route::post('/toggle-status/{courses}', 'CoursesController@toggleStatus')->name('toggle-status');
      Route::post('/duplicate/{courses}', 'CoursesController@duplicate')->name('duplicate');
      Route::post('/restore/{courses}', 'CoursesController@restore')->name('restore');
      Route::delete('/force-delete/{courses}', 'CoursesController@forceDelete')->name('force-delete');
      Route::get('/edit/{courses}', 'CoursesController@edit')->name('edit');
      Route::post('/edit/{courses}', 'CoursesController@update')->name('post-edit');
      Route::delete('/delete/{courses}', 'CoursesController@delete')->name('delete');
      Route::get('logs/{courses}', 'CoursesController@logs')->name('logs');
   });
});

Route::group(['prefix' => 'filemanager', 'middleware' => ['web']], function () {
   \UniSharp\LaravelFilemanager\Lfm::routes();
});

Route::group([
   'as' => 'courses.',
   'prefix' => '{locale}',
   'where' => ['locale' => 'vi|en|ko|ja|zh'],
   'middleware' => ['setLocale']
], function () {
   Route::get('/khoa-hoc', 'Clients\CoursesController@index')->name('home');
   Route::get('/khoa-hoc/{slug}', 'Clients\CoursesController@detail')->name('detail');
   Route::post('/khoa-hoc/{slug}/comments', 'Clients\CourseCommentController@store')
      ->middleware(['auth:students', 'verified', 'user.block'])
      ->name('comments.store');
   Route::post('/khoa-hoc/{slug}/comments/{commentId}/reply', 'Clients\CourseCommentController@reply')
      ->middleware(['auth'])
      ->name('comments.reply');
   Route::post('/khoa-hoc/{slug}/comments/{commentId}/toggle-visibility', 'Clients\CourseCommentController@toggleVisibility')
      ->middleware(['auth'])
      ->name('comments.toggle');
   Route::prefix('data')->name('data.')->group(function () {
      Route::get('/trial/{lessonId?}', 'Clients\CoursesController@getTrialVideo')->name('trial');
      Route::get('/stream', 'Clients\CoursesController@streamVideo')->name('stream');
   });
   Route::post('/tao-don', 'Clients\CoursesController@create')->middleware(['auth:students', 'verified', 'user.block'])->name('create');
});
