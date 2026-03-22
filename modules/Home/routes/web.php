<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
   $locale = session('locale');

   if (!in_array($locale, ['vi', 'en', 'ko', 'ja', 'zh'], true)) {
      $locale = request()->getPreferredLanguage(['vi', 'en', 'ko', 'ja', 'zh']) ?? 'vi';
   }

   return redirect()->route('home', ['locale' => $locale]);
});
Route::group([
   'prefix' => '{locale}',
   'where' => ['locale' => 'vi|en|ko|ja|zh'],
   'middleware' => 'setLocale',
], function () {
   Route::get('/', 'HomeController@index')->name('home');
   Route::post('/tro-ly-ban-hang/lien-he', 'ChatbotController@saveLead')->name('home.sales-chatbot.lead');
   Route::post('/tro-ly-ban-hang', 'ChatbotController@reply')->name('home.sales-chatbot');
   Route::get('/ve-chung-toi', 'HomeController@about')->name('home.about');
   Route::get('/ho-tro-hoc-vien', 'HomeController@studentSupport')->name('home.student-support');
   Route::get('/cau-hoi-thuong-gap', 'HomeController@faq')->name('home.faq');
   Route::get('/cam-nhan-hoc-vien', 'HomeController@testimonials')->name('home.testimonials');
   Route::get('/chinh-sach-thanh-toan', 'HomeController@paymentPolicy')->name('home.payment-policy');
   Route::get('/chinh-sach-hoan-tien-huy-don', 'HomeController@refundPolicy')->name('home.refund-policy');
   Route::get('/dieu-khoan-dich-vu', 'HomeController@termsOfService')->name('home.terms-of-service');
   Route::get('/chinh-sach-bao-mat', 'HomeController@privacyPolicy')->name('home.privacy-policy');
});
