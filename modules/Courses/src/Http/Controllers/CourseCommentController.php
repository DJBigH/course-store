<?php

namespace Modules\Courses\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Modules\Courses\src\Models\CourseComment;
use Modules\Courses\src\Models\Courses;

class CourseCommentController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Quản lý bình luận khóa học';

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
            $keyword = trim((string) $request->q);
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

        return back()->with('msg', 'Đã cập nhật trạng thái hiển thị bình luận.');
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('bulk_action');
        $selectedIds = collect(explode(',', (string) $request->input('selected_ids', '')))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            throw ValidationException::withMessages([
                'bulk_action' => 'Vui lòng chọn ít nhất một bình luận.',
            ]);
        }

        $comments = CourseComment::query()->whereIn('id', $selectedIds)->get();

        if ($comments->isEmpty()) {
            return back()->with('msg_danger', 'Không tìm thấy bình luận để xử lý.');
        }

        if (in_array($action, ['show', 'hide', 'delete'], true)) {
            return $this->handleSelectedCommentAction($action, $selectedIds, $comments);
        }

        if (in_array($action, ['show_student', 'hide_student', 'delete_student'], true)) {
            return $this->handleRoleCommentAction($action, $selectedIds, 'student');
        }

        if (in_array($action, ['show_admin', 'hide_admin', 'delete_admin'], true)) {
            return $this->handleRoleCommentAction($action, $selectedIds, 'admin');
        }

        return back()->with('msg_danger', 'Thao tác hàng loạt không hợp lệ.');
    }

    private function handleSelectedCommentAction(string $action, Collection $selectedIds, Collection $comments)
    {
        if ($action === 'show') {
            CourseComment::query()->whereIn('id', $selectedIds)->update(['is_visible' => true]);

            return back()->with('msg', 'Đã hiển thị ' . $comments->count() . ' bình luận đã chọn.');
        }

        if ($action === 'hide') {
            CourseComment::query()->whereIn('id', $selectedIds)->update(['is_visible' => false]);

            return back()->with('msg', 'Đã ẩn ' . $comments->count() . ' bình luận đã chọn.');
        }

        $idsToDelete = $this->collectCommentTreeIds($selectedIds);
        CourseComment::query()->whereIn('id', $idsToDelete)->delete();

        return back()->with('msg', 'Đã xóa ' . $idsToDelete->count() . ' bình luận trong vùng chọn.');
    }

    private function handleRoleCommentAction(string $action, Collection $selectedIds, string $role)
    {
        $roleComments = CourseComment::query()
            ->whereIn('id', $selectedIds)
            ->when($role === 'student', fn($query) => $query->whereNotNull('student_id'))
            ->when($role === 'admin', fn($query) => $query->whereNotNull('user_id'))
            ->get();

        if ($roleComments->isEmpty()) {
            $roleText = $role === 'student' ? 'học viên' : 'admin';

            return back()->with('msg_danger', 'Không có bình luận ' . $roleText . ' nào trong vùng chọn.');
        }

        $roleIds = $roleComments->pluck('id')->map(fn($id) => (int) $id)->values();
        $roleText = $role === 'student' ? 'học viên' : 'admin';

        if (str_starts_with($action, 'show_')) {
            CourseComment::query()->whereIn('id', $roleIds)->update(['is_visible' => true]);

            return back()->with('msg', 'Đã hiển thị ' . $roleComments->count() . ' bình luận của ' . $roleText . '.');
        }

        if (str_starts_with($action, 'hide_')) {
            CourseComment::query()->whereIn('id', $roleIds)->update(['is_visible' => false]);

            return back()->with('msg', 'Đã ẩn ' . $roleComments->count() . ' bình luận của ' . $roleText . '.');
        }

        $idsToDelete = $this->collectCommentTreeIds($roleIds);
        CourseComment::query()->whereIn('id', $idsToDelete)->delete();

        return back()->with('msg', 'Đã xóa ' . $idsToDelete->count() . ' bình luận thuộc nhóm ' . $roleText . '.');
    }

    private function collectCommentTreeIds(Collection $selectedIds): Collection
    {
        $allIds = $selectedIds
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $queue = $allIds;

        while ($queue->isNotEmpty()) {
            $childIds = CourseComment::query()
                ->whereIn('parent_id', $queue->all())
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->filter()
                ->values();

            $newIds = $childIds->diff($allIds)->values();

            if ($newIds->isEmpty()) {
                break;
            }

            $allIds = $allIds->merge($newIds)->unique()->values();
            $queue = $newIds;
        }

        return $allIds;
    }
}
