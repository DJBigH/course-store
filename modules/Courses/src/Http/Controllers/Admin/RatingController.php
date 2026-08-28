<?php

namespace Modules\Courses\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Courses\src\Models\CourseRating;
use Modules\Students\src\Models\TeacherRating;
use Yajra\DataTables\Facades\DataTables;

class RatingController extends Controller
{
    public function index()
    {
        $pageTitle = 'Quản lý đánh giá';
        return view('courses::admin.ratings.index', compact('pageTitle'));
    }

    public function courseData()
    {
        $ratings = CourseRating::query()->with(['course', 'student'])->latest();

        return DataTables::of($ratings)
            ->addColumn('course_name', function ($rating) {
                return $rating->course ? e($rating->course->name) : '<span class="text-muted">N/A</span>';
            })
            ->addColumn('student_name', function ($rating) {
                return $rating->student ? e($rating->student->name) : '<span class="text-muted">N/A</span>';
            })
            ->editColumn('rating', function ($rating) {
                $html = '<span class="text-warning fw-bold"><i class="fa-solid fa-star me-1"></i>' . $rating->rating . ' / 5</span>';
                if ($rating->status == 0) {
                    $html .= ' <span class="badge bg-secondary ms-2">Đang ẩn</span>';
                }
                return $html;
            })
            ->editColumn('created_at', function ($rating) {
                return $rating->created_at->format('d/m/Y H:i');
            })
            ->addColumn('action', function ($rating) {
                $html = '<div class="d-flex gap-1">';
                
                if (auth()->user()?->hasPermission('ratings.moderate')) {
                    $toggleLabel = $rating->status == 1 ? '<i class="fa-solid fa-eye-slash"></i>' : '<i class="fa-solid fa-eye"></i>';
                    $toggleTitle = $rating->status == 1 ? 'Ẩn đánh giá' : 'Hiện đánh giá';
                    $toggleClass = $rating->status == 1 ? 'btn-outline-secondary' : 'btn-outline-success';
                    
                    $html .= '<button type="button" class="btn ' . $toggleClass . ' btn-sm toggle-visibility" data-id="' . $rating->id . '" data-type="course" title="' . $toggleTitle . '">
                                ' . $toggleLabel . '
                            </button>';
                }

                if (auth()->user()?->hasPermission('ratings.delete')) {
                    $html .= '<button type="button" class="btn btn-outline-danger btn-sm delete-rating" data-id="' . $rating->id . '" data-type="course" title="Xóa đánh giá">
                                <i class="fa-solid fa-trash"></i>
                            </button>';
                }

                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['rating', 'action', 'course_name', 'student_name'])
            ->make(true);
    }

    public function teacherData()
    {
        $ratings = TeacherRating::query()->with(['teacher', 'student'])->latest();

        return DataTables::of($ratings)
            ->addColumn('teacher_name', function ($rating) {
                return $rating->teacher ? e($rating->teacher->name) : '<span class="text-muted">N/A</span>';
            })
            ->addColumn('student_name', function ($rating) {
                return $rating->student ? e($rating->student->name) : '<span class="text-muted">N/A</span>';
            })
            ->editColumn('rating', function ($rating) {
                $html = '<span class="text-warning fw-bold"><i class="fa-solid fa-star me-1"></i>' . $rating->rating . ' / 5</span>';
                if ($rating->status == 0) {
                    $html .= ' <span class="badge bg-secondary ms-2">Đang ẩn</span>';
                }
                return $html;
            })
            ->editColumn('created_at', function ($rating) {
                return $rating->created_at->format('d/m/Y H:i');
            })
            ->addColumn('action', function ($rating) {
                $html = '<div class="d-flex gap-1">';

                if (auth()->user()?->hasPermission('ratings.moderate')) {
                    $toggleLabel = $rating->status == 1 ? '<i class="fa-solid fa-eye-slash"></i>' : '<i class="fa-solid fa-eye"></i>';
                    $toggleTitle = $rating->status == 1 ? 'Ẩn đánh giá' : 'Hiện đánh giá';
                    $toggleClass = $rating->status == 1 ? 'btn-outline-secondary' : 'btn-outline-success';

                    $html .= '<button type="button" class="btn ' . $toggleClass . ' btn-sm toggle-visibility" data-id="' . $rating->id . '" data-type="teacher" title="' . $toggleTitle . '">
                                ' . $toggleLabel . '
                            </button>';
                }

                if (auth()->user()?->hasPermission('ratings.delete')) {
                    $html .= '<button type="button" class="btn btn-outline-danger btn-sm delete-rating" data-id="' . $rating->id . '" data-type="teacher" title="Xóa đánh giá">
                                <i class="fa-solid fa-trash"></i>
                            </button>';
                }

                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['rating', 'action', 'teacher_name', 'student_name'])
            ->make(true);
    }

    public function toggleVisibility(Request $request)
    {
        $id = $request->input('id');
        $type = $request->input('type');

        if ($type === 'course') {
            $rating = CourseRating::query()->findOrFail($id);
        } else {
            $rating = TeacherRating::query()->findOrFail($id);
        }

        $oldStatus = $rating->status;
        $rating->status = $rating->status == 1 ? 0 : 1;
        $rating->save();

        activity_log(
            action: 'toggle_visibility',
            subject: $rating,
            properties: [
                'type' => $type,
                'old_status' => $oldStatus,
                'new_status' => $rating->status,
                'rating_data' => $rating->toArray(),
            ],
            logName: 'admin_rating_management',
            description: ($rating->status == 1 ? 'Hiện' : 'Ẩn') . " đánh giá " . ($type === 'course' ? 'khóa học' : 'giảng viên') . " (ID: {$rating->id})"
        );

        return response()->json(['success' => true]);
    }

    public function delete(Request $request)
    {
        $id = $request->input('id');
        $type = $request->input('type');

        if ($type === 'course') {
            $rating = CourseRating::query()->findOrFail($id);
        } else {
            $rating = TeacherRating::query()->findOrFail($id);
        }

        $snapshot = $rating->toArray();
        $rating->delete();

        activity_log(
            action: 'delete',
            subject: null,
            properties: [
                'type' => $type,
                'data' => $snapshot,
            ],
            logName: 'admin_rating_management',
            description: "Xóa đánh giá " . ($type === 'course' ? 'khóa học' : 'giảng viên') . " (ID: {$id})"
        );

        return response()->json(['success' => true]);
    }
}
