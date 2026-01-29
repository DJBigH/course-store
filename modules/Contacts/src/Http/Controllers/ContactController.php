<?php

namespace Modules\Contacts\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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
            ->rawColumns(['status', 'view', 'delete'])
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

    public function accept($id)
    {
        $contact = $this->contactrepository->find($id);

        if (!$contact) {
            abort(404);
        }
        if ($contact->status == 0) {
            $this->contactrepository->update($id, [
                'status' => 1,
            ]);
        }

        return back()->with('msg', 'Tiếp nhận liên hệ thành công');
    }


    public function delete($id)
    {
        $pageTitle = 'Chi tiết liên hệ thông tin';
        $pageName = 'Liên hệ';
        $contact = $this->contactrepository->find($id);
        if (!$contact) {
            abort(404);
        }
        $this->contactrepository->delete($id);
        return view('contacts::index', compact('contact'));
    }
}
