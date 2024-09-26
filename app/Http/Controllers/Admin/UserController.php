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
use Stripe\Customer;
use Stripe\Stripe;

class UserController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::with('userSubs.plan:id,name')->paginate(10);
            return view('admin.users.index', compact('users'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load users: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $plans = Plan::all();
            return view('admin.users.create', compact('plans'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load plans: ' . $e->getMessage());
        }
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

            // Create Stripe Customer
            $stripeCustomer = Customer::create([
                'email' => $user->email,
                'name' => $user->name,
            ]);

            // Save Stripe customer ID to the user record
            $user->customer_id = $stripeCustomer->id;
            $user->save();

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
        // Not implemented
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        try {
            $plans = Plan::all();
            $userPlan = $user->userPlan;
            return view('admin.users.edit', compact('user', 'plans', 'userPlan'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load user or plans: ' . $e->getMessage());
        }
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
        try {
            $user = User::findOrFail($id);

            // Optional: If the user has a Stripe customer, you can delete it or manage it.
            if ($user->customer_id) {
                $stripeCustomer = Customer::retrieve($user->customer_id);
                $stripeCustomer->delete();
            }

            $user->delete();

            return redirect()->route('users.index')->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}
