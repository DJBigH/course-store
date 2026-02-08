<?php

namespace Modules\Categories\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Categories\src\Repositories\CategoriesRepository;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\ActiveLogs\src\Models\ActiveLog;
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

    public function getCategoriesTable($categories, $char = '', &$result = [])
    {
        if (!empty($categories)) {
            foreach ($categories as $key => $category) {
                $row = $category;
                $row['name'] = $char . $category['name'];
                $row['logs'] = '<a href="' . route('categories.logs', $category['id']) . '" class="btn btn-info">Lịch sử</a>';
                $row['edit'] = '<a href="' . route('categories.edit', $category['id']) . '" class="btn btn-warning">Sửa</a>';
                $row['delete'] = '<a href="' . route('categories.delete', $category['id']) . '" class="btn btn-danger delete-action">Xóa</a>';
                $row['link'] = '<a href="' . route('categories.category', $category['slug']) . '" class="btn btn-primary" target="_blank">Xem</a>';
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

    public function store(CategoriesRequest $request)
    {
        $dataInsert = [
            'name'      => $request->name,
            'slug'      => $request->slug,
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
        $pageTitle = 'Cập nhập chuyên mục';
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
                logName: 'Cập nhập',
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

        $status = $this->category->delete($id);

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
}
