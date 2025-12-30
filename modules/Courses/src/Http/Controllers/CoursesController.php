<?php

namespace Modules\Courses\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Modules\Categories\src\Repositories\CategoriesRepository;
use Modules\Categories\src\Repositories\CategoriesRepositoryInterface;
use Modules\Courses\src\Http\Requests\CoursesRequest;
use Modules\Courses\src\Repositories\CoursesRepository;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Teacher\src\Repositories\TeacherRepository;
use Modules\Teacher\src\Repositories\TeacherRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;

class CoursesController extends Controller
{
    protected $courseRepository;

    protected $categoriesRepository;

    protected $teacherRepository;
    public function __construct(CoursesRepositoryInterface $courseRepository, CategoriesRepositoryInterface $categoriesRepository, TeacherRepositoryInterface $teacherRepository)
    {
        $this->courseRepository = $courseRepository;
        $this->categoriesRepository = $categoriesRepository;
        $this->teacherRepository = $teacherRepository;
    }
    public function index()
    {
        $pageTitle = 'Quản lý khóa học';
        return view('courses::lists', compact('pageTitle'));
    }

    public function data()
    {
        $courses = $this->courseRepository->getAllCourses();

        return DataTables::of($courses)
            ->addColumn('edit', function ($courses) {
                return '<a href="' . route('courses.edit', $courses->id) . '" class="btn btn-warning">Sửa</a>';
            })
            ->addColumn('delete', function ($courses) {
                return '<a href="' . route('courses.delete', $courses->id) . '" class="btn btn-danger delete-action">Xóa</a>';
            })
            ->editColumn('created_at', function ($courses) {
                return Carbon::parse($courses->created_at)->format('d/m/Y H:i:s');
            })
            ->editColumn('status', function ($courses) {
                return $courses->status == 1 ? '<button class="btn btn-success">Đã ra mắt</button>' : '<button class="btn btn-warning">Chưa ra mắt</button>';
            })
            ->editColumn('price', function ($courses) {
                if ($courses->price) {
                    if ($courses->sale_price) {
                        $price = number_format($courses->sale_price, 0) . 'đ';
                    } else {
                        $price = number_format($courses->price, 0) . 'đ';
                    }
                } else {
                    $price = "Miễn phí";
                }
                return $price;
            })
            ->rawColumns(['edit', 'delete', 'status'])
            ->toJson();
    }

    public function create()
    {
        $pageTitle = 'Thêm mới khóa học';

        $categories = $this->categoriesRepository->getAllCategories();

        $teacher = $this->teacherRepository->getAllTeacher()->get();

        return view('courses::create', compact('pageTitle', 'categories', 'teacher'));
    }

    public function store(CoursesRequest $request)
    {
        $courses = $request->except(['_token']);
        if (!$courses['sale_price']) {
            $courses['sale_price'] = 0;
        }

        if (!$courses['price']) {
            $courses['price'] = 0;
        }
        if ($courses['price'] == 0) {
            $courses['sale_price'] = 0;
            return back()->with('msg_danger', 'Khi giá = 0 thì không có khuyến mãi');
        }
        $course = $this->courseRepository->create($courses);
        $categories = $this->getCategories($courses);
        $this->courseRepository->createCoursesCategory($course, $categories);
        return redirect()->route('courses.index')->with('msg', __('courses::messages.create.success'));
    }

    public function edit($id)
    {
        $pageTitle = 'Cập nhập khóa học';
        $courses = $this->courseRepository->find($id);
        $categoriesId = $this->courseRepository->getRelatedCategories($courses);
        $categories = $this->categoriesRepository->getAllCategories();
        $teacher = $this->teacherRepository->getAllTeacher()->get();
        if (
            empty($courses) ||
            empty($categoriesId) ||
            empty($categories) ||
            empty($teacher)
        ) {
            abort(404);
        }

        return view('courses::edit', compact('courses', 'pageTitle', 'categories', 'categoriesId', 'teacher'));
    }

    public function update(CoursesRequest $request, $id)
    {
        $courses = $request->except(['_token']);
        if (!$courses['sale_price']) {
            $courses['sale_price'] = 0;
        }

        if (!$courses['price']) {
            $courses['price'] = 0;
        }

        if ($courses['price'] == 0) {
            $courses['sale_price'] = 0;
            return back()->with('msg_danger', 'Khi giá = 0 thì không có khuyến mãi');
        }
        $this->courseRepository->update($id, $courses);
        $categories = $this->getCategories($courses);
        $courses = $this->courseRepository->find($id);
        $data = $this->courseRepository->updateCoursesCategories($courses, $categories);
        return back()->with('msg', __('courses::messages.update.success'));
    }

    public function getCategories($courses)
    {
        $categories = [];
        foreach ($courses['categories'] as $category) {
            $categories[$category] = ['created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')];
        }
        return $categories;
    }

    public function delete($id)
    {
        $courses = $this->courseRepository->find($id);
        if (empty($courses)) {
            abort(404);
        }
        // $this->courseRepository->deleteCoursesCategories($courses);
        $status = $this->courseRepository->delete($id);
        if($status){
            deleteFileStorage($courses->thumbnail);
        }
        return back()->with('msg', __('courses::messages.delete.success'));
    }
}
