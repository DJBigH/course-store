<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Teacher\src\Http\Controllers\Admin\TeacherApplicationController as AdminTeacherApplicationController;
use Modules\Teacher\src\Http\Controllers\Admin\TeacherAnnouncementController as AdminTeacherAnnouncementController;
use Modules\Teacher\src\Http\Controllers\Clients\StudentQuizController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherApplicationController as ClientTeacherApplicationController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherCancellationController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherAuthController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherDashboardController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherLandingController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherPublicController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherProfileController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherQuizController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherCourseController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherLessonController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherOrderController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherBundleController;
use Modules\Teacher\src\Http\Controllers\Clients\TeacherNotificationController;

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
      Route::post('/restore/{teacher}', 'TeacherController@restore')->middleware('permission:teachers.restore,teachers.delete,teachers.soft_delete')->name('restore');
      Route::delete('/force-delete/{teacher}', 'TeacherController@forceDelete')->middleware('permission:teachers.force_delete')->name('force-delete');
      Route::get('/edit/{teacher}', 'TeacherController@edit')->middleware('permission:teachers.edit')->name('edit');
      Route::post('/edit/{teacher}', 'TeacherController@update')->middleware('permission:teachers.edit')->name('post-edit');
      Route::delete('/delete/{teacher}', 'TeacherController@delete')->middleware('permission:teachers.delete,teachers.soft_delete')->name('delete');
      Route::get('logs/{teacher}', 'TeacherController@logs')->middleware('permission:teachers.logs')->name('logs');
      Route::post('/toggle-lock/{teacher}', 'TeacherController@toggleLock')->middleware('permission:teachers.edit')->name('toggle-lock');
      Route::post('/toggle-ceased/{teacher}', 'TeacherController@toggleCeased')->middleware('permission:teachers.edit')->name('toggle-ceased');
   });

   Route::prefix('teacher-applications')->name('teacher-applications.')->group(function () {
      Route::get('/', [AdminTeacherApplicationController::class, 'index'])->middleware('permission:teachers.view')->name('index');
      Route::get('/{id}', [AdminTeacherApplicationController::class, 'show'])->middleware('permission:teachers.view')->name('show');
      Route::post('/{id}/approve', [AdminTeacherApplicationController::class, 'approve'])->middleware('permission:teachers.edit')->name('approve');
      Route::post('/{id}/reject', [AdminTeacherApplicationController::class, 'reject'])->middleware('permission:teachers.edit')->name('reject');
   });


   Route::prefix('teacher-announcements')->name('teacher-announcements.')->group(function () {
      Route::get('/', [AdminTeacherAnnouncementController::class, 'index'])->middleware('permission:teachers.view')->name('index');
      Route::get('/create', [AdminTeacherAnnouncementController::class, 'create'])->middleware('permission:teachers.edit')->name('add');
      Route::post('/create', [AdminTeacherAnnouncementController::class, 'store'])->middleware('permission:teachers.edit')->name('post-add');
      Route::get('/edit/{id}', [AdminTeacherAnnouncementController::class, 'edit'])->middleware('permission:teachers.edit')->name('edit');
      Route::post('/edit/{id}', [AdminTeacherAnnouncementController::class, 'update'])->middleware('permission:teachers.edit')->name('post-edit');
      Route::delete('/delete/{id}', [AdminTeacherAnnouncementController::class, 'delete'])->middleware('permission:teachers.delete')->name('delete');
   });

   Route::prefix('teacher-finance')->name('teacher-finance.')->group(function () {
      Route::get('/cancellations', 'Admin\TeacherCancellationController@index')->middleware('permission:teachers.view')->name('cancellations.index');
      Route::post('/cancellations/{id}/approve', 'Admin\TeacherCancellationController@approve')->middleware('permission:teachers.edit')->name('cancellations.approve');
      Route::post('/cancellations/{id}/reject', 'Admin\TeacherCancellationController@reject')->middleware('permission:teachers.edit')->name('cancellations.reject');
   });
});

