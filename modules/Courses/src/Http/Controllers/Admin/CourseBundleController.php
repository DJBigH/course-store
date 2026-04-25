<?php

namespace Modules\Courses\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Modules\Courses\src\Models\CourseBundle;
use Modules\Teacher\src\Repositories\TeacherRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;

class CourseBundleController extends Controller
{
    protected $teacherRepository;

    public function __construct(TeacherRepositoryInterface $teacherRepository)
    {
        $this->teacherRepository = $teacherRepository;
    }

    public function index()
    {
        $pageTitle = 'Quản lý Combo khóa học';
        $teachers = $this->teacherRepository->getAllTeacher()->get(['id', 'name']);
        
        return view('courses::admin.bundles.index', compact('pageTitle', 'teachers'));
    }

    public function data(Request $request)
    {
        $user = auth()->user();
        $canEdit = $user?->hasPermission('courses.edit');
        $canDelete = $user?->hasPermission('courses.delete');

        $bundles = CourseBundle::with(['teacher', 'items.course'])
            ->withCount('items')
            ->when($request->filled('status_filter'), function ($query) use ($request) {
                $query->where('status', (int) $request->status_filter);
            })
            ->when($request->filled('teacher_filter'), function ($query) use ($request) {
                $query->where('teacher_id', (int) $request->teacher_filter);
            })
            ->orderBy('position', 'asc')
            ->orderBy('created_at', 'desc');

        return DataTables::of($bundles)
            ->addColumn('select', function ($bundle) {
                return '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bundle-row-checkbox" value="' . $bundle->id . '"></div>';
            })
            ->addColumn('overview', function ($bundle) {
                $name = e($bundle->name ?: 'Chưa có tên');
                $teacher = e(optional($bundle->teacher)->name ?: 'Chưa gán giảng viên');
                $itemsCount = $bundle->items_count;
                
                $hotBadge = $bundle->is_hot ? '<span class="badge bg-danger ms-2" style="font-size: 10px;">HOT</span>' : '';
                
                return '
                    <div class="course-cell">
                        <div class="course-cell__title d-flex align-items-center">' . $name . $hotBadge . '</div>
                        <div class="course-cell__meta">
                            <span><i class="fa-solid fa-chalkboard-user"></i> ' . $teacher . '</span>
                            <span><i class="fa-solid fa-layer-group"></i> ' . $itemsCount . ' khóa học</span>
                        </div>
                    </div>
                ';
            })
            ->editColumn('price', function ($bundle) {
                return '<strong>' . number_format($bundle->price, 0) . ' đ</strong>';
            })
            ->editColumn('status', function ($bundle) {
                return $bundle->status
                    ? '<span class="badge rounded-pill text-success-emphasis bg-success-subtle">Đang hiển thị</span>'
                    : '<span class="badge rounded-pill text-warning-emphasis bg-warning-subtle">Đang ẩn</span>';
            })
            ->editColumn('position', function ($bundle) use ($canEdit) {
                if (!$canEdit) return $bundle->position;
                return '<input type="number" class="form-control form-control-sm update-position" data-id="' . $bundle->id . '" value="' . $bundle->position . '" style="width: 70px; margin: 0 auto;">';
            })
            ->addColumn('toggle_hot', function ($bundle) use ($canEdit) {
                if (!$canEdit) return '';
                $class = $bundle->is_hot ? 'btn btn-danger' : 'btn btn-outline-danger';
                $icon = $bundle->is_hot ? 'fa-solid fa-fire' : 'fa-solid fa-fire';
                return '
                    <form method="POST" action="' . route('courses.bundles.toggle-hot', $bundle->id) . '">
                        ' . csrf_field() . '
                        <button type="submit" class="' . $class . ' btn-sm" title="Bật/Tắt trạng thái HOT"><i class="' . $icon . '"></i></button>
                    </form>
                ';
            })
            ->addColumn('toggle_status', function ($bundle) use ($canEdit) {
                if (!$canEdit) return '';
                $label = $bundle->status ? 'Ẩn' : 'Hiện';
                $class = $bundle->status ? 'btn btn-light border' : 'btn btn-success';
                return '
                    <form method="POST" action="' . route('courses.bundles.toggle-status', $bundle->id) . '">
                        ' . csrf_field() . '
                        <button type="submit" class="' . $class . ' btn-sm">' . $label . '</button>
                    </form>
                ';
            })
            ->addColumn('delete', function ($bundle) use ($canDelete) {
                if (!$canDelete) return '';
                return '
                    <form method="POST" action="' . route('courses.bundles.delete', $bundle->id) . '" onsubmit="return confirm(\'Xác nhận xóa combo này?\')">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="btn btn-outline-danger btn-sm">Xóa</button>
                    </form>
                ';
            })
            ->editColumn('created_at', function ($bundle) {
                return Carbon::parse($bundle->created_at)->format('d/m/Y H:i');
            })
            ->rawColumns(['select', 'overview', 'price', 'status', 'position', 'toggle_hot', 'toggle_status', 'delete'])
            ->toJson();
    }

    public function toggleStatus($id)
    {
        $bundle = CourseBundle::findOrFail($id);
        $bundle->status = !$bundle->status;
        $bundle->save();

        return back()->with('msg', 'Đã cập nhật trạng thái combo.');
    }

    public function toggleHot($id)
    {
        $bundle = CourseBundle::findOrFail($id);
        $bundle->is_hot = !$bundle->is_hot;
        $bundle->save();

        return back()->with('msg', 'Đã cập nhật trạng thái HOT cho combo.');
    }

    public function updatePosition(Request $request)
    {
        $bundle = CourseBundle::findOrFail($request->id);
        $bundle->position = (int) $request->position;
        $bundle->save();

        return response()->json(['success' => true]);
    }

    public function delete($id)
    {
        $bundle = CourseBundle::findOrFail($id);
        $bundle->delete();

        return back()->with('msg', 'Đã xóa combo thành công.');
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('bulk_action');
        $selectedIds = explode(',', (string) $request->input('selected_ids', ''));
        
        if (empty($selectedIds)) return back()->with('msg_danger', 'Vui lòng chọn combo.');

        if ($action === 'delete') {
            CourseBundle::whereIn('id', $selectedIds)->delete();
            return back()->with('msg', 'Đã xóa các combo được chọn.');
        }

        if ($action === 'show') {
            CourseBundle::whereIn('id', $selectedIds)->update(['status' => 1]);
            return back()->with('msg', 'Đã hiển thị các combo được chọn.');
        }

        if ($action === 'hide') {
            CourseBundle::whereIn('id', $selectedIds)->update(['status' => 0]);
            return back()->with('msg', 'Đã ẩn các combo được chọn.');
        }

        if ($action === 'hot') {
            CourseBundle::whereIn('id', $selectedIds)->update(['is_hot' => 1]);
            return back()->with('msg', 'Đã đánh dấu HOT các combo được chọn.');
        }

        if ($action === 'unhot') {
            CourseBundle::whereIn('id', $selectedIds)->update(['is_hot' => 0]);
            return back()->with('msg', 'Đã bỏ đánh dấu HOT các combo được chọn.');
        }

        return back();
    }
}
