<?php

namespace Modules\Students\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Students\src\Models\StudentsCourses;
use Modules\Students\src\Models\CourseGrant;

class GiftCourseController extends Controller
{
    public function index()
    {
        $student = auth('students')->user();
        $gifts = $this->pendingGiftsQuery($student->id)->latest('invited_at')->get();

        if ($gifts->isEmpty()) {
            abort(404);
        }

        return view('students::clients.gifts.index', [
            'pageTitle' => __('teacher::gifts.student.index_title'),
            'pageName' => __('teacher::gifts.student.index_title'),
            'gifts' => $gifts,
        ]);
    }

    public function show(string $locale, string $token)
    {
        $student = auth('students')->user();
        $gift = $this->pendingGiftsQuery($student->id)
            ->where('token', $token)
            ->firstOrFail();

        return view('students::clients.gifts.show', [
            'pageTitle' => __('teacher::gifts.student.show_title'),
            'pageName' => __('teacher::gifts.student.show_title'),
            'gift' => $gift,
        ]);
    }

    public function accept(Request $request, string $locale, string $token)
    {
        $student = auth('students')->user();
        $gift = $this->pendingGiftsQuery($student->id)
            ->where('token', $token)
            ->firstOrFail();

        StudentsCourses::query()->firstOrCreate([
            'student_id' => $student->id,
            'course_id' => $gift->course_id,
        ], [
            'status' => true,
        ]);

        $gift->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return redirect()
            ->route('students.account.my-courses', ['locale' => $locale])
            ->with('msg_success', __('teacher::gifts.flash.accepted'));
    }

    protected function pendingGiftsQuery(int $studentId)
    {
        return CourseGrant::query()
            ->with(['teacher', 'course'])
            ->where('student_id', $studentId)
            ->whereNull('revoked_at')
            ->where('status', 'pending');
    }
}
