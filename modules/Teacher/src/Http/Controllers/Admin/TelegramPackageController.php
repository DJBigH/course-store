<?php

namespace Modules\Teacher\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Teacher\src\Http\Requests\TelegramPackageRequest;
use Modules\Teacher\src\Repositories\TelegramPackageRepositoryInterface;
use Modules\Teacher\src\Models\TelegramPackage;
use Yajra\DataTables\Facades\DataTables;

class TelegramPackageController extends Controller
{
    protected $packageRepository;

    public function __construct(TelegramPackageRepositoryInterface $packageRepository)
    {
        $this->packageRepository = $packageRepository;
    }

    public function index()
    {
        $pageTitle = __('teacher::admin.titles.telegram_packages');
        $packages = TelegramPackage::where('is_active', true)->orderBy('sort_order')->get();
        return view('teacher::admin.telegram_packages.index', compact('pageTitle', 'packages'));
    }

    public function data()
    {
        $packages = $this->packageRepository->getAllPackages();

        return DataTables::of($packages)
            ->addColumn('name', function ($package) {
                return '<div class="fw-bold">' . $package->name_locale . '</div>';
            })
            ->addColumn('price', function ($package) {
                $html = '<div>' . $package->formatted_price . '</div>';
                if ($package->sale_price > 0) {
                    $html .= '<div class="text-danger small"><i class="fa-solid fa-tag me-1"></i>Sale: ' . number_format($package->sale_price) . ' VNĐ</div>';
                }
                return $html;
            })
            ->addColumn('duration', function ($package) {
                return '<span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-3">' . $package->formatted_duration . '</span>';
            })
            ->editColumn('is_active', function ($package) {
                return $package->is_active 
                    ? '<span class="badge bg-success rounded-pill px-3">Hoạt động</span>' 
                    : '<span class="badge bg-danger rounded-pill px-3">Tạm ngưng</span>';
            })
            ->addColumn('sort_order', function ($package) {
                return '<span class="fw-bold">' . $package->sort_order . '</span>';
            })
            ->addColumn('edit', function ($package) {
                return '<a href="' . route('teacher.telegram-packages.edit', $package->id) . '" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen-to-square"></i></a>';
            })
            ->addColumn('delete', function ($package) {
                return '<form action="' . route('teacher.telegram-packages.delete', $package->id) . '" method="POST" class="d-inline-block delete-action">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button></form>';
            })
            ->filterColumn('name', function($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                      ->orWhere('name_en', 'like', "%{$keyword}%")
                      ->orWhere('name_ja', 'like', "%{$keyword}%")
                      ->orWhere('name_ko', 'like', "%{$keyword}%")
                      ->orWhere('name_zh', 'like', "%{$keyword}%");
            })
            ->rawColumns(['name', 'price', 'duration', 'is_active', 'sort_order', 'edit', 'delete'])
            ->toJson();
    }

    public function create()
    {
        $pageTitle = __('teacher::admin.titles.create_telegram_package');
        $units = TelegramPackage::getUnits();
        return view('teacher::admin.telegram_packages.create', compact('pageTitle', 'units'));
    }

    public function store(TelegramPackageRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');

        $package = $this->packageRepository->create($data);

        activity_log(
            action: 'create',
            subject: $package,
            properties: ['data' => $data],
            logName: 'admin_telegram_package_management',
            description: 'Tạo mới gói Telegram: ' . $package->name
        );

        return redirect()->route('teacher.telegram-packages.index')->with('msg', 'Thêm gói thành công');
    }

    public function edit($id)
    {
        $package = $this->packageRepository->find($id);
        if (!$package) abort(404);

        $pageTitle = __('teacher::admin.titles.edit_telegram_package');
        $units = TelegramPackage::getUnits();
        return view('teacher::admin.telegram_packages.edit', compact('package', 'pageTitle', 'units'));
    }

    public function update(TelegramPackageRequest $request, $id)
    {
        $package = $this->packageRepository->find($id);
        if (!$package) abort(404);

        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');

        $oldData = $package->toArray();
        $this->packageRepository->update($id, $data);
        $package->refresh();

        activity_log(
            action: 'update',
            subject: $package,
            properties: ['old' => $oldData, 'new' => $package->toArray()],
            logName: 'admin_telegram_package_management',
            description: 'Cập nhật gói Telegram: ' . $package->name
        );

        return back()->with('msg', 'Cập nhật gói thành công');
    }

