<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Support\StudentTwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\StudentTwoFactorCodeMail;
use Modules\Teacher\src\Models\TeacherCancellationRequest;

use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;

class TeacherCancellationController extends Controller
{
    use TeacherDashboardHelpers;
    public function index(Request $request)
    {
        $teacher = Auth::guard('students')->user()->teacher;
        
        // Handle reset request (dismiss rejected/cancelled status to send a new one)
        if ($request->has('reset')) {
            $activeRequest = null;
        } else {
            $activeRequest = $teacher->latestCancellationRequest;
        }

        $pageTitle = __('teacher::teacher/cancellation.title');
        $pageName = $pageTitle;

        // Structured as folder/file: teacher/cancellation/cancellation.blade.php
        return view('teacher::teacher.cancellation.cancellation', compact('pageTitle', 'pageName', 'teacher', 'activeRequest'));
    }

    public function sendOtp(Request $request)
    {
        $teacher = Auth::guard('students')->user()->teacher;
        
        // Cooldown check (60 seconds)
        if (session()->has('cancellation_otp_sent_at') && now()->diffInSeconds(session('cancellation_otp_sent_at')) < 60) {
            $remaining = 60 - now()->diffInSeconds(session('cancellation_otp_sent_at'));
            return response()->json([
                'success' => false,
                'message' => __('teacher::teacher/cancellation.js.resend_wait', ['time' => $remaining . 's']),
            ]);
        }

        $otp = (string) random_int(100000, 999999);
        session([
            'cancellation_otp' => Hash::make($otp),
            'cancellation_otp_expires_at' => now()->addMinutes(10),
            'cancellation_otp_sent_at' => now(),
        ]);

        Mail::to($teacher->student->email)->queue(new StudentTwoFactorCodeMail(
            $teacher->student,
            $otp,
            __('teacher::teacher/cancellation.title'),
            app()->getLocale()
        ));

        $this->logTeacherCancellationActivity(
            $teacher,
            'cancellation_otp_requested',
            'Yêu cầu mã OTP xác nhận hủy hợp tác'
        );

        return response()->json([
            'success' => true,
            'message' => __('teacher::teacher/cancellation.flash.otp_sent'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string',
            'otp' => 'required|digits:6',
        ], [
            'reason.required' => __('teacher::teacher/cancellation.js.validate_reason'),
            'otp.required' => __('teacher::teacher/cancellation.js.validate_otp'),
            'otp.digits' => __('teacher::teacher/cancellation.js.validate_otp'),
        ]);

        $teacher = Auth::guard('students')->user()->teacher;

        // Verify OTP
        $hashedOtp = session('cancellation_otp');
        $expiresAt = session('cancellation_otp_expires_at');

        if (!$hashedOtp || !$expiresAt || now()->isAfter($expiresAt)) {
            return back()->with('msg_danger', __('teacher::teacher/cancellation.flash.otp_expired'));
        }

        if (!Hash::check($request->otp, $hashedOtp)) {
            return back()->with('msg_danger', __('teacher::teacher/cancellation.flash.otp_incorrect'));
        }

        // Clean up OTP session
        session()->forget(['cancellation_otp', 'cancellation_otp_expires_at', 'cancellation_otp_sent_at']);

        // Check if there's already a pending request
        $existing = TeacherCancellationRequest::where('teacher_id', $teacher->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('msg_danger', __('teacher::teacher/cancellation.flash.pending_exists'));
        }

        TeacherCancellationRequest::create([
            'teacher_id' => $teacher->id,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        $this->logTeacherCancellationActivity(
            $teacher,
            'cancellation_request_submitted',
            'Gửi đơn yêu cầu hủy hợp tác',
            ['reason' => $request->reason]
        );

        return redirect()->route('teacher.dashboard.cancellation')->with('msg_success', __('teacher::teacher/cancellation.flash.success'));
    }
}
