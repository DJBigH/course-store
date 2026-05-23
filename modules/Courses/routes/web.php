<?php

use Illuminate\Support\Facades\Route;
use Modules\Courses\src\Http\Controllers\Clients\CoursesController;

Route::prefix('admin')->group(function () {
   Route::prefix('courses')->name('courses.')->group(function () {
      Route::get('/', 'CoursesController@index')->middleware('permission:courses.view')->name('index');
      Route::get('/trash', 'CoursesController@trash')->middleware('permission:courses.view')->name('trash');
      Route::get('/comments', 'CourseCommentController@index')->middleware('permission:comments.moderate')->name('comments.admin');
      Route::post('/comments/bulk', 'CourseCommentController@bulkAction')->middleware('permission:comments.moderate')->name('comments.admin-bulk');
      Route::post('/comments/{commentId}/toggle-visibility', 'CourseCommentController@toggleVisibility')->middleware('permission:comments.moderate')->name('comments.admin-toggle');
      Route::get('data', 'CoursesController@data')->middleware('permission:courses.view')->name('data');
      Route::get('/trash/data', 'CoursesController@trashData')->middleware('permission:courses.view')->name('trash.data');
      Route::get('/create', 'CoursesController@create')->middleware('permission:courses.create')->name('add');
      Route::post('/create', 'CoursesController@store')->middleware('permission:courses.create')->name('post-add');
      Route::post('/bulk', 'CoursesController@bulkAction')->middleware('permission:courses.publish,courses.edit,courses.soft_delete')->name('bulk');
      Route::post('/trash/bulk', 'CoursesController@trashBulkAction')->middleware('permission:courses.publish,courses.force_delete')->name('trash.bulk');
      Route::post('/toggle-status/{courses}', 'CoursesController@toggleStatus')->middleware('permission:courses.publish')->name('toggle-status');
      Route::post('/duplicate/{courses}', 'CoursesController@duplicate')->middleware('permission:courses.edit')->name('duplicate');
      Route::post('/restore/{courses}', 'CoursesController@restore')->middleware('permission:courses.restore,courses.publish')->name('restore');
      Route::delete('/force-delete/{courses}', 'CoursesController@forceDelete')->middleware('permission:courses.force_delete')->name('force-delete');
      Route::get('/edit/{courses}', 'CoursesController@edit')->middleware('permission:courses.edit')->name('edit');
      Route::post('/edit/{courses}', 'CoursesController@update')->middleware('permission:courses.edit')->name('post-edit');
      Route::delete('/delete/{courses}', 'CoursesController@delete')->middleware('permission:courses.soft_delete')->name('delete');
      Route::get('logs/{courses}', 'CoursesController@logs')->middleware('permission:courses.view')->name('logs');

      // Combo quản lý
      Route::prefix('bundles')->name('bundles.')->group(function () {
          Route::get('/', 'Admin\CourseBundleController@index')->middleware('permission:courses.view')->name('index');
          Route::get('/data', 'Admin\CourseBundleController@data')->middleware('permission:courses.view')->name('data');
          Route::get('/create', 'Admin\CourseBundleController@create')->middleware('permission:courses.create')->name('add');
          Route::post('/create', 'Admin\CourseBundleController@store')->middleware('permission:courses.create')->name('post-add');
          Route::get('/edit/{id}', 'Admin\CourseBundleController@edit')->middleware('permission:courses.edit')->name('edit');
          Route::post('/edit/{id}', 'Admin\CourseBundleController@update')->middleware('permission:courses.edit')->name('post-edit');
          Route::post('/toggle-status/{id}', 'Admin\CourseBundleController@toggleStatus')->middleware('permission:courses.edit')->name('toggle-status');
          Route::post('/toggle-hot/{id}', 'Admin\CourseBundleController@toggleHot')->middleware('permission:courses.edit')->name('toggle-hot');
          Route::post('/update-position', 'Admin\CourseBundleController@updatePosition')->middleware('permission:courses.edit')->name('update-position');
          Route::delete('/delete/{id}', 'Admin\CourseBundleController@delete')->middleware('permission:courses.delete')->name('delete');
          Route::post('/bulk', 'Admin\CourseBundleController@bulkAction')->middleware('permission:courses.edit')->name('bulk');
      });

      // Ratings
      Route::prefix('ratings')->name('ratings.')->group(function () {
          Route::get('/', 'Admin\RatingController@index')->middleware('permission:ratings.view')->name('index');
          Route::get('/course-data', 'Admin\RatingController@courseData')->middleware('permission:ratings.view')->name('course-data');
          Route::get('/teacher-data', 'Admin\RatingController@teacherData')->middleware('permission:ratings.view')->name('teacher-data');
          Route::post('/toggle-visibility', 'Admin\RatingController@toggleVisibility')->middleware('permission:ratings.moderate')->name('toggle-visibility');
          Route::delete('/delete', 'Admin\RatingController@delete')->middleware('permission:ratings.delete')->name('delete');
      });
   });
});

    Route::group(['prefix' => 'filemanager', 'middleware' => ['web', 'permission:media.manage']], function () {
       \UniSharp\LaravelFilemanager\Lfm::routes();
    });

Route::group([
   'as' => 'courses.',
   'prefix' => '{locale}',
   'where' => ['locale' => 'vi|en|ko|ja|zh'],
   'middleware' => ['setLocale']
], function () {
   Route::get('/khoa-hoc', 'Clients\CoursesController@index')->name('home');
   Route::get('/combo-khoa-hoc/{slug}', [CoursesController::class, 'bundleDetail'])->name('bundle.detail');
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
   Route::post('/khoa-hoc/{slug}/rating', 'Clients\CourseRatingController@store')
      ->middleware(['auth:students', 'verified', 'user.block'])
      ->name('rating.store');
    Route::prefix('data')->name('data.')->group(function () {
       Route::get('/search/suggest', 'Clients\CourseSearchController@suggest')->name('search.suggest');
       Route::get('/trial/{lessonId?}', 'Clients\CoursesController@getTrialVideo')->name('trial');
      Route::get('/stream', 'Clients\CoursesController@streamVideo')->name('stream');
   });
   Route::post('/tao-don-combo', [CoursesController::class, 'createBundleOrder'])->middleware(['auth:students', 'verified', 'user.block'])->name('bundle.create');
   Route::post('/tao-don', 'Clients\CoursesController@create')->middleware(['auth:students', 'verified', 'user.block'])->name('create');
});
