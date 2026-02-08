<?php

namespace Modules\Auth\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\RegisterNotification;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\Auth\src\Http\Requests\RegisterRequest;
use Modules\Students\src\Repositories\StudentsRepositoryInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Modules\User\src\Models\User as ModelsUser;

class RegisterController extends Controller
{
    protected $studentRepository;

    public function __construct(StudentsRepositoryInterface $studentRepository)
    {
        $this->studentRepository = $studentRepository;
        $this->middleware('guest:students');
    }

    public function showRegistrationForm()
    {
        $pageTitle = "Đăng ký tài khoản";
        return view('auth::clients.register', compact('pageTitle'));
    }

    public function register(RegisterRequest $request)
    {
        $dataInsert = [
            'name' => $request->name,
            'email' => $request->email,
            'status' => 1,
            'password' => bcrypt($request->password),
            'address' => null,
            'phone' => $request->phone
        ];
        $user = $this->studentRepository->create($dataInsert);
        if (!$user) {
            return back()->with('msg_danger', __('auth::messages.register.failure'));
        }
        event(new Registered($user));
        Auth::guard('students')->login($user);
        $admins = ModelsUser::where('group_id', 1)->get();

        foreach ($admins as $admin) {
            $admin->notify(new RegisterNotification($user));
        }
        return redirect()->route('verification.notice');
    }
}
