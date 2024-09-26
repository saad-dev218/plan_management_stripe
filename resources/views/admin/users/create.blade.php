@extends('layouts.app')
@section('title', 'Manage Users')

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
                        <input type="text" id="name" placeholder="Enter Full Name" class="form-control"
                            name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-2">
                        <label for="email">Email</label>
                        <input type="email" id="email" placeholder="Enter Email Address" class="form-control"
                            name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-2">
                        <label for="password">Password</label>
                        <input type="password" id="password" placeholder="Enter Password" class="form-control"
                            name="password" required>
                        @error('password')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-2">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" placeholder="Confirm Password"
                            class="form-control" name="password_confirmation" required>
                        @error('password_confirmation')
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
    <script>
        $(document).ready(function() {
            $("#manage-user-form").validate();
        });
    </script>
@endpush
