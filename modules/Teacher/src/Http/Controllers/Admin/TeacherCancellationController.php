<?php

namespace Modules\Teacher\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Notifications\StudentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Modules\Teacher\src\Models\TeacherCancellationRequest;
use App\Mail\TeacherCancellationStatusMail;
use Modules\Finances\src\Models\AffiliateLink;
use Modules\Students\src\Models\Coupons;
use Modules\Courses\src\Models\Courses;

class TeacherCancellationController extends Controller
{
    public function index()
    {
        $pageTitle = __('teacher::admin.titles.cancellations');
        $requests = TeacherCancellationRequest::with(['teacher.student', 'processor'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->latest()
            ->paginate(20);

        return view('teacher::cancellations.index', compact('requests', 'pageTitle'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'required|string',
        ], [
            'admin_note.required' => __('teacher::admin.fields.admin_note_required'),
        ]);

        $cancelRequest = TeacherCancellationRequest::findOrFail($id);

        if (!$cancelRequest->isPending()) {
            return back()->with('msg_danger', __('teacher::admin.messages.already_processed'));
        }

        $teacher = $cancelRequest->teacher;

        DB::beginTransaction();
        try {
            // 1. Update Request
            $cancelRequest->update([
                'status' => 'approved',
                'admin_note' => $request->admin_note,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            // 2. Hide Courses (status = 2)
            Courses::where('teacher_id', $teacher->id)
                ->update(['status' => 2]);

            // 3. Deactivate Marketing Tools
            AffiliateLink::where('teacher_id', $teacher->id)
                ->update(['status' => 0]);
            
            Coupons::where('teacher_id', $teacher->id)
                ->delete(); // Soft delete or just update status if it has one. 
                // Based on model view, it has SoftDeletes.

            // 4. Downgrade Teacher
            $teacher->update(['status' => 'inactive']);

            // 5. Notify Students who bought the courses
            // Find student IDs who bought courses from this teacher
            $studentIds = DB::table('orders')
                ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                ->join('courses', 'order_details.course_id', '=', 'courses.id')
                ->where('courses.teacher_id', $teacher->id)
                ->where('orders.status', 'finished')
                ->distinct()
                ->pluck('orders.student_id');

            if ($studentIds->isNotEmpty()) {
                $students = \Modules\Students\src\Models\Student::whereIn('id', $studentIds)->get();
                Notification::send($students, new StudentNotification([
                    'type' => 'teacher.cancelled',
                    'title' => __('teacher::admin.notifications.system_title'),
                    'message' => __('teacher::admin.notifications.teacher_cancelled_msg', ['name' => $teacher->name]),
                    'severity' => 'warning',
                    'icon' => 'fas fa-info-circle'
                ]));
            }

            // 6. Send Email to Teacher
            Mail::to($teacher->student->email)->queue(new TeacherCancellationStatusMail(
                $teacher,
                'approved',
                $request->admin_note,
                app()->getLocale()
            ));

            DB::commit();

            return back()->with('msg_success', __('teacher::admin.messages.cancellation_approved'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('msg_danger', __('teacher::admin.messages.error_occurred', ['message' => $e->getMessage()]));
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'required|string',
        ], [
            'admin_note.required' => __('teacher::admin.fields.admin_note_required'),
        ]);

        $cancelRequest = TeacherCancellationRequest::findOrFail($id);

        if (!$cancelRequest->isPending()) {
            return back()->with('msg_danger', __('teacher::admin.messages.already_processed'));
        }

        $cancelRequest->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        // Send Email to Teacher (Using Layout)
        $teacher = $cancelRequest->teacher;
        Mail::to($teacher->student->email)->queue(new TeacherCancellationStatusMail(
            $teacher,
            'rejected',
            $request->admin_note,
            app()->getLocale()
        ));

        return back()->with('msg_success', __('teacher::admin.messages.cancellation_rejected'));
    }
}
