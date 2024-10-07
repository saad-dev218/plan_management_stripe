<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('userPlan.plan')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $plans = Plan::all();
        return view('admin.users.create', compact('plans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            UserPlan::create([
                'user_id' => $user->id,
                'plan_id' => $request->plan_id,
                'payment_status' => $request->payment_status,
            ]);

            return redirect()->route('users.index')->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $plans = Plan::all();
        $userPlan = $user->userPlan;
        return view('admin.users.edit', compact('user', 'plans', 'userPlan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $validatedData = $request->validated();
            // Handle password update
            if ($request->filled('password')) {
                $validatedData['password'] = bcrypt($request->input('password'));
            } else {
                unset($validatedData['password']);
            }
            // Update user data
            $user->update($validatedData);
            return redirect()->route('users.index')->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
