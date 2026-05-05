<?php

namespace Modules\Lessons\src\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\StudentNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Courses\src\Models\Courses;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;
use Modules\Lessons\src\Http\Requests\LessonRequest;
use Modules\Lessons\src\Models\Lesson;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Students\src\Models\Student;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;

class LessonController extends Controller
{
    protected CoursesRepositoryInterface $courseRepository;
    protected VideoRepositoryInterface $videoRepository;
    protected DocumentRepositoryInterface $documentRepository;
    protected LessonsRepositoryInterface $lessonRepository;
    public function __construct(CoursesRepositoryInterface $courseRepository, VideoRepositoryInterface $videoRepository, DocumentRepositoryInterface $documentRepository, LessonsRepositoryInterface $lessonRepository)
    {
        $this->courseRepository = $courseRepository;
        $this->videoRepository = $videoRepository;
        $this->documentRepository = $documentRepository;
        $this->lessonRepository = $lessonRepository;
    }

    public function index($courseId)
    {
        $courses = $this->courseRepository->getCourse($courseId);
        if (!$courses) {
            abort(404);
        }
        $pageTitle = !empty($courses) ? 'Bải giảng: ' . $courses->name : 'Bài giảng: ';

        $this->updateDurations($courseId);
        return view('lessons::lists', compact('pageTitle', 'courses'));
    }

    public function trash($courseId)
    {
        $courses = $this->courseRepository->getCourse($courseId);
        if (!$courses) {
            abort(404);
        }

        $pageTitle = 'Thùng rác bài giảng: ' . $courses->name;
        $trashedLessons = Lesson::query()
            ->onlyTrashed()
            ->where('course_id', $courseId)
            ->orderByRaw('COALESCE(parent_id, 0)')
            ->orderBy('position')
            ->get();

        $trashedLessonRows = $this->flattenTrashedLessons($trashedLessons);

        return view('lessons::trash', compact('pageTitle', 'courses', 'trashedLessonRows'));
    }

    public function sort(Request $request, $courseId)
    {
        $pageTitle = 'Sắp xếp bài giảng';
        $modules = $this->lessonRepository->getLessons($courseId)->with('children')->get();
        return view('lessons::sort', compact('pageTitle', 'courseId', 'modules'));
    }

