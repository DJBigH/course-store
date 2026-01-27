<?php

namespace Modules\Home\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Students\src\Repositories\StudentsRepositoryInterface;

class HomeController extends Controller
{
    protected $courseRepository;
    protected $studentRepository;
    public function __construct(CoursesRepositoryInterface $coursesRepository, StudentsRepositoryInterface $studentsRepository)
    {
        $this->courseRepository = $coursesRepository;
        $this->studentRepository = $studentsRepository;
    }

    public function index()
    {
        $pageTitle  = 'Trang chủ';
        $courseFree = $this->courseRepository->getCourseFree();
        $courseView = $this->courseRepository->getCourseView();
        $courseNew  = $this->courseRepository->getCourseCreateUpdate();

        $studentId = Auth::guard('students')->id();
        $myCourse  = collect();

        if ($studentId) {
            $myCourse = $this->studentRepository->getPurchasedCourses($studentId, config('paginate.home_mycourse_limit'));
        }
        
        $courseAll = $this->courseRepository->getCourseForYou($studentId);
        return view(
            'home::index',
            compact('pageTitle', 'courseFree', 'courseView', 'courseNew', 'myCourse', 'courseAll')
        );
    }
}
