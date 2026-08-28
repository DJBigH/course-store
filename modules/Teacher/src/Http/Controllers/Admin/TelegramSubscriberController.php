<?php

namespace Modules\Teacher\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Teacher\src\Models\TeacherTelegramSubscription;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class TelegramSubscriberController extends Controller
{
    public function index()
    {
        $pageTitle = 'Danh sách Người dùng Telegram';
        return view('teacher::admin.telegram_subscribers.index', compact('pageTitle'));
    }

    public function data()
    {
        $subscribers = TeacherTelegramSubscription::with(['teacher', 'package'])
            ->orderBy('created_at', 'desc');

        return DataTables::of($subscribers)
            ->addColumn('teacher_name', function ($sub) {
                return '<div class="fw-bold">' . ($sub->teacher->full_name ?? 'N/A') . '</div>' .
                       '<div class="small text-muted">' . ($sub->teacher->email ?? '') . '</div>';
            })
            ->addColumn('package_name', function ($sub) {
                return $sub->package->name_locale ?? 'N/A';
            })
            ->editColumn('amount', function ($sub) {
                return number_format($sub->amount) . ' VNĐ';
            })
            ->editColumn('expires_at', function ($sub) {
                $date = $sub->expires_at ? $sub->expires_at->format('d/m/Y H:i') : 'N/A';
                $class = $sub->expires_at && $sub->expires_at->isPast() ? 'text-danger fw-bold' : '';
                return '<span class="' . $class . '">' . $date . '</span>';
            })
            ->editColumn('status', function ($sub) {
                $status = $sub->status;
                if ($sub->expires_at && $sub->expires_at->isPast() && $status === 'active') {
                    $status = 'expired';
                }

                return match ($status) {
                    'active' => '<span class="badge bg-success">Đang hoạt động</span>',
                    'expired' => '<span class="badge bg-secondary">Hết hạn</span>',
                    'cancelled' => '<span class="badge bg-danger">Đã hủy</span>',
                    default => '<span class="badge bg-info">' . ucfirst($status) . '</span>',
                };
            })
            ->addColumn('actions', function ($sub) {
                if ($sub->status === 'active' && (!$sub->expires_at || $sub->expires_at->isFuture())) {
                    return '<button type="button" class="btn btn-warning btn-sm cancel-subscriber" data-id="' . $sub->id . '" title="Hủy gói">
                                <i class="fa-solid fa-ban"></i>
                            </button>';
                }
                return '-';
            })
            ->rawColumns(['teacher_name', 'expires_at', 'status', 'actions'])
            ->toJson();
    }

    public function cancel($id)
    {
        $sub = TeacherTelegramSubscription::find($id);
        if (!$sub) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy người dùng']);
        }

        $sub->update([
            'status' => 'cancelled',
            'expires_at' => now()
        ]);

        activity_log(
            action: 'update',
            subject: $sub,
            properties: ['status' => 'cancelled'],
            logName: 'admin_telegram_subscriber_management',
            description: 'Hủy gói Telegram cho giảng viên: ' . ($sub->teacher->full_name ?? 'N/A')
        );

        return response()->json(['success' => true, 'message' => 'Đã hủy gói thành công']);
    }
}
