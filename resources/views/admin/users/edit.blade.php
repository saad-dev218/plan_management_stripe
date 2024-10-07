@extends('layouts.app')
@section('title', 'Edit User')

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-content-center">
                <h3>Edit User</h3>
                <a href="{{ route('users.index') }}" class="btn btn-primary float-end">Manage Users</a>
            </div>
            <form action="{{ route('users.update', $user->id) }}" method="POST" id="edit-user-form">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" placeholder="Enter Full Name" class="form-control"
                            name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-2">
                        <label for="email">Email</label>
                        <input type="email" id="email" placeholder="Enter Email Address" class="form-control"
                            name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-2">
                        <label for="password">Password (leave blank to keep current)</label>
                        <input type="password" id="password" placeholder="Enter New Password" class="form-control"
                            name="password">
                        @error('password')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-2">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" placeholder="Confirm New Password"
                            class="form-control" name="password_confirmation">
                        @error('password_confirmation')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-2">
                        <label for="plan">Select Plan</label>
                        <select id="plan" class="form-control select2" name="plan_id" data-placeholder="Select a Plan"
                            required>
                            <option value=""></option> <!-- Placeholder option -->
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}"
                                    {{ $user->user_plan && $user->user_plan->plan_id == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }}
                                </option>
                            @endforeach

                        </select>
                        @error('plan_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-2">
                        <label for="payment_status">Payment Status</label>
                        <select id="payment_status" class="form-control" name="payment_status" required>
                            <option value="" disabled>Select Payment Status</option>
                            <option value="paid" {{ $user->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="unpaid" {{ $user->payment_status == 'unpaid' ? 'selected' : '' }}>Unpaid
                            </option>
                        </select>
                        @error('payment_status')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success">Update User</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Select a Plan',
                allowClear: true,
            });
        });
    </script>
@endpush
