<?php

namespace Modules\Contacts\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\ActiveLogs\src\Models\ActiveLog;
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
        $pageTitle = 'Liên hệ thông tin';
        $pageName = 'Liên hệ';

        return view('contacts::index', compact('pageName', 'pageTitle'));
    }

    public function data(Request $request)
    {
        $contacts = $this->contactrepository->getContacts();
        $user = auth()->user();
        $canLogs = $user?->hasPermission('contacts.logs');
        $canView = $user?->hasPermission('contacts.view');
        $canDelete = $user?->hasPermission('contacts.delete');

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));

            $contacts->where(function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%')
                    ->orWhere('phone', 'like', '%' . $keyword . '%')
                    ->orWhere('message', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->filled('status_filter')) {
            $contacts->where('status', (int) $request->input('status_filter'));
        }

        if ($request->filled('from_date')) {
            $contacts->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $contacts->whereDate('created_at', '<=', $request->input('to_date'));
        }

        return datatables()->of($contacts)
            ->addColumn(
                'select',
                fn($c) => '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $c->id . '"></div>'
            )
            ->addColumn(
                'logs',
                fn($c) => $canLogs ? '<a href="' . route('contacts.logs', $c->id) . '" class="btn btn-sm btn-secondary"><i class="fas fa-clock"></i></a>' : '<span class="text-muted small">-</span>'
            )
            ->addColumn('name', fn($c) => $c->name)
            ->addColumn('phone', fn($c) => $c->phone)
            ->addColumn('email', fn($c) => $c->email)
            ->addColumn('status', function ($c) {
                return $c->status == 1
                    ? '<span class="badge bg-success">ÄÃ£ tiáº¿p nháº­n</span>'
                    : '<span class="badge bg-warning text-dark">Chá» tiáº¿p xá»­</span>';
            })
            ->addColumn('created_at', function ($c) {
                return Carbon::parse($c->created_at)->format('d/m/Y H:i:s');
            })
            ->addColumn(
                'view',
                fn($c) => $canView ? '<a href="' . route('contacts.show', $c->id) . '" class="btn btn-sm btn-primary">Xem</a>' : '<span class="text-muted small">Không có quyền</span>'
            )
            ->addColumn(
                'delete',
                fn($c) => $canDelete ? '<a href="' . route('contacts.delete', $c->id) . '" class="btn btn-outline-danger delete-action">Xóa</a>' : '<span class="text-muted small">Không có quyền</span>'
            )
            ->rawColumns(['select', 'status', 'view', 'delete', 'logs'])
            ->make(true);

        return datatables()->of($contacts)
            ->addColumn(
                'select',
                fn($c) => '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $c->id . '"></div>'
            )
            ->addColumn(
                'logs',
                fn($c) => '<a href="' . route('contacts.logs', $c->id) . '" class="btn btn-sm btn-secondary"><i class="fas fa-clock"></i></a>'
            )
            ->addColumn('name', fn($c) => $c->name)
            ->addColumn('phone', fn($c) => $c->phone)
            ->addColumn('email', fn($c) => $c->email)
            ->addColumn('status', function ($c) {
                return $c->status == 1
                    ? '<span class="badge bg-success">Đã tiếp nhận</span>'
                    : '<span class="badge bg-warning text-dark">Chờ tiếp xử</span>';
            })
            ->addColumn('created_at', function ($c) {
                return Carbon::parse($c->created_at)->format('d/m/Y H:i:s');
            })
            ->addColumn(
                'view',
                fn($c) => '<a href="' . route('contacts.show', $c->id) . '" class="btn btn-sm btn-primary">Xem</a>'
            )
            ->addColumn(
                'delete',
                fn($c) => '<a href="' . route('contacts.delete', $c->id) . '" class="btn btn-outline-danger delete-action">Xóa</a>'
            )
            ->rawColumns(['select', 'status', 'view', 'delete', 'logs'])
            ->make(true);
    }

    public function show($id)
    {
        $pageTitle = 'Chi tiết liên hệ thông tin';
        $pageName = 'Liên hệ';

        $contact = $this->contactrepository->find($id);

        if (!$contact) {
            abort(404);
        }

        return view('contacts::show', compact('contact', 'pageName', 'pageTitle'));
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
                'bulk_action' => 'Vui lòng chọn ít nhất một liên hệ.',
            ]);
        }

        $contacts = $selectedIds
            ->map(fn($id) => $this->contactrepository->find($id))
            ->filter();

        if ($contacts->isEmpty()) {
            return back()->with('msg_danger', 'Không tìm thấy liên hệ để xử lý.');
        }

        if ($action === 'accept') {
            foreach ($contacts as $contact) {
                if ((int) $contact->status === 0) {
                    $this->contactrepository->update($contact->id, ['status' => 1]);

                    activity_log(
                        action: 'accept',
                        subject: $contact,
                        properties: [
                            'old' => ['status' => 0],
                            'new' => ['status' => 1],
                        ],
                        logName: 'Tiếp nhận hàng loạt',
                        description: 'Tiếp nhận liên hệ'
                    );
                }
            }

            return back()->with('msg', 'Đã tiếp nhận ' . $contacts->count() . ' liên hệ.');
        }

        if ($action === 'delete') {
            foreach ($contacts as $contact) {
                $snapshot = $contact->toArray();
                $this->contactrepository->delete($contact->id);

                activity_log(
                    action: 'delete',
                    subject: $contact,
                    properties: [
                        'data' => [
                            'name' => $snapshot['name'] ?? null,
                            'email' => $snapshot['email'] ?? null,
                            'phone' => $snapshot['phone'] ?? null,
                            'status' => $snapshot['status'] ?? null,
                            'created_at' => $snapshot['created_at'] ?? null,
                        ]
                    ],
                    logName: 'Xóa hàng loạt',
                    description: 'Xóa liên hệ'
                );
            }

            return back()->with('msg', 'Đã xóa ' . $contacts->count() . ' liên hệ.');
        }

        return back()->with('msg_danger', 'Thao tác hàng loạt không hợp lệ.');
    }

    public function accept($id)
    {
        $contact = $this->contactrepository->find($id);

        if (!$contact) {
            abort(404);
        }

        $old = is_object($contact) && method_exists($contact, 'toArray') ? $contact->toArray() : (array) $contact;

        if ((int) $contact->status === 0) {
            $this->contactrepository->update($id, ['status' => 1]);

            $fresh = $this->contactrepository->find($id);
            $new = $fresh ? $fresh->toArray() : ['status' => 1];

            activity_log(
                action: 'accept',
                subject: $fresh ?? $contact,
                properties: [
                    'old' => ['status' => $old['status'] ?? 0],
                    'new' => ['status' => $new['status'] ?? 1],
                ],
                logName: 'Liên hệ',
                description: 'Tiếp nhận liên hệ'
            );
        } else {
            activity_log(
                action: 'accept',
                subject: $contact,
                properties: ['status' => $contact->status],
                logName: 'Chấp nhận',
                description: 'Liên hệ đã được tiếp nhận trước đó'
            );
        }

        return back()->with('msg', 'Tiếp nhận liên hệ thành công');
    }

    public function delete($id)
    {
        $contact = $this->contactrepository->find($id);

        if (!$contact) {
            abort(404);
        }

        $snapshot = $contact->toArray();
        $status = $this->contactrepository->delete($id);

        if ($status) {
            activity_log(
                action: 'delete',
                subject: $contact,
                properties: [
                    'data' => [
                        'name' => $snapshot['name'] ?? null,
                        'email' => $snapshot['email'] ?? null,
                        'phone' => $snapshot['phone'] ?? null,
                        'status' => $snapshot['status'] ?? null,
                        'created_at' => $snapshot['created_at'] ?? null,
                    ]
                ],
                logName: 'Xóa',
                description: 'Xóa liên hệ'
            );

            return redirect()->route('contacts.index')->with('msg', 'Xóa liên hệ thành công');
        }

        return back()->with('msg_danger', 'Xóa thất bại');
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
}
