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
        $pageTitle = 'Quản lý gói Telegram';
        return view('teacher::admin.telegram_packages.index', compact('pageTitle'));
    }

    public function data()
    {
        $packages = $this->packageRepository->getAllPackages();

        return DataTables::of($packages)
            ->editColumn('price', function ($package) {
                $html = '<div>' . $package->formatted_price . '</div>';
                if ($package->sale_price > 0) {
                    $html .= '<div class="text-danger small"><i class="fa-solid fa-tag me-1"></i>Sale: ' . number_format($package->sale_price) . ' VNĐ</div>';
                }
                return $html;
            })
            ->editColumn('duration', function ($package) {
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
            ->rawColumns(['price', 'duration', 'is_active', 'sort_order', 'edit', 'delete'])
            ->toJson();
    }

    public function create()
    {
        $pageTitle = 'Thêm gói Telegram mới';
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

        $pageTitle = 'Chỉnh sửa gói Telegram';
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
}
