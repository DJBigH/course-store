<?php

namespace Modules\Courses\src\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\StudentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Categories\src\Repositories\CategoriesRepository;
use Modules\Categories\src\Repositories\CategoriesRepositoryInterface;
use Modules\Courses\src\Http\Requests\CoursesRequest;
use Modules\Courses\src\Repositories\CoursesRepository;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Students\src\Models\Student;
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
            ->addColumn('logs', function ($courses) {
                return '<a href="' . route('courses.logs', $courses->id) . '" class="btn btn-info">Lịch sử</a>';
            })

            ->addColumn('lessions', function ($courses) {
                return '<a href="' . route('lessons.index', $courses->id) . '" class="btn btn-primary">Bài giảng</a>';
            })
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
            ->rawColumns(['edit', 'delete', 'status', 'lessions', 'logs'])
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
        $payload = $request->except(['_token']);

        $payload['sale_price'] = $payload['sale_price'] ?: 0;
        $payload['price']      = $payload['price'] ?: 0;

        $course = $this->courseRepository->create($payload);

        $categories = $this->getCategories($payload);
        $this->courseRepository->createCoursesCategory($course, $categories);
        activity_log(
            action: 'create',
            subject: $course,
            properties: [
                'data' => $payload,
                'categories' => array_keys($categories),
            ],
            logName: 'course',
            description: 'Tạo mới khóa học'
        );

        // 🔔 Notify (nếu muốn log việc gửi notify thì log riêng)
        Student::chunk(100, function ($students) use ($course) {
            foreach ($students as $student) {
                $student->notify(new StudentNotification([
                    'title' => 'Khóa học mới',
                    'message' => 'Khóa học ' . $course->name . ' vừa được đăng',
                    'url' => route('courses.detail', $course->slug),
                ]));
            }
        });

        // activity_log(
        //     action: 'notify_students',
        //     subject: $course,
        //     properties: [
        //         'title' => 'Khóa học mới',
        //     ],
        //     logName: 'course',
        //     description: 'Gửi thông báo khóa học mới cho học viên'
        // );

        return redirect()->route('courses.index')->with('msg', __('courses::messages.create.success'));
    }


    public function edit($id)
    {
        $pageTitle = 'Cập nhập khóa học';
        $courses = $this->courseRepository->getCourse($id);
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
        $payload = $request->except(['_token']);
        $payload['sale_price'] = $payload['sale_price'] ?: 0;
        $payload['price']      = $payload['price'] ?: 0;

        $course = $this->courseRepository->getCourse($id);
        if (empty($course)) abort(404);

        // lấy dữ liệu cũ để so sánh
        $oldData = $course->toArray();
        $oldCategoryIds = $this->courseRepository->getRelatedCategories($course) ?? [];

        $this->courseRepository->updateCourse($id, $payload);

        $categories = $this->getCategories($payload);
        $course = $this->courseRepository->getCourse($id); // reload
        $this->courseRepository->updateCoursesCategories($course, $categories);

        $newData = $course->toArray();
        $newCategoryIds = array_keys($categories);

        activity_log(
            action: 'update',
            subject: $course,
            properties: [
                'old' => $oldData,
                'new' => $newData,
                'categories_old' => $oldCategoryIds,
                'categories_new' => $newCategoryIds,
            ],
            logName: 'course',
            description: 'Cập nhật khóa học'
        );

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
        $course = $this->courseRepository->getCourse($id);
        if (empty($course)) abort(404);

        $snapshot = $course->toArray();

        $status = $this->courseRepository->deleteCourse($id);

        if ($status) {
            deleteFileStorage($course->thumbnail);

            activity_log(
                action: 'delete',
                subject: $course,
                properties: [
                    'data' => $snapshot,
                ],
                logName: 'course',
                description: 'Xóa khóa học'
            );

            return back()->with('msg', __('courses::messages.delete.success'));
        }

        return back()->with('msg_danger', 'Xóa thất bại');
    }

    public function logs(Request $request, $id)
    {
        $course = $this->courseRepository->find($id);
        if (empty($course)) abort(404);

        $pageTitle = "Lịch sử: {$course->name}";

        $query = ActiveLog::query()
            ->where('subject_type', get_class($course))
            ->where('subject_id', $course->id)->withoutGlobalScopes();

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
            ->paginate(20)
            ->withQueryString();

        return view('courses::logs', compact('pageTitle', 'course', 'logs'));
    }
}
