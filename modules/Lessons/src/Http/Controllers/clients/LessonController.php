<?php

namespace Modules\Lessons\src\Http\Controllers\clients;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;

class LessonController extends Controller
{
    protected $lessonRepository;
    public function __construct(LessonsRepositoryInterface $lessonRepository)
    {
       
        $this->lessonRepository = $lessonRepository;
    }

    public function index($slug)
    {
        $lesson = $this->lessonRepository->getLessonActive($slug);
        if(!$lesson){
            abort(404);
        }
        $pageTitle = $lesson->name;
        $pageName = $lesson->name;
        return view('lessons::clients.index', compact('pageTitle', 'pageName','lesson'));
    }
}
