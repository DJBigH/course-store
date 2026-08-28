<?php

namespace Modules\Categories\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Categories\src\Repositories\CategoriesRepository;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Categories\src\Models\Category;
use Modules\Categories\src\Repositories\CategoriesRepositoryInterface;
use Modules\Categories\src\Requests\CategoriesRequest;

class CategoriesController extends Controller
{

    protected $category;

    public function __construct(CategoriesRepositoryInterface $categoriesRepository)
    {
        $this->category = $categoriesRepository;
    }

    public function index()
    {
        $pageTitle = 'Quản lý chuyên mục';

        return view('categories::lists', compact('pageTitle'));
    }

    public function trash()
    {
        $pageTitle = 'Thùng rác danh mục';

        return view('categories::trash', compact('pageTitle'));
    }

    public function data()
    {
        $categories = $this->category->getCategories();
        $categories = DataTables::of($categories)
            // ->addColumn('edit', function ($category) {
            //     return '<a href="' . route('categories.edit', $category->id) . '" class="btn btn-warning">Sửa</a>';
            // })
            // ->addColumn('delete', function ($category) {
            //     return '<a href="' . route('categories.delete', $category->id) . '" class="btn btn-danger delete-action">Xóa</a>';
            // })
            // ->addColumn('link', function ($category) {
            //     return '<a href="#" class="btn btn-primary">Xem</a>';
            // })
            // ->editColumn('created_at', function ($category) {
            //     return Carbon::parse($category->created_at)->format('d/m/Y H:i:s');
            // })
            // ->rawColumns(['edit', 'delete', 'link'])
            ->toArray();

        $categories['data'] = $this->getCategoriesTable($categories['data']);
        return $categories;
    }

