<?php

namespace Modules\Courses\src\Http\Controllers\clients;

use App\Http\Controllers\Controller;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;

class CoursesController extends Controller
{
    protected $courseRepository;

    public function __construct(CoursesRepositoryInterface $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }
    public function index()
    {
        $pageTitle = 'Khóa học';
        $pageName = 'Khóa học';
        $courses = $this->courseRepository->getCourses(config('paginate.limit'));
        return view('courses::clients.index', compact('pageTitle', 'pageName','courses'));
    }
}
