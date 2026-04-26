<?php

namespace Modules\Courses\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Scopes\ActiveScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Iman\Streamer\VideoStreamer;
use Modules\Categories\src\Models\Category;
use Modules\Courses\src\Models\Courses;
use Modules\Courses\src\Models\CourseViewTracking;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Orders\src\Repositories\OrdersRepositoryInterface;
use Modules\Finances\src\Support\AffiliateLinkManager;
use Modules\Courses\src\Models\CourseBundle;

class CoursesController extends Controller
{
    protected $courseRepository;
    protected $lessonRepository;
    protected $orderRepository;

    public function __construct(
        CoursesRepositoryInterface $courseRepository,
        LessonsRepositoryInterface $lessonRepository,
        OrdersRepositoryInterface $orderRepository,
        protected AffiliateLinkManager $affiliateLinkManager
    ) {
        $this->courseRepository = $courseRepository;
        $this->lessonRepository = $lessonRepository;
        $this->orderRepository = $orderRepository;
    }

    public function index(Request $request)
    {
        $perPage = 4;
        $searchKeyword = trim((string) $request->input('keyword', ''));
        $sort = (string) $request->input('sort', 'latest');
        $ratingMin = $request->filled('rating_min') ? (string) $request->input('rating_min') : '';
        $allowedSorts = ['latest', 'rating_desc', 'rating_asc'];
        $allowedRatingMins = ['', '4', '4.5'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'latest';
        }
        if (!in_array($ratingMin, $allowedRatingMins, true)) {
            $ratingMin = '';
        }

        $pageTitle = __('courses::clients/common.page_title');
        $pageName = __('courses::clients/common.page_title');
        $courses = Courses::query()
            ->withCount(['students', 'ratings' => function ($query) {
                $query->where('status', 1);
            }])
            ->withAvg(['ratings' => function ($query) {
                $query->where('status', 1);
            }], 'rating')
            ->whereHas('teacher', function ($query) {
                $query->where('status', '!=', \Modules\Teacher\src\Models\Teacher::STATUS_CEASED);
            })
            ->when($searchKeyword !== '', function ($query) use ($searchKeyword) {
                $query->where(function ($searchQuery) use ($searchKeyword) {
                    $searchQuery->where('name', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('name_en', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('name_ko', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('name_ja', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('name_zh', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('detail', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('detail_en', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('detail_ko', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('detail_ja', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('detail_zh', 'like', '%' . $searchKeyword . '%');
                });
            })
            ->when($ratingMin !== '', function ($query) use ($ratingMin) {
                $query->having('ratings_avg_rating', '>=', (float) $ratingMin);
            });

        match ($sort) {
            'rating_desc' => $courses
                ->orderByRaw('COALESCE(ratings_avg_rating, 0) DESC')
                ->orderByDesc('ratings_count')
                ->latest('id'),
            'rating_asc' => $courses
                ->orderByRaw('COALESCE(ratings_avg_rating, 0) ASC')
                ->orderBy('ratings_count')
                ->latest('id'),
            default => $courses->latest('id'),
        };

        $courses = $courses
            ->paginate($perPage)
            ->withQueryString();

        return view('courses::clients.index', compact('pageTitle', 'pageName', 'courses', 'searchKeyword', 'sort', 'ratingMin'));
    }

    public function detail($locale, $slug_locale)
    {
        $student = Auth::guard('students')->user();
        $course = $this->courseRepository->getCourseForClientAccess($slug_locale, $student?->id);

        if (!$course) {
            abort(404);
        }

        $isAdmin = auth('web')->check() && auth('web')->user()->hasPermission('dashboard.view');
        $isImpersonating = session()->has('admin_impersonator');
        $hasCourse = $isAdmin || $isImpersonating || ($student && $student->courses()->where('courses.id', $course->id)->wherePivot('status', 1)->exists());

        if ((int) $course->status !== 1 && !$hasCourse) {
            abort(404);
        }

        if ((int) $course->is_learning_locked === 1 && $hasCourse) {
            abort(403, 'Khóa học này đang tạm thời bị khóa học tập.');
        }

        if ($course->teacher) {
            if ($expiredResponse = $this->affiliateLinkManager->ensurePublicAccessAllowed(
                request(),
                $course->teacher,
                'course',
                (int) $course->id
            )) {
                return $expiredResponse;
            }
            $this->affiliateLinkManager->captureClick(
                request(),
                $course->teacher,
                'course',
                (int) $course->id,
                route('courses.detail', ['locale' => $locale, 'slug' => $slug_locale])
            );
        }

        $this->trackCourseView($course, $student?->id);

        $pageTitle = $course->name_locale;
        $pageName = $course->name_locale;
        $index = 0;
        $canComment = $hasCourse;
        $canRate = $hasCourse;
        $viewerIsAdmin = Auth::check();
        $course->loadCount(['ratings' => function ($query) {
            $query->where('status', 1);
        }]);
        $course->loadAvg(['ratings' => function ($query) {
            $query->where('status', 1);
        }], 'rating');
        if ($course->teacher) {
            $course->teacher->loadCount(['ratings' => function ($query) {
                $query->where('status', 1);
            }]);
            $course->teacher->loadAvg(['ratings' => function ($query) {
                $query->where('status', 1);
            }], 'rating');
        }
        $threads = courseCommentThreads($course->id, $viewerIsAdmin);
        $viewerCourseRating = $student
            ? $student->courseRatings()->where('course_id', $course->id)->value('rating')
            : null;

        return view('courses::clients.detail', compact(
            'pageTitle',
            'pageName',
            'course',
            'index',
            'threads',
            'canComment',
            'canRate',
            'viewerIsAdmin',
            'hasCourse',
            'viewerCourseRating'
        ));
    }

    public function bundleDetail($locale, $slug)
    {
        $student = Auth::guard('students')->user();
        $bundle = CourseBundle::query()
            ->with([
                'teacher',
                'items.course' => function ($query) {
                    $query->withoutGlobalScope(ActiveScope::class)
                        ->withCount('students');
                },
            ])
            ->where('slug', $slug)
            ->where('status', true)
            ->firstOrFail();

        if ($bundle->teacher) {
            if ($expiredResponse = $this->affiliateLinkManager->ensurePublicAccessAllowed(
                request(),
                $bundle->teacher,
                'bundle',
                (int) $bundle->id
            )) {
                return $expiredResponse;
            }
            $this->affiliateLinkManager->captureClick(
                request(),
                $bundle->teacher,
                'bundle',
                (int) $bundle->id,
                route('courses.bundle.detail', ['locale' => $locale, 'slug' => $slug])
            );
        }

        $courses = $bundle->items
            ->pluck('course')
            ->filter(function ($course) use ($bundle) {
                return $course
                    && (int) $course->teacher_id === (int) $bundle->teacher_id
                    && (int) $course->status === 1
                    && (int) $course->is_learning_locked !== 1;
            })
            ->values();

        if ($courses->isEmpty()) {
            abort(404);
        }

        $ownedCourseIds = collect();
        if ($student) {
            $ownedCourseIds = $student->courses()
                ->wherePivot('status', 1)
                ->whereIn('courses.id', $courses->pluck('id')->all())
                ->pluck('courses.id');
        }

        $pageTitle = $bundle->name;
        $pageName = $bundle->name;
        $sourceTotal = (float) $courses->sum(function ($course) {
            return ($course->sale_price && $course->sale_price > 0)
                ? (float) $course->sale_price
                : (float) $course->price;
        });
        $detailRows = collect($this->buildBundleOrderDetails($courses, (float) $bundle->price));
        $remainingDetailRows = $detailRows
            ->reject(fn ($row) => $ownedCourseIds->contains((int) $row['course_id']))
            ->values();
        $remainingCourseIds = $remainingDetailRows->pluck('course_id')->map(fn ($id) => (int) $id)->all();
        $remainingCourses = $courses
            ->filter(fn ($course) => in_array((int) $course->id, $remainingCourseIds, true))
            ->values();
        $hasOwnedCourses = $ownedCourseIds->isNotEmpty();
        $allCoursesOwned = $remainingCourses->isEmpty();
        $payableAmount = (float) $remainingDetailRows->sum('price');
        $ownedValue = (float) max((float) $bundle->price - $payableAmount, 0);

        return view('courses::clients.bundle_detail', compact(
            'pageTitle',
            'pageName',
            'bundle',
            'courses',
            'remainingCourses',
            'sourceTotal',
            'ownedCourseIds',
            'hasOwnedCourses',
            'allCoursesOwned',
            'payableAmount',
            'ownedValue'
        ));
    }

    public function getTrialVideo($locale, $lessonId = 0)
    {
        if (!Auth::guard('students')->check()) {
            return [
                'success' => false,
                'requires_login' => true,
                'message' => __('courses::clients/common.trial_login_required'),
            ];
        }

        $lesson = $this->lessonRepository->find($lessonId);
        if (!$lesson || (int) $lesson->is_trial !== 1) {
            return ['success' => false];
        }

        $student = Auth::guard('students')->user();
        $course = Courses::query()->withoutGlobalScopes()->find($lesson->course_id);

        if (!$course) {
            return ['success' => false];
        }

        $hasCourse = $student
            ? $student
                ->courses()
                ->where('courses.id', $course->id)
                ->wherePivot('status', 1)
                ->exists()
            : false;

        if ((int) $course->status !== 1 && !$hasCourse) {
            return [
                'success' => false,
                'message' => 'Khóa học này hiện không khả dụng.',
            ];
        }

        if ((int) $course->is_learning_locked === 1) {
            return [
                'success' => false,
                'message' => 'Khóa học này đang tạm thời bị khóa học tập.',
            ];
        }

        return [
            'success' => true,
            'data' => [
                'id' => $lesson->id,
                'name' => $lesson->name_locale,
                'is_trial' => (int) $lesson->is_trial,
                'video' => videoPlaybackMeta($lesson->video?->url, $locale),
            ],
        ];
    }

    public function streamVideo(Request $request)
    {
        $videoPath = $request->video;
        $path = public_path($videoPath);
        VideoStreamer::streamFile($path);
    }

    public function create(Request $request)
    {
        $studentId = Auth::guard('students')->user()->id;

        $course = $this->courseRepository->getCourse($request->course_id);

        if (!$course) {
            abort(404);
        }

        if ((int) $course->status !== 1) {
            abort(404);
        }

        if ((int) $course->is_learning_locked === 1) {
            abort(403, 'Khóa học này đang tạm thời bị khóa học tập.');
        }

        // Kiểm tra Sắp ra mắt cho khóa học lẻ
        if ($course->is_coming_soon && $course->coming_soon_start_at && $course->coming_soon_start_at->isFuture()) {
            return back()
                ->with('msg', 'Khóa học này chưa mở bán. Vui lòng chờ đến ngày ra mắt!')
                ->with('msgType', 'warning');
        }

        $price = $course->sale_price && $course->sale_price > 0
            ? $course->sale_price
            : $course->price;

        $orderData = [
            'code' => generateUniqueCouponCode(),
            'student_id' => $studentId,
            'affiliate_link_id' => $course->teacher
                ? optional($this->affiliateLinkManager->resolveTrackedLink(request(), $course->teacher, 'course', (int) $course->id))->id
                : null,
            'discount' => 0,
            'price' => $price,
            'coupon' => null,
            'status_id' => 1,
            'payment_date' => null,
        ];

        $detailData = [
            'course_id' => $course->id,
            'price' => $price,
        ];

        $order = $this->orderRepository->createOrderWithDetail($orderData, $detailData);

        return redirect()->route('students.account.checkout', ['locale' => app()->getLocale(), 'id' => $order->id]);
    }

    public function createBundleOrder(Request $request)
    {
        $student = Auth::guard('students')->user();
        $bundle = CourseBundle::query()
            ->with([
                'items.course' => function ($query) {
                    $query->withoutGlobalScope(ActiveScope::class);
                },
            ])
            ->where('id', (int) $request->input('bundle_id'))
            ->where('status', true)
            ->firstOrFail();

        // Kiểm tra các điều kiện bán hàng cho Combo
        if ($bundle->is_coming_soon && $bundle->coming_soon_start_at && $bundle->coming_soon_start_at->isFuture()) {
            return back()
                ->with('msg', 'Combo này chưa chính thức mở bán. Vui lòng quay lại sau!')
                ->with('msgType', 'warning');
        }

        if ($bundle->end_at && $bundle->end_at->isPast()) {
            return back()
                ->with('msg', 'Rất tiếc, thời gian đăng ký combo này đã kết thúc!')
                ->with('msgType', 'danger');
        }

        if ($bundle->quantity !== null && $bundle->quantity <= 0) {
            return back()
                ->with('msg', 'Combo này hiện đã hết lượt đăng ký!')
                ->with('msgType', 'danger');
        }

        $courses = $bundle->items
            ->pluck('course')
            ->filter(function ($course) use ($bundle) {
                return $course
                    && (int) $course->teacher_id === (int) $bundle->teacher_id
                    && (int) $course->status === 1
                    && (int) $course->is_learning_locked !== 1;
            })
            ->values();

        if ($courses->count() < 2) {
            return back()
                ->with('msg', __('teacher::dashboard.bundles.flash.public_not_available'))
                ->with('msgType', 'danger');
        }

        $ownedCourseIds = $student->courses()
            ->wherePivot('status', 1)
            ->whereIn('courses.id', $courses->pluck('id')->all())
            ->pluck('courses.id');

        $detailRows = collect($this->buildBundleOrderDetails($courses, (float) $bundle->price))
            ->reject(fn ($row) => $ownedCourseIds->contains((int) $row['course_id']))
            ->values();

        if ($detailRows->isEmpty()) {
            return back()
                ->with('msg', __('teacher::dashboard.bundles.flash.all_courses_owned'))
                ->with('msgType', 'danger');
        }

        $payableAmount = (float) $detailRows->sum('price');

        $orderData = [
            'code' => generateUniqueCouponCode(),
            'student_id' => $student->id,
            'bundle_id' => $bundle->id,
            'affiliate_link_id' => $bundle->teacher
                ? optional($this->affiliateLinkManager->resolveTrackedLink(request(), $bundle->teacher, 'bundle', (int) $bundle->id))->id
                : null,
            'discount' => 0,
            'price' => $payableAmount,
            'coupon' => null,
            'status_id' => 1,
            'payment_date' => null,
        ];

        $order = $this->orderRepository->createOrderWithDetails(
            $orderData,
            $detailRows->all()
        );

        return redirect()->route('students.account.checkout', ['locale' => app()->getLocale(), 'id' => $order->id]);
    }

    public function category($locale, $slug)
    {
        $category = Category::where(function ($query) use ($slug) {
            $query->where('slug', $slug)
                ->orWhere('slug_en', $slug)
                ->orWhere('slug_ko', $slug)
                ->orWhere('slug_ja', $slug)
                ->orWhere('slug_zh', $slug);
        })->firstOrFail();

        $pageTitle = __('courses::clients/common.page_title') . ' ' . $category->name_locale;
        $pageName = $pageTitle;

        $courses = $category->courses()
            ->where('status', 1)
            ->paginate(config('paginate.limit'));

        return view('courses::clients.index', compact('category', 'courses', 'pageTitle', 'pageName'));
    }

    private function buildBundleOrderDetails($courses, float $bundlePrice): array
    {
        $courses = collect($courses)->values();
        if ($courses->isEmpty()) {
            return [];
        }

        if ($bundlePrice <= 0) {
            return $courses->map(fn ($course) => [
                'course_id' => $course->id,
                'price' => 0,
            ])->all();
        }

        $basePrices = $courses->map(function ($course) {
            return ($course->sale_price && $course->sale_price > 0)
                ? (float) $course->sale_price
                : (float) $course->price;
        });
        $baseTotal = (float) $basePrices->sum();

        if ($baseTotal <= 0) {
            $equalPrice = round($bundlePrice / max($courses->count(), 1), 2);

            return $courses->map(function ($course, $index) use ($courses, $bundlePrice, $equalPrice) {
                $price = $index === $courses->count() - 1
                    ? round($bundlePrice - ($equalPrice * ($courses->count() - 1)), 2)
                    : $equalPrice;

                return [
                    'course_id' => $course->id,
                    'price' => $price,
                ];
            })->all();
        }

        $allocated = [];
        $runningTotal = 0;

        foreach ($courses as $index => $course) {
            if ($index === $courses->count() - 1) {
                $price = round($bundlePrice - $runningTotal, 2);
            } else {
                $courseBasePrice = (float) $basePrices[$index];
                $price = round(($courseBasePrice / $baseTotal) * $bundlePrice, 2);
                $runningTotal += $price;
            }

            $allocated[] = [
                'course_id' => $course->id,
                'price' => max($price, 0),
            ];
        }

        return $allocated;
    }

    private function trackCourseView(Courses $course, ?int $studentId = null): void
    {
        $visitorHash = $this->resolveCourseVisitorHash($course->id, $studentId);
        $cacheKey = 'course_view_tracking_' . $course->id . '_' . $visitorHash;

        if (Cache::has($cacheKey)) {
            return;
        }

        $tracking = CourseViewTracking::query()->firstOrCreate(
            [
                'course_id' => $course->id,
                'view_date' => now()->toDateString(),
                'visitor_hash' => $visitorHash,
            ],
            [
                'student_id' => $studentId,
                'viewed_at' => now(),
            ]
        );

        if ($tracking->wasRecentlyCreated) {
            $course->increment('view');
        }

        Cache::put($cacheKey, true, now()->addMinutes(30));
    }

    private function resolveCourseVisitorHash(int $courseId, ?int $studentId = null): string
    {
        if ($studentId) {
            return hash('sha256', 'student:' . $studentId . ':course:' . $courseId);
        }

        $ipAddress = (string) request()->ip();
        $userAgent = (string) request()->userAgent();

        return hash('sha256', 'guest:' . $courseId . ':' . $ipAddress . ':' . Str::limit($userAgent, 180, ''));
    }
}