    public function trashData()
    {
        $canRestore = auth()->user()?->canAnyPermission(['categories.soft_delete', 'categories.delete']);
        $canForceDelete = auth()->user()?->hasPermission('categories.force_delete');

        $categories = Category::query()
            ->onlyTrashed()
            ->latest('deleted_at');

        return DataTables::of($categories)
            ->addColumn('select', fn($category) => '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $category->id . '"></div>')
            ->addColumn('name', fn($category) => e($category->name_locale))
            ->addColumn('link', function ($category) {
                return '<a href="' . route('categories.category', [
                    'locale' => app()->getLocale(),
                    'slug' => $category->slug_locale,
                ]) . '" class="btn btn-primary" target="_blank">Xem</a>';
            })
            ->addColumn('deleted_at', fn($category) => Carbon::parse($category->deleted_at)->format('d/m/Y H:i:s'))
            ->addColumn('restore', function ($category) use ($canRestore) {
                if (!$canRestore) {
                    return '<span class="text-muted small">Không có quyền</span>';
                }

                return '<form method="POST" action="' . route('categories.restore', $category->id) . '" class="d-inline-block">'
                    . csrf_field()
                    . '<button type="submit" class="btn btn-success">Khôi phục</button>'
                    . '</form>';
            })
            ->addColumn('force_delete', function ($category) use ($canForceDelete) {
                if (!$canForceDelete) {
                    return '<span class="text-muted small">Không có quyền</span>';
                }

                return '<form method="POST" action="' . route('categories.force-delete', $category->id) . '" class="d-inline-block" onsubmit="return confirm(\'Xóa vĩnh viễn danh mục này?\');">'
                    . csrf_field()
                    . method_field('DELETE')
                    . '<button type="submit" class="btn btn-outline-danger">Xóa vĩnh viễn</button>'
                    . '</form>';
            })
            ->rawColumns(['select', 'link', 'restore', 'force_delete'])
            ->toJson();
    }

    public function getCategoriesTable($categories, $char = '', &$result = [])
    {
        $user = auth()->user();
        $canLogs = $user?->hasPermission('categories.logs');
        $canEdit = $user?->hasPermission('categories.edit');
        $canDelete = $user?->canAnyPermission(['categories.soft_delete', 'categories.delete']);

        if (empty($categories)) {
            return $result;
        }

        foreach ($categories as $key => $category) {
            $row = $category;
            $localizedName = $category['name'] ?? '';
            if (app()->getLocale() === 'zh') {
                $localizedName = $category['name_zh'] ?? $category['name'] ?? $category['name_en'] ?? $category['name_ko'] ?? $category['name_ja'] ?? '';
            } elseif (app()->getLocale() === 'ja') {
                $localizedName = $category['name_ja'] ?? $category['name'] ?? $category['name_en'] ?? $category['name_ko'] ?? $category['name_zh'] ?? '';
            } elseif (app()->getLocale() === 'ko') {
                $localizedName = $category['name_ko'] ?? $category['name'] ?? $category['name_en'] ?? $category['name_ja'] ?? $category['name_zh'] ?? '';
            } elseif (app()->getLocale() === 'en') {
                $localizedName = $category['name_en'] ?? $category['name'] ?? $category['name_ko'] ?? $category['name_ja'] ?? $category['name_zh'] ?? '';
            }
            $row['name'] = $char . $localizedName;
            $row['select'] = '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $category['id'] . '"></div>';
            $row['logs'] = $canLogs
                ? '<a href="' . route('categories.logs', $category['id']) . '" class="btn btn-light border">Lịch sử</a>'
                : '<span class="text-muted small">Không có quyền</span>';
            $row['edit'] = $canEdit
                ? '<a href="' . route('categories.edit', $category['id']) . '" class="btn btn-warning">Sửa</a>'
                : '<span class="text-muted small">Không có quyền</span>';
            $row['delete'] = $canDelete
                ? '<a href="' . route('categories.delete', $category['id']) . '" class="btn btn-outline-danger delete-action">Xóa</a>'
                : '<span class="text-muted small">Không có quyền</span>';
            $locale = app()->getLocale();
            if ($locale === 'zh') {
                $slug = $category['slug_zh'] ?? $category['slug'] ?? $category['slug_en'] ?? $category['slug_ko'] ?? $category['slug_ja'];
            } elseif ($locale === 'ja') {
                $slug = $category['slug_ja'] ?? $category['slug'] ?? $category['slug_en'] ?? $category['slug_ko'] ?? $category['slug_zh'];
            } elseif ($locale === 'ko') {
                $slug = $category['slug_ko'] ?? $category['slug'] ?? $category['slug_en'] ?? $category['slug_ja'] ?? $category['slug_zh'];
            } elseif ($locale === 'en') {
                $slug = $category['slug_en'] ?? $category['slug'] ?? $category['slug_ko'] ?? $category['slug_ja'] ?? $category['slug_zh'];
            } else {
                $slug = $category['slug'] ?? $category['slug_en'] ?? $category['slug_ko'] ?? $category['slug_ja'] ?? $category['slug_zh'];
            }

            $row['link'] = '<a href="' . route('categories.category', [
                'locale' => $locale,
                'slug' => $slug
            ]) . '" class="btn btn-primary" target="_blank">Xem</a>';

            $row['created_at'] = Carbon::parse($category['created_at'])->format('d/m/Y H:i:s');
            unset($row['sub_categories']);
            unset($row['updated_at']);
            $result[] = $row;
            if (!empty($category['sub_categories'])) {
                $this->getCategoriesTable($category['sub_categories'], $char . '|--', $result);
            }
        }

        return $result;

        if (!empty($categories)) {
            foreach ($categories as $key => $category) {
                $row = $category;
                $localizedName = $category['name'] ?? '';
                if (app()->getLocale() === 'zh') {
                    $localizedName = $category['name_zh'] ?? $category['name'] ?? $category['name_en'] ?? $category['name_ko'] ?? $category['name_ja'] ?? '';
                } elseif (app()->getLocale() === 'ja') {
                    $localizedName = $category['name_ja'] ?? $category['name'] ?? $category['name_en'] ?? $category['name_ko'] ?? $category['name_zh'] ?? '';
                } elseif (app()->getLocale() === 'ko') {
                    $localizedName = $category['name_ko'] ?? $category['name'] ?? $category['name_en'] ?? $category['name_ja'] ?? $category['name_zh'] ?? '';
                } elseif (app()->getLocale() === 'en') {
                    $localizedName = $category['name_en'] ?? $category['name'] ?? $category['name_ko'] ?? $category['name_ja'] ?? $category['name_zh'] ?? '';
                }
                $row['name'] = $char . $localizedName;
                $row['select'] = '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $category['id'] . '"></div>';
                $row['logs'] = '<a href="' . route('categories.logs', $category['id']) . '" class="btn btn-light border">Lịch sử</a>';
                $row['edit'] = '<a href="' . route('categories.edit', $category['id']) . '" class="btn btn-warning">Sửa</a>';
                $row['delete'] = '<a href="' . route('categories.delete', $category['id']) . '" class="btn btn-outline-danger delete-action">Xóa</a>';
                $locale = app()->getLocale();
                if ($locale === 'zh') {
                    $slug = $category['slug_zh'] ?? $category['slug'] ?? $category['slug_en'] ?? $category['slug_ko'] ?? $category['slug_ja'];
                } elseif ($locale === 'ja') {
                    $slug = $category['slug_ja'] ?? $category['slug'] ?? $category['slug_en'] ?? $category['slug_ko'] ?? $category['slug_zh'];
                } elseif ($locale === 'ko') {
                    $slug = $category['slug_ko'] ?? $category['slug'] ?? $category['slug_en'] ?? $category['slug_ja'] ?? $category['slug_zh'];
                } elseif ($locale === 'en') {
                    $slug = $category['slug_en'] ?? $category['slug'] ?? $category['slug_ko'] ?? $category['slug_ja'] ?? $category['slug_zh'];
                } else {
                    $slug = $category['slug'] ?? $category['slug_en'] ?? $category['slug_ko'] ?? $category['slug_ja'] ?? $category['slug_zh'];
                }

                $row['link'] = '<a href="' . route('categories.category', [
                    'locale' => $locale,
                    'slug' => $slug
                ]) . '" class="btn btn-primary" target="_blank">Xem</a>';

                $row['created_at'] = Carbon::parse($category['created_at'])->format('d/m/Y H:i:s');
                unset($row['sub_categories']);
                unset($row['updated_at']);
                $result[] = $row;
                if (!empty($category['sub_categories'])) {
                    $this->getCategoriesTable($category['sub_categories'], $char . '|--', $result);
                }
            }
        }
        return $result;
    }

    public function create()
    {
        $pageTitle = 'Thêm mới chuyên mục';
        $categories = $this->category->getAllCategories();
        return view('categories::create', compact('pageTitle', 'categories'));
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('bulk_action');
        $selectedIds = collect(explode(',', (string) $request->input('selected_ids', '')))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            throw ValidationException::withMessages([
                'bulk_action' => 'Vui lòng chọn ít nhất một chuyên mục.',
            ]);
        }

        $categories = collect();
        foreach ($selectedIds as $id) {
            $category = $this->category->find($id);
            if ($category) {
                $categories->push($category);
            }
        }

        if ($categories->isEmpty()) {
            return back()->with('msg_danger', 'Không tìm thấy chuyên mục để xử lý.');
        }

        if ($action === 'delete') {
            foreach ($categories as $category) {
                $snapshot = method_exists($category, 'toArray') ? $category->toArray() : (array) $category;
                $this->softDeleteCategory($category);

                activity_log(
                    action: 'delete',
                    subject: $category,
                    properties: [
                        'data' => $snapshot,
                    ],
                    logName: 'Xóa hàng loạt',
                    description: 'Xóa chuyên mục'
                );
            }

            return back()->with('msg', 'Đã xóa ' . $categories->count() . ' chuyên mục.');
        }

        return back()->with('msg_danger', 'Thao tác hàng loạt không hợp lệ.');
    }