Route::group([
   'prefix' => '{locale}',
   'where' => ['locale' => 'vi|en|ko|ja|zh'],
   'middleware' => ['setLocale'],
], function () {
   Route::get('/tro-thanh-giang-vien', [TeacherLandingController::class, 'index'])->name('teacher.portal.index');
   Route::get('/teacher/login', [TeacherAuthController::class, 'showLoginForm'])->name('teacher.auth.login');
   Route::post('/teacher/login', [TeacherAuthController::class, 'login'])->name('teacher.auth.post-login');
   Route::get('/teacher/forgot-password', [TeacherAuthController::class, 'showForgotForm'])->name('teacher.auth.forgot');
   Route::post('/teacher/forgot-password', [TeacherAuthController::class, 'sendResetLink'])->name('teacher.auth.post-forgot');
   Route::get('/teacher/reset-password/{token}', [TeacherAuthController::class, 'showResetForm'])->name('teacher.password.reset');
   Route::post('/teacher/reset-password', [TeacherAuthController::class, 'updatePassword'])->name('teacher.auth.password.update');
   Route::get('/tro-thanh-giang-vien/bat-dau', [ClientTeacherApplicationController::class, 'begin'])->name('teacher.account.begin');
   Route::get('/tro-thanh-giang-vien/dang-ky', [ClientTeacherApplicationController::class, 'create'])->name('teacher.account.apply');
   Route::post('/tro-thanh-giang-vien/dang-ky', [ClientTeacherApplicationController::class, 'store'])->name('teacher.account.submit');
   Route::post('/tro-thanh-giang-vien/dang-ky/preview-coupon', [ClientTeacherApplicationController::class, 'previewCoupon'])->name('teacher.account.preview-coupon');
   Route::post('/tro-thanh-giang-vien/dang-ky/clear-coupon', [ClientTeacherApplicationController::class, 'clearCouponPreview'])->name('teacher.account.clear-coupon');
   Route::get('/tro-thanh-giang-vien/trang-thai', [ClientTeacherApplicationController::class, 'status'])->name('teacher.account.status');
   Route::get('/tro-thanh-giang-vien/chinh-sua', [ClientTeacherApplicationController::class, 'edit'])->name('teacher.account.edit');
   Route::post('/tro-thanh-giang-vien/chinh-sua', [ClientTeacherApplicationController::class, 'update'])->name('teacher.account.update');
   Route::post('/tro-thanh-giang-vien/xac-nhan-da-thanh-toan', [ClientTeacherApplicationController::class, 'markPaid'])->name('teacher.account.mark-paid');
   Route::get('/giang-vien/{slug}', [TeacherPublicController::class, 'show'])->name('teacher.public.show');
   Route::post('/giang-vien/{slug}/rating', [TeacherPublicController::class, 'rate'])->middleware(['auth:students', 'verified', 'user.block'])->name('teacher.public.rate');
});

