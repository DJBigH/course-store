<?php

use Illuminate\Support\Facades\Route;
use Modules\Courses\src\Http\Controllers\Clients\CoursesController;

Route::prefix('admin')->group(function () {
  Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('/', 'CategoriesController@index')->name('index');
    Route::get('data', 'CategoriesController@data')->name('data');
    Route::post('/bulk', 'CategoriesController@bulkAction')->name('bulk');
    Route::get('/create', 'CategoriesController@create')->name('add');
    Route::post('/create', 'CategoriesController@store')->name('post-add');
    Route::get('/edit/{category}', 'CategoriesController@edit')->name('edit');
    Route::post('/edit/{category}', 'CategoriesController@update')->name('post-edit');
    Route::delete('/delete/{category}', 'CategoriesController@delete')->name('delete');
    Route::get('logs/{category}', 'CategoriesController@logs')->name('logs');
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
