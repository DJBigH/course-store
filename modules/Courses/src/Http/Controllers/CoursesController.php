<?php

namespace Modules\Courses\src\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\StudentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Categories\src\Repositories\CategoriesRepositoryInterface;
use Modules\Courses\src\Http\Requests\CoursesRequest;
use Modules\Courses\src\Models\Courses;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Document\src\Models\Document;
use Modules\Lessons\src\Models\Lesson;
use Modules\Students\src\Models\Student;
use Modules\Teacher\src\Repositories\TeacherRepositoryInterface;
use Modules\Video\src\Models\Video;
use Yajra\DataTables\Facades\DataTables;

class CoursesController extends Controller
{
    protected $courseRepository;

    protected $categoriesRepository;

    protected $teacherRepository;

    public function __construct(
        CoursesRepositoryInterface $courseRepository,
        CategoriesRepositoryInterface $categoriesRepository,
        TeacherRepositoryInterface $teacherRepository
    ) {
        $this->courseRepository = $courseRepository;
        $this->categoriesRepository = $categoriesRepository;
        $this->teacherRepository = $teacherRepository;
    }

    public function index()
    {
        $pageTitle = 'Quản lý khóa học';
        $stats = $this->courseRepository->getAdminCourseStats();
        $teachers = $this->teacherRepository->getAllTeacher()->get(['id', 'name']);
        $categories = $this->categoriesRepository->getAllCategories();

        return view('courses::lists', compact('pageTitle', 'stats', 'teachers', 'categories'));
    }

