<?php

namespace Modules\Contacts\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Contacts\src\Models\Contacts;
use Modules\Contacts\src\Repositories\ContactsRepositoryInterface;

class ContactController extends Controller
{
    protected $contactrepository;

    public function __construct(ContactsRepositoryInterface $contactsRepository)
    {
        $this->contactrepository = $contactsRepository;
    }

    public function index()
    {
        $pageTitle = 'Liên hệ';
        $pageName = 'Liên hệ';

        return view('contacts::index', compact('pageName', 'pageTitle'))
            ->with('mode', 'contact');
    }

    public function supportIndex()
    {
        $pageTitle = 'Góp ý / Báo cáo';
        $pageName = 'Góp ý / Báo cáo';

        return view('contacts::index', compact('pageName', 'pageTitle'))
            ->with('mode', 'support');
    }

    public function trash()
    {
        $pageTitle = 'Thùng rác hỗ trợ';
        $pageName = 'Hỗ trợ';

        return view('contacts::trash', compact('pageName', 'pageTitle'));
    }

    public function data(Request $request)
    {
        $contacts = $this->contactrepository->getContacts()
            ->where('submission_type', Contacts::TYPE_CONTACT);
        return $this->renderDatatable($contacts, $request);
    }

    public function supportData(Request $request)
    {
        $contacts = $this->contactrepository->getContacts()
            ->whereIn('submission_type', [Contacts::TYPE_FEEDBACK, Contacts::TYPE_REPORT])
            ->where('source', 'teacher_portal');
        return $this->renderDatatable($contacts, $request);
    }

