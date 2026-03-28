<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
   Route::prefix('teacher')->name('teacher.')->group(function () {
      Route::get('/', 'TeacherController@index')->middleware('permission:teachers.view,teachers.create,teachers.edit,teachers.delete,teachers.soft_delete,teachers.force_delete,teachers.logs')->name('index');
      Route::get('/trash', 'TeacherController@trash')->middleware('permission:teachers.view,teachers.soft_delete,teachers.force_delete')->name('trash');
      Route::get('data', 'TeacherController@data')->middleware('permission:teachers.view,teachers.create,teachers.edit,teachers.delete,teachers.soft_delete,teachers.force_delete,teachers.logs')->name('data');
      Route::get('/trash/data', 'TeacherController@trashData')->middleware('permission:teachers.view,teachers.soft_delete,teachers.force_delete')->name('trash.data');
      Route::post('/bulk', 'TeacherController@bulkAction')->middleware('permission:teachers.delete,teachers.soft_delete')->name('bulk');
      Route::post('/trash/bulk', 'TeacherController@trashBulkAction')->middleware('permission:teachers.delete,teachers.soft_delete,teachers.force_delete')->name('trash.bulk');
      Route::get('/create', 'TeacherController@create')->middleware('permission:teachers.create')->name('add');
      Route::post('/create', 'TeacherController@store')->middleware('permission:teachers.create')->name('post-add');
      Route::post('/restore/{teacher}', 'TeacherController@restore')->middleware('permission:teachers.delete,teachers.soft_delete')->name('restore');
      Route::delete('/force-delete/{teacher}', 'TeacherController@forceDelete')->middleware('permission:teachers.force_delete')->name('force-delete');
      Route::get('/edit/{teacher}', 'TeacherController@edit')->middleware('permission:teachers.edit')->name('edit');
      Route::post('/edit/{teacher}', 'TeacherController@update')->middleware('permission:teachers.edit')->name('post-edit');
      Route::delete('/delete/{teacher}', 'TeacherController@delete')->middleware('permission:teachers.delete,teachers.soft_delete')->name('delete');
      Route::get('logs/{teacher}', 'TeacherController@logs')->middleware('permission:teachers.logs')->name('logs');
   });
});