Route::group([
   'prefix' => 'teacher',
    'as' => 'teacher.dashboard.',
    'middleware' => ['setLocale', 'auth:students', 'verified', 'user.block', 'teacher.active', 'teacher.locked', 'teacher.activity'],
], function () {
   Route::get('/locale/{locale}', function (Request $request, string $locale) {
      if (!in_array($locale, ['vi', 'en', 'ko', 'ja', 'zh'], true)) {
         $locale = 'vi';
      }

      session(['locale' => $locale]);
      app()->setLocale($locale);

      $redirect = (string) $request->query('redirect', route('teacher.dashboard.index'));
      $fallback = route('teacher.dashboard.index');

      if (!str_starts_with($redirect, url('/')) && !str_starts_with($redirect, '/')) {
         $redirect = $fallback;
      }

      return redirect()->to($redirect);
   })->name('locale');

    Route::get('/locked', [TeacherDashboardController::class, 'locked'])->name('locked');
    Route::get('/', [TeacherDashboardController::class, 'index'])->name('index');
   Route::get('/ho-so', [TeacherProfileController::class, 'show'])->name('profile');
   Route::post('/ho-so', [TeacherProfileController::class, 'update'])->name('profile.update');
   Route::get('/thong-bao', [TeacherNotificationController::class, 'notifications'])->name('notifications');
   Route::get('/thong-bao/doc', [TeacherNotificationController::class, 'readNotification'])->name('notifications.read');
   Route::get('/thong-bao/announcement/{announcement}/doc', [TeacherNotificationController::class, 'readAnnouncement'])->name('notifications.announcements.read');
   Route::get('/combo-khoa-hoc', [TeacherBundleController::class, 'bundles'])->name('bundles');
   Route::get('/combo-khoa-hoc/tao-moi', [TeacherBundleController::class, 'createBundle'])->name('bundles.create');
   Route::post('/combo-khoa-hoc/tao-moi', [TeacherBundleController::class, 'storeBundle'])->name('bundles.store');
   Route::get('/combo-khoa-hoc/{bundle}/chinh-sua', [TeacherBundleController::class, 'editBundle'])->name('bundles.edit');
   Route::post('/combo-khoa-hoc/{bundle}/chinh-sua', [TeacherBundleController::class, 'updateBundle'])->name('bundles.update');
   Route::delete('/combo-khoa-hoc/{bundle}', [TeacherBundleController::class, 'deleteBundle'])->name('bundles.delete');
   Route::get('/khoa-hoc', [TeacherCourseController::class, 'courses'])->name('courses');
   Route::get('/khoa-hoc/thung-rac', [TeacherCourseController::class, 'coursesTrash'])->name('courses.trash');
   Route::get('/khoa-hoc/tao-moi', [TeacherCourseController::class, 'createCourse'])->name('courses.create');
   Route::post('/khoa-hoc/tao-moi', [TeacherCourseController::class, 'storeCourse'])->name('courses.store');
   Route::get('/khoa-hoc/{course}/chinh-sua', [TeacherCourseController::class, 'editCourse'])->name('courses.edit');
   Route::post('/khoa-hoc/{course}/chinh-sua', [TeacherCourseController::class, 'updateCourse'])->name('courses.update');
   Route::post('/khoa-hoc/{course}/uu-tien', [TeacherCourseController::class, 'toggleCoursePriority'])->name('courses.priority');
   Route::post('/khoa-hoc/{course}/trang-thai', [TeacherCourseController::class, 'updateCourseVisibility'])->name('courses.visibility');
   Route::post('/khoa-hoc/{course}/nhan-ban', [TeacherCourseController::class, 'duplicateCourse'])->name('courses.duplicate');
   Route::delete('/khoa-hoc/{course}', [TeacherCourseController::class, 'deleteCourse'])->name('courses.delete');
   Route::post('/khoa-hoc/{course}/khoi-phuc', [TeacherCourseController::class, 'restoreCourse'])->name('courses.restore');
   Route::get('/khoa-hoc/{course}/bai-hoc', [TeacherLessonController::class, 'lessons'])->name('lessons.index');
   Route::get('/khoa-hoc/{course}/bai-hoc/{lesson}/preview-data', [TeacherLessonController::class, 'getLessonPreviewData'])->name('lessons.preview_data');
   Route::get('/khoa-hoc/{course}/quiz', [TeacherQuizController::class, 'index'])->name('quizzes.index');
   Route::post('/khoa-hoc/{course}/quiz', [TeacherQuizController::class, 'store'])->name('quizzes.store');
   Route::get('/khoa-hoc/{course}/quiz/{quiz}/edit', [TeacherQuizController::class, 'edit'])->name('quizzes.edit');
   Route::post('/khoa-hoc/{course}/quiz/{quiz}/assign', [TeacherQuizController::class, 'assign'])->name('quizzes.assign');
   Route::get('/khoa-hoc/{course}/quiz/{quiz}/results', [TeacherQuizController::class, 'results'])->name('quizzes.results');
   Route::get('/khoa-hoc/{course}/quiz/{quiz}/results/export', [TeacherQuizController::class, 'exportResults'])->name('quizzes.results.export');
   Route::post('/khoa-hoc/{course}/quiz/{quiz}', [TeacherQuizController::class, 'update'])->name('quizzes.update');
   Route::delete('/khoa-hoc/{course}/quiz/{quiz}', [TeacherQuizController::class, 'destroy'])->name('quizzes.destroy');
   Route::post('/khoa-hoc/{course}/quiz/{quiz}/cau-hoi', [TeacherQuizController::class, 'storeQuestion'])->name('quizzes.question.store');
   Route::post('/khoa-hoc/{course}/quiz/{quiz}/cau-hoi/{question}', [TeacherQuizController::class, 'updateQuestion'])->name('quizzes.question.update');
   Route::delete('/khoa-hoc/{course}/quiz/{quiz}/cau-hoi/{question}', [TeacherQuizController::class, 'deleteQuestion'])->name('quizzes.question.delete');
   Route::post('/khoa-hoc/{course}/quiz/{quiz}/ai-generate', [TeacherQuizController::class, 'generateAiQuestions'])->name('quizzes.ai.generate');
   Route::get('/khoa-hoc/{course}/quiz/{quiz}/import/template', [TeacherQuizController::class, 'importTemplate'])->name('quizzes.import.template');
   Route::post('/khoa-hoc/{course}/quiz/{quiz}/import', [TeacherQuizController::class, 'importQuestions'])->name('quizzes.import');
   Route::get('/khoa-hoc/{course}/quiz/{quiz}/export', [TeacherQuizController::class, 'exportQuestions'])->name('quizzes.export');
   Route::get('/khoa-hoc/{course}/bai-hoc/thung-rac', [TeacherLessonController::class, 'lessonsTrash'])->name('lessons.trash');
   Route::get('/khoa-hoc/{course}/bai-hoc/export/{format?}', [TeacherLessonController::class, 'exportLessons'])->name('lessons.export');
   Route::get('/khoa-hoc/{course}/bai-hoc/import/template', [TeacherLessonController::class, 'downloadLessonImportTemplate'])->name('lessons.import.template');
   Route::get('/khoa-hoc/{course}/bai-hoc/import/example/{format?}', [TeacherLessonController::class, 'downloadLessonImportExample'])->name('lessons.import.example');
   Route::post('/khoa-hoc/{course}/bai-hoc/import/preview', [TeacherLessonController::class, 'previewLessonImport'])->name('lessons.import.preview');
   Route::post('/khoa-hoc/{course}/bai-hoc/import', [TeacherLessonController::class, 'importLessons'])->name('lessons.import');
   Route::post('/khoa-hoc/{course}/bai-hoc/import/confirm', [TeacherLessonController::class, 'confirmLessonImport'])->name('lessons.import.confirm');
   Route::post('/khoa-hoc/{course}/bai-hoc/import/clear', [TeacherLessonController::class, 'clearLessonImportPreview'])->name('lessons.import.clear');
   Route::get('/khoa-hoc/{course}/bai-hoc/tao-moi', [TeacherLessonController::class, 'createLesson'])->name('lessons.create');
   Route::post('/khoa-hoc/{course}/bai-hoc/tao-moi', [TeacherLessonController::class, 'storeLesson'])->name('lessons.store');
   Route::get('/khoa-hoc/{course}/bai-hoc/{lesson}/chinh-sua', [TeacherLessonController::class, 'editLesson'])->name('lessons.edit');
   Route::post('/khoa-hoc/{course}/bai-hoc/{lesson}/chinh-sua', [TeacherLessonController::class, 'updateLesson'])->name('lessons.update');
   Route::delete('/khoa-hoc/{course}/bai-hoc/{lesson}', [TeacherLessonController::class, 'deleteLesson'])->name('lessons.delete');
   Route::post('/khoa-hoc/{course}/bai-hoc/{lesson}/khoi-phuc', [TeacherLessonController::class, 'restoreLesson'])->name('lessons.restore');
   Route::get('/huy-hop-tac', [TeacherCancellationController::class, 'index'])->name('cancellation');
   Route::post('/huy-hop-tac/otp', [TeacherCancellationController::class, 'sendOtp'])->name('cancellation.otp');
   Route::post('/huy-hop-tac', [TeacherCancellationController::class, 'store'])->name('cancellation.store');
});

// ─── Routes làm bài quiz dành cho học viên ──────────────────────────────────
// Prefix /teacher/quiz/... nhưng dùng middleware học viên, KHÔNG yêu cầu teacher.active
Route::group([
    'prefix'     => 'teacher',
    'as'         => 'teacher.dashboard.',
    'middleware' => ['setLocale', 'auth:students', 'user.block'],
], function () {
    Route::get('/lam-bai/{course}/{quiz}', [StudentQuizController::class, 'show'])->name('quizzes.show');
    Route::post('/lam-bai/{course}/{quiz}/bat-dau', [StudentQuizController::class, 'start'])->name('quizzes.start');
    Route::post('/lam-bai/{course}/{quiz}/nop-bai', [StudentQuizController::class, 'submit'])->name('quizzes.submit');
    Route::get('/lam-bai/{course}/{quiz}/ket-qua/{submission}', [StudentQuizController::class, 'result'])->name('quizzes.result');
});
