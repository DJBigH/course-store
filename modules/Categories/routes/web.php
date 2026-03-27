<?php

use Illuminate\Support\Facades\Route;
use Modules\Courses\src\Http\Controllers\Clients\CoursesController;

Route::prefix('admin')->group(function () {
  Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('/', 'CategoriesController@index')->middleware('permission:categories.view,categories.create,categories.edit,categories.delete,categories.logs')->name('index');
    Route::get('data', 'CategoriesController@data')->middleware('permission:categories.view,categories.create,categories.edit,categories.delete,categories.logs')->name('data');
    Route::post('/bulk', 'CategoriesController@bulkAction')->middleware('permission:categories.delete')->name('bulk');
    Route::get('/create', 'CategoriesController@create')->middleware('permission:categories.create')->name('add');
    Route::post('/create', 'CategoriesController@store')->middleware('permission:categories.create')->name('post-add');
    Route::get('/edit/{category}', 'CategoriesController@edit')->middleware('permission:categories.edit')->name('edit');
    Route::post('/edit/{category}', 'CategoriesController@update')->middleware('permission:categories.edit')->name('post-edit');
    Route::delete('/delete/{category}', 'CategoriesController@delete')->middleware('permission:categories.delete')->name('delete');
    Route::get('logs/{category}', 'CategoriesController@logs')->middleware('permission:categories.logs')->name('logs');
  });
});

Route::group([
  'as' => 'categories.',
  'prefix' => '{locale}',
  'where' => ['locale' => 'vi|en|ko|ja|zh'],
  'middleware' => ['setLocale']
], function () {
  Route::get('danh-muc/{slug}', [CoursesController::class, 'category'])->name('category');
});