    public function trashBulkAction(Request $request)
    {
        $action = $request->input('bulk_action');
        $selectedIds = collect(explode(',', (string) $request->input('selected_ids', '')))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            throw ValidationException::withMessages([
                'bulk_action' => 'Vui lòng chọn ít nhất một chuyên mục trong thùng rác.',
            ]);
        }

        $categories = Category::query()->onlyTrashed()->whereIn('id', $selectedIds)->get();

        if ($categories->isEmpty()) {
            return back()->with('msg_danger', 'Không tìm thấy chuyên mục hợp lệ trong thùng rác.');
        }

        if ($action === 'restore') {
            foreach ($categories as $category) {
                $this->restoreCategory($category);
                activity_log(
                    action: 'restore',
                    subject: $category->fresh(),
                    properties: [
                        'restored_from_trash' => true,
                        'category_id' => $category->id,
                    ],
                    logName: 'Khôi phục hàng loạt',
                    description: 'Khôi phục chuyên mục từ thùng rác'
                );
            }

            return back()->with('msg', 'Đã khôi phục ' . $categories->count() . ' chuyên mục.');
        }

        if ($action === 'force_delete') {
            foreach ($categories as $category) {
                $snapshot = method_exists($category, 'toArray') ? $category->toArray() : (array) $category;
                $this->forceDeleteCategory($category);
                activity_log(
                    action: 'force_delete',
                    subject: $category,
                    properties: [
                        'data' => $snapshot,
                        'deleted_permanently' => true,
                    ],
                    logName: 'Xóa vĩnh viễn hàng loạt',
                    description: 'Xóa vĩnh viễn chuyên mục khỏi thùng rác'
                );
            }

            return back()->with('msg', 'Đã xóa vĩnh viễn ' . $categories->count() . ' chuyên mục.');
        }

