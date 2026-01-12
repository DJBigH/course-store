<?php

namespace Modules\Auth\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Auth\src\Http\Requests\LoginRequest;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:students', ['except' => 'logout']);
        // $this->middleware('auth')->only('logout');
    }

    public function showLoginForm()
    {
        $pageTitle = "Đăng nhập tài khoản";
        return view('auth::clients.login', compact('pageTitle'));
    }

    public function login(LoginRequest $request)
    {
        $dataLogin = [
            'email' => $request->email,
            'password' => $request->password,
        ];
        $status = Auth::guard('students')->attempt($dataLogin,$request->remember == 1 ? true : false);

        if ($status) {
            return redirect('/');
        } else {
            return back()->with('msg_danger', __('auth::messages.login.failure'));
        }
    }

    public function logout(){
        Auth::guard('students')->logout();
        return redirect()->route('home');
    }
}
