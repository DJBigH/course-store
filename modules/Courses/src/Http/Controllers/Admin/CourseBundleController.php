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
                $comingSoonBadge = $bundle->is_coming_soon ? '<span class="badge bg-warning text-dark ms-1" style="font-size: 10px;">SẮP RA MẮT</span>' : '';
                
                $stockInfo = $bundle->quantity !== null ? '<span><i class="fa-solid fa-box"></i> Kho: ' . $bundle->quantity . '</span>' : '<span><i class="fa-solid fa-infinity"></i> Vĩnh viễn</span>';
                $deadlineInfo = $bundle->end_at ? '<span class="text-danger"><i class="fa-solid fa-calendar-xmark"></i> Hết hạn: ' . $bundle->end_at->format('d/m/Y H:i') . '</span>' : '';

                return '
                    <div class="course-cell">
                        <div class="course-cell__title d-flex align-items-center">' . $name . $hotBadge . $comingSoonBadge . '</div>
                        <div class="course-cell__meta">
                            <span><i class="fa-solid fa-chalkboard-user"></i> ' . $teacher . '</span>
                            <span><i class="fa-solid fa-layer-group"></i> ' . $itemsCount . ' khóa học</span>
                            ' . $stockInfo . '
                            ' . $deadlineInfo . '
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
            ->addColumn('action', function ($bundle) use ($canEdit, $canDelete) {
                $btns = '';
                if ($canEdit) {
                    $btns .= '<a href="' . route('courses.bundles.edit', $bundle->id) . '" class="btn btn-primary btn-sm me-1" title="Sửa"><i class="fa-solid fa-pen-to-square"></i></a>';
                }
                if ($canDelete) {
                    $btns .= '
                        <form method="POST" action="' . route('courses.bundles.delete', $bundle->id) . '" onsubmit="return confirm(\'Xác nhận xóa combo này?\')" class="d-inline-block">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    ';
                }
                return $btns;
            })
            ->editColumn('created_at', function ($bundle) {
                return Carbon::parse($bundle->created_at)->format('d/m/Y H:i');
            })
            ->rawColumns(['select', 'overview', 'price', 'status', 'position', 'toggle_hot', 'toggle_status', 'action'])
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

    public function create()
    {
        $pageTitle = 'Thêm Combo khóa học';
        $teachers = $this->teacherRepository->getAllTeacher()->get(['id', 'name']);
        
        return view('courses::admin.bundles.create', compact('pageTitle', 'teachers'));
    }

    public function store(\Modules\Teacher\src\Http\Requests\CourseBundleRequest $request)
    {
        $data = $request->validated();
        
        // Admin cần teacher_id
        if (!$request->filled('teacher_id')) {
            return back()->withInput()->withErrors(['teacher_id' => 'Vui lòng chọn giảng viên.']);
        }

        $baseSlug = \Illuminate\Support\Str::slug((string) $data['name']);
        $slug = $this->resolveUniqueBundleSlug((int)$data['teacher_id'], $baseSlug !== '' ? $baseSlug : 'combo-khoa-hoc');

        \Illuminate\Support\Facades\DB::transaction(function () use ($data, $slug) {
            $bundle = CourseBundle::create([
                'teacher_id' => $data['teacher_id'],
                'name' => trim((string) $data['name']),
                'slug' => $slug,
                'description' => trim((string) ($data['description'] ?? '')),
                'thumbnail' => trim((string) ($data['thumbnail'] ?? '')),
                'price' => (float) $data['price'],
                'sale_price' => isset($data['sale_price']) && $data['sale_price'] !== '' ? (float) $data['sale_price'] : null,
                'status' => (bool) ($data['status'] ?? false),
                'quantity' => isset($data['quantity']) && $data['quantity'] !== '' ? (int) $data['quantity'] : null,
                'is_coming_soon' => (bool) ($data['is_coming_soon'] ?? false),
                'coming_soon_start_at' => $data['coming_soon_start_at'] ?? null,
                'end_at' => $data['end_at'] ?? null,
                'position' => ((int) CourseBundle::query()->where('teacher_id', $data['teacher_id'])->max('position')) + 1,
            ]);

            if (!empty($data['course_ids'])) {
                foreach ($data['course_ids'] as $index => $courseId) {
                    $bundle->items()->create([
                        'course_id' => $courseId,
                        'position' => $index + 1,
                    ]);
                }
            }
        });

        return redirect()->route('courses.bundles.index')->with('msg', 'Thêm combo thành công.');
    }

    public function edit($id)
    {
        $bundle = CourseBundle::with('items')->findOrFail($id);
        $pageTitle = 'Chỉnh sửa Combo khóa học';
        $teachers = $this->teacherRepository->getAllTeacher()->get(['id', 'name']);
        
        return view('courses::admin.bundles.edit', compact('pageTitle', 'teachers', 'bundle'));
    }

    public function update(\Modules\Teacher\src\Http\Requests\CourseBundleRequest $request, $id)
    {
        $bundle = CourseBundle::findOrFail($id);
        $data = $request->validated();

        $baseSlug = \Illuminate\Support\Str::slug((string) $data['name']);
        $slug = $this->resolveUniqueBundleSlug((int)$bundle->teacher_id, $baseSlug !== '' ? $baseSlug : 'combo-khoa-hoc', $bundle->id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($bundle, $data, $slug) {
            $bundle->update([
                'name' => trim((string) $data['name']),
                'slug' => $slug,
                'description' => trim((string) ($data['description'] ?? '')),
                'thumbnail' => trim((string) ($data['thumbnail'] ?? '')),
                'price' => (float) $data['price'],
                'sale_price' => isset($data['sale_price']) && $data['sale_price'] !== '' ? (float) $data['sale_price'] : null,
                'status' => (bool) ($data['status'] ?? false),
                'quantity' => isset($data['quantity']) && $data['quantity'] !== '' ? (int) $data['quantity'] : null,
                'is_coming_soon' => (bool) ($data['is_coming_soon'] ?? false),
                'coming_soon_start_at' => $data['coming_soon_start_at'] ?? null,
                'end_at' => $data['end_at'] ?? null,
            ]);

            $bundle->items()->delete();
            if (!empty($data['course_ids'])) {
                foreach ($data['course_ids'] as $index => $courseId) {
                    $bundle->items()->create([
                        'course_id' => $courseId,
                        'position' => $index + 1,
                    ]);
                }
            }
        });

        return redirect()->route('courses.bundles.index')->with('msg', 'Cập nhật combo thành công.');
    }

    protected function resolveUniqueBundleSlug(int $teacherId, string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug;
        $index = 2;

        while (
            CourseBundle::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $index;
            $index++;
        }

        return $slug;
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
