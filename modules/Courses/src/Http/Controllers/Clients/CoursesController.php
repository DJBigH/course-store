<?php

namespace Modules\Courses\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Iman\Streamer\VideoStreamer;
use Modules\Categories\src\Models\Category;
use Modules\Courses\src\Models\Courses;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Orders\src\Repositories\OrdersRepositoryInterface;

class CoursesController extends Controller
{
    protected $courseRepository;
    protected $lessonRepository;
    protected $orderRepository;

    public function __construct(
        CoursesRepositoryInterface $courseRepository,
        LessonsRepositoryInterface $lessonRepository,
        OrdersRepositoryInterface $orderRepository
    ) {
        $this->courseRepository = $courseRepository;
        $this->lessonRepository = $lessonRepository;
        $this->orderRepository = $orderRepository;
    }

    public function index(Request $request)
    {
        $searchKeyword = trim((string) $request->input('keyword', ''));
        $pageTitle = __('courses::clients/common.page_title');
        $pageName = __('courses::clients/common.page_title');
        $courses = $this->courseRepository->getCourses(config('paginate.limit'));

        if ($searchKeyword !== '') {
            $courses = Courses::query()
                ->withCount('students')
                ->where(function ($query) use ($searchKeyword) {
                    $query->where('name', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('name_en', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('name_ko', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('name_ja', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('name_zh', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('detail', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('detail_en', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('detail_ko', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('detail_ja', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('detail_zh', 'like', '%' . $searchKeyword . '%');
                })
                ->latest('id')
                ->paginate(config('paginate.limit'))
                ->withQueryString();
        }

        return view('courses::clients.index', compact('pageTitle', 'pageName', 'courses', 'searchKeyword'));
    }

    public function detail($locale, $slug_locale)
    {
        $student = Auth::guard('students')->user();
        $course = $this->courseRepository->getCourseForClientAccess($slug_locale, $student?->id);

        if (!$course) {
            abort(404);
        }

        $hasCourse = $student
            ? $student
                ->courses()
                ->where('courses.id', $course->id)
                ->wherePivot('status', 1)
                ->exists()
            : false;

        if ((int) $course->status !== 1 && !$hasCourse) {
            abort(404);
        }

        if ((int) $course->is_learning_locked === 1 && $hasCourse) {
            abort(403, 'Khóa học này đang tạm thời bị khóa học tập.');
        }

        $cacheKey = 'course_view_' . $course->id . '_' . request()->ip();

        if (!Cache::has($cacheKey)) {
            $course->increment('view');
            Cache::put($cacheKey, true, now()->addMinutes(30));
        }

        $pageTitle = $course->name_locale;
        $pageName = $course->name_locale;
        $index = 0;
        $canComment = $hasCourse;
        $viewerIsAdmin = Auth::check();
        $threads = courseCommentThreads($course->id, $viewerIsAdmin);

        return view('courses::clients.detail', compact(
            'pageTitle',
            'pageName',
            'course',
            'index',
            'threads',
            'canComment',
            'viewerIsAdmin',
            'hasCourse'
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

        $price = $course->sale_price && $course->sale_price > 0
            ? $course->sale_price
            : $course->price;

        $orderData = [
            'code' => generateUniqueCouponCode(),
            'student_id' => $studentId,
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
}
