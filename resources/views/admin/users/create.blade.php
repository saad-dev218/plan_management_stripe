@extends('layouts.app')
@section('title', 'Manage Users')

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>Create User</h3>
                <a href="{{ route('users.index') }}" class="btn btn-primary float-end">View All Users</a>
            </div>
            <form action="{{ route('users.store') }}" method="POST" id="manage-user-form">
                <div class="card-body">
                    @csrf
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" placeholder="Enter Full Name" class="form-control" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-2">
                        <label for="email">Email</label>
                        <input type="email" id="email" placeholder="Enter Email Address" class="form-control" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-2">
                        <label for="password">Password</label>
                        <input type="password" id="password" placeholder="Enter Password" class="form-control" name="password" required>
                        @error('password')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-2">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" placeholder="Confirm Password" class="form-control" name="password_confirmation" required>
                        @error('password_confirmation')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-2">
                        <label for="plan">Select Plan</label>
                        <select id="plan" class="form-control select2" name="plan_id" data-placeholder="Select a Plan" required>
                            <option value=""></option> <!-- Placeholder option -->
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                        @error('plan_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-2">
                        <label for="payment_status">Payment Status</label>
                        <select id="payment_status" class="form-control" name="payment_status" required>
                            <option value="" disabled selected>Select Payment Status</option>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                        </select>
                        @error('payment_status')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success">Create User</button>
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
