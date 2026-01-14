<?php

namespace Modules\Students\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\Students\src\Http\Requests\studentRequest;
use Modules\Students\src\Repositories\StudentsRepositoryInterface;

class AccountController extends Controller
{

    protected $studentRepository;

    public function __construct(StudentsRepositoryInterface $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }
    public function index()
    {
       $pageTitle = 'Thông tin tài khoản';
       $pageName = 'Thông tin tài khoản';
        return view('students::clients.account', compact('pageTitle','pageName'));
    }

    public function profile(){
        $pageTitle = 'Thông tin cá nhân';
       $pageName = 'Thông tin cá nhân';
        return view('students::clients.profile', compact('pageTitle','pageName'));
    }

    public function myCourse(){
        $pageTitle = 'Khóa học của tôi';
       $pageName = 'Khóa học của tôi';
        return view('students::clients.my_courses', compact('pageTitle','pageName'));
    }

    public function myOrder(){
        $pageTitle = 'Đơn hàng';
       $pageName = 'Đơn hàng';
        return view('students::clients.my_order', compact('pageTitle','pageName'));
    }

    public function changePassword(){
        $pageTitle = 'Đổi mật khẩu';
       $pageName = 'Đổi mật khẩu';
        return view('students::clients.change_password', compact('pageTitle','pageName'));
    }
}
