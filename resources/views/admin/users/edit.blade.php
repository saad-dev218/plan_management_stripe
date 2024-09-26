@extends('layouts.app')
@section('title', 'Edit User')

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
    <script>
        $(document).ready(function() {
            $("#edit-user-form").validate();
        });
    </script>
@endpush
