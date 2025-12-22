<?php

namespace Modules\DashBoard\src\Http\Controllers;

use App\Http\Controllers\Controller;

class DashboardController extends Controller{
    public function index(){
        $pageTitle = 'Trang tổng quan';
        return view('dashboard::dashboard', compact('pageTitle'));
    }
}