<?php

namespace Modules\Contacts\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Contacts\src\Http\Requests\ContactRequest;
use Modules\Contacts\src\Repositories\ContactsRepositoryInterface;

class ContactController extends Controller
{
    protected $contactrepository;
    public function __construct(ContactsRepositoryInterface $contactsRepository) {
        $this->contactrepository = $contactsRepository;
    }

    public function index()
    {
        $pageTitle = 'Liên hệ thông tin';
        $pageName = 'Liên hệ';
        $studentData = Auth::guard('students')->user();
        return view('contacts::clients.index', compact('pageName', 'pageTitle', 'studentData'));
    }

    public function store(ContactRequest $request)
    {
        $contacts = $request->except(['_token']);
        $contacts = $this->contactrepository->create($contacts);
        return back()->with('msg','Bạn đã gửi yêu cầu tư vấn thành công');
    }
}