    public function trash()
    {
        $pageTitle = 'Thùng rác khóa học';

        return view('courses::trash', compact('pageTitle'));
    }
    public function data(Request $request)
    {
        $courses = $this->courseRepository
            ->getAllCourses()
            ->when($request->filled('status_filter'), function ($query) use ($request) {
                $query->where('status', (int) $request->status_filter);
            })
            ->when($request->filled('teacher_filter'), function ($query) use ($request) {
                $query->where('teacher_id', (int) $request->teacher_filter);
            })
            ->when($request->filled('category_filter'), function ($query) use ($request) {
                $query->whereHas('categories', function ($categoryQuery) use ($request) {
                    $categoryQuery->where('categories.id', (int) $request->category_filter);
                });
            })
            ->when($request->filled('price_filter'), function ($query) use ($request) {
                match ($request->price_filter) {
                    'free' => $query->where('price', 0),
                    'discounted' => $query->where('sale_price', '>', 0)->whereColumn('sale_price', '<', 'price'),
                    'under_500k' => $query->where(function ($subQuery) {
                        $subQuery->where(function ($priceQuery) {
                            $priceQuery->where('sale_price', '>', 0)->where('sale_price', '<=', 500000);
                        })->orWhere(function ($priceQuery) {
                            $priceQuery->where('sale_price', 0)->where('price', '>', 0)->where('price', '<=', 500000);
                        });
                    }),
                    '500k_1m' => $query->where(function ($subQuery) {
                        $subQuery->where(function ($priceQuery) {
                            $priceQuery->where('sale_price', '>', 500000)->where('sale_price', '<=', 1000000);
                        })->orWhere(function ($priceQuery) {
                            $priceQuery->where('sale_price', 0)->where('price', '>', 500000)->where('price', '<=', 1000000);
                        });
                    }),
                    'above_1m' => $query->where(function ($subQuery) {
                        $subQuery->where('sale_price', '>', 1000000)
                            ->orWhere(function ($priceQuery) {
                                $priceQuery->where('sale_price', 0)->where('price', '>', 1000000);
                            });
                    }),
                    default => $query,
                };
            })
            ->when($request->filled('trial_filter'), function ($query) use ($request) {
                match ($request->trial_filter) {
                    'with_trial' => $query->whereHas('lessons', function ($lessonQuery) {
                        $lessonQuery->where('is_trial', 1);
                    }),
                    'without_trial' => $query->whereDoesntHave('lessons', function ($lessonQuery) {
                        $lessonQuery->where('is_trial', 1);
                    }),
                    default => $query,
                };
            });

        return DataTables::of($courses)
            ->addColumn('select', function ($course) {
                return '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input course-row-checkbox" value="' . $course->id . '"></div>';
            })
            ->addColumn('overview', function ($course) {
                $name = e($course->name ?: 'Chưa có tên');
                $teacher = e(optional($course->teacher)->name ?: 'Chưa gán giảng viên');
                $views = number_format((int) ($course->view ?? 0));

                return '
                    <div class="course-cell">
                        <div class="course-cell__title">' . $name . '</div>
                        <div class="course-cell__meta">
                            <span><i class="fa-solid fa-chalkboard-user"></i> ' . $teacher . '</span>
                            <span><i class="fa-solid fa-eye"></i> ' . $views . ' lượt xem</span>
                        </div>
                    </div>
                ';
            })
            ->addColumn('learning', function ($course) {
                return '
                    <div class="course-learning">
                        <span class="course-pill"><i class="fa-solid fa-circle-play"></i> ' . number_format((int) ($course->lessons_count ?? 0)) . ' bài</span>
                        <span class="course-pill"><i class="fa-solid fa-users"></i> ' . number_format((int) ($course->students_count ?? 0)) . ' học viên</span>
                    </div>
                ';
            })
            ->addColumn('publish', function ($course) {
                $label = (int) $course->status === 1 ? 'Ẩn' : 'Xuất bản';
                $class = (int) $course->status === 1 ? 'btn btn-light border' : 'btn btn-success';

                return '
                    <form method="POST" action="' . route('courses.toggle-status', $course->id) . '" class="d-inline-block">
                        ' . csrf_field() . '
                        <button type="submit" class="' . $class . '">' . $label . '</button>
                    </form>
                ';
            })
            ->addColumn('duplicate', function ($course) {
                return '
                    <form method="POST" action="' . route('courses.duplicate', $course->id) . '" class="d-inline-block">
                        ' . csrf_field() . '
                        <button type="submit" class="btn btn-outline-secondary">Nhân bản</button>
                    </form>
                ';
            })
            ->addColumn('logs', function ($course) {
                return '<a href="' . route('courses.logs', $course->id) . '" class="btn btn-light border">Lịch sử</a>';
            })
            ->addColumn('lessions', function ($course) {
                return '<a href="' . route('lessons.index', $course->id) . '" class="btn btn-primary">Bài giảng</a>';
            })
            ->addColumn('edit', function ($course) {
                return '<a href="' . route('courses.edit', $course->id) . '" class="btn btn-warning">Sửa</a>';
            })
            ->addColumn('delete', function ($course) {
                return '<a href="' . route('courses.delete', $course->id) . '" class="btn btn-outline-danger delete-action">Xóa</a>';
            })
            ->editColumn('created_at', function ($course) {
                return Carbon::parse($course->created_at)->format('d/m/Y H:i:s');
            })
            ->editColumn('status', function ($course) {
                return (int) $course->status === 1
                    ? '<span class="badge rounded-pill text-success-emphasis bg-success-subtle">Đã xuất bản</span>'
                    : '<span class="badge rounded-pill text-warning-emphasis bg-warning-subtle">Bản nháp</span>';
            })
            ->editColumn('price', function ($course) {
                if ($course->price) {
                    if ($course->sale_price) {
                        return '<div class="course-price"><strong>' . number_format($course->sale_price, 0) . ' đ</strong><span>' . number_format($course->price, 0) . ' đ</span></div>';
                    }

                    return '<div class="course-price"><strong>' . number_format($course->price, 0) . ' đ</strong></div>';
                }

                return '<span class="badge rounded-pill text-success-emphasis bg-success-subtle">Miễn phí</span>';
            })
            ->rawColumns(['select', 'overview', 'learning', 'publish', 'duplicate', 'logs', 'lessions', 'edit', 'delete', 'status', 'price'])
            ->toJson();
    }
    public function trashData()
    {
        $courses = Courses::query()
            ->withoutGlobalScopes()
            ->onlyTrashed()
            ->with('teacher')
            ->withCount(['lessons', 'students'])
            ->latest('deleted_at');

        return DataTables::of($courses)
            ->addColumn('select', function ($course) {
                return '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input trashed-course-row-checkbox" value="' . $course->id . '"></div>';
            })
            ->addColumn('overview', function ($course) {
                $name = e($course->name ?: 'Chưa có tên');
                $teacher = e(optional($course->teacher)->name ?: 'Chưa gán giảng viên');

                return '
                    <div class="course-cell">
                        <div class="course-cell__title">' . $name . '</div>
                        <div class="course-cell__meta">
                            <span><i class="fa-solid fa-chalkboard-user"></i> ' . $teacher . '</span>
                            <span><i class="fa-solid fa-trash"></i> Đã xóa mềm</span>
                        </div>
                    </div>
                ';
            })
            ->addColumn('learning', function ($course) {
                return '
                    <div class="course-learning">
                        <span class="course-pill"><i class="fa-solid fa-circle-play"></i> ' . number_format((int) ($course->lessons_count ?? 0)) . ' bài</span>
                        <span class="course-pill"><i class="fa-solid fa-users"></i> ' . number_format((int) ($course->students_count ?? 0)) . ' học viên</span>
                    </div>
                ';
            })
            ->addColumn('price', function ($course) {
                if ($course->price) {
                    if ($course->sale_price) {
                        return '<div class="course-price"><strong>' . number_format($course->sale_price, 0) . ' đ</strong><span>' . number_format($course->price, 0) . ' đ</span></div>';
                    }

                    return '<div class="course-price"><strong>' . number_format($course->price, 0) . ' đ</strong></div>';
                }

                return '<span class="badge rounded-pill text-success-emphasis bg-success-subtle">Miễn phí</span>';
            })
            ->addColumn('deleted_at', function ($course) {
                return $course->deleted_at
                    ? Carbon::parse($course->deleted_at)->format('d/m/Y H:i:s')
                    : '';
            })
            ->addColumn('restore', function ($course) {
                return '
                    <form method="POST" action="' . route('courses.restore', $course->id) . '" class="d-inline-block">
                        ' . csrf_field() . '
                        <button type="submit" class="btn btn-success">Khôi phục</button>
                    </form>
                ';
            })
            ->addColumn('force_delete', function ($course) {
                return '
                    <form method="POST" action="' . route('courses.force-delete', $course->id) . '" class="d-inline-block" onsubmit="return confirm(\'Xóa vĩnh viễn khóa học này?\');">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="btn btn-outline-danger">Xóa vĩnh viễn</button>
                    </form>
                ';
            })
            ->rawColumns(['select', 'overview', 'learning', 'price', 'restore', 'force_delete'])
            ->toJson();
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
                'bulk_action' => 'Vui lÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â²ng chÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Ân ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­t nhÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¥t mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢t khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc.',
            ]);
        }

        $courses = Courses::query()->whereIn('id', $selectedIds)->get();

        if ($courses->isEmpty()) {
            return back()->with('msg_danger', 'KhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´ng tÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¬m thÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¥y khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢ xÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â­ lÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â½.');
        }

        if ($action === 'publish') {
            Courses::query()->whereIn('id', $selectedIds)->update(['status' => 1]);

            foreach ($courses as $course) {
                activity_log(
                    action: 'update',
                    subject: $course,
                    properties: [
                        'old' => ['status' => $course->status],
                        'new' => ['status' => 1],
                    ],
                    logName: 'XuÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¥t bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n hÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â ng loÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡t',
                    description: 'XuÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¥t bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc'
                );
            }

            return back()->with('msg', 'ÃƒÆ’Ã¢â‚¬Å¾Ãƒâ€šÃ‚ÂÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£ xuÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¥t bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n ' . $courses->count() . ' khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc.');
        }

        if ($action === 'draft') {
            Courses::query()->whereIn('id', $selectedIds)->update(['status' => 0]);

            foreach ($courses as $course) {
                activity_log(
                    action: 'update',
                    subject: $course,
                    properties: [
                        'old' => ['status' => $course->status],
                        'new' => ['status' => 0],
                    ],
                    logName: 'NhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡p hÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â ng loÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡t',
                    description: 'ChuyÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢n khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc vÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n nhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡p'
                );
            }

            return back()->with('msg', 'ÃƒÆ’Ã¢â‚¬Å¾Ãƒâ€šÃ‚ÂÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£ chuyÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢n ' . $courses->count() . ' khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc vÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n nhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡p.');
        }

        if ($action === 'duplicate') {
            $duplicatedCount = 0;

            foreach ($courses as $course) {
                $duplicatedCourse = $this->performCourseDuplicate($course);

                activity_log(
                    action: 'duplicate',
                    subject: $duplicatedCourse,
                    properties: [
                        'source_course_id' => $course->id,
                        'new_course_id' => $duplicatedCourse->id,
                    ],
                    logName: 'NhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢n bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n hÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â ng loÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡t',
                    description: 'NhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢n bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc'
                );

                $duplicatedCount++;
            }

            return back()->with('msg', 'ÃƒÆ’Ã¢â‚¬Å¾Ãƒâ€šÃ‚ÂÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£ nhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢n bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n ' . $duplicatedCount . ' khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc.');
        }

        if ($action === 'soft_delete') {
            foreach ($courses as $course) {
                $course->delete();

                activity_log(
                    action: 'delete',
                    subject: $course,
                    properties: ['data' => $course->toArray()],
                    logName: 'XÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âm hÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â ng loÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡t',
                    description: 'XÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âm khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc'
                );
            }

            return back()->with('msg', 'ÃƒÆ’Ã¢â‚¬Å¾Ãƒâ€šÃ‚ÂÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£ xÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âm ' . $courses->count() . ' khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc.');
        }

        return back()->with('msg_danger', 'Thao tÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡c hÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â ng loÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡t khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´ng hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â£p lÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡.');
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
                'bulk_action' => 'Vui lÃƒÆ’Ã‚Â²ng chÃƒÂ¡Ã‚Â»Ã‚Ân ÃƒÆ’Ã‚Â­t nhÃƒÂ¡Ã‚ÂºÃ‚Â¥t mÃƒÂ¡Ã‚Â»Ã¢â€žÂ¢t khÃƒÆ’Ã‚Â³a hÃƒÂ¡Ã‚Â»Ã‚Âc trong thÃƒÆ’Ã‚Â¹ng rÃƒÆ’Ã‚Â¡c.',
            ]);
        }

        $courses = Courses::query()
            ->withoutGlobalScopes()
            ->onlyTrashed()
            ->whereIn('id', $selectedIds)
            ->get();

        if ($courses->isEmpty()) {
            return back()->with('msg_danger', 'KhÃƒÆ’Ã‚Â´ng tÃƒÆ’Ã‚Â¬m thÃƒÂ¡Ã‚ÂºÃ‚Â¥y khÃƒÆ’Ã‚Â³a hÃƒÂ¡Ã‚Â»Ã‚Âc Ãƒâ€žÃ¢â‚¬ËœÃƒÆ’Ã‚Â£ xÃƒÆ’Ã‚Â³a mÃƒÂ¡Ã‚Â»Ã‚Âm Ãƒâ€žÃ¢â‚¬ËœÃƒÂ¡Ã‚Â»Ã†â€™ xÃƒÂ¡Ã‚Â»Ã‚Â­ lÃƒÆ’Ã‚Â½.');
        }

        if ($action === 'restore') {
            foreach ($courses as $course) {
                $course->restore();

                activity_log(
                    action: 'restore',
                    subject: $course->fresh(),
                    properties: [
                        'restored_from_trash' => true,
                        'course_id' => $course->id,
                    ],
                    logName: 'KhÃƒÆ’Ã‚Â´i phÃƒÂ¡Ã‚Â»Ã‚Â¥c hÃƒÆ’Ã‚Â ng loÃƒÂ¡Ã‚ÂºÃ‚Â¡t',
                    description: 'KhÃƒÆ’Ã‚Â´i phÃƒÂ¡Ã‚Â»Ã‚Â¥c khÃƒÆ’Ã‚Â³a hÃƒÂ¡Ã‚Â»Ã‚Âc tÃƒÂ¡Ã‚Â»Ã‚Â« thÃƒÆ’Ã‚Â¹ng rÃƒÆ’Ã‚Â¡c'
                );
            }

            return back()->with('msg', 'Ãƒâ€žÃ‚ÂÃƒÆ’Ã‚Â£ khÃƒÆ’Ã‚Â´i phÃƒÂ¡Ã‚Â»Ã‚Â¥c ' . $courses->count() . ' khÃƒÆ’Ã‚Â³a hÃƒÂ¡Ã‚Â»Ã‚Âc.');
        }

        if ($action === 'force_delete') {
            foreach ($courses as $course) {
                $snapshot = $course->toArray();
                $this->permanentlyDeleteCourse($course);

                activity_log(
                    action: 'force_delete',
                    subject: $course,
                    properties: [
                        'data' => $snapshot,
                        'deleted_permanently' => true,
                    ],
                    logName: 'XÃƒÆ’Ã‚Â³a vÃƒâ€žÃ‚Â©nh viÃƒÂ¡Ã‚Â»Ã¢â‚¬Â¦n hÃƒÆ’Ã‚Â ng loÃƒÂ¡Ã‚ÂºÃ‚Â¡t',
                    description: 'XÃƒÆ’Ã‚Â³a vÃƒâ€žÃ‚Â©nh viÃƒÂ¡Ã‚Â»Ã¢â‚¬Â¦n khÃƒÆ’Ã‚Â³a hÃƒÂ¡Ã‚Â»Ã‚Âc khÃƒÂ¡Ã‚Â»Ã‚Âi thÃƒÆ’Ã‚Â¹ng rÃƒÆ’Ã‚Â¡c'
                );
            }

            return back()->with('msg', 'Ãƒâ€žÃ‚ÂÃƒÆ’Ã‚Â£ xÃƒÆ’Ã‚Â³a vÃƒâ€žÃ‚Â©nh viÃƒÂ¡Ã‚Â»Ã¢â‚¬Â¦n ' . $courses->count() . ' khÃƒÆ’Ã‚Â³a hÃƒÂ¡Ã‚Â»Ã‚Âc.');
        }

        return back()->with('msg_danger', 'Thao tÃƒÆ’Ã‚Â¡c trong thÃƒÆ’Ã‚Â¹ng rÃƒÆ’Ã‚Â¡c khÃƒÆ’Ã‚Â´ng hÃƒÂ¡Ã‚Â»Ã‚Â£p lÃƒÂ¡Ã‚Â»Ã¢â‚¬Â¡.');
    }

    public function toggleStatus($id)
    {
        $course = $this->courseRepository->getCourse($id);
        if (!$course) {
            abort(404);
        }

        $oldStatus = (int) $course->status;
        $newStatus = $oldStatus === 1 ? 0 : 1;

        $this->courseRepository->updateCourse($id, ['status' => $newStatus]);

        $freshCourse = $this->courseRepository->getCourse($id);

        activity_log(
            action: 'update',
            subject: $freshCourse ?? $course,
            properties: [
                'old' => ['status' => $oldStatus],
                'new' => ['status' => $newStatus],
            ],
            logName: 'CÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â­p nhÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â­t trÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡ng thÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡i',
            description: $newStatus === 1 ? 'XuÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¥t bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc' : 'ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¨n khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc'
        );

        return back()->with('msg', $newStatus === 1 ? 'ÃƒÆ’Ã¢â‚¬Å¾Ãƒâ€šÃ‚ÂÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£ xuÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¥t bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc.' : 'ÃƒÆ’Ã¢â‚¬Å¾Ãƒâ€šÃ‚ÂÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£ chuyÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢n khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc vÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n nhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡p.');
    }

    public function duplicate($id)
    {
        $course = $this->courseRepository->getCourse($id);
        if (!$course) {
            abort(404);
        }

        $duplicatedCourse = $this->performCourseDuplicate($course);

        activity_log(
            action: 'duplicate',
            subject: $duplicatedCourse,
            properties: [
                'source_course_id' => $course->id,
                'new_course_id' => $duplicatedCourse->id,
            ],
            logName: 'NhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢n bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n',
            description: 'NhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢n bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc'
        );

        return redirect()
            ->route('courses.edit', $duplicatedCourse->id)
            ->with('msg', 'ÃƒÆ’Ã¢â‚¬Å¾Ãƒâ€šÃ‚ÂÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£ tÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡o bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n sao khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â  chuyÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢n vÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â trÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡ng thÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡i nhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡p.');
    }

    public function restore($id)
    {
        $course = Courses::query()
            ->withoutGlobalScopes()
            ->onlyTrashed()
            ->find($id);

        if (!$course) {
            abort(404);
        }

        $course->restore();

        activity_log(
            action: 'restore',
            subject: $course->fresh(),
            properties: [
                'restored_from_trash' => true,
                'course_id' => $course->id,
            ],
            logName: 'KhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´i phÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â¥c',
            description: 'KhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´i phÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â¥c khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc tÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â« thÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¹ng rÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡c'
        );

        return back()->with('msg', 'KhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´i phÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â¥c khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc thÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â nh cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´ng.');
    }

    public function forceDelete($id)
    {
        $course = Courses::query()
            ->withoutGlobalScopes()
            ->onlyTrashed()
            ->find($id);

        if (!$course) {
            abort(404);
        }

        $snapshot = $course->toArray();
        $this->permanentlyDeleteCourse($course);

        activity_log(
            action: 'force_delete',
            subject: $course,
            properties: [
                'data' => $snapshot,
                'deleted_permanently' => true,
            ],
            logName: 'XÃ³a vÄ©nh viá»…n',
            description: 'XÃ³a vÄ©nh viá»…n khÃ³a há»c khá»i thÃ¹ng rÃ¡c'
        );

        return back()->with('msg', 'ÄÃ£ xÃ³a vÄ©nh viá»…n khÃ³a há»c.');
    }
    public function create()
    {
        $pageTitle = 'ThÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªm mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºi khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc';
        $categories = $this->categoriesRepository->getAllCategories();
        $teacher = $this->teacherRepository->getAllTeacher()->get();

        return view('courses::create', compact('pageTitle', 'categories', 'teacher'));
    }

    public function store(CoursesRequest $request)
    {
        $payload = $request->except(['_token']);

        $payload['sale_price'] = $payload['sale_price'] ?: 0;
        $payload['price'] = $payload['price'] ?: 0;

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
            logName: 'ThÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªm mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºi',
            description: 'TÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡o mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºi khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc'
        );

        Student::chunk(100, function ($students) use ($course) {
            foreach ($students as $student) {
                $student->notify(new StudentNotification([
                    'title' => 'KhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºi',
                    'title_translations' => [
                        'vi' => 'KhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºi',
                        'en' => 'New course',
                        'ko' => 'ÃƒÆ’Ã‚Â¬Ãƒâ€ Ã¢â‚¬â„¢Ãƒâ€¹Ã¢â‚¬Â  ÃƒÆ’Ã‚ÂªÃƒâ€šÃ‚Â°ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¢ÃƒÆ’Ã‚Â¬Ãƒâ€šÃ‚ÂÃƒâ€¹Ã…â€œ',
                        'ja' => 'ÃƒÆ’Ã‚Â¦ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“Ãƒâ€šÃ‚Â°ÃƒÆ’Ã‚Â£Ãƒâ€šÃ‚ÂÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬ÂÃƒÆ’Ã‚Â£Ãƒâ€šÃ‚ÂÃƒÂ¢Ã¢â€šÂ¬Ã…Â¾ÃƒÆ’Ã‚Â£ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â³ÃƒÆ’Ã‚Â£Ãƒâ€ Ã¢â‚¬â„¢Ãƒâ€šÃ‚Â¼ÃƒÆ’Ã‚Â£ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¹',
                        'zh' => 'ÃƒÆ’Ã‚Â¦ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“Ãƒâ€šÃ‚Â°ÃƒÆ’Ã‚Â¨Ãƒâ€šÃ‚Â¯Ãƒâ€šÃ‚Â¾ÃƒÆ’Ã‚Â§Ãƒâ€šÃ‚Â¨ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹',
                    ],
                    'message' => 'KhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc ' . localizedModelField($course, 'name', 'vi') . ' vÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â«a ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã¢â‚¬Â Ãƒâ€šÃ‚Â°ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â£c ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã¢â‚¬Å¾Ãƒâ€ Ã¢â‚¬â„¢ng',
                    'message_translations' => [
                        'vi' => 'KhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc ' . localizedModelField($course, 'name', 'vi') . ' vÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â«a ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã¢â‚¬Â Ãƒâ€šÃ‚Â°ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â£c ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã¢â‚¬Å¾Ãƒâ€ Ã¢â‚¬â„¢ng',
                        'en' => 'Course ' . localizedModelField($course, 'name', 'en') . ' has just been published',
                        'ko' => localizedModelField($course, 'name', 'ko') . ' ÃƒÆ’Ã‚ÂªÃƒâ€šÃ‚Â°ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¢ÃƒÆ’Ã‚Â¬Ãƒâ€šÃ‚ÂÃƒâ€¹Ã…â€œÃƒÆ’Ã‚ÂªÃƒâ€šÃ‚Â°ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ ÃƒÆ’Ã‚Â¬Ãƒâ€ Ã¢â‚¬â„¢Ãƒâ€¹Ã¢â‚¬Â ÃƒÆ’Ã‚Â«Ãƒâ€šÃ‚Â¡Ãƒâ€¦Ã¢â‚¬Å“ ÃƒÆ’Ã‚Â«ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â±ÃƒÆ’Ã‚Â«Ãƒâ€šÃ‚Â¡Ãƒâ€šÃ‚ÂÃƒÆ’Ã‚Â«Ãƒâ€šÃ‚ÂÃƒâ€¹Ã…â€œÃƒÆ’Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬ÂÃƒâ€¹Ã¢â‚¬Â ÃƒÆ’Ã‚Â¬Ãƒâ€¦Ã‚Â Ãƒâ€šÃ‚ÂµÃƒÆ’Ã‚Â«ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹Ãƒâ€¹Ã¢â‚¬Â ÃƒÆ’Ã‚Â«ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹Ãƒâ€šÃ‚Â¤',
                        'ja' => 'ÃƒÆ’Ã‚Â£ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â³ÃƒÆ’Ã‚Â£Ãƒâ€ Ã¢â‚¬â„¢Ãƒâ€šÃ‚Â¼ÃƒÆ’Ã‚Â£ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¹ ' . localizedModelField($course, 'name', 'ja') . ' ÃƒÆ’Ã‚Â£Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¥ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã‚Â©ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹ÃƒÆ’Ã‚Â£Ãƒâ€šÃ‚ÂÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¢ÃƒÆ’Ã‚Â£ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€¦Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â£Ãƒâ€šÃ‚ÂÃƒâ€šÃ‚Â¾ÃƒÆ’Ã‚Â£Ãƒâ€šÃ‚ÂÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬ÂÃƒÆ’Ã‚Â£Ãƒâ€šÃ‚ÂÃƒâ€¦Ã‚Â¸',
                        'zh' => 'ÃƒÆ’Ã‚Â¨Ãƒâ€šÃ‚Â¯Ãƒâ€šÃ‚Â¾ÃƒÆ’Ã‚Â§Ãƒâ€šÃ‚Â¨ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹ ' . localizedModelField($course, 'name', 'zh') . ' ÃƒÆ’Ã‚Â¥Ãƒâ€šÃ‚Â·Ãƒâ€šÃ‚Â²ÃƒÆ’Ã‚Â¥Ãƒâ€šÃ‚ÂÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã‚Â¥Ãƒâ€šÃ‚Â¸Ãƒâ€ Ã¢â‚¬â„¢',
                    ],
                    'url' => route('courses.detail', ['locale' => app()->getLocale(), 'slug' => $course->slug]),
                ]));
            }
        });

        return redirect()->route('courses.index')->with('msg', __('courses::messages.create.success'));
    }

    public function edit($id)
    {
        $pageTitle = 'CÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â­p nhÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â­p khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc';
        $courses = $this->courseRepository->getCourse($id);
        $categoriesId = $this->courseRepository->getRelatedCategories($courses);
        $categories = $this->categoriesRepository->getAllCategories();
        $teacher = $this->teacherRepository->getAllTeacher()->get();

        if (empty($courses) || empty($categoriesId) || empty($categories) || empty($teacher)) {
            abort(404);
        }

        return view('courses::edit', compact('courses', 'pageTitle', 'categories', 'categoriesId', 'teacher'));
    }

    public function update(CoursesRequest $request, $id)
    {
        $payload = $request->except(['_token']);
        $payload['sale_price'] = $payload['sale_price'] ?: 0;
        $payload['price'] = $payload['price'] ?: 0;

        $course = $this->courseRepository->getCourse($id);
        if (empty($course)) {
            abort(404);
        }

        $oldData = $course->toArray();
        $oldCategoryIds = $this->courseRepository->getRelatedCategories($course) ?? [];

        $this->courseRepository->updateCourse($id, $payload);

        $categories = $this->getCategories($payload);
        $course = $this->courseRepository->getCourse($id);
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
            logName: 'CÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â­p nhÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â­p',
            description: 'CÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â­p nhÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â­t khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc'
        );

        return back()->with('msg', __('courses::messages.update.success'));
    }

    public function getCategories($courses)
    {
        $categories = [];

        foreach ($courses['categories'] as $category) {
            $categories[$category] = [
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ];
        }

        return $categories;
    }

    public function delete($id)
    {
        $course = $this->courseRepository->getCourse($id);
        if (empty($course)) {
            abort(404);
        }

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
                logName: 'XÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a',
                description: 'XÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc'
            );

            return back()->with('msg', __('courses::messages.delete.success'));
        }

        return back()->with('msg_danger', 'XÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a thÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¥t bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡i');
    }

    public function logs(Request $request, $id)
    {
        $course = $this->courseRepository->find($id);
        if (empty($course)) {
            abort(404);
        }

        $pageTitle = "LÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹ch sÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â­: {$course->name}";

        $query = ActiveLog::query()
            ->withoutGlobalScopes()
            ->where(function ($q) use ($course) {
                $q->where(function ($qq) use ($course) {
                    $qq->where('subject_type', get_class($course))
                        ->where('subject_id', $course->id);
                });

                $q->orWhere(function ($qq) use ($course) {
                    $qq->where('subject_type', Lesson::class)
                        ->where('properties->course_id', $course->id);
                });

                $q->orWhere(function ($qq) use ($course) {
                    $qq->whereNull('subject_type')
                        ->where('action', 'sort_lessons')
                        ->where('properties->course_id', $course->id);
                });
            });

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

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

        return view('courses::logs', compact('pageTitle', 'course', 'logs'));
    }

    protected function categoriesPivotPayload(array $categoryIds): array
    {
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');
        $payload = [];

        foreach ($categoryIds as $categoryId) {
            $payload[$categoryId] = [
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        return $payload;
    }

    protected function duplicateTitle(?string $value, string $suffix): ?string
    {
        if (!$value) {
            return $value;
        }

        return trim($value . ' (' . $suffix . ')');
    }

    protected function duplicateSlug(?string $value, string $suffix = 'copy'): ?string
    {
        if (!$value) {
            return $value;
        }

        return trim($value . '-' . $suffix . '-' . Str::lower(Str::random(4)), '-');
    }

    protected function duplicateCode(?string $value): string
    {
        $base = $value ?: 'COURSE';

        return $base . '-COPY-' . strtoupper(Str::random(4));
    }

    protected function performCourseDuplicate(Courses $course): Courses
    {
        return DB::transaction(function () use ($course) {
            $courseData = $course->getAttributes();

            unset($courseData['id'], $courseData['created_at'], $courseData['updated_at'], $courseData['deleted_at']);

            $courseData['name'] = $this->duplicateTitle($course->name, 'BÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n sao');
            $courseData['name_en'] = $this->duplicateTitle($course->name_en, 'Copy');
            $courseData['name_ko'] = $this->duplicateTitle($course->name_ko, 'ÃƒÆ’Ã‚Â«Ãƒâ€šÃ‚Â³Ãƒâ€šÃ‚ÂµÃƒÆ’Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã‚Â«Ãƒâ€šÃ‚Â³Ãƒâ€šÃ‚Â¸');
            $courseData['name_ja'] = $this->duplicateTitle($course->name_ja, 'ÃƒÆ’Ã‚Â£ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â³ÃƒÆ’Ã‚Â£Ãƒâ€ Ã¢â‚¬â„¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÆ’Ã‚Â£Ãƒâ€ Ã¢â‚¬â„¢Ãƒâ€šÃ‚Â¼');
            $courseData['name_zh'] = $this->duplicateTitle($course->name_zh, 'ÃƒÆ’Ã‚Â¥ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â°Ãƒâ€šÃ‚Â¯ÃƒÆ’Ã‚Â¦Ãƒâ€¦Ã¢â‚¬Å“Ãƒâ€šÃ‚Â¬');
            $courseData['slug'] = $this->duplicateSlug($course->slug, 'copy');
            $courseData['slug_en'] = $this->duplicateSlug($course->slug_en, 'copy');
            $courseData['slug_ko'] = $this->duplicateSlug($course->slug_ko, 'copy');
            $courseData['slug_ja'] = $this->duplicateSlug($course->slug_ja, 'copy');
            $courseData['slug_zh'] = $this->duplicateSlug($course->slug_zh, 'copy');
            $courseData['code'] = $this->duplicateCode($course->code);
            $courseData['status'] = 0;
            $courseData['view'] = 0;

            $newCourse = $this->courseRepository->create($courseData);

            $categoryIds = $this->courseRepository->getRelatedCategories($course);
            if (!empty($categoryIds)) {
                $newCourse->categories()->attach($this->categoriesPivotPayload($categoryIds));
            }

            $lessonMap = [];
            $lessons = Lesson::query()
                ->where('course_id', $course->id)
                ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
                ->orderBy('parent_id')
                ->orderBy('position')
                ->orderBy('id')
                ->get();

            foreach ($lessons as $lesson) {
                $lessonData = $lesson->getAttributes();

                unset($lessonData['id'], $lessonData['created_at'], $lessonData['updated_at']);

                $oldParentId = $lessonData['parent_id'] ?? null;
                $lessonData['course_id'] = $newCourse->id;
                $lessonData['parent_id'] = $oldParentId ? ($lessonMap[$oldParentId] ?? null) : null;
                $lessonData['name'] = $this->duplicateTitle($lesson->name, 'BÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n sao');
                $lessonData['name_en'] = $this->duplicateTitle($lesson->name_en, 'Copy');
                $lessonData['name_ko'] = $this->duplicateTitle($lesson->name_ko, 'ÃƒÆ’Ã‚Â«Ãƒâ€šÃ‚Â³Ãƒâ€šÃ‚ÂµÃƒÆ’Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã‚Â«Ãƒâ€šÃ‚Â³Ãƒâ€šÃ‚Â¸');
                $lessonData['name_ja'] = $this->duplicateTitle($lesson->name_ja, 'ÃƒÆ’Ã‚Â£ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â³ÃƒÆ’Ã‚Â£Ãƒâ€ Ã¢â‚¬â„¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÆ’Ã‚Â£Ãƒâ€ Ã¢â‚¬â„¢Ãƒâ€šÃ‚Â¼');
                $lessonData['name_zh'] = $this->duplicateTitle($lesson->name_zh, 'ÃƒÆ’Ã‚Â¥ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â°Ãƒâ€šÃ‚Â¯ÃƒÆ’Ã‚Â¦Ãƒâ€¦Ã¢â‚¬Å“Ãƒâ€šÃ‚Â¬');
                $lessonData['slug'] = $this->duplicateSlug($lesson->slug, 'copy');
                $lessonData['slug_en'] = $this->duplicateSlug($lesson->slug_en, 'copy');
                $lessonData['slug_ko'] = $this->duplicateSlug($lesson->slug_ko, 'copy');
                $lessonData['slug_ja'] = $this->duplicateSlug($lesson->slug_ja, 'copy');
                $lessonData['slug_zh'] = $this->duplicateSlug($lesson->slug_zh, 'copy');
                $lessonData['view'] = 0;
                $lessonData['status'] = 0;

                $newLesson = Lesson::query()->create($lessonData);
                $lessonMap[$lesson->id] = $newLesson->id;
            }

            return $newCourse;
        });
    }

    protected function permanentlyDeleteCourse(Courses $course): void
    {
        DB::transaction(function () use ($course) {
            $videoIds = Lesson::query()
                ->where('course_id', $course->id)
                ->whereNotNull('video_id')
                ->pluck('video_id')
                ->filter()
                ->unique()
                ->values();

            $documentIds = Lesson::query()
                ->where('course_id', $course->id)
                ->whereNotNull('document_id')
                ->pluck('document_id')
                ->filter()
                ->unique()
                ->values();

            Lesson::query()->where('course_id', $course->id)->delete();
            $course->categories()->detach();
            $course->students()->detach();

            if (!empty($course->thumbnail)) {
                deleteFileStorage($course->thumbnail);
            }

            $this->cleanupUnusedVideos($videoIds);
            $this->cleanupUnusedDocuments($documentIds);

            $course->forceDelete();
        });
    }

    protected function cleanupUnusedVideos($videoIds): void
    {
        foreach ($videoIds as $videoId) {
            $video = Video::query()->find($videoId);

            if (!$video) {
                continue;
            }

            $isStillUsed = Lesson::query()->where('video_id', $videoId)->exists();

            if ($isStillUsed) {
                continue;
            }

            if (!empty($video->url) && !preg_match('~^https?://~i', (string) $video->url)) {
                deleteFileStorage($video->url);
            }

            $video->delete();
        }
    }

    protected function cleanupUnusedDocuments($documentIds): void
    {
        foreach ($documentIds as $documentId) {
            $document = Document::query()->find($documentId);

            if (!$document) {
                continue;
            }

            $isStillUsed = Lesson::query()->where('document_id', $documentId)->exists();

            if ($isStillUsed) {
                continue;
            }

            if (!empty($document->url) && !preg_match('~^https?://~i', (string) $document->url)) {
                deleteFileStorage($document->url);
            }

            $document->delete();
        }
    }
}



