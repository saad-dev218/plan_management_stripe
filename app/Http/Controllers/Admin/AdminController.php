<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $users_count = User::count();
        $plans_count = Plan::count();

        return view('admin.home', compact('users_count', 'plans_count'));
    }
}
