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

class TeacherCancellationController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::guard('students')->user()->teacher;
        
        // Handle reset request (dismiss rejected/cancelled status to send a new one)
        if ($request->has('reset')) {
            $activeRequest = null;
        } else {
            $activeRequest = $teacher->latestCancellationRequest;
        }

        $pageTitle = 'Hủy hợp tác';
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.cancellation', compact('pageTitle', 'pageName', 'teacher', 'activeRequest'));
    }

    public function sendOtp(Request $request)
    {
        $teacher = Auth::guard('students')->user()->teacher;
        
        // Cooldown check (60 seconds)
        if (session()->has('cancellation_otp_sent_at') && now()->diffInSeconds(session('cancellation_otp_sent_at')) < 60) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đợi ' . (60 - now()->diffInSeconds(session('cancellation_otp_sent_at'))) . 's để gửi lại mã.',
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
            'Xác nhận hủy hợp tác giảng viên',
            app()->getLocale()
        ));

        return response()->json([
            'success' => true,
            'message' => 'Mã xác nhận đã được gửi đến email của bạn.',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string',
            'otp' => 'required|digits:6',
        ], [
            'reason.required' => 'Vui lòng nhập lý do hủy hợp tác.',
            'otp.required' => 'Vui lòng nhập mã xác nhận.',
            'otp.digits' => 'Mã xác nhận phải gồm 6 chữ số.',
        ]);

        $teacher = Auth::guard('students')->user()->teacher;

        // Verify OTP
        $hashedOtp = session('cancellation_otp');
        $expiresAt = session('cancellation_otp_expires_at');

        if (!$hashedOtp || !$expiresAt || now()->isAfter($expiresAt)) {
            return back()->with('msg_danger', 'Mã xác nhận đã hết hạn hoặc không tồn tại. Vui lòng gửi lại.');
        }

        if (!Hash::check($request->otp, $hashedOtp)) {
            return back()->with('msg_danger', 'Mã xác nhận không chính xác.');
        }

        // Clean up OTP session
        session()->forget(['cancellation_otp', 'cancellation_otp_expires_at', 'cancellation_otp_sent_at']);

        // Check if there's already a pending request
        $existing = TeacherCancellationRequest::where('teacher_id', $teacher->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('msg_danger', 'Bạn đã có một yêu cầu đang chờ xử lý.');
        }

        TeacherCancellationRequest::create([
            'teacher_id' => $teacher->id,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('teacher.dashboard.cancellation')->with('msg_success', 'Yêu cầu hủy hợp tác của bạn đã được gửi thành công. Admin sẽ xem xét và phản hồi sớm nhất.');
    }
}
