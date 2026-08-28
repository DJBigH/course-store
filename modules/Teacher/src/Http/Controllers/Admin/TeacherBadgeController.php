<?php

namespace Modules\Teacher\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Teacher\src\Repositories\TeacherBadgeRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class TeacherBadgeController extends Controller
{
    protected $badgeRepository;

    public function __construct(TeacherBadgeRepositoryInterface $badgeRepository)
    {
        $this->badgeRepository = $badgeRepository;
    }

    public function index()
    {
        $pageTitle = 'Quản lý huy hiệu giảng viên';

        return view('teacher::admin.badges.index', compact('pageTitle'));
    }

    public function data()
    {
        $badges = $this->badgeRepository->getAllBadges();

        return DataTables::of($badges)
            ->addColumn('select', function ($badge) {
                return '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $badge->id . '"></div>';
            })
            ->editColumn('name', function ($badge) {
                return e($badge->name_locale);
            })
            ->addColumn('preview', function ($badge) {
                return '<span class="badge" style="background-color: ' . e($badge->color_bg) . '; color: ' . e($badge->color_text) . ';">
                            <i class="' . e($badge->icon) . ' me-1"></i> ' . e($badge->name_locale) . '
                        </span>';
            })
            ->addColumn('edit', function ($badge) {
                return '<a href="' . route('teacher.badges.edit', $badge->id) . '" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen-to-square"></i></a>';
            })
            ->addColumn('delete', function ($badge) {
                return '<form action="' . route('teacher.badges.delete', $badge->id) . '" method="POST" class="d-inline-block delete-action">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button></form>';
            })
            ->rawColumns(['select', 'preview', 'edit', 'delete'])
            ->toJson();
    }

    public function create()
    {
        $pageTitle = 'Thêm huy hiệu mới';

        return view('teacher::admin.badges.create', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_vi' => 'required|max:255',
            'name_en' => 'required|max:255',
            'code' => 'required|unique:teacher_badges,code',
            'color_bg' => 'required',
            'color_text' => 'required',
        ]);

        $data = [
            'name' => [
                'vi' => $request->name_vi,
                'en' => $request->name_en,
            ],
            'code' => $request->code,
            'icon' => $request->icon,
            'color_bg' => $request->color_bg,
            'color_text' => $request->color_text,
            'is_active' => $request->has('is_active'),
            'description' => [
                'vi' => $request->description_vi,
                'en' => $request->description_en,
            ],
        ];

        $badge = $this->badgeRepository->create($data);

        activity_log(
            action: 'create',
            subject: $badge,
            properties: ['data' => $data],
            logName: 'admin_teacher_badge_management',
            description: 'Tạo mới huy hiệu giảng viên: ' . $request->name_vi
        );

        return redirect()->route('teacher.badges.index')->with('msg', 'Thêm huy hiệu thành công');
    }

    public function edit($id)
    {
        $badge = $this->badgeRepository->find($id);
        if (!$badge) {
            abort(404);
        }

        $pageTitle = 'Chỉnh sửa huy hiệu';

        return view('teacher::admin.badges.edit', compact('badge', 'pageTitle'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name_vi' => 'required|max:255',
            'name_en' => 'required|max:255',
            'code' => 'required|unique:teacher_badges,code,' . $id,
            'color_bg' => 'required',
            'color_text' => 'required',
        ]);

        $data = [
            'name' => [
                'vi' => $request->name_vi,
                'en' => $request->name_en,
            ],
            'code' => $request->code,
            'icon' => $request->icon,
            'color_bg' => $request->color_bg,
            'color_text' => $request->color_text,
            'is_active' => $request->has('is_active'),
            'description' => [
                'vi' => $request->description_vi,
                'en' => $request->description_en,
            ],
        ];

        $badge = $this->badgeRepository->find($id);
        $old = $badge ? $badge->toArray() : [];
        
        $this->badgeRepository->update($id, $data);
        
        $badge->refresh();

        activity_log(
            action: 'update',
            subject: $badge,
            properties: [
                'old' => $old,
                'new' => $badge->toArray(),
            ],
            logName: 'admin_teacher_badge_management',
            description: 'Cập nhật huy hiệu giảng viên: ' . $badge->name_locale
        );

        return back()->with('msg', 'Cập nhật huy hiệu thành công');
    }

    public function delete($id)
    {
        $badge = $this->badgeRepository->find($id);
        $snapshot = $badge ? $badge->toArray() : [];
        $this->badgeRepository->delete($id);

        activity_log(
            action: 'delete',
            subject: $badge,
            properties: ['data' => $snapshot],
            logName: 'admin_teacher_badge_management',
            description: 'Xóa huy hiệu giảng viên: ' . ($snapshot['name']['vi'] ?? 'N/A')
        );

        return back()->with('msg', 'Xóa huy hiệu thành công');
    }

    public function trash()
    {
        $pageTitle = 'Thùng rác huy hiệu';

        return view('teacher::admin.badges.trash', compact('pageTitle'));
    }

    public function trashData()
    {
        $badges = $this->badgeRepository->getTrashBadges();

        return DataTables::of($badges)
            ->editColumn('name', function ($badge) {
                return e($badge->name_locale);
            })
            ->addColumn('restore', function ($badge) {
                return '<form action="' . route('teacher.badges.restore', $badge->id) . '" method="POST" class="d-inline-block">' . csrf_field() . '<button type="submit" class="btn btn-success btn-sm">Khôi phục</button></form>';
            })
            ->addColumn('force_delete', function ($badge) {
                return '<form action="' . route('teacher.badges.force-delete', $badge->id) . '" method="POST" class="d-inline-block" onsubmit="return confirm(\'Xóa vĩnh viễn?\')">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm">Xóa vĩnh viễn</button></form>';
            })
            ->rawColumns(['restore', 'force_delete'])
            ->toJson();
    }

    public function restore($id)
    {
        $badge = $this->badgeRepository->getTrashBadges()->find($id);
        if ($badge) {
            $snapshot = $badge->toArray();
            $badge->restore();

            activity_log(
                action: 'restore',
                subject: $badge,
                properties: ['data' => $snapshot],
                logName: 'admin_teacher_badge_management',
                description: 'Khôi phục huy hiệu giảng viên: ' . $badge->name_locale
            );
        }

        return back()->with('msg', 'Khôi phục huy hiệu thành công');
    }

    public function forceDelete($id)
    {
        $badge = $this->badgeRepository->getTrashBadges()->find($id);
        if ($badge) {
            $snapshot = $badge->toArray();
            $badge->forceDelete();

            activity_log(
                action: 'force_delete',
                subject: null,
                properties: ['data' => $snapshot],
                logName: 'admin_teacher_badge_management',
                description: 'Xóa vĩnh viễn huy hiệu giảng viên: ' . ($snapshot['name']['vi'] ?? 'N/A')
            );
        }

        return back()->with('msg', 'Đã xóa vĩnh viễn huy hiệu');
    }
}
