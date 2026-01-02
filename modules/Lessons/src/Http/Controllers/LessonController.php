<?php

namespace Modules\Lessons\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;
use Modules\Lessons\src\Http\Requests\LessonRequest;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;

class LessonController extends Controller
{
    protected $courseRepository;
    protected $videoRepository;
    protected $documentRepository;
    protected $lessonRepository;
    public function __construct(CoursesRepositoryInterface $courseRepository, VideoRepositoryInterface $videoRepository, DocumentRepositoryInterface $documentRepository, LessonsRepositoryInterface $lessonRepository)
    {
        $this->courseRepository = $courseRepository;
        $this->videoRepository = $videoRepository;
        $this->documentRepository = $documentRepository;
        $this->lessonRepository = $lessonRepository;
    }

    public function index($courseId)
    {
        $courses = $this->courseRepository->find($courseId);
        if (!$courses) {
            abort(404);
        }
        $pageTitle = !empty($courses) ? 'Bải giảng: ' . $courses->name : 'Bài giảng: ';
        return view('lessons::lists', compact('pageTitle', 'courses'));
    }

    public function sort(Request $request, $courseId)
    {
        $pageTitle = 'Sắp xếp bài giảng';
        $modules = $this->lessonRepository->getLessons($courseId)->with('children')->get();
        return view('lessons::sort', compact('pageTitle', 'courseId','modules'));
    }

    public function handleSort(Request $request, $courseId){
        $lessons = $request->lesson;
        if($lessons){
            foreach($lessons as $index => $lessonsId){
                $this->lessonRepository->update($lessonsId, [
                    'position' => $index
                ]);
            }
        }
        return redirect()->route('lessons.sort', $courseId)->with('msg', __('lessons::messages.update.success'));
    }

    public function data($courseId)
    {
        $lessons = $this->lessonRepository->getLessons($courseId);

        $lessons = DataTables::of($lessons)->toArray();
        $lessons['data'] = $this->getLessonTable($lessons['data']);
        return $lessons;
    }

    public function getLessonTable($lessons, $char = '', &$result = [])
    {
        if (!empty($lessons)) {
            foreach ($lessons as $key => $lesson) {
                $row = $lesson;
                $row['name'] = $char . $lesson['name'];
                if ($row['parent_id'] == null) {
                    $row['is_trial'] = '';
                    $row['view'] = '';
                    $row['durations'] = '';
                    $row['add'] = '<a href="' . route('lessons.add', $row['course_id']) . '?module=' . $row['id'] . '" class="btn btn-primary btn-sm">Thêm bài</a>';
                    $row['edit'] = '<a href="' . route('lessons.edit', $row['id']) . '" class="btn btn-warning btn-sm">Sửa</a>';
                    $row['delete'] = '<a href="' . route('lessons.delete', $lesson['id']) . '" class="btn btn-danger btn-sm delete-action">Xóa</a>';
                } else {
                    $row['is_trial'] = $row['is_trial'] == 1 ? 'Có' : 'Không';
                    $row['view'] = $lesson['view'];
                    $row['durations'] = getTime($row['durations']);
                    $row['add'] = '';
                    $row['edit'] = '<a href="' . route('lessons.edit', $row['id']) . '" class="btn btn-warning btn-sm">Sửa</a>';
                    $row['delete'] = '<a href="' . route('lessons.delete', $lesson['id']) . '" class="btn btn-danger btn-sm delete-action">Xóa</a>';
                }
                unset($row['sub_lessons']);
                unset($row['course_id']);
                unset($row['created_at']);
                unset($row['updated_at']);
                $result[] = $row;
                if (!empty($lesson['sub_lessons'])) {
                    $this->getLessonTable($lesson['sub_lessons'], $char . '|--', $result);
                }
            }
        }
        return $result;
    }

    public function create(Request $request, $courseId)
    {
        $pageTitle = 'Thêm bài giảng';
        $position = $this->lessonRepository->getPosition($courseId);
        $lessons = $this->lessonRepository->getAllLessions();
        return view('lessons::create', compact('pageTitle', 'courseId', 'position', 'lessons'));
    }

