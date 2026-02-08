<?php

namespace Modules\Contacts\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Contacts\src\Http\Requests\ContactRequest;
use Modules\Contacts\src\Repositories\ContactsRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;

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

    public function data()
    {
        $contacts = $this->contactrepository->getContacts();

        return datatables()->of($contacts)
            ->addColumn(
                'logs',
                fn($c) =>
                '<a href="' . route('contacts.logs', $c->id) . '" class="btn btn-sm btn-secondary">
        <i class="fas fa-clock"></i>
     </a>'
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
                fn($c) =>
                '<a href="' . route('contacts.show', $c->id) . '" class="btn btn-sm btn-primary">Xem</a>'
            )
            ->addColumn(
                'delete',
                fn($c) =>
                '<a href="' . route('contacts.delete', $c->id) . '" class="btn btn-danger delete-action">Xóa</a>'
            )
            ->rawColumns(['status', 'view', 'delete', 'logs'])
            ->make(true);
    }

    public function show($id)
    {
        $pageTitle = 'Chi tiết liên hệ thông tin';
        $pageName = 'Liên hệ';

        $contact = $this->contactrepository->find($id);
        if (!$contact) abort(404);

        // ✅ log view (tránh spam: chỉ log khi không có ?page=)
        // if (!request()->has('page')) {
        //     activity_log(
        //         action: 'view',
        //         subject: $contact,
        //         properties: [
        //             'contact' => [
        //                 'name' => $contact->name,
        //                 'email' => $contact->email,
        //                 'phone' => $contact->phone,
        //             ]
        //         ],
        //         logName: 'Xem chi tiết',
        //         description: 'Xem chi tiết liên hệ'
        //     );
        // }

        return view('contacts::show', compact('contact', 'pageName', 'pageTitle'));
    }

    public function accept($id)
    {
        $contact = $this->contactrepository->find($id);
        if (!$contact) abort(404);

        $old = is_object($contact) && method_exists($contact, 'toArray') ? $contact->toArray() : (array)$contact;

        if ((int)$contact->status === 0) {
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
                logName: 'contact',
                description: 'Tiếp nhận liên hệ'
            );
        } else {
            // Optional: log thao tác "tiếp nhận lại"
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
        if (!$contact) abort(404);

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
        if (empty($contacts)) abort(404);

        $pageTitle = "Lịch sử: {$contacts->name}";

        $query = ActiveLog::query()
            ->where('subject_type', get_class($contacts))
            ->where('subject_id', $contacts->id)->withoutGlobalScopes();

        // 🔹 Filter theo action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // 🔹 Filter theo khoảng thời gian
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // 🔹 Filter theo keyword (description hoặc log_name)
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
