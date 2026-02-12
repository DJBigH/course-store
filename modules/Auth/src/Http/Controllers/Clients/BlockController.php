<?php

namespace Modules\Auth\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Auth\src\Http\Requests\LoginRequest;

class BlockController extends Controller
{
    
    public function index(Request $request){
        $user = $request->user();
        if($user->status){
            return redirect()->route('home');
        }
        $pageTitle = __('auth::clients/auth.block.page_title');
        return view('auth::clients.block',compact('pageTitle'));
    }
}
