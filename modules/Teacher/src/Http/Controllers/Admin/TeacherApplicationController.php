<?php

namespace Modules\Teacher\src\Http\Controllers\Admin;

use App\Mail\TeacherApplicationApprovedMail;
use App\Mail\TeacherApplicationRejectedMail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Students\src\Models\Student;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherApplication;

class TeacherApplicationController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Ung tuyen giang vien';
        $applications = TeacherApplication::query()
            ->with(['student', 'package', 'teacher', 'reviewer'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('teacher::applications.lists', compact('pageTitle', 'applications'));
    }

    public function show($id)
    {
        $pageTitle = 'Chi tiet ung tuyen giang vien';
        $application = TeacherApplication::query()
            ->with(['student', 'package', 'teacher', 'reviewer'])
            ->findOrFail($id);

        return view('teacher::applications.show', compact('pageTitle', 'application'));
    }

    public function approve(Request $request, $id)
    {
        $application = TeacherApplication::query()
            ->with(['student', 'package'])
            ->findOrFail($id);

        if ($application->status === 'pending_payment') {
            return back()->with('msg_danger', 'Ho so nay van dang cho thanh toan, chua the duyet.');
        }

        $teacher = $application->teacher;
        $displayName = $application->display_name ?: $application->full_name;
        $plainPassword = null;
        $student = $application->student;

        if (!$student) {
            $student = Student::query()->where('email', $application->email)->first();
        }

        if (!$student) {
            $plainPassword = Str::random(12);
            $student = Student::query()->create([
                'name' => $application->full_name,
                'email' => $application->email,
                'phone' => $application->phone,
                'status' => 1,
                'password' => Hash::make($plainPassword),
                'email_verified_at' => now(),
            ]);
        }

        if (!$teacher) {
            $teacher = new Teacher();
        }

        $teacher->fill([
            'student_id' => $student->id,
            'application_id' => $application->id,
            'name' => $displayName,
            'slug' => $this->makeUniqueSlug($displayName, $teacher->id),
            'description' => $application->bio,
            'exp' => $application->experience_years,
            'image' => $teacher->image ?: null,
            'status' => 'active',
            'commission_rate' => $application->package?->commission_rate ?? 50,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);
        $teacher->save();

        if (!$teacher->slug) {
            $teacher->update(['slug' => $this->makeUniqueSlug($displayName, $teacher->id)]);
        }

        $application->update([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'status' => 'approved',
            'reviewed_at' => now(),
            'account_created_at' => $plainPassword ? now() : $application->account_created_at,
            'account_credentials_sent_at' => $plainPassword ? now() : $application->account_credentials_sent_at,
            'reviewed_by' => auth()->id(),
            'admin_note' => $request->input('admin_note'),
        ]);

        activity_log(
            action: 'teacher_application_approved',
            subject: $teacher,
            properties: [
                'application_id' => $application->id,
                'student_id' => $student->id,
                'package' => $application->package?->name,
            ],
            logName: 'Duyet giang vien',
            description: 'Admin phe duyet ho so giang vien'
        );

        Mail::to($application->email)
            ->locale(app()->getLocale())
            ->queue(new TeacherApplicationApprovedMail($application->fresh(['package', 'teacher', 'student']), $plainPassword, app()->getLocale()));

        return redirect()->route('teacher-applications.show', $application->id)
            ->with('msg', 'Da phe duyet ho so giang vien thanh cong.');
    }

    public function reject(Request $request, $id)
    {
        $application = TeacherApplication::query()->findOrFail($id);

        $application->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'admin_note' => trim((string) $request->input('admin_note')) ?: 'Admin can bo sung them thong tin ho so.',
        ]);

        Mail::to($application->email)
            ->locale(app()->getLocale())
            ->queue(new TeacherApplicationRejectedMail($application, app()->getLocale()));

        return redirect()->route('teacher-applications.show', $application->id)
            ->with('msg', 'Da tu choi ho so va gui ghi chu cho hoc vien.');
    }

    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'teacher';
        $slug = $base;
        $counter = 1;

        while (Teacher::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $counter++;
            $slug = $base . '-' . $counter;
        }

        return $slug;
    }
}