    protected function renderDatatable($contacts, Request $request)
    {
        $user = auth()->user();
        $canLogs = $user?->hasPermission('contacts.logs');
        $canView = $user?->hasPermission('contacts.view');
        $canDelete = $user?->canAnyPermission(['contacts.soft_delete', 'contacts.delete']);

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));

            $contacts->where(function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%')
                    ->orWhere('phone', 'like', '%' . $keyword . '%')
                    ->orWhere('subject', 'like', '%' . $keyword . '%')
                    ->orWhere('message', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->filled('submission_type')) {
            $contacts->where('submission_type', $request->input('submission_type'));
        }

        if ($request->filled('category')) {
            $contacts->where('category', $request->input('category'));
        }

        if ($request->filled('workflow_status')) {
            $contacts->where('workflow_status', $request->input('workflow_status'));
        }

        if ($request->filled('from_date')) {
            $contacts->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $contacts->whereDate('created_at', '<=', $request->input('to_date'));
        }

        return datatables()->of($contacts)
            ->addColumn('select', fn($c) => '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $c->id . '"></div>')
            ->addColumn('logs', fn($c) => $canLogs ? '<a href="' . route('contacts.logs', $c->id) . '" class="btn btn-sm btn-secondary"><i class="fas fa-clock"></i></a>' : '<span class="text-muted small">-</span>')
            ->addColumn('name', fn($c) => e($c->name))
            ->addColumn('submission_type', fn($c) => $this->renderTypeBadge($c))
            ->addColumn('category', fn($c) => e($this->categoryLabel($c->category)))
            ->addColumn('phone', fn($c) => e($c->phone))
            ->addColumn('email', fn($c) => e((string) $c->email))
            ->addColumn('status', fn($c) => $this->renderWorkflowBadge($c))
            ->addColumn('created_at', fn($c) => Carbon::parse($c->created_at)->format('d/m/Y H:i:s'))
            ->addColumn('view', function ($c) use ($canView) {
                if (!$canView) {
                    return '<span class="text-muted small">Không có quyền</span>';
                }

                $route = $c->submission_type === Contacts::TYPE_CONTACT ? 'contacts.show' : 'contacts.support-show';

                return '<a href="' . route($route, $c->id) . '" class="btn btn-sm btn-primary">Xem</a>';
            })
            ->addColumn('delete', fn($c) => $canDelete ? '<a href="' . route('contacts.delete', $c->id) . '" class="btn btn-outline-danger delete-action">Xóa</a>' : '<span class="text-muted small">Không có quyền</span>')
            ->rawColumns(['select', 'submission_type', 'status', 'view', 'delete', 'logs'])
            ->make(true);
    }

    public function trashData()
    {
        $canRestore = auth()->user()?->canAnyPermission(['contacts.soft_delete', 'contacts.delete']);
        $canForceDelete = auth()->user()?->hasPermission('contacts.force_delete');
        $contacts = Contacts::query()->onlyTrashed()->latest('deleted_at');

        return datatables()->of($contacts)
            ->addColumn('select', fn($c) => '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $c->id . '"></div>')
            ->addColumn('name', fn($c) => e($c->name))
            ->addColumn('email', fn($c) => e((string) $c->email))
            ->addColumn('phone', fn($c) => e($c->phone))
            ->addColumn('deleted_at', fn($c) => Carbon::parse($c->deleted_at)->format('d/m/Y H:i:s'))
            ->addColumn('restore', function ($c) use ($canRestore) {
                if (!$canRestore) {
                    return '<span class="text-muted small">Không có quyền</span>';
                }

                return '<form method="POST" action="' . route('contacts.restore', $c->id) . '" class="d-inline-block">'
                    . csrf_field()
                    . '<button type="submit" class="btn btn-success btn-sm">Khôi phục</button>'
                    . '</form>';
            })
            ->addColumn('force_delete', function ($c) use ($canForceDelete) {
                if (!$canForceDelete) {
                    return '<span class="text-muted small">Không có quyền</span>';
                }

                return '<form method="POST" action="' . route('contacts.force-delete', $c->id) . '" class="d-inline-block" onsubmit="return confirm(\'Xóa vĩnh viễn yêu cầu này?\');">'
                    . csrf_field()
                    . method_field('DELETE')
                    . '<button type="submit" class="btn btn-outline-danger btn-sm">Xóa vĩnh viễn</button>'
                    . '</form>';
            })
            ->rawColumns(['select', 'restore', 'force_delete'])
            ->toJson();
    }

    public function show($id)
    {
        $pageTitle = 'Chi tiết liên hệ';
        $pageName = 'Liên hệ';
        $contact = Contacts::query()->with(['student', 'teacher'])
            ->where('submission_type', Contacts::TYPE_CONTACT)
            ->find($id);

        if (!$contact) {
            abort(404);
        }

        return view('contacts::show', compact('contact', 'pageName', 'pageTitle'))
            ->with('mode', 'contact');
    }

    public function supportShow($id)
    {
        $pageTitle = 'Chi tiết góp ý / báo cáo';
        $pageName = 'Góp ý / Báo cáo';
        $contact = Contacts::query()->with(['student', 'teacher'])
            ->whereIn('submission_type', [Contacts::TYPE_FEEDBACK, Contacts::TYPE_REPORT])
            ->where('source', 'teacher_portal')
            ->find($id);

        if (!$contact) {
            abort(404);
        }

        return view('contacts::show', compact('contact', 'pageName', 'pageTitle'))
            ->with('mode', 'support');
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
                'bulk_action' => 'Vui lòng chọn ít nhất một yêu cầu.',
            ]);
        }

        $contacts = $selectedIds
            ->map(fn($id) => $this->contactrepository->find($id))
            ->filter();

        if ($contacts->isEmpty()) {
            return back()->with('msg_danger', 'Không tìm thấy yêu cầu để xử lý.');
        }

        if ($action === 'accept') {
            foreach ($contacts as $contact) {
                $this->contactrepository->update($contact->id, [
                    'status' => 1,
                    'workflow_status' => Contacts::STATUS_IN_PROGRESS,
                ]);

                activity_log(
                    action: 'accept',
                    subject: $contact,
                    properties: [
                        'old' => ['status' => $contact->status, 'workflow_status' => $contact->workflow_status],
                        'new' => ['status' => 1, 'workflow_status' => Contacts::STATUS_IN_PROGRESS],
                    ],
                    logName: 'Tiếp nhận hàng loạt',
                    description: 'Tiếp nhận yêu cầu'
                );
            }

            return back()->with('msg', 'Đã tiếp nhận ' . $contacts->count() . ' yêu cầu.');
        }

        if ($action === 'delete') {
            foreach ($contacts as $contact) {
                $snapshot = method_exists($contact, 'toArray') ? $contact->toArray() : (array) $contact;
                $this->contactrepository->delete($contact->id);

                activity_log(
                    action: 'delete',
                    subject: $contact,
                    properties: ['data' => $snapshot],
                    logName: 'Xóa hàng loạt',
                    description: 'Xóa yêu cầu/liên hệ'
                );
            }

            return back()->with('msg', 'Đã xóa ' . $contacts->count() . ' yêu cầu.');
        }

        return back()->with('msg_danger', 'Thao tác hàng loạt không hợp lệ.');
    }

    public function trashBulkAction(Request $request)
    {
        $action = $request->input('bulk_action');
        $selectedIds = collect(explode(',', (string) $request->input('selected_ids', '')))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            throw ValidationException::withMessages([
                'bulk_action' => 'Vui lòng chọn ít nhất một yêu cầu trong thùng rác.',
            ]);
        }

        $contacts = Contacts::query()->onlyTrashed()->whereIn('id', $selectedIds)->get();

        if ($contacts->isEmpty()) {
            return back()->with('msg_danger', 'Không tìm thấy yêu cầu hợp lệ trong thùng rác.');
        }

        if ($action === 'restore') {
            foreach ($contacts as $contact) {
                $contact->restore();

                activity_log(
                    action: 'restore',
                    subject: $contact->fresh(),
                    properties: ['restored_from_trash' => true],
                    logName: 'Khôi phục hàng loạt',
                    description: 'Khôi phục yêu cầu/liên hệ'
                );
            }

            return back()->with('msg', 'Đã khôi phục ' . $contacts->count() . ' yêu cầu.');
        }

        if ($action === 'force_delete') {
            foreach ($contacts as $contact) {
                $snapshot = method_exists($contact, 'toArray') ? $contact->toArray() : (array) $contact;
                $contact->forceDelete();

                activity_log(
                    action: 'force_delete',
                    subject: $contact,
                    properties: [
                        'data' => $snapshot,
                        'deleted_permanently' => true,
                    ],
                    logName: 'Xóa vĩnh viễn hàng loạt',
                    description: 'Xóa vĩnh viễn yêu cầu'
                );
            }

            return back()->with('msg', 'Đã xóa vĩnh viễn ' . $contacts->count() . ' yêu cầu.');
        }

        return back()->with('msg_danger', 'Thao tác trong thùng rác không hợp lệ.');
    }

    public function accept($id)
    {
        $contact = $this->contactrepository->find($id);

        if (!$contact) {
            abort(404);
        }

        $this->contactrepository->update($id, [
            'status' => 1,
            'workflow_status' => Contacts::STATUS_IN_PROGRESS,
        ]);

        $freshContact = $this->contactrepository->find($id);

        activity_log(
            action: 'accept',
            subject: $freshContact ?? $contact,
            properties: [
                'old' => ['status' => $contact->status, 'workflow_status' => $contact->workflow_status],
                'new' => ['status' => 1, 'workflow_status' => Contacts::STATUS_IN_PROGRESS],
            ],
            logName: 'Tiếp nhận',
            description: 'Tiếp nhận yêu cầu'
        );

        return back()->with('msg', 'Tiếp nhận yêu cầu thành công.');
    }

    public function updateStatus(Request $request, $id)
    {
        $contact = $this->contactrepository->find($id);

        if (!$contact) {
            abort(404);
        }

        $data = $request->validate([
            'workflow_status' => ['required', Rule::in(Contacts::workflowStatuses())],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['status'] = in_array($data['workflow_status'], [Contacts::STATUS_IN_PROGRESS, Contacts::STATUS_RESOLVED], true) ? 1 : 0;
        
        $oldSnapshot = $contact->toArray();
        $this->contactrepository->update($id, $data);
        $freshContact = $this->contactrepository->find($id);

        activity_log(
            action: 'update_status',
            subject: $freshContact ?? $contact,
            properties: [
                'old' => ['status' => $oldSnapshot['status'], 'workflow_status' => $oldSnapshot['workflow_status'], 'admin_note' => $oldSnapshot['admin_note']],
                'new' => ['status' => $data['status'], 'workflow_status' => $data['workflow_status'], 'admin_note' => $data['admin_note'] ?? null],
            ],
            logName: 'Cập nhật trạng thái',
            description: 'Cập nhật trạng thái xử lý yêu cầu'
        );

        return back()->with('msg', 'Đã cập nhật trạng thái xử lý.');
    }

    public function delete($id)
    {
        $contact = $this->contactrepository->find($id);

        if (!$contact) {
            abort(404);
        }

        $snapshot = method_exists($contact, 'toArray') ? $contact->toArray() : (array) $contact;
        $status = $this->contactrepository->delete($id);

        if ($status) {
            activity_log(
                action: 'delete',
                subject: $contact,
                properties: ['data' => $snapshot],
                logName: 'Xóa',
                description: 'Xóa yêu cầu/liên hệ'
            );

            return redirect()->route('contacts.index')->with('msg', 'Xóa yêu cầu thành công');
        }

        return back()->with('msg_danger', 'Xóa thất bại');
    }

    public function restore($id)
    {
        $contact = Contacts::query()->onlyTrashed()->find($id);

        if (!$contact) {
            abort(404);
        }

        $contact->restore();

        activity_log(
            action: 'restore',
            subject: $contact->fresh(),
            properties: ['restored_from_trash' => true],
            logName: 'Khôi phục',
            description: 'Khôi phục yêu cầu thành công.'
        );

        return back()->with('msg', 'Khôi phục yêu cầu thành công.');
    }

    public function forceDelete($id)
    {
        $contact = Contacts::query()->onlyTrashed()->find($id);

        if (!$contact) {
            abort(404);
        }

        $snapshot = method_exists($contact, 'toArray') ? $contact->toArray() : (array) $contact;
        $contact->forceDelete();

        activity_log(
            action: 'force_delete',
            subject: $contact,
            properties: [
                'data' => $snapshot,
                'deleted_permanently' => true,
            ],
            logName: 'Xóa vĩnh viễn',
            description: 'Xóa vĩnh viễn yêu cầu'
        );

        return back()->with('msg', 'Đã xóa vĩnh viễn yêu cầu.');
    }

    public function logs(Request $request, $id)
    {
        $contacts = $this->contactrepository->find($id);

        if (empty($contacts)) {
            abort(404);
        }

        $pageTitle = "Lịch sử: {$contacts->name}";

        $query = ActiveLog::query()
            ->where('subject_type', get_class($contacts))
            ->where('subject_id', $contacts->id)
            ->withoutGlobalScopes();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('description', 'like', "%{$q}%")
                    ->orWhere('log_name', 'like', "%{$q}%");
            });
        }

        $logs = $query
            ->latest()
            ->paginate(config('paginate.log_limit'))
            ->withQueryString();

        return view('contacts::logs', compact('pageTitle', 'contacts', 'logs'));
    }

    protected function renderTypeBadge(Contacts $contact): string
    {
        $map = [
            Contacts::TYPE_CONTACT => ['Liên hệ', 'bg-info-subtle text-info-emphasis'],
            Contacts::TYPE_FEEDBACK => ['Góp ý', 'bg-primary-subtle text-primary-emphasis'],
            Contacts::TYPE_REPORT => ['Báo cáo', 'bg-danger-subtle text-danger-emphasis'],
        ];

        [$label, $classes] = $map[$contact->submission_type] ?? ['Khác', 'bg-secondary-subtle text-secondary-emphasis'];

        return '<span class="badge rounded-pill ' . $classes . '">' . e($label) . '</span>';
    }

    protected function renderWorkflowBadge(Contacts $contact): string
    {
        $map = [
            Contacts::STATUS_NEW => ['Mới gửi', 'bg-warning text-dark'],
            Contacts::STATUS_IN_PROGRESS => ['Đang xử lý', 'bg-info text-dark'],
            Contacts::STATUS_NEED_INFO => ['Cần thêm thông tin', 'bg-secondary'],
            Contacts::STATUS_RESOLVED => ['Đã giải quyết', 'bg-success'],
            Contacts::STATUS_REJECTED => ['Đã từ chối', 'bg-danger'],
        ];

        [$label, $classes] = $map[$contact->workflow_status] ?? ['Mới gửi', 'bg-warning text-dark'];

        return '<span class="badge ' . $classes . '">' . e($label) . '</span>';
    }

    protected function categoryLabel(?string $category): string
    {
        return match ($category) {
            'general_contact' => 'Liên hệ chung',
            'feature_request' => 'Tính năng mới',
            'ui_ux' => 'UI/UX',
            'teacher_portal' => 'Teacher portal',
            'student_portal' => 'Student portal',
            'payment_package' => 'Thanh toán / gói',
            'system_bug' => 'Lỗi hệ thống',
            'course_lesson' => 'Khóa học / bài học',
            'comment_rating' => 'Bình luận / đánh giá',
            'content_violation' => 'Nội dung vi phạm',
            'account' => 'Tài khoản',
            default => 'Khác',
        };
    }
}
