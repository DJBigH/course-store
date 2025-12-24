<?php 

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('/', 'CategoriesController@index')->name('index');
      Route::get('data', 'CategoriesController@data')->name('data');
      Route::get('/create','CategoriesController@create')->name('add');
      Route::post('/create','CategoriesController@store')->name('post-add');
      Route::get('/edit/{category}','CategoriesController@edit')->name('edit');
      Route::post('/edit/{category}','CategoriesController@update')->name('post-edit');
      Route::delete('/delete/{category}','CategoriesController@delete')->name('delete');
});
});