    public function handleSort(Request $request, $courseId)
    {
        $lessons = $request->lesson;

        if ($lessons) {
            foreach ($lessons as $index => $lessonsId) {
                $this->lessonRepository->update($lessonsId, ['position' => $index]);
            }

            activity_log(
                action: 'sort_lessons',
                subject: null,
                properties: [
                    'course_id' => $courseId,
                    'lesson_ids' => $lessons,
                ],
                logName: 'lesson',
                description: 'Sắp xếp bài giảng'
            );
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
        $user = auth()->user();
        $canCreateLessons = $user?->hasPermission('lessons.create');
        $canEditLessons = $user?->hasPermission('lessons.edit');
        $canDeleteLessons = $user?->canAnyPermission(['lessons.delete', 'lessons.soft_delete']);

        if (empty($lessons)) {
            return $result;
        }

        foreach ($lessons as $key => $lesson) {
            $row = $lesson;
            $lessonName = e($lesson['name'] ?? '');
            $publicLocale = in_array(app()->getLocale(), ['vi', 'en', 'ko', 'ja', 'zh'], true)
                ? app()->getLocale()
                : 'vi';

            if ($row['parent_id'] == null) {
                $row['name'] = $char . $lessonName;
            } else {
                $lessonSlug = trim((string) (($lesson['slug_locale'] ?? '') ?: ($lesson['slug'] ?? '')));

                if ($lessonSlug !== '') {
                    $lessonUrl = route('lessons.home', [
                        'locale' => $publicLocale,
                        'slug' => $lessonSlug,
                    ]);
                    $row['name'] = $char . '<a href="' . $lessonUrl . '" target="_blank" rel="noopener noreferrer" class="lesson-name-link">' . $lessonName . '</a>';
                } else {
                    $row['name'] = $char . $lessonName;
                }
            }

            if ($row['parent_id'] == null) {
                $row['is_trial'] = '';
                $row['document_id'] = $row['document_id'] != null ? 'Có' : 'Không';
                $row['view'] = '';
                $row['durations'] = '';
                $row['status'] = $row['status'] == 1
                    ? '<span class="text-success"><i class="fa-solid fa-circle-check"></i>Kích hoạt</span>'
                    : '<span class="text-muted"><i class="fa-solid fa-circle-xmark"></i>Chưa kích hoạt</span>';

                $row['add'] = $canCreateLessons
                    ? '<a href="' . route('lessons.add', ['courseId' => $row['course_id']]) . '?module=' . $row['id'] . '" class="btn btn-primary btn-sm">Thêm bài</a>'
                    : '<span class="text-muted small">Không có quyền</span>';
                $row['edit'] = $canEditLessons
                    ? '<a href="' . route('lessons.edit', ['lessonId' => $row['id']]) . '" class="btn btn-warning btn-sm">Sửa</a>'
                    : '<span class="text-muted small">Không có quyền</span>';
                $row['delete'] = $canDeleteLessons
                    ? '<a href="' . route('lessons.delete', ['lessonId' => $lesson['id']]) . '" class="btn btn-danger btn-sm delete-action">Xóa mềm</a>'
                    : '<span class="text-muted small">Không có quyền</span>';
            } else {
                $row['is_trial'] = $row['is_trial'] == 1 ? 'Có' : 'Không';
                $row['document_id'] = $row['document_id']  != null ? 'Có' : 'Không';
                $row['view'] = $lesson['view'];
                $row['durations'] = getTime($row['durations']);
                $row['status'] = $row['status'] == 1
                    ? '<span class="text-success"><i class="fa-solid fa-circle-check"></i>Kích hoạt</span>'
                    : '<span class="text-muted"><i class="fa-solid fa-circle-xmark"></i>Chưa kích hoạt</span>';
                $row['add'] = '';
                $row['edit'] = $canEditLessons
                    ? '<a href="' . route('lessons.edit', ['lessonId' => $row['id']]) . '" class="btn btn-warning btn-sm">Sửa</a>'
                    : '<span class="text-muted small">Không có quyền</span>';
                $row['delete'] = $canDeleteLessons
                    ? '<a href="' . route('lessons.delete', ['lessonId' => $lesson['id']]) . '" class="btn btn-danger btn-sm delete-action">Xóa mềm</a>'
                    : '<span class="text-muted small">Không có quyền</span>';
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

        return $result;
    }

    public function create(Request $request, $courseId)
    {
        $pageTitle = 'Thêm bài giảng';
        $position = $this->lessonRepository->getPosition($courseId);
        $lessons = $this->lessonRepository->getAllLessions($courseId);
        return view('lessons::create', compact('pageTitle', 'courseId', 'position', 'lessons'));
    }

    public function store(LessonRequest $request, $courseId)
    {
        $name = $request->name;
        $name_en = $request->name_en;
        $name_ko = $request->name_ko;
        $name_ja = $request->name_ja;
        $name_zh = $request->name_zh;
        $slug = $request->slug;
        $slug_en = $request->slug_en;
        $slug_ko = $request->slug_ko;
        $slug_ja = $request->slug_ja;
        $slug_zh = $request->slug_zh;
        $video = $request->video;
        $document = $request->document;
        $parent_id = $request->parent_id == 0 ? null : $request->parent_id;
        $is_trial = $request->is_trial;
        $position = $request->position;
        $description = $request->description;
        $description_en = $request->description_en;
        $description_ko = $request->description_ko;
        $description_ja = $request->description_ja;
        $description_zh = $request->description_zh;
        $status = $request->status ?? 0;
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

            $host = strtolower((string) parse_url($video, PHP_URL_HOST));
            $isExternal = $host && (
                str_contains($host, 'youtube.com') ||
                str_contains($host, 'youtu.be') ||
                str_contains($host, 'vimeo.com')
            );

            if ($isExternal) {
                // YouTube/Vimeo: không getVideoInfo
                $videoModel = $this->videoRepository->createVideo(
                    [
                        'url' => $video,
                        'name' => $name, // hoặc 'Youtube video'
                        'size' => 0,
                    ],
                    $video
                );

                $video_id = $videoModel ? $videoModel->id : null;
                $durations = externalVideoDuration($video);
            } else {
                // MP4/file nội bộ: giữ logic cũ
                $videoInfo = getVideoInfo($video);

                $videoModel = $this->videoRepository->createVideo(
                    [
                        'url' => $video,
                        'name' => $videoInfo['filename'] ?? $name,
                        'size' => $videoInfo['playtime_seconds'] ?? 0
                    ],
                    $video
                );

                $video_id = $videoModel ? $videoModel->id : null;
                $durations = $videoInfo['playtime_seconds'] ?? 0;
            }
        }

        $lesson = $this->lessonRepository->create([
            'name' => $name,
            'name_en' => $name_en,
            'name_ko' => $name_ko,
            'name_ja' => $name_ja,
            'name_zh' => $name_zh,
            'slug' => $slug,
            'slug_en' => $slug_en,
            'slug_ko' => $slug_ko,
            'slug_ja' => $slug_ja,
            'slug_zh' => $slug_zh,
            'video_id' => $video_id,
            'course_id' => $courseId,
            'document_id' => $document_id,
            'parent_id' => $parent_id,
            'is_trial' => $is_trial,
            'position' => $position,
            'durations' => $durations ?? 0,
            'description' => $description,
            'description_en' => $description_en,
            'description_ko' => $description_ko,
            'description_ja' => $description_ja,
            'description_zh' => $description_zh,
            'status' => $status,
        ]);
        activity_log(
            action: 'create',
            subject: $lesson,
            properties: [
                'course_id' => $courseId,
                'data' => [
                    'name' => $lesson->name,
                    'name_en' => $lesson->name_en,
                    'name_ko' => $lesson->name_ko,
                    'name_ja' => $lesson->name_ja,
                    'name_zh' => $lesson->name_zh,
                    'slug' => $lesson->slug,
                    'slug_en' => $lesson->slug_en,
                    'slug_ko' => $lesson->slug_ko,
                    'slug_ja' => $lesson->slug_ja,
                    'slug_zh' => $lesson->slug_zh,
                    'parent_id' => $lesson->parent_id,
                    'is_trial' => $lesson->is_trial,
                    'position' => $lesson->position,
                    'status' => $lesson->status,
                    'video_id' => $lesson->video_id,
                    'document_id' => $lesson->document_id,
                ],
            ],
            logName: 'Thêm mới',
            description: 'Tạo mới bài giảng'
        );

        $studentIds = \Illuminate\Support\Facades\DB::table('orders')
            ->join('orders_detail', 'orders.id', '=', 'orders_detail.order_id')
            ->where('orders_detail.course_id', $courseId)
            ->where('orders.status', 'finished')
            ->distinct()
            ->pluck('orders.student_id');

        if ($studentIds->isNotEmpty()) {
            Student::whereIn('id', $studentIds)->chunk(100, function ($students) use ($lesson) {
                foreach ($students as $student) {
                    $student->notify(new StudentNotification([
                        'type' => 'student.lesson.new',
                        'title' => 'Bài học mới',
                        'title_translations' => [
                            'vi' => 'Bài học mới',
                            'en' => 'New lesson',
                            'ko' => '새 레슨',
                            'ja' => '新しいレッスン',
                            'zh' => '新课程内容',
                        ],
                        'message' => localizedModelField($lesson, 'name', 'vi') . ' vừa được thêm vào',
                        'message_translations' => [
                            'vi' => localizedModelField($lesson, 'name', 'vi') . ' vừa được thêm vào',
                            'en' => localizedModelField($lesson, 'name', 'en') . ' has just been added',
                            'ko' => localizedModelField($lesson, 'name', 'ko') . ' 레슨이 새로 추가되었습니다',
                            'ja' => localizedModelField($lesson, 'name', 'ja') . ' が新しく追加されました',
                            'zh' => localizedModelField($lesson, 'name', 'zh') . ' 已新增',
                        ],
                        'url' => route('lessons.home', ['locale' => app()->getLocale(), 'slug' => $lesson->slug]),
                        'severity' => 'info',
                        'icon' => 'fas fa-play-circle',
                        'entity_type' => 'lesson',
                        'entity_id' => $lesson->id,
                        'meta' => [
                            'course_id' => $lesson->course_id,
                            'lesson_id' => $lesson->id,
                        ],
                    ]));
                }
            });
        }
        $this->updateDurations($courseId);
        return redirect()->route('lessons.index', $courseId)->with('msg', __('lessons::messages.create.success'));
    }

    public function edit(Request $request, $lessonId)
    {
        $pageTitle = 'Cập nhật bài học';
        // $position = $this->lessonRepository->getPosition($courseId);
        $lesson = $this->lessonRepository->find($lessonId);
        $lessons = $this->lessonRepository->getAllLessions($lesson->course_id);
        $lesson->video = $lesson->video?->url;
        $lesson->document = $lesson->document?->url;
        if (!$lesson) {
            return abort(404);
        }
        $courseId = $lesson->course_id;
        return view('lessons::edit', compact('pageTitle', 'lessons', 'lesson', 'courseId'));
    }

    public function update(LessonRequest $request, $lessonId)
    {
        $lessonOld = $this->lessonRepository->find($lessonId);

        $name = $request->name;
        $name_en = $request->name_en;
        $name_ko = $request->name_ko;
        $name_ja = $request->name_ja;
        $name_zh = $request->name_zh;
        $slug = $request->slug;
        $slug_en = $request->slug_en;
        $slug_ko = $request->slug_ko;
        $slug_ja = $request->slug_ja;
        $slug_zh = $request->slug_zh;
        $videoUrl = trim((string)$request->video);
        $documentUrl = trim((string)$request->document);

        $parent_id = (int)$request->parent_id === 0 ? null : (int)$request->parent_id;
        $is_trial = (int)$request->is_trial;
        $position = (int)$request->position;
        $description = $request->description;
        $description_en = $request->description_en;
        $description_ko = $request->description_ko;
        $description_ja = $request->description_ja;
        $description_zh = $request->description_zh;
        $status = $request->status ?? 0;

        // Mặc định giữ nguyên ID cũ (đừng reset về null)
        $document_id = $lessonOld?->document_id;
        $video_id = $lessonOld?->video_id;
        $durations = $lessonOld?->durations ?? 0;

        // --- DOCUMENT: chỉ tạo mới khi có url mới ---
        if ($documentUrl !== '') {
            $documentInfo = getFileInfo($documentUrl);

            $document = $this->documentRepository->createDocument(
                [
                    'name' => $documentInfo['name'] ?? $name,
                    'url' => $documentUrl,
                    'size' => $documentInfo['size'] ?? 0
                ],
                $documentUrl
            );

            $document_id = $document ? $document->id : $document_id;
        }

        // --- VIDEO: chỉ tạo mới khi có url mới ---
        if ($videoUrl !== '') {

            // normalize: thiếu https
            if (!preg_match('~^https?://~i', $videoUrl)) {
                $videoUrl = 'https://' . ltrim($videoUrl, '/');
            }

            // nếu video không đổi thì khỏi tạo record mới
            $oldVideoUrl = $lessonOld?->video?->url ? trim((string)$lessonOld->video->url) : null;
            if ($oldVideoUrl && $oldVideoUrl === $videoUrl) {
                // giữ nguyên $video_id và $durations
            } else {
                $host = strtolower((string) parse_url($videoUrl, PHP_URL_HOST));
                $isExternal = $host && (
                    str_contains($host, 'youtube.com') ||
                    str_contains($host, 'youtu.be') ||
                    str_contains($host, 'vimeo.com')
                );

                if ($isExternal) {
                    $video = $this->videoRepository->createVideo(
                        [
                            'url' => $videoUrl,
                            'name' => $name,
                            'size' => 0,
                        ],
                        $videoUrl
                    );

                    $video_id = $video ? $video->id : $video_id;
                    $durations = externalVideoDuration($videoUrl);
                } else {
                    $videoInfo = getVideoInfo($videoUrl);

                    $video = $this->videoRepository->createVideo(
                        [
                            'url' => $videoUrl,
                            'name' => $videoInfo['filename'] ?? $name,
                            'size' => $videoInfo['playtime_seconds'] ?? 0
                        ],
                        $videoUrl
                    );

                    $video_id = $video ? $video->id : $video_id;
                    $durations = $videoInfo['playtime_seconds'] ?? 0;
                }
            }
        }


        $old = $lessonOld ? $lessonOld->toArray() : [];

        $this->lessonRepository->update($lessonId, [
            'name' => $name,
            'name_en' => $name_en,
            'name_ko' => $name_ko,
            'name_ja' => $name_ja,
            'name_zh' => $name_zh,
            'slug' => $slug,
            'slug_en' => $slug_en,
            'slug_ko' => $slug_ko,
            'slug_ja' => $slug_ja,
            'slug_zh' => $slug_zh,
            'video_id' => $video_id,
            'document_id' => $document_id,
            'parent_id' => $parent_id,
            'is_trial' => $is_trial,
            'position' => $position,
            'durations' => $durations,
            'description' => $description,
            'description_en' => $description_en,
            'description_ko' => $description_ko,
            'description_ja' => $description_ja,
            'description_zh' => $description_zh,
            'status' => $status,
        ]);

        $lessonFresh = $this->lessonRepository->find($lessonId);
        $new = $lessonFresh ? $lessonFresh->toArray() : [];

        activity_log(
            action: 'update',
            subject: $lessonFresh ?? $lessonOld,
            properties: [
                'course_id' => $lessonFresh?->course_id ?? $lessonOld?->course_id,
                'old' => $old,
                'new' => $new,
            ],
            logName: 'Cập nhật',
            description: 'Cập nhật bài giảng'
        );

        $lesson = $lessonFresh ?? $lessonOld;
        if ($lesson) {
            $this->updateDurations($lesson->course_id);
        }

        return redirect()->route('lessons.edit', $lessonId)
            ->with('msg', __('lessons::messages.update.success'));
    }


    public function delete(Request $request, $lessonId)
    {
        $lesson = $this->lessonRepository->find($lessonId);
        if (!$lesson) abort(404);

        $snapshot = $lesson->toArray();
        $courseId = $lesson->course_id;

        $branchIds = $this->collectLessonBranchIds($lesson->id);
        Lesson::query()->whereIn('id', $branchIds)->delete();

        activity_log(
            action: 'soft_delete',
            subject: $lesson,
            properties: [
                'course_id' => $courseId,
                'data' => [
                    'id' => $snapshot['id'] ?? null,
                    'name' => $snapshot['name'] ?? null,
                    'slug' => $snapshot['slug'] ?? null,
                    'parent_id' => $snapshot['parent_id'] ?? null,
                    'branch_ids' => $branchIds,
                ],
            ],
            logName: 'Xóa mềm',
            description: 'Xóa mềm bài giảng'
        );
        $this->updateDurations($lesson->course_id);
        return redirect()->route('lessons.index', $lesson->course_id)->with('msg', 'Đã chuyển bài giảng vào thùng rác.');
    }

    public function restore($lessonId)
    {
        $lesson = Lesson::query()->onlyTrashed()->findOrFail($lessonId);
        $branchIds = $this->collectLessonBranchIds($lesson->id);

        Lesson::query()
            ->onlyTrashed()
            ->whereIn('id', $branchIds)
            ->restore();

        activity_log(
            action: 'restore',
            subject: $lesson,
            properties: [
                'course_id' => $lesson->course_id,
                'data' => [
                    'id' => $lesson->id,
                    'name' => $lesson->name,
                    'branch_ids' => $branchIds,
                ],
            ],
            logName: 'Khôi phục',
            description: 'Khôi phục bài giảng'
        );

        $this->updateDurations($lesson->course_id);

        return redirect()->route('lessons.trash', $lesson->course_id)->with('msg', 'Đã khôi phục bài giảng thành công.');
    }

    public function forceDelete($lessonId)
    {
        $lesson = Lesson::query()->onlyTrashed()->findOrFail($lessonId);
        $snapshot = $lesson->toArray();
        $branchIds = $this->collectLessonBranchIds($lesson->id);

        Lesson::query()
            ->onlyTrashed()
            ->whereIn('id', $branchIds)
            ->forceDelete();

        activity_log(
            action: 'force_delete',
            subject: $lesson,
            properties: [
                'course_id' => $snapshot['course_id'] ?? null,
                'data' => [
                    'id' => $snapshot['id'] ?? null,
                    'name' => $snapshot['name'] ?? null,
                    'slug' => $snapshot['slug'] ?? null,
                    'branch_ids' => $branchIds,
                ],
            ],
            logName: 'Xóa vĩnh viễn',
            description: 'Xóa vĩnh viễn bài giảng'
        );

        $this->updateDurations($snapshot['course_id'] ?? null);

        return redirect()->route('lessons.trash', ['courseId' => $snapshot['course_id']])->with('msg', 'Đã xóa vĩnh viễn bài giảng thành công.');
    }

    private function updateDurations($courseId)
    {
        if (!$courseId) {
            return;
        }

        $lessons = $this->lessonRepository->getAllLessions($courseId);

        $durations = $lessons->reduce(function ($prev, $item) {
            return $prev + $item->durations;
        }, 0);

        $this->courseRepository->updateCourse($courseId, ['durations' => $durations]);
    }

    public function logs(Request $request, $lessonId)
    {
        $lesson = $this->lessonRepository->find($lessonId);
        if (!$lesson) abort(404);

        $pageTitle = "Lịch sử bài giảng: {$lesson->name}";

        $query = ActiveLog::query()
            ->withoutGlobalScopes()
            ->where('subject_type', Lesson::class)
            ->where('subject_id', $lesson->id);

        // filter giống bạn đang làm
        if ($request->filled('action')) $query->where('action', $request->action);
        if ($request->filled('from')) $query->whereDate('created_at', '>=', $request->from);
        if ($request->filled('to')) $query->whereDate('created_at', '<=', $request->to);
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('description', 'like', "%{$q}%")
                    ->orWhere('log_name', 'like', "%{$q}%");
            });
        }

