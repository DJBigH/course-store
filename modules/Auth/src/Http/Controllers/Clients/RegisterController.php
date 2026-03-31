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

    public function showRegistrationForm($locale)
    {
        $pageTitle = __('auth::clients/auth.register.page_title');
        return view('auth::clients.register', compact('pageTitle'));
    }

    public function register(RegisterRequest $request,$locale)
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
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('auth::messages.register.failure'),
                ], 422);
            }

            return back()->with('msg_danger', __('auth::messages.register.failure'));
        }
        event(new Registered($user));
        Auth::guard('students')->login($user);
        $admins = ModelsUser::query()->inGroup('super_admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new RegisterNotification($user));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('auth::clients/auth.register.success_title'),
                'redirect' => route('verification.notice', ['locale' => app()->getLocale()]),
            ]);
        }

        return redirect()->route('verification.notice', ['locale' => app()->getLocale()]);
    }
}