        return back()->with('msg_danger', 'Thao tác trong thùng rác không hợp lệ.');
    }

    public function store(CategoriesRequest $request)
    {
        $dataInsert = [
            'name'      => $request->name,
            'name_en'   => $request->name_en,
            'name_ko'   => $request->name_ko,
            'name_ja'   => $request->name_ja,
            'name_zh'   => $request->name_zh,
            'slug'      => $request->slug,
            'slug_en'   => $request->slug_en,
            'slug_ko'   => $request->slug_ko,
            'slug_ja'   => $request->slug_ja,
            'slug_zh'   => $request->slug_zh,
            'parent_id' => $request->parent_id,
        ];

        $cate = $this->category->create($dataInsert);

        if (!$cate) {
            $cate = $this->category->findBySlug($dataInsert['slug'] ?? null) ?? null;
        }

        activity_log(
            action: 'create',
            subject: $cate,
            properties: [
                'data' => $dataInsert,
            ],
            logName: 'Thêm mới',
            description: 'Tạo mới chuyên mục'
        );

        return redirect()->route('categories.index')->with('msg', __('categories::messages.create.success'));
    }


    public function edit($id)
    {
        $pageTitle = 'Cập nhật chuyên mục';
        $category = $this->category->find($id);
        $categories = $this->category->getAllCategories();
        if (empty($category)) {
            abort(404);
        }

        return view('categories::edit', compact('category', 'pageTitle', 'categories'));
    }

    public function update(CategoriesRequest $request, $id)
    {
        $cate = $this->category->find($id);
        if (empty($cate)) abort(404);

        $old = is_object($cate) && method_exists($cate, 'toArray') ? $cate->toArray() : (array) $cate;

        $data = $request->except('_token');

        $status = $this->category->update($id, $data);

        if (!empty($status)) {
            $cateFresh = $this->category->find($id);
            $new = is_object($cateFresh) && method_exists($cateFresh, 'toArray') ? $cateFresh->toArray() : (array) $cateFresh;

            activity_log(
                action: 'update',
                subject: $cateFresh ?? $cate,
                properties: [
                    'old' => $old,
                    'new' => $new,
                ],
                logName: 'Cập nhật',
                description: 'Cập nhật chuyên mục'
            );

            return back()->with('msg', __('categories::messages.update.success'));
        }

        return back()->with('msg_danger', __('categories::messages.update.failure'));
    }


    public function delete($id)
    {
        $cate = $this->category->find($id);
        if (empty($cate)) abort(404);

        $snapshot = is_object($cate) && method_exists($cate, 'toArray') ? $cate->toArray() : (array) $cate;

        $status = $this->softDeleteCategory($cate);

        if ($status) {
            activity_log(
                action: 'delete',
                subject: $cate,
                properties: [
                    'data' => $snapshot,
                ],
                logName: 'category',
                description: 'Xóa chuyên mục'
            );

            return back()->with('msg', __('categories::messages.delete.success'));
        }

        return back()->with('msg_danger', 'Xóa thất bại');
    }


    public function restore($id)
    {
        $category = Category::query()->onlyTrashed()->find($id);

        if (!$category) {
            abort(404);
        }

        $this->restoreCategory($category);

        activity_log(
            action: 'restore',
            subject: $category->fresh(),
            properties: [
                'restored_from_trash' => true,
                'category_id' => $category->id,
            ],
            logName: 'Khôi phục',
            description: 'Khôi phục chuyên mục thành công.'
        );

        return back()->with('msg', 'Khôi phục chuyên mục thành công.');
    }

    public function forceDelete($id)
    {
        $category = Category::query()->onlyTrashed()->find($id);

        if (!$category) {
            abort(404);
        }

        $snapshot = method_exists($category, 'toArray') ? $category->toArray() : (array) $category;
        $this->forceDeleteCategory($category);

        activity_log(
            action: 'force_delete',
            subject: $category,
            properties: [
                'data' => $snapshot,
                'deleted_permanently' => true,
            ],
            logName: 'Xóa vĩnh viễn',
            description: 'Xóa vĩnh viễn chuyên mục khỏi thùng rác'
        );

        return back()->with('msg', 'Đã xóa vĩnh viễn chuyên mục.');
    }


    public function logs(Request $request, $id)
    {
        $cate = $this->category->find($id);
        if (empty($cate)) abort(404);

        $pageTitle = "Lịch sử: {$cate->name}";

        $query = ActiveLog::query()
            ->where('subject_type', get_class($cate))
            ->where('subject_id', $cate->id)->withoutGlobalScopes();

        // 🔹 Filter theo action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // 🔹 Filter theo khoảng thời gian
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // 🔹 Filter theo keyword (description hoặc log_name)
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('description', 'like', "%{$q}%")
                    ->orWhere('log_name', 'like', "%{$q}%");
            });
        }

        $logs = $query
            ->latest()
            ->paginate(config('paginate.log_limit'))
            ->withQueryString();

        return view('categories::logs', compact('pageTitle', 'cate', 'logs'));
    }

    protected function softDeleteCategory(Category $category): bool
    {
        foreach (Category::query()->where('parent_id', $category->id)->get() as $child) {
            $this->softDeleteCategory($child);
        }

        return (bool) $category->delete();
    }

    protected function restoreCategory(Category $category): void
    {
        $category->restore();

        foreach (Category::query()->onlyTrashed()->where('parent_id', $category->id)->get() as $child) {
            $this->restoreCategory($child);
        }
    }

    protected function forceDeleteCategory(Category $category): void
    {
        foreach (Category::query()->withTrashed()->where('parent_id', $category->id)->get() as $child) {
            $this->forceDeleteCategory($child);
        }

        $category->courses()->detach();
        $category->forceDelete();
    }
}
