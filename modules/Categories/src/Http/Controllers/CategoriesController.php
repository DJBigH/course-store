<?php

namespace Modules\Categories\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Categories\src\Repositories\CategoriesRepository;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
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
            'name' => $request->name,
            'slug' => $request->slug,
            'parent_id' => $request->parent_id,
        ];
        $this->category->create($dataInsert);

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
        $data = $request->except('_token');

        $status = $this->category->update($id, $data);
        if (!empty($status)) {
            return back()->with('msg', __('categories::messages.update.success'));
        } else {
            return back()->with('msg_danger', __('categories::messages.update.failure'));
        }
    }

    public function delete($id)
    {
        $category = $this->category->find($id);
        if (empty($category)) {
            abort(404);
        }
        $this->category->delete($id);
        return back()->with('msg', __('categories::messages.delete.success'));
    }
}
