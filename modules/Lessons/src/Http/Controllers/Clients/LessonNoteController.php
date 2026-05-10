<?php

namespace Modules\Lessons\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Lessons\src\Repositories\LessonNotesRepositoryInterface;

class LessonNoteController extends Controller
{
    protected $lessonNotesRepository;

    public function __construct(LessonNotesRepositoryInterface $lessonNotesRepository)
    {
        $this->lessonNotesRepository = $lessonNotesRepository;
    }

    public function index($locale, $lessonId)
    {
        $studentId = auth('students')->id();
        $notes = $this->lessonNotesRepository->getNotesByLesson($lessonId, $studentId);

        return response()->json([
            'success' => true,
            'data' => $notes->map(function ($note) {
                return [
                    'id' => $note->id,
                    'time_at' => $note->time_at,
                    'time_formatted' => $note->time_formatted,
                    'content' => $note->content,
                    'created_at' => $note->created_at->format('d/m/Y H:i'),
                ];
            }),
        ]);
    }

    public function store(Request $request, $locale, $lessonId)
    {
        $request->validate([
            'content' => 'required|string',
            'time_at' => 'required|integer',
        ]);

        $studentId = auth('students')->id();

        $note = $this->lessonNotesRepository->create([
            'student_id' => $studentId,
            'lesson_id' => $lessonId,
            'time_at' => $request->time_at,
            'content' => $request->content,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('lessons::clients/common.note_saved') ?? 'Ghi chú đã được lưu',
            'data' => [
                'id' => $note->id,
                'time_at' => $note->time_at,
                'time_formatted' => $note->time_formatted,
                'content' => $note->content,
                'created_at' => $note->created_at->format('d/m/Y H:i'),
            ],
        ]);
    }

    public function destroy($locale, $id)
    {
        $studentId = auth('students')->id();
        $note = $this->lessonNotesRepository->find($id);

        if (!$note || $note->student_id !== $studentId) {
            return response()->json([
                'success' => false,
                'message' => __('lessons::clients/common.note_not_found') ?? 'Không tìm thấy ghi chú',
            ], 404);
        }

        $this->lessonNotesRepository->delete($id);

        return response()->json([
            'success' => true,
            'message' => __('lessons::clients/common.note_deleted') ?? 'Ghi chú đã được xóa',
        ]);
    }
}