    public function store(LessonRequest $request, $courseId)
    {
        $name = $request->name;
        $slug = $request->slug;
        $video = $request->video;
        $document = $request->document;
        $parent_id = $request->parent_id == 0 ? null : $request->parent_id;
        $is_trial = $request->is_trial;
        $position = $request->position;
        $description = $request->description;
        $document_id = null;
        $video_id = null;
        if (!empty($document)) {
            $documentInfo = getFileInfo($document);
            $document = $this->documentRepository->createDocument(
                [
                    'name' => $documentInfo['name'],
                    'url' => $document,
                    'size' => $documentInfo['size']
                ],
                $document
            );
            $document_id = $document ? $document->id : null;
        }

        if (!empty($video)) {
            $videoInfo = getVideoInfo($video);
            $video = $this->videoRepository->createVideo(
                [
                    'url' => $video,
                    'name' => $videoInfo['filename'],
                    'size' => $videoInfo['playtime_seconds']
                ],
                $video
            );
            $video_id = $video ? $video->id : null;
        }
        $this->lessonRepository->create([
            'name' => $name,
            'slug' => $slug,
            'video_id' => $video_id,
            'course_id' => $courseId,
            'document_id' => $document_id,
            'parent_id' => $parent_id,
            'is_trial' => $is_trial,
            'position' => $position,
            'durations' => $videoInfo['playtime_seconds'] ?? 0,
            'description' => $description,
        ]);

        return redirect()->route('lessons.index', $courseId)->with('msg', __('lessons::messages.create.success'));
    }

    public function edit(Request $request, $lessonId)
    {
        $pageTitle = 'Cập nhập bài học';
        // $position = $this->lessonRepository->getPosition($courseId);
        $lessons = $this->lessonRepository->getAllLessions();
        $lesson = $this->lessonRepository->find($lessonId);
        $lesson->video = $lesson->video?->url;
        $lesson->document = $lesson->document?->url;
        if (!$lesson) {
            return abort(404);
        }
        $courseId = $lesson->course_id;
        return view('lessons::edit', compact('pageTitle', 'lessons', 'lesson', 'courseId'));
    }

    public function update(Request $request, $lessonId)
    {
        $name = $request->name;
        $slug = $request->slug;
        $video = $request->video;
        $document = $request->document;
        $parent_id = $request->parent_id == 0 ? null : $request->parent_id;
        $is_trial = $request->is_trial;
        $position = $request->position;
        $description = $request->description;
        $document_id = null;
        $video_id = null;
        if (!empty($document)) {
            $documentInfo = getFileInfo($document);
            $document = $this->documentRepository->createDocument(
                [
                    'name' => $documentInfo['name'],
                    'url' => $document,
                    'size' => $documentInfo['size']
                ],
                $document
            );
            $document_id = $document ? $document->id : null;
        }

        if (!empty($video)) {
            $videoInfo = getVideoInfo($video);
            $video = $this->videoRepository->createVideo(
                [
                    'url' => $video,
                    'name' => $videoInfo['filename'],
                    'size' => $videoInfo['playtime_seconds']
                ],
                $video
            );
            $video_id = $video ? $video->id : null;
        }
        $this->lessonRepository->update($lessonId, [
            'name' => $name,
            'slug' => $slug,
            'video_id' => $video_id,
            'document_id' => $document_id,
            'parent_id' => $parent_id,
            'is_trial' => $is_trial,
            'position' => $position,
            'durations' => $videoInfo['playtime_seconds'] ?? 0,
            'description' => $description,
        ]);

        return redirect()->route('lessons.edit', $lessonId)->with('msg', __('lessons::messages.update.success'));
    }

    public function delete(Request $request, $lessonId)
    {
        $lesson = $this->lessonRepository->find($lessonId);
        if (!$lessonId) {
            abort(404);
        }
        $this->lessonRepository->delete($lessonId);
        return redirect()->route('lessons.index', $lesson->course_id)->with('msg', __('lessons::messages.delete.success'));
    }
}
