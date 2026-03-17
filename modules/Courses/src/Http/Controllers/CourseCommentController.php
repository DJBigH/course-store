<?php

namespace Modules\Courses\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Courses\src\Models\CourseComment;
use Modules\Courses\src\Models\Courses;

class CourseCommentController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Quan ly binh luan khoa hoc';

        $query = CourseComment::query()
            ->with(['course', 'student', 'admin', 'parent'])
            ->latest();

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->integer('course_id'));
        }

        if ($request->filled('visibility')) {
            $query->where('is_visible', $request->visibility === 'visible');
        }

        if ($request->filled('role')) {
            if ($request->role === 'student') {
                $query->whereNotNull('student_id');
            }

            if ($request->role === 'admin') {
                $query->whereNotNull('user_id');
            }
        }

        if ($request->boolean('flagged')) {
            $query->where('is_flagged', true);
        }

        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where('content', 'like', '%' . $keyword . '%');
        }

        $comments = $query->paginate(20)->withQueryString();
        $courses = Courses::withoutGlobalScopes()->orderBy('name')->get(['id', 'name']);

        return view('courses::comments', compact('pageTitle', 'comments', 'courses'));
    }

    public function toggleVisibility($commentId)
    {
        $comment = CourseComment::findOrFail($commentId);

        $comment->update([
            'is_visible' => !$comment->is_visible,
        ]);

        return back()->with('msg', 'Da cap nhat trang thai hien thi binh luan.');
    }
}
