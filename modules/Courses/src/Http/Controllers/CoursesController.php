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

        $breadcrumbs = [
            ['label' => 'Quản lý khóa học']
        ];

        return view('courses::lists', compact('pageTitle', 'stats', 'teachers', 'categories', 'breadcrumbs'));
    }

    public function trash()
    {
        $pageTitle = 'Thùng rác khóa học';
        $breadcrumbs = [
            ['label' => 'Quản lý khóa học', 'link' => route('courses.index')],
            ['label' => 'Thùng rác']
        ];

        return view('courses::trash', compact('pageTitle', 'breadcrumbs'));
    }
    public function data(Request $request)
    {
        $user = auth()->user();
        $canPublish = $user?->hasPermission('courses.publish');
        $canEdit = $user?->hasPermission('courses.edit');
        $canView = $user?->hasPermission('courses.view');
        $canSoftDelete = $user?->hasPermission('courses.soft_delete');
        $canAccessLessons = $user?->canAnyPermission(['lessons.view', 'lessons.create', 'lessons.edit', 'lessons.delete', 'lessons.sort']);

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
                $courseSlug = trim((string) ($course->slug_locale ?: $course->slug ?: ''));
                $publicLocale = in_array(app()->getLocale(), ['vi', 'en', 'ko', 'ja', 'zh'], true)
                    ? app()->getLocale()
                    : 'vi';

                $titleHtml = $name;

                if ($courseSlug !== '') {
                    $titleHtml = '<a href="' . route('courses.detail', [
                        'locale' => $publicLocale,
                        'slug' => $courseSlug,
                    ]) . '" target="_blank" rel="noopener noreferrer" class="course-cell__link fw-bold text-dark text-decoration-none">' . $name . '</a>';
                }

                $badges = '';
                if ($course->is_coming_soon) {
                    $badges .= '<span class="badge bg-purple ms-1" style="background: #8b5cf6; font-size: 10px;">Coming Soon</span>';
                }
                if ($course->quantity !== null && $course->quantity <= 0) {
                    $badges .= '<span class="badge bg-danger ms-1" style="font-size: 10px;">Hết chỗ</span>';
                } elseif ($course->quantity !== null) {
                    $badges .= '<span class="badge bg-success ms-1" style="font-size: 10px;">' . $course->quantity . ' chỗ</span>';
                }
                if ($course->end_at && $course->end_at->isPast()) {
                    $badges .= '<span class="badge bg-secondary ms-1" style="font-size: 10px;">Hết hạn</span>';
                }

                $thumbnail = $course->thumbnail ? asset($course->thumbnail) : 'https://placehold.co/600x400?text=Course';

                return '
                    <div class="d-flex align-items-center gap-3">
                        <img src="' . $thumbnail . '" class="rounded shadow-sm" style="width: 72px; height: 45px; object-fit: cover;">
                        <div>
                            <div class="course-cell__title mb-1">' . $titleHtml . $badges . '</div>
                            <div class="course-cell__meta small text-muted">
                                <span><i class="fa-solid fa-chalkboard-user me-1"></i> ' . $teacher . '</span>
                                <span class="ms-3"><i class="fa-solid fa-eye me-1"></i> ' . $views . ' lượt xem</span>
                            </div>
                        </div>
                    </div>';
            })
            ->addColumn('learning_stat', function ($course) {
                $avg = round((float) ($course->ratings_avg_rating ?? 0), 1);
                $count = (int) ($course->ratings_count ?? 0);
                
                return '
                    <div class="course-learning-cell small">
                        <div><i class="fa-solid fa-circle-play text-muted me-1"></i> <strong>' . number_format((int) ($course->lessons_count ?? 0)) . '</strong> bài giảng</div>
                        <div class="mt-1"><i class="fa-solid fa-users text-muted me-1"></i> <strong>' . number_format((int) ($course->students_count ?? 0)) . '</strong> học viên</div>
                        <div class="text-warning mt-1">
                            <i class="fa-solid fa-star me-1"></i><strong>' . $avg . '</strong> <span class="text-muted">(' . $count . ')</span>
                        </div>
                    </div>';
            })
            ->addColumn('price_status', function ($course) use ($canPublish) {
                $priceHtml = '';
                if ($course->price) {
                    if ($course->sale_price) {
                        $priceHtml = '<div class="course-price fw-bold text-dark">' . number_format($course->sale_price, 0) . ' đ <span class="text-muted text-decoration-line-through small fw-normal ms-1">' . number_format($course->price, 0) . ' đ</span></div>';
                    } else {
                        $priceHtml = '<div class="course-price fw-bold text-dark">' . number_format($course->price, 0) . ' đ</div>';
                    }
                } else {
                    $priceHtml = '<span class="badge bg-success-subtle text-success px-2 py-1">Miễn phí</span>';
                }

                $statusHtml = (int) $course->status === 1
                    ? '<span class="badge bg-success-subtle text-success px-2 py-1 mt-2 d-inline-block">Đã xuất bản</span>'
                    : '<span class="badge bg-warning-subtle text-warning px-2 py-1 mt-2 d-inline-block">Bản nháp</span>';

                if ($canPublish) {
                    $label = (int) $course->status === 1 ? 'Ẩn' : 'Xuất bản';
                    $btnClass = (int) $course->status === 1 ? 'btn btn-outline-secondary btn-xs py-0 px-2 ms-1' : 'btn btn-success btn-xs py-0 px-2 ms-1';
                    $statusHtml .= ' <form method="POST" action="' . route('courses.toggle-status', $course->id) . '" class="d-inline-block">
                                        ' . csrf_field() . '
                                        <button type="submit" class="' . $btnClass . '" style="font-size: 10px; padding: 2px 5px;">' . $label . '</button>
                                     </form>';
                }

                return '<div class="course-price-status">' . $priceHtml . $statusHtml . '</div>';
            })
            ->editColumn('created_at', function ($course) {
                return '<div class="small text-muted">' . Carbon::parse($course->created_at)->format('d/m/Y') . '</div>';
            })
            ->addColumn('actions', function ($course) use ($canEdit, $canSoftDelete, $canView, $canAccessLessons) {
                $btn = '<div class="dropdown">
                            <button class="btn btn-light btn-sm border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2">';
                
                if ($canEdit) {
                    $btn .= '<li><a class="dropdown-item py-2" href="' . route('courses.edit', $course->id) . '"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Sửa khóa học</a></li>';
                    $btn .= '<li>
                                <form method="POST" action="' . route('courses.duplicate', $course->id) . '" class="d-inline-block w-100">' . csrf_field() . '
                                    <button type="submit" class="dropdown-item py-2"><i class="fa-solid fa-clone text-secondary me-2"></i>Nhân bản</button>
                                </form>
                             </li>';
                }

                if ($canAccessLessons) {
                    $btn .= '<li><a class="dropdown-item py-2" href="' . route('lessons.index', $course->id) . '"><i class="fa-solid fa-circle-play text-success me-2"></i>Quản lý bài giảng</a></li>';
                }

                if ($canView) {
                    $btn .= '<li><a class="dropdown-item py-2" href="' . route('courses.logs', $course->id) . '"><i class="fa-solid fa-clock-rotate-left text-muted me-2"></i>Xem lịch sử</a></li>';
                }

                if ($canSoftDelete) {
                    $btn .= '<li><hr class="dropdown-divider my-1"></li>';
                    $btn .= '<li><a class="dropdown-item py-2 text-danger delete-action" href="' . route('courses.delete', $course->id) . '"><i class="fa-solid fa-trash me-2"></i>Xóa mềm</a></li>';
                }

                $btn .= '</ul></div>';
                return $btn;
            })
            ->rawColumns(['select', 'overview', 'learning_stat', 'price_status', 'created_at', 'actions'])
            ->toJson();
    }
    public function trashData()
    {
        $canRestore = auth()->user()?->hasPermission('courses.publish');
        $canForceDelete = auth()->user()?->hasPermission('courses.force_delete');

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
                        <div class="course-cell__title">
                        <a href="' . route('courses.home', ['locale' => app()->getLocale(), 'slug' => $course->slug]) . '">' . $name . '</a>
                    </div>
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

                return '<span class="course-free-badge">Miễn phí</span>';
            })
            ->addColumn('deleted_at', function ($course) {
                return $course->deleted_at
                    ? Carbon::parse($course->deleted_at)->format('d/m/Y H:i:s')
                    : '';
            })
            ->addColumn('restore', function ($course) use ($canRestore) {
                if (!$canRestore) {
                    return '<span class="text-muted small">Không có quyền</span>';
                }

                return '
                    <form method="POST" action="' . route('courses.restore', $course->id) . '" class="d-inline-block">
                        ' . csrf_field() . '
                        <button type="submit" class="btn btn-success">Khôi phục</button>
                    </form>
                ';
            })
            ->addColumn('force_delete', function ($course) use ($canForceDelete) {
                if (!$canForceDelete) {
                    return '<span class="text-muted small">Không có quyền</span>';
                }

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
        $user = auth()->user();
        $selectedIds = collect(explode(',', (string) $request->input('selected_ids', '')))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            return back()->withErrors([
                'bulk_action' => 'Vui lòng chọn ít nhất một khóa học.',
            ]);
        }

        $courses = Courses::query()->whereIn('id', $selectedIds)->get();

        if ($courses->isEmpty()) {
            return back()->with('msg_danger', 'Không tìm thấy khóa học hợp lệ cho thao tác này.');
        }

        if ($action === 'publish') {
            abort_unless($user?->hasPermission('courses.publish'), 403);
            Courses::query()->whereIn('id', $selectedIds)->update(['status' => 1]);

            foreach ($courses as $course) {
                activity_log(
                    action: 'update',
                    subject: $course,
                    properties: [
                        'old' => ['status' => $course->status],
                        'new' => ['status' => 1],
                    ],
                    logName: 'Xuất bản hàng loạt',
                    description: 'Xuất bản khóa học'
                );
            }
            return back()->with('msg', 'Đã xuất bản ' . $courses->count() . ' khóa học.');
        }

        if ($action === 'draft') {
            abort_unless($user?->hasPermission('courses.publish'), 403);
            Courses::query()->whereIn('id', $selectedIds)->update(['status' => 0]);

            foreach ($courses as $course) {
                activity_log(
                    action: 'update',
                    subject: $course,
                    properties: [
                        'old' => ['status' => $course->status],
                        'new' => ['status' => 0],
                    ],
                    logName: 'Chuyển nháp hàng loạt',
                    description: 'Chuyển khóa học về bản nháp'
                );
            }
            return back()->with('msg', 'Đã chuyển ' . $courses->count() . ' khóa học về bản nháp.');
        }

        if ($action === 'duplicate') {
            abort_unless($user?->hasPermission('courses.edit'), 403);
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
                    logName: 'Nhân bản hàng loạt',
                    description: 'Nhân bản khóa học'
                );

                $duplicatedCount++;
            }
            return back()->with('msg', 'Đã nhân bản ' . $duplicatedCount . ' khóa học.');
        }

        if ($action === 'soft_delete') {
            abort_unless($user?->hasPermission('courses.soft_delete'), 403);
            foreach ($courses as $course) {
                $course->delete();

                activity_log(
                    action: 'delete',
                    subject: $course,
                    logName: 'Xóa mềm hàng loạt',
                    description: 'Xóa mềm khóa học'
                );
            }
            return back()->with('msg', 'Đã xóa mềm ' . $courses->count() . ' khóa học.');
        }
        return back()->with('msg_danger', 'Thao tác hàng loạt không hợp lệ.');
    }

    public function trashBulkAction(Request $request)
    {
        $action = $request->input('bulk_action');
        $user = auth()->user();
        $selectedIds = collect(explode(',', (string) $request->input('selected_ids', '')))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            return back()->withErrors([
                'bulk_action' => 'Vui lòng chọn ít nhất một khóa học trong thùng rác.',
            ]);
        }

        $courses = Courses::query()
            ->withoutGlobalScopes()
            ->onlyTrashed()
            ->whereIn('id', $selectedIds)
            ->get();

        if ($courses->isEmpty()) {
            return back()->with('msg_danger', 'Không tìm thấy khóa học trong thùng rác.');
        }

        if ($action === 'restore') {
            abort_unless($user?->hasPermission('courses.publish'), 403);
            foreach ($courses as $course) {
                $course->restore();

                activity_log(
                    action: 'restore',
                    subject: $course->fresh(),
                    properties: [
                        'restored_from_trash' => true,
                        'course_id' => $course->id,
                    ],
                    logName: 'Khôi phục hàng loạt',
                    description: 'Khôi phục khóa học từ thùng rác'
                );
            }
            return back()->with('msg', 'Đã khôi phục ' . $courses->count() . ' khóa học.');
        }

        if ($action === 'force_delete') {
            abort_unless($user?->hasPermission('courses.force_delete'), 403);
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
                    logName: 'Xóa vĩnh viễn hàng loạt',
                    description: 'Xóa vĩnh viễn khóa học khỏi thùng rác'
                );
            }
            return back()->with('msg', 'Đã xóa vĩnh viễn ' . $courses->count() . ' khóa học.');
        }
        return back()->with('msg_danger', 'Thao tác trong thùng rác không hợp lệ.');
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
            logName: 'Cập nhật trạng thái',
            description: $newStatus === 1 ? 'Xuất bản khóa học' : 'Ẩn khóa học'
        );
        return back()->with('msg', $newStatus === 1 ? 'Đã xuất bản khóa học.' : 'Đã chuyển khóa học về bản nháp.');
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
            logName: 'Nhân bản',
            description: 'Nhân bản khóa học'
        );

        return redirect()
            ->route('courses.index')
            ->with('msg', 'Đã tạo bản sao khóa học và chuyển về trạng thái nháp.');
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
            logName: 'Khôi phục',
            description: 'Khôi phục khóa học thành công.'
        );
        return back()->with('msg', 'Khôi phục khóa học thành công.');
    }

    public function forceDelete($id)
    {
        abort_unless(auth()->user()?->hasPermission('courses.force_delete'), 403);

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
            logName: 'Xóa vĩnh viễn',
            description: 'Xóa vĩnh viễn khóa học khỏi thùng rác'
        );

        return back()->with('msg', 'Đã xóa vĩnh viễn khóa học.');
    }
    public function create()
    {
        $pageTitle = 'Thêm mới khóa học';
        $categories = $this->categoriesRepository->getAllCategories();
        $teacher = $this->teacherRepository->getAllTeacher()->get();

        $breadcrumbs = [
            ['label' => 'Quản lý khóa học', 'link' => route('courses.index')],
            ['label' => 'Thêm mới']
        ];

        return view('courses::create', compact('pageTitle', 'categories', 'teacher', 'breadcrumbs'));
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
            logName: 'Thêm mới',
            description: 'Tạo mới khóa học'
        );

        Student::chunk(100, function ($students) use ($course) {
            foreach ($students as $student) {
                $student->notify(new StudentNotification([
                    'title' => 'Khóa học mới',
                    'title_translations' => [
                        'vi' => 'Khóa học mới',
                        'en' => 'New course',
                        'ko' => '새 강좌',
                        'ja' => '新しいコース',
                        'zh' => '新课程',
                    ],
                    'message' => 'Khóa học ' . localizedModelField($course, 'name', 'vi') . ' vừa được đăng tải',
                    'message_translations' => [
                        'vi' => 'Khóa học ' . localizedModelField($course, 'name', 'vi') . ' vừa được đăng tải',
                        'en' => 'Course ' . localizedModelField($course, 'name', 'en') . ' has just been published',
                        'ko' => '강좌 ' . localizedModelField($course, 'name', 'ko') . '가 방금 게시되었습니다',
                        'ja' => 'コース ' . localizedModelField($course, 'name', 'ja') . ' が公開されました',
                        'zh' => '课程 ' . localizedModelField($course, 'name', 'zh') . ' 刚刚发布',
                    ],
                    'url' => route('courses.detail', [
                        'locale' => app()->getLocale(),
                        'slug' => $course->slug
                    ]),
                ]));
            }
        });

        return redirect()->route('courses.index')->with('msg', __('courses::messages.create.success'));
    }

    public function edit($id)
    {
        $pageTitle = 'Cập nhật khóa học';
        $courses = $this->courseRepository->getCourse($id);
        $categoriesId = $this->courseRepository->getRelatedCategories($courses);
        $categories = $this->categoriesRepository->getAllCategories();
        $teacher = $this->teacherRepository->getAllTeacher()->get();

        if (empty($courses) || empty($categoriesId) || empty($categories) || empty($teacher)) {
            abort(404);
        }

        $breadcrumbs = [
            ['label' => 'Quản lý khóa học', 'link' => route('courses.index')],
            ['label' => 'Cập nhật']
        ];

        return view('courses::edit', compact('courses', 'pageTitle', 'categories', 'categoriesId', 'teacher', 'breadcrumbs'));
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
            logName: 'Cập nhật',
            description: 'Cập nhật khóa học'
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
        abort_unless(auth()->user()?->hasPermission('courses.soft_delete'), 403);

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
                logName: 'Xóa',
                description: 'Xóa khóa học'
            );

            return back()->with('msg', __('courses::messages.delete.success'));
        }
        return back()->with('msg_danger', 'Xóa tháº¥t báº¡i');
    }

    public function logs(Request $request, $id)
    {
        $course = $this->courseRepository->find($id);
        if (empty($course)) {
            abort(404);
        }
        $pageTitle = "Lịch sử: {$course->name}";

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

        $breadcrumbs = [
            ['label' => 'Quản lý khóa học', 'link' => route('courses.index')],
            ['label' => 'Lịch sử hoạt động']
        ];

        return view('courses::logs', compact('pageTitle', 'course', 'logs', 'breadcrumbs'));
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
            $courseData['name'] = $this->duplicateTitle($course->name, 'Bản sao');
            $courseData['name_ko'] = $this->duplicateTitle($course->name_ko, 'ë³µì œë³¸');
            $courseData['name_ja'] = $this->duplicateTitle($course->name_ja, 'è¤‡è£½ç‰ˆ');
            $courseData['name_zh'] = $this->duplicateTitle($course->name_zh, 'Ã¥Â¤ÂÃ¥Ë†Â¶Ã§â€°Ë†');
            $courseData['slug'] = $this->duplicateSlug($course->slug, 'copy');
            $courseData['slug_en'] = $this->duplicateSlug($course->slug_en, 'copy');
            $courseData['slug_ko'] = $this->duplicateSlug($course->slug_ko, 'copy');
            $courseData['slug_ja'] = $this->duplicateSlug($course->slug_ja, 'copy');
            $courseData['slug_zh'] = $this->duplicateSlug($course->slug_zh, 'copy');
            $courseData['code'] = $this->duplicateCode($course->code);
            $courseData['status'] = 0;
            $courseData['is_learning_locked'] = 0;
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
                $lessonData['name'] = $this->duplicateTitle($lesson->name, 'Bản sao');
                $lessonData['name_ko'] = $this->duplicateTitle($lesson->name_ko, 'ë³µì œë³¸');
                $lessonData['name_ja'] = $this->duplicateTitle($lesson->name_ja, 'è¤‡è£½ç‰ˆ');
                $lessonData['name_zh'] = $this->duplicateTitle($lesson->name_zh, 'Ã¥Â¤ÂÃ¥Ë†Â¶Ã§â€°Ë†');
                $lessonData['slug'] = $this->duplicateSlug($lesson->slug, 'copy');
                $lessonData['slug_en'] = $this->duplicateSlug($lesson->slug_en, 'copy');
                $lessonData['slug_ko'] = $this->duplicateSlug($lesson->slug_ko, 'copy');
                $lessonData['slug_ja'] = $this->duplicateSlug($lesson->slug_ja, 'copy');
                $lessonData['slug_zh'] = $this->duplicateSlug($lesson->slug_zh, 'copy');
                $lessonData['status'] = 0;
                $lessonData['view'] = 0;
                $lessonData['parent_id'] = null;

                $newLesson = Lesson::query()->create($lessonData);
                $lessonMap[$lesson->id] = [
                    'id' => $newLesson->id,
                    'parent_id' => $oldParentId,
                ];
            }

            foreach ($lessonMap as $oldLessonId => $map) {
                if (!empty($map['parent_id']) && isset($lessonMap[$map['parent_id']])) {
                    Lesson::query()
                        ->whereKey($map['id'])
                        ->update(['parent_id' => $lessonMap[$map['parent_id']]['id']]);
                }
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
