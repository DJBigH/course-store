<?php

namespace Modules\Courses\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Iman\Streamer\VideoStreamer;
use Modules\Categories\src\Models\Category;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Orders\src\Repositories\OrdersRepositoryInterface;

class CoursesController extends Controller
{
    protected $courseRepository;
    protected $lessonRepository;
    protected $orderRepository;

    public function __construct(CoursesRepositoryInterface $courseRepository, LessonsRepositoryInterface $lessonRepository, OrdersRepositoryInterface $orderRepository)
    {
        $this->courseRepository = $courseRepository;
        $this->lessonRepository = $lessonRepository;
        $this->orderRepository = $orderRepository;
    }
    public function index(Request $request)
    {
        $student = Auth::guard('students')->user();

        $course = $this->courseRepository->getCourse($request->course_id);
        $pageTitle = 'Khóa học';
        $pageName = 'Khóa học';
        $courses = $this->courseRepository->getCourses(config('paginate.limit'));

        return view('courses::clients.index', compact('pageTitle', 'pageName', 'courses'));
    }

    public function detail($locale, $slug)
    {
        // middleware setLocale đã set app()->getLocale() rồi
        $course = $this->courseRepository->getCourseActive($slug);

        if (!$course) abort(404);

        $cacheKey = 'course_view_' . $course->id . '_' . request()->ip();

        if (!Cache::has($cacheKey)) {
            $course->increment('view');
            Cache::put($cacheKey, true, now()->addMinutes(30));
        }

        $pageTitle = $course->name;
        $pageName  = $course->name;
        $index = 0;

        return view('courses::clients.detail', compact('pageTitle', 'pageName', 'course', 'index'));
    }

    public function getTrialVideo($locale, $lessonId = 0)
    {
        $lesson = $this->lessonRepository->find($lessonId);
        if (!$lesson) {
            return ['success' => false];
        }
        return ['success' => true, 'data' => $lesson];
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
        $price = $course->sale_price && $course->sale_price > 0
            ? $course->sale_price
            : $course->price;

        $orderData = [
            'code'       => generateUniqueCouponCode(),
            'student_id' => $studentId,
            'discount'   => 0,
            'price'     => $price,
            'coupon'     => null,
            'status_id'  => 1,
            'payment_date' => null,
        ];

        $detailData = [
            'course_id' => $course->id,
            'price'     => $price
        ];

        $order = $this->orderRepository
            ->createOrderWithDetail($orderData, $detailData);

        return redirect()->route('students.account.checkout', ['locale' => app()->getLocale(), 'id' => $order->id]);
    }

    public function category($locale, $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $pageTitle = __('courses::clients/common.page_title') . ' ' . $category->name;
        $pageName  = $pageTitle;

        if (!$category) {
            abort(404);
        }
        $courses = $category->courses()
            ->where('status', 1)
            ->paginate(config('paginate.limit'));

        return view('courses::clients.index', compact('category', 'courses', 'pageTitle', 'pageName'));
    }
}