    public function delete($id)
    {
        $package = $this->packageRepository->find($id);
        if (!$package) abort(404);

        $snapshot = $package->toArray();
        $this->packageRepository->delete($id);

        activity_log(
            action: 'delete',
            subject: $package,
            properties: ['data' => $snapshot],
            logName: 'admin_telegram_package_management',
            description: 'Xóa gói Telegram: ' . ($snapshot['name'] ?? 'N/A')
        );

        return back()->with('msg', 'Xóa gói thành công');
    }

    public function subscriberData()
    {
        $teachers = \Modules\Teacher\src\Models\Teacher::query()
            ->with(['latestTelegramSubscription.package', 'student'])
            ->where('status', '!=', 'ceased');

        return DataTables::of($teachers)
            ->addColumn('teacher_name', function ($teacher) {
                return '<div class="fw-bold">' . ($teacher->name ?? 'N/A') . '</div>' .
                       '<div class="small text-muted">' . ($teacher->student->email ?? '') . '</div>';
            })
            ->addColumn('package_name', function ($teacher) {
                $sub = $teacher->latestTelegramSubscription;
                if (!$sub) return '<span class="text-muted">Chưa đăng ký</span>';
                
                return $sub->package->name_locale ?? 'N/A';
            })
            ->addColumn('expires_at', function ($teacher) {
                $sub = $teacher->latestTelegramSubscription;
                if (!$sub) return '-';
                
                $date = $sub->expires_at ? $sub->expires_at->format('d/m/Y H:i') : 'N/A';
                $class = $sub->expires_at && $sub->expires_at->isPast() ? 'text-danger fw-bold' : '';
                return '<span class="' . $class . '">' . $date . '</span>';
            })
            ->addColumn('status', function ($teacher) {
                $sub = $teacher->latestTelegramSubscription;
                if (!$sub) return '<span class="badge bg-light text-dark border">Chưa có gói</span>';
                
                if ($sub->status === 'expired' || ($sub->expires_at && $sub->expires_at->isPast())) {
                    $badge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle">Hết hạn</span>';
                } elseif ($sub->status === 'active') {
                    $badge = '<span class="badge bg-success-subtle text-success border border-success-subtle">Đang hoạt động</span>';
                } elseif ($sub->status === 'pending_claim') {
                    $badge = '<span class="badge bg-info-subtle text-info border border-info-subtle"><i class="fa-solid fa-gift me-1"></i> Chờ nhận quà</span>';
                } elseif ($sub->status === 'cancelled') {
                    $badge = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Đã hủy</span>';
                } else {
                    $badge = '<span class="badge bg-info rounded-pill px-3">' . ucfirst($sub->status) . '</span>';
                }

                return $badge;
            })
            ->addColumn('actions', function ($teacher) {
                $html = '<div class="d-flex gap-2">';
                $html .= '<button type="button" class="btn btn-outline-primary btn-sm view-history" data-id="' . $teacher->id . '" data-name="' . ($teacher->name ?? 'N/A') . '" title="Xem lịch sử">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                         </button>';
                
                $html .= '<button type="button" class="btn btn-outline-success btn-sm gift-package" data-id="' . $teacher->id . '" data-name="' . ($teacher->name ?? 'N/A') . '" title="Tặng gói">
                            <i class="fa-solid fa-gift"></i>
                         </button>';
                
                $sub = $teacher->latestTelegramSubscription;
                if ($sub && in_array($sub->status, ['active', 'pending_claim']) && (!$sub->expires_at || $sub->expires_at->isFuture())) {
                    $html .= '<button type="button" class="btn btn-outline-danger btn-sm cancel-subscriber" data-id="' . $sub->id . '" title="Hủy gói">
                                <i class="fa-solid fa-ban"></i>
                            </button>';
                }
                $html .= '</div>';
                return $html;
            })
            ->filterColumn('teacher_name', function($query, $keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhereHas('student', function($sq) use ($keyword) {
                          $sq->where('email', 'like', "%{$keyword}%");
                      });
                });
            })
            ->filterColumn('package_name', function($query, $keyword) {
                $query->whereHas('latestTelegramSubscription.package', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('name_en', 'like', "%{$keyword}%")
                      ->orWhere('name_ja', 'like', "%{$keyword}%")
                      ->orWhere('name_ko', 'like', "%{$keyword}%")
                      ->orWhere('name_zh', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['teacher_name', 'package_name', 'expires_at', 'status', 'actions'])
            ->toJson();
    }

    public function subscriberHistory($teacherId)
    {
        $history = \Modules\Teacher\src\Models\TeacherTelegramSubscription::with(['package', 'orders'])
            ->where('teacher_id', $teacherId)
            ->orderBy('created_at', 'desc')
            ->get();

        return DataTables::of($history)
            ->addColumn('package_name', function ($sub) {
                return $sub->package->name_locale ?? 'N/A';
            })
            ->editColumn('amount', function ($sub) {
                return number_format($sub->amount) . ' VNĐ';
            })
            ->addColumn('payment_method', function ($sub) {
                $order = $sub->orders->first();
                if (!$order) return '-';
                
                return match ($order->payment_method) {
                    'wallet' => '<span class="badge bg-primary">Ví</span>',
                    'bank_transfer' => '<span class="badge bg-info">Ngân hàng</span>',
                    'momo' => '<span class="badge bg-danger">MoMo</span>',
                    'vnpay' => '<span class="badge bg-primary">VNPay</span>',
                    'gift' => '<span class="badge bg-success">Quà tặng</span>',
                    default => '<span class="badge bg-secondary">' . ucfirst($order->payment_method) . '</span>',
                };
            })
            ->editColumn('created_at', function ($sub) {
                return $sub->created_at->format('d/m/Y H:i');
            })
            ->editColumn('expires_at', function ($sub) {
                return $sub->expires_at ? $sub->expires_at->format('d/m/Y H:i') : '-';
            })
            ->editColumn('status', function ($sub) {
                $status = $sub->status;
                if ($sub->expires_at && $sub->expires_at->isPast() && $status === 'active') {
                    $status = 'expired';
                }

                return match ($status) {
                    'active' => '<span class="badge bg-success-subtle text-success border border-success-subtle small">Đang dùng</span>',
                    'expired' => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle small">Hết hạn</span>',
                    'cancelled' => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small">Đã hủy</span>',
                    'pending_claim' => '<span class="badge bg-info-subtle text-info border border-info-subtle small">Chờ nhận</span>',
                    default => '<span class="badge bg-info small">' . ucfirst($status) . '</span>',
                };
            })
            ->rawColumns(['status', 'payment_method'])
            ->toJson();
    }

    public function cancelSubscriber($id)
    {
        $sub = \Modules\Teacher\src\Models\TeacherTelegramSubscription::find($id);
        if (!$sub) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy người dùng']);
        }

        $sub->update([
            'status' => 'cancelled',
            'expires_at' => now()
        ]);

        // Sync with teacher record
        if ($sub->teacher) {
            $sub->teacher->update([
                'telegram_feature_expires_at' => now()
            ]);
        }

        activity_log(
            action: 'update',
            subject: $sub,
            properties: ['status' => 'cancelled'],
            logName: 'admin_telegram_subscriber_management',
            description: 'Hủy gói Telegram cho giảng viên: ' . ($sub->teacher->full_name ?? 'N/A')
        );

        return response()->json(['success' => true, 'message' => 'Đã hủy gói thành công']);
    }

    public function giftSubscriber(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teacher,id',
            'package_id' => 'required|exists:telegram_packages,id'
        ]);

        $teacher = \Modules\Teacher\src\Models\Teacher::find($request->teacher_id);
        $package = TelegramPackage::find($request->package_id);

        // Don't calculate expiresAt now, we'll do it when claimed
        // $expiresAt = $teacher->addTelegramDuration($package->duration_value, $package->duration_unit);

        $token = bin2hex(random_bytes(20));
        $sub = \Modules\Teacher\src\Models\TeacherTelegramSubscription::create([
            'teacher_id' => $teacher->id,
            'telegram_package_id' => $package->id,
            'started_at' => null,
            'expires_at' => null,
            'amount' => 0,
            'status' => 'pending_claim',
            'claim_token' => $token,
        ]);

        $sub->orders()->create([
            'code' => 'GIFT' . strtoupper(uniqid()),
            'student_id' => $teacher->student_id,
            'total' => 0,
            'status_id' => 2, // Completed/Success
            'type' => 'telegram_package',
            'payment_method' => 'gift',
            'payment_complete_date' => now(),
            'currency' => 'VND'
        ]);

        activity_log(
            action: 'create',
            subject: $sub,
            properties: ['package' => $package->name, 'teacher' => $teacher->name],
            logName: 'admin_telegram_gift',
            description: 'Tặng gói Telegram "' . $package->name . '" cho giảng viên: ' . $teacher->name
        );

        // Send notification (Mocking notification center if not available, but usually it is)
        try {
            $notificationCenter = app(\Modules\Teacher\src\Support\TeacherNotificationCenter::class);
            $notificationCenter->sendTelegramGiftNotification($teacher, $package, $sub);
        } catch (\Exception $e) {
            // Silently fail if notification fails
        }

        return response()->json(['success' => true, 'message' => 'Đã tặng gói thành công cho giáo viên']);
    }
}
