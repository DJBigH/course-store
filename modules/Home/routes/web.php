<?php 

use Illuminate\Support\Facades\Route;

// Route::prefix('home')->name('home.')->group(function () {
   // });
   Route::get('/','HomeController@index')->name('home');