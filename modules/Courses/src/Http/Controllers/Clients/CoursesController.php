<?php

namespace Modules\Courses\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Iman\Streamer\VideoStreamer;
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

    public function detail($slug)
    {
        $course = $this->courseRepository->getCourseActive($slug);
        if (!$course) {
            abort(404);
        }
        $pageTitle = $course->name;
        $pageName = $course->name;
        $index = 0;
        return view('courses::clients.detail', compact('pageTitle', 'pageName', 'course', 'index'));
    }

    public function getTrialVideo($lessonId = 0)
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

        return redirect()->route('students.account.checkout', $order->id);
    }
}