        $logs = $query->latest()->paginate(config('paginate.log_limit'));
        $logs->withQueryString();

        return view('courses::logs', compact('pageTitle', 'lesson', 'logs'));
    }

    private function collectLessonBranchIds(int $lessonId): array
    {
        $ids = [$lessonId];

        $childIds = Lesson::query()
            ->withTrashed()
            ->where('parent_id', $lessonId)
            ->pluck('id');

        foreach ($childIds as $childId) {
            $ids = array_merge($ids, $this->collectLessonBranchIds((int) $childId));
        }

        return array_values(array_unique($ids));
    }

    private function flattenTrashedLessons(Collection $lessons, ?int $parentId = null, string $prefix = '', array &$rows = []): array
    {
        $items = $lessons
            ->where('parent_id', $parentId)
            ->sortBy('position');

        foreach ($items as $lesson) {
            $rows[] = [
                'id' => $lesson->id,
                'name' => $prefix . $lesson->name,
                'is_trial' => $lesson->parent_id ? ((int) $lesson->is_trial === 1 ? 'Có' : 'Không') : '',
                'document' => $lesson->document_id ? 'Có' : 'Không',
                'view' => $lesson->parent_id ? $lesson->view : '',
                'durations' => $lesson->parent_id ? getTime($lesson->durations) : '',
                'deleted_at' => optional($lesson->deleted_at)?->format('d/m/Y H:i:s'),
            ];

            $this->flattenTrashedLessons($lessons, $lesson->id, $prefix . '|--', $rows);
        }

        return $rows;
    }
}
